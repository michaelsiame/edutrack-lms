# Live Lessons Feature - User Guide

## Overview

The Live Lessons feature enables real-time, interactive online classes using Jitsi Meet video conferencing. Instructors can schedule live sessions for any lesson, and students can join these sessions to learn in real-time.

## Features

- **Easy Scheduling**: Instructors can schedule live sessions with just a few clicks
- **Jitsi Meet Integration**: Free, open-source video conferencing platform
- **Automatic Notifications**: Students receive notifications when sessions are scheduled
- **Attendance Tracking**: Automatic tracking of who joined and how long they stayed
- **Flexible Access**: Join from web browsers on desktop or mobile devices
- **Recording Support**: Option to enable/disable session recording
- **Buffer Time**: Sessions can be joined before and after scheduled time

## For Instructors

### Scheduling a Live Session

1. **Navigate to Live Sessions**
   - Go to your instructor dashboard
   - Click on "Live Sessions" in the navigation menu

2. **Create New Session**
   - Click the "Schedule New Live Session" button
   - Fill in the required information:
     - **Course**: Select the course for this session
     - **Lesson**: Select the specific lesson
     - **Date**: Choose the session date
     - **Time**: Set the start time
     - **Duration**: Select duration (30 min to 3 hours)
     - **Description**: Add details about what will be covered
     - **Max Participants**: Optional limit on attendees
     - **Options**: Enable/disable recording, chat, screen sharing

3. **Confirm and Schedule**
   - Click "Schedule Session"
   - Students enrolled in the course will be notified automatically

### Managing Live Sessions

**View Sessions**
- All scheduled, live, and past sessions appear on the Live Sessions page
- Sessions are color-coded by status:
  - 🟢 **Live**: Currently active
  - 🔵 **Scheduled**: Upcoming session
  - ⚪ **Ended**: Completed session
  - 🔴 **Cancelled**: Cancelled session

**Join a Session**
- Click "Start/Join" button on any scheduled or live session
- You'll join as a moderator with additional controls

**Cancel a Session**
- Click the "Cancel" button on a scheduled session
- Students will be notified automatically

**View Details**
- Click "Details" to see session information
- View attendance records
- Copy meeting link to share manually

### During a Live Session

As an instructor/moderator, you have access to:
- **Screen Sharing**: Share your screen for presentations
- **Recording**: Start/stop session recording
- **Chat**: Text chat with participants
- **Participant Management**: Mute participants if needed
- **Settings**: Adjust audio/video quality

### After a Session

- View attendance report to see who joined
- Check duration each participant was in the session
- Add recording URL if you saved the session

## For Students

### Viewing Live Sessions

**In Course Page**
- Live sessions appear in the "Upcoming Live Sessions" section
- Shows date, time, and lesson title
- 🔴 **LIVE** indicator for sessions in progress

**In Lesson View**
- When viewing a "Live Session" lesson type
- See full session details including:
  - Date and time
  - Duration
  - Instructor name
  - Session description

### Joining a Live Session

1. **Before Session Time**
   - You'll see a message that the session will be available 15 minutes before start time
   - Sessions can typically be joined 15 minutes early

2. **During Session**
   - Click the "JOIN LIVE SESSION NOW" button
   - Grant camera/microphone permissions when prompted
   - Enter your display name if prompted
   - Click "Join Meeting"

3. **After Session**
   - If a recording is available, you'll see a "WATCH RECORDING" button
   - Recordings remain available for review

### During a Live Session

**Controls Available:**
- 🎤 Microphone on/off
- 📹 Camera on/off
- 💬 Chat
- 🖐️ Raise hand
- ⚙️ Settings
- 🔴 Leave session

**Best Practices:**
- Join with a stable internet connection
- Use headphones to reduce echo
- Mute when not speaking
- Use "Raise Hand" to ask questions
- Stay engaged and participate

### Technical Requirements

**Internet Connection:**
- Minimum: 1 Mbps download/upload
- Recommended: 3+ Mbps for better quality

**Supported Browsers:**
- Chrome (recommended)
- Firefox
- Safari
- Edge

**Devices:**
- Desktop/Laptop computer
- Tablet
- Smartphone (iOS/Android)

## Installation & Setup

### Database Migration

1. **Run the migration script**:
   ```bash
   php run-migration.php
   ```

   This creates two tables:
   - `live_sessions`: Stores session information
   - `live_session_attendance`: Tracks attendance

### Configuration

1. **Jitsi Settings** (already configured):
   - Default domain: `meet.jit.si` (free public server)
   - Can be changed to self-hosted Jitsi server in `config/app.php`

2. **Optional Environment Variables**:
   ```env
   JITSI_ENABLED=true
   JITSI_DOMAIN=meet.jit.si
   JITSI_ROOM_PREFIX=edutrack
   ```

### Upgrading to Self-Hosted Jitsi (Optional)

For better control, privacy, and customization:

1. **Set up Jitsi Meet server**
   - Follow: https://jitsi.github.io/handbook/docs/devops-guide/devops-guide-quickstart

2. **Update configuration**:
   ```php
   // In config/app.php
   'jitsi' => [
       'domain' => 'meet.yourdomain.com',
       // ... other settings
   ]
   ```

## File Structure

```
/public/
├── instructor/
│   └── live-sessions.php          # Instructor session management
├── api/
│   ├── live-sessions.php          # API endpoints
│   └── lessons.php                # Lesson API (for dropdowns)
├── live-session.php               # Join page with Jitsi embed
└── learn.php                      # Updated with live session display

/src/
├── classes/
│   └── LiveSession.php            # Core class for session management
└── ...

/config/
└── app.php                        # Jitsi configuration added

/database/
└── migrations/
    └── add_live_sessions.sql      # Database schema
```

## API Endpoints

### GET `/api/live-sessions.php`

**Get Session Details**
```
GET /api/live-sessions.php?action=get&session_id=123
```

**List Sessions for Course**
```
GET /api/live-sessions.php?action=list&course_id=456
```

**Get Attendance**
```
GET /api/live-sessions.php?action=attendance&session_id=123
```

### POST `/api/live-sessions.php`

**Create Session**
```json
{
  "action": "create",
  "lesson_id": 123,
  "scheduled_start_time": "2025-11-30 14:00:00",
  "duration_minutes": 60,
  "description": "Introduction to Live Lessons",
  "allow_recording": 1,
  "enable_chat": 1,
  "enable_screen_share": 1
}
```

**Update Session**
```json
{
  "action": "update",
  "session_id": 123,
  "scheduled_start_time": "2025-11-30 15:00:00"
}
```

**Cancel Session**
```json
{
  "action": "cancel",
  "session_id": 123
}
```

## Troubleshooting

### Students Can't Join

**Check:**
- Is the session status "scheduled" or "live"?
- Is it within the buffer time window?
- Are they enrolled in the course?
- Do they have a stable internet connection?

### Video/Audio Not Working

**Solutions:**
- Check browser permissions for camera/microphone
- Try a different browser (Chrome recommended)
- Restart the browser
- Check if other applications are using the camera/mic

### Session Not Showing

**Verify:**
- Database migration ran successfully
- Lesson type is set to "Live Session"
- A session is actually scheduled for that lesson
- Student is logged in and enrolled

### Recording Not Available

**Note:**
- Jitsi's free server (meet.jit.si) doesn't support automatic recording
- Use Dropbox integration in Jitsi or manual recording
- Or upgrade to self-hosted Jitsi with recording enabled

## Security & Privacy

- **Access Control**: Only enrolled students can join
- **Unique Room IDs**: Each session gets a unique meeting room
- **Moderator Role**: Instructors automatically get moderator permissions
- **Time-Limited Access**: Sessions auto-close based on schedule
- **Attendance Logging**: All joins/leaves are tracked

## Best Practices

### For Instructors

1. **Schedule in Advance**: Give students at least 24 hours notice
2. **Test First**: Do a test session before your first live class
3. **Prepare Materials**: Have slides/materials ready to share
4. **Start Early**: Join 5-10 minutes before start time
5. **Record Sessions**: Enable recording for students who can't attend
6. **Follow Up**: Share notes and recordings after the session

### For Students

1. **Join Early**: Join a few minutes before start time
2. **Test Equipment**: Check audio/video before joining
3. **Minimize Distractions**: Find a quiet space
4. **Participate**: Use chat and raise hand features
5. **Take Notes**: Download recordings for review

## Support

For issues or questions:
1. Check this guide first
2. Contact your instructor for session-specific issues
3. Contact system administrator for technical problems
4. Visit Jitsi documentation: https://jitsi.github.io/handbook/

## Credits

- **Jitsi Meet**: Open-source video conferencing platform
- **Integration**: Custom-built for Edutrack LMS
- **Version**: 1.0.0
