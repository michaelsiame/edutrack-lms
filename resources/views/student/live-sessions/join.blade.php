@extends('layouts.dashboard')

@section('title','Live Session — ' . ($session->meeting_room_id))
@section('page_title','Live Session')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
<style>
    #jitsi-container {
        width: 100%;
        height: calc(100vh - 64px);
        min-height: 500px;
        background: #1a1a1a;
        border-radius: 12px;
        overflow: hidden;
    }
    @media (max-width: 768px) {
        #jitsi-container {
            height: calc(100vh - 56px);
            border-radius: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="flex flex-col h-full -m-4 md:-m-6 lg:-m-8">

    <!-- Session Header -->
    <div class="px-4 py-3 md:px-6 md:py-4 flex items-center justify-between flex-wrap gap-3" style="background: var(--od-surface); border-bottom: 1px solid var(--od-border);">
        <div class="flex items-center gap-3">
            <a href="{{ route('student.live-sessions.index', $course) }}" class="od-btn od-btn-sm" style="background: var(--od-fg-soft); color: var(--od-fg);">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-sm font-semibold" style="color: var(--od-fg);">{{ $session->description ?: 'Live Session' }}</h1>
                <p class="text-xs" style="color: var(--od-muted);">
                    Room: <span class="font-mono">{{ $session->meeting_room_id }}</span>
                    @if($attendance?->is_moderator)
                        <span class="ml-2 px-1.5 py-0.5 rounded text-xs font-medium" style="background: var(--od-navy-soft); color: var(--od-navy);">Moderator</span>
                    @endif
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('student.live-sessions.leave', $session) }}" id="leave-form" class="m-0">
            @csrf
            <button type="submit" class="od-btn od-btn-danger od-btn-sm" onclick="leaveSession(event)">
                <i class="fas fa-phone-slash mr-1.5"></i> Leave Session
            </button>
        </form>
    </div>

    @if($attendance?->is_moderator)
    <!-- Instructor notice -->
    <div class="mx-4 mt-3 md:mx-6 md:mt-4 px-4 py-2.5 rounded-lg text-xs font-medium" style="background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
        <i class="fas fa-info-circle mr-1.5"></i>
        You are the moderator. Click <strong>"Log-in"</strong> inside Jitsi to start the room, then students can join.
    </div>
    @else
    <!-- Student notice -->
    <div class="mx-4 mt-3 md:mx-6 md:mt-4 px-4 py-2.5 rounded-lg text-xs font-medium" style="background: #e0f2fe; color: #075985; border: 1px solid #7dd3fc;">
        <i class="fas fa-clock mr-1.5"></i>
        Waiting for the instructor to start the session. Please keep this page open.
    </div>
    @endif

    <!-- Jitsi Container -->
    <div class="flex-1 p-0 md:p-4 lg:p-6">
        <div id="jitsi-container"></div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://{{ config('edutrack.jitsi_domain') }}/external_api.js"></script>
<script>
    let api = null;
    let hasLeft = false;

    function initJitsi() {
        const domain = '{{ config('edutrack.jitsi_domain') }}';
        const options = {
            roomName: '{{ $session->meeting_room_id }}',
            parentNode: document.querySelector('#jitsi-container'),
            userInfo: {
                displayName: '{{ auth()->user()->full_name }}',
            },
            configOverwrite: {
                startWithAudioMuted: true,
                startWithVideoMuted: true,
                prejoinPageEnabled: false,
                disableDeepLinking: true,
                requireDisplayName: false,
                enableWelcomePage: false,
                enableClosePage: false,
                enableLobby: false,
                defaultLanguage: 'en',
                toolbarButtons: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'chat', 'recording',
                    'livestreaming', 'etherpad', 'sharedvideo', 'settings',
                    'raisehand', 'videoquality', 'filmstrip', 'feedback',
                    'stats', 'shortcuts', 'tileview', 'select-background', 'download',
                    'help', 'mute-everyone', 'mute-video-everyone', 'security'
                ],
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                DEFAULT_BACKGROUND: '#1a1a1a',
                DISABLE_JOIN_LEAVE_NOTIFICATIONS: false,
            },
        };

        api = new JitsiMeetExternalAPI(domain, options);

        // Handle ready to close (user clicked hangup inside Jitsi)
        api.on('readyToClose', function () {
            leaveSession();
        });
    }

    function leaveSession(e) {
        if (e) e.preventDefault();
        if (hasLeft) return;
        hasLeft = true;

        // Dispose Jitsi iframe
        if (api) {
            api.dispose();
        }

        // Submit the leave form
        document.getElementById('leave-form').submit();
    }

    // Warn user if they try to close the tab without leaving properly
    window.addEventListener('beforeunload', function (e) {
        if (!hasLeft) {
            // Send beacon to record leave even if tab is closed
            const form = document.getElementById('leave-form');
            const formData = new FormData(form);
            navigator.sendBeacon(form.action, formData);

            // Show browser warning
            e.preventDefault();
            e.returnValue = 'You are still in the live session. Leave first to record your attendance.';
            return e.returnValue;
        }
    });

    // Start Jitsi when DOM is ready
    initJitsi();
</script>
@endpush
