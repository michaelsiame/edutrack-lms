<?php
/**
 * Live Session Join Page
 * Jitsi Meet Integration for Live Lessons
 */

require_once '../src/bootstrap.php';

// Require authentication
if (!isset($_SESSION['user_id'])) {
    redirect(url('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'])));
    exit;
}

$user = User::current();
if (!$user) {
    redirect(url('login.php'));
    exit;
}

$sessionId = $_GET['session_id'] ?? null;
if (!$sessionId) {
    die('Session ID is required');
}

// Load session
$session = LiveSession::find($sessionId);
if (!$session) {
    die('Session not found');
}

// Update session status based on current time
$session->updateStatus();

// Check if user can join
if (!$session->canJoin($user->getId())) {
    die('You are not enrolled in this course or do not have permission to join this session');
}

// Check if session is available to join
if (!$session->isLive() && $session->status !== 'live') {
    $startTime = new DateTime($session->scheduled_start_time);
    $bufferBefore = (int)$session->buffer_minutes_before;
    $startTime->modify("-{$bufferBefore} minutes");

    die('This session is not available yet. It will be available from ' . $startTime->format('M d, Y g:i A'));
}

// Record attendance
$isModerator = ($session->instructor_id == $user->getId());
$attendanceId = $session->recordAttendance($user->getId(), $isModerator);

// Store attendance ID in session for exit tracking
$_SESSION['live_session_attendance_id'] = $attendanceId;

// Load Jitsi configuration
$config = require __DIR__ . '/../config/app.php';
$jitsiConfig = $config['jitsi'];

// Build Jitsi config options
$jitsiOptions = [
    'roomName' => $session->meeting_room_id,
    'width' => '100%',
    'height' => '100%',
    'parentNode' => 'jitsi-container',
    'configOverwrite' => array_merge($jitsiConfig['options'], [
        'prejoinPageEnabled' => !$isModerator, // Instructors skip prejoin
        'startWithAudioMuted' => !$isModerator && ($session->max_participants > 50),
    ]),
    'interfaceConfigOverwrite' => $jitsiConfig['interface'],
    'userInfo' => [
        'displayName' => $user->getName(),
        'email' => $user->getEmail(),
    ],
];

$sessionData = $session->toArray();
$userName = htmlspecialchars($user->getName());
$userRole = $isModerator ? 'Instructor' : 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sessionData['lesson_title']); ?> - Live Session</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #111827;
            color: white;
            overflow: hidden;
        }

        .header {
            background: #1f2937;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .session-info h1 {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
            color: #f3f4f6;
        }

        .session-info p {
            font-size: 0.875rem;
            color: #9ca3af;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 500;
            color: #f3f4f6;
        }

        .user-role {
            font-size: 0.875rem;
            color: #9ca3af;
        }

        .role-moderator {
            color: #10b981;
        }

        #jitsi-container {
            position: absolute;
            top: 80px;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
        }

        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .spinner {
            border: 4px solid #374151;
            border-top: 4px solid #2E70DA;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .exit-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }

        .exit-btn:hover {
            background: #dc2626;
        }

        .error-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #1f2937;
            padding: 2rem;
            border-radius: 0.5rem;
            text-align: center;
            max-width: 500px;
        }

        .error-message h2 {
            color: #ef4444;
            margin-bottom: 1rem;
        }

        .error-message p {
            color: #d1d5db;
            margin-bottom: 1.5rem;
        }

        .btn-back {
            background: #2E70DA;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back:hover {
            background: #1E4A8A;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="session-info">
            <h1><?php echo htmlspecialchars($sessionData['lesson_title']); ?></h1>
            <p><?php echo htmlspecialchars($sessionData['course_title']); ?></p>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo $userName; ?></div>
            <div class="user-role <?php echo $isModerator ? 'role-moderator' : ''; ?>">
                <?php echo $userRole; ?>
            </div>
        </div>
    </div>

    <div id="jitsi-container">
        <div class="loading">
            <div class="spinner"></div>
            <p>Loading live session...</p>
        </div>
    </div>

    <!-- Jitsi Meet External API -->
    <script src="https://<?php echo $jitsiConfig['domain']; ?>/external_api.js"></script>

    <script>
        const domain = '<?php echo $jitsiConfig['domain']; ?>';
        const options = <?php echo json_encode($jitsiOptions, JSON_PRETTY_PRINT); ?>;

        let api;

        try {
            // Initialize Jitsi Meet
            api = new JitsiMeetExternalAPI(domain, options);

            // Event listeners
            api.addEventListener('videoConferenceJoined', (event) => {
                console.log('Joined conference:', event);
                document.querySelector('.loading')?.remove();
            });

            api.addEventListener('videoConferenceLeft', () => {
                console.log('Left conference');
                handleExit();
            });

            api.addEventListener('readyToClose', () => {
                console.log('Ready to close');
                handleExit();
            });

            api.addEventListener('participantLeft', (event) => {
                console.log('Participant left:', event);
            });

            api.addEventListener('errorOccurred', (event) => {
                console.error('Jitsi error:', event);
                showError('An error occurred with the video conference. Please refresh and try again.');
            });

            // Set user display name
            api.executeCommand('displayName', '<?php echo addslashes($userName); ?>');

            <?php if ($isModerator): ?>
            // Instructor/moderator specific settings
            api.executeCommand('toggleLobby', false); // Disable lobby for easier access
            console.log('Moderator mode enabled');
            <?php endif; ?>

        } catch (error) {
            console.error('Failed to initialize Jitsi:', error);
            showError('Failed to load the video conference. Please check your internet connection and try again.');
        }

        function handleExit() {
            // Update attendance exit time
            fetch('<?php echo url('api/live-sessions.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'exit',
                    attendance_id: <?php echo $attendanceId; ?>
                })
            }).then(() => {
                // Redirect back to appropriate page
                <?php if ($isModerator): ?>
                window.location.href = '<?php echo url('instructor/live-sessions.php'); ?>';
                <?php else: ?>
                window.location.href = '<?php echo url('learn.php?course_id=' . $sessionData['course_id']); ?>';
                <?php endif; ?>
            });
        }

        function showError(message) {
            const container = document.getElementById('jitsi-container');
            container.innerHTML = `
                <div class="error-message">
                    <h2>Unable to Join Session</h2>
                    <p>${message}</p>
                    <a href="javascript:history.back()" class="btn-back">Go Back</a>
                </div>
            `;
        }

        // Handle page unload (track when user closes tab/window)
        window.addEventListener('beforeunload', (event) => {
            if (api) {
                navigator.sendBeacon(
                    '<?php echo url('api/live-sessions.php'); ?>',
                    JSON.stringify({
                        action: 'exit',
                        attendance_id: <?php echo $attendanceId; ?>
                    })
                );
            }
        });

        // Prevent accidental page navigation
        window.addEventListener('beforeunload', (event) => {
            event.preventDefault();
            event.returnValue = '';
        });
    </script>
</body>
</html>
