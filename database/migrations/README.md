# Database Migration for Live Lessons

## Migration File
The migration file `database/migrations/add_live_sessions.sql` is **not tracked in git** because the `.gitignore` file excludes all `*.sql` files (to prevent accidental commits of database backups).

## Migration Location
The migration SQL file exists locally at:
```
/home/user/edutrack-lms/database/migrations/add_live_sessions.sql
```

## How to Run the Migration

### Option 1: Using the Migration Script (Recommended)
```bash
php run-migration.php
```

This script will:
- Read the migration file
- Execute all CREATE TABLE and CREATE INDEX statements
- Verify that tables were created successfully
- Show a summary of results

### Option 2: Manual Execution
If you prefer to run the migration manually via MySQL client:

```bash
mysql -u [username] -p [database_name] < database/migrations/add_live_sessions.sql
```

Or using phpMyAdmin:
1. Open phpMyAdmin
2. Select your database
3. Go to the "Import" tab
4. Upload the `add_live_sessions.sql` file
5. Click "Go"

## What the Migration Creates

### Tables

#### 1. `live_sessions`
Stores all live session information:
- Session scheduling (start/end times, duration)
- Meeting room details (unique room ID for Jitsi)
- Status (scheduled, live, ended, cancelled)
- Settings (recording, chat, screen share)
- Buffer times for joining early/late

**Foreign Keys:**
- `lesson_id` → `lessons.id` (CASCADE DELETE)
- `instructor_id` → `instructors.id` (CASCADE DELETE)

#### 2. `live_session_attendance`
Tracks student and instructor attendance:
- Who joined (user_id)
- When they joined (joined_at)
- When they left (left_at)
- Total duration in seconds
- Whether they were a moderator

**Foreign Keys:**
- `live_session_id` → `live_sessions.id` (CASCADE DELETE)
- `user_id` → `users.id` (CASCADE DELETE)

### Indexes
Performance indexes for common queries:
- `idx_live_session_status_time` - Fast queries by status and time
- `idx_attendance_user_session` - Fast attendance lookups

## Verification

After running the migration, verify tables exist:

```sql
SHOW TABLES LIKE 'live_%';
```

You should see:
- `live_sessions`
- `live_session_attendance`

Check table structure:
```sql
DESCRIBE live_sessions;
DESCRIBE live_session_attendance;
```

## Important Notes

### Database Relationships
The migration creates proper foreign key relationships:
- `instructor_id` references `instructors.id` (NOT `users.id`)
- This matches the existing course structure where instructors have their own table
- User information is accessed by joining: `instructors → users`

### Migration Updates
If you need to share the migration with others:
1. The file exists locally at `database/migrations/add_live_sessions.sql`
2. It can be manually copied/shared
3. Or modify `.gitignore` to allow this specific file:
   ```
   # In .gitignore, before *.sql line:
   !database/migrations/*.sql
   ```

## Troubleshooting

### "Table already exists" Error
This is normal if running the migration multiple times. The SQL uses `CREATE TABLE IF NOT EXISTS`, so it won't fail. Any changes to the schema would need to be handled as separate ALTER TABLE statements.

### Foreign Key Constraint Errors
If you get foreign key errors:
1. Ensure the `lessons` table exists
2. Ensure the `instructors` table exists
3. Ensure the `users` table exists
4. Check that InnoDB engine is being used (required for foreign keys)

### Permission Errors
Ensure your database user has these privileges:
- CREATE
- ALTER
- INDEX
- REFERENCES (for foreign keys)

## Schema Evolution

If you need to modify the schema later:
1. Create a new migration file (e.g., `alter_live_sessions_add_column.sql`)
2. Use ALTER TABLE statements instead of CREATE TABLE
3. Run it the same way using the migration script
