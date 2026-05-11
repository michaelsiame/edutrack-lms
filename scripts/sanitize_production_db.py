#!/usr/bin/env python3
"""
Sanitize production_db.sql into test_db.sql
Removes/replaces all real user data while preserving course content & structure.
"""

import re
import sys
import random
import hashlib
from pathlib import Path

def random_name():
    first = ["Test","Demo","Sample","User","Student","Admin","Instructor","Finance","Guest"]
    last = ["One","Two","Three","Four","Five","Six","Seven","Eight","Nine","Ten"]
    return random.choice(first), random.choice(last)

def hash_email(prefix, uid):
    return f"user{uid}@example.com"

def hash_phone(uid):
    return f"+260-000-000-{uid:03d}"

def anonymize_users(lines):
    """Anonymize users table INSERTs."""
    out = []
    in_users = False
    for line in lines:
        if "INSERT INTO `users`" in line:
            in_users = True
            out.append(line)
            continue
        if in_users:
            if line.strip().startswith('('):
                # Parse the VALUES row
                # (id, 'username', 'email', 'google_id', 'password_hash', 'first_name', 'last_name', 'phone', ...)
                m = re.match(r"\((\d+),\s*'([^']*)',\s*'([^']*)',\s*('[^']*'|NULL),\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*'([^']*)',\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*(\d),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*(\d+),\s*('[^']*'|NULL),\s*'([^']*)',\s*'([^']*)'\)(,|;)?", line.strip())
                if m:
                    uid = int(m.group(1))
                    username = f"user{uid}"
                    email = hash_email("user", uid)
                    google_id = m.group(4)
                    pw_hash = "$2y$10$" + "x" * 53  # bcrypt placeholder
                    fn, ln = random_name()
                    first_name = f"{fn}{uid}"
                    last_name = ln
                    phone = hash_phone(uid)
                    avatar = "NULL"
                    status = m.group(10)
                    token = "NULL"
                    expires = "NULL"
                    verified = m.group(13)
                    last_login = "NULL"
                    last_ip = "'127.0.0.1'"
                    failed = m.group(16)
                    locked = "NULL"
                    created = m.group(18)
                    updated = m.group(19)
                    ending = m.group(20) if m.group(20) else ""
                    new_line = f"({uid}, '{username}', '{email}', {google_id}, '{pw_hash}', '{first_name}', '{last_name}', '{phone}', {avatar}, '{status}', {token}, {expires}, {verified}, {last_login}, {last_ip}, {failed}, {locked}, '{created}', '{updated}'){ending}\n"
                    out.append(new_line)
                    continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_users = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_user_profiles(lines):
    """Anonymize user_profiles table INSERTs."""
    out = []
    in_table = False
    for line in lines:
        if "INSERT INTO `user_profiles`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                # (id, user_id, bio, phone, dob, gender, address, city, country, postal, avatar, created, updated, avatar2, province, nrc, education, occupation, linkedin, facebook, twitter)
                m = re.match(r"\((\d+),\s*(\d+),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL)\)(,|;)?", line.strip())
                if m:
                    pid = m.group(1)
                    uid = m.group(2)
                    bio = "NULL"
                    phone = f"'+260-000-000-{int(uid):03d}'"
                    dob = "NULL"
                    gender = "NULL"
                    address = "NULL"
                    city = "NULL"
                    country = "NULL"
                    postal = "NULL"
                    avatar = "NULL"
                    created = m.group(12)
                    updated = m.group(13)
                    ending = m.group(22) if m.group(22) else ""
                    new_line = f"({pid}, {uid}, {bio}, {phone}, {dob}, {gender}, {address}, {city}, {country}, {postal}, {avatar}, {created}, {updated}, {avatar}, {avatar}, {avatar}, {avatar}, {avatar}, {avatar}, {avatar}, {avatar}){ending}\n"
                    out.append(new_line)
                    continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_contacts(lines):
    """Anonymize contacts table - replace with generic test messages."""
    out = []
    in_table = False
    idx = 1
    for line in lines:
        if "INSERT INTO `contacts`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                m = re.match(r"\((\d+),\s*'([^']*)',\s*'([^']*)',\s*('[^']*'|NULL),\s*'([^']*)',\s*'([^']*)',\s*(\d),\s*'([^']*)'\)(,|;)?", line.strip())
                if m:
                    cid = m.group(1)
                    name = f"Test Contact {idx}"
                    email = f"contact{idx}@example.com"
                    phone = "NULL"
                    subject = "General Inquiry"
                    message = "This is a test message for development purposes."
                    is_read = m.group(7)
                    created = m.group(8)
                    ending = m.group(9) if m.group(9) else ""
                    new_line = f"({cid}, '{name}', '{email}', {phone}, '{subject}', '{message}', {is_read}, '{created}'){ending}\n"
                    out.append(new_line)
                    idx += 1
                    continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_payments(lines):
    """Anonymize payments table - keep amounts but replace phone/transaction IDs."""
    out = []
    in_table = False
    idx = 1
    for line in lines:
        if "INSERT INTO `payments`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                m = re.match(r"\((\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*('[^']*'|NULL),\s*([\d\.]+),\s*'([^']*)',\s*(\d+),\s*'([^']*)',\s*('[^']*'|NULL),\s*'([^']*)',\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL),\s*('[^']*'|NULL)\)(,|;)?", line.strip())
                if m:
                    parts = list(m.groups())
                    parts[11] = f"'TXN-TEST-{idx:06d}'"  # transaction_id
                    parts[12] = "NULL"  # phone_number
                    parts[13] = "'Test payment'"  # notes
                    ending = parts[15] if parts[15] else ""
                    new_line = f"({', '.join(parts[:15])}){ending}\n"
                    out.append(new_line)
                    idx += 1
                    continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_activity_logs(lines):
    """Anonymize activity_logs - replace IPs with 127.0.0.1."""
    out = []
    in_table = False
    for line in lines:
        if "INSERT INTO `activity_logs`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                line = re.sub(r"'\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}'", "'127.0.0.1'", line)
                line = re.sub(r"Viewed lesson: ([^']*)", lambda m: f"Viewed lesson: [REDACTED]", line)
                line = re.sub(r"Completed lesson: ([^']*)", lambda m: f"Completed lesson: [REDACTED]", line)
                out.append(line)
                continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_email_queue(lines):
    """Anonymize email_queue - replace emails with example.com."""
    out = []
    in_table = False
    for line in lines:
        if "INSERT INTO `email_queue`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                line = re.sub(r"'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}'", "'test@example.com'", line)
                out.append(line)
                continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_messages(lines):
    """Anonymize messages table."""
    out = []
    in_table = False
    idx = 1
    for line in lines:
        if "INSERT INTO `messages`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                m = re.match(r"\((\d+),\s*(\d+),\s*(\d+),\s*'([^']*)',\s*('[^']*'|NULL),\s*(\d),\s*'([^']*)',\s*('[^']*'|NULL)\)(,|;)?", line.strip())
                if m:
                    mid = m.group(1)
                    sender = m.group(2)
                    receiver = m.group(3)
                    subject = f"Test Message {idx}"
                    body = "This is a test message content for development."
                    is_read = m.group(6)
                    created = m.group(7)
                    read_at = m.group(8)
                    ending = m.group(9) if m.group(9) else ""
                    new_line = f"({mid}, {sender}, {receiver}, '{subject}', '{body}', {is_read}, '{created}', {read_at}){ending}\n"
                    out.append(new_line)
                    idx += 1
                    continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_students(lines):
    """Anonymize students table."""
    out = []
    in_table = False
    for line in lines:
        if "INSERT INTO `students`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                line = re.sub(r"'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}'", "'student@example.com'", line)
                line = re.sub(r"'\+?\d[\d\s\-\(\)]{7,20}'", "'+260-000-000-000'", line)
                out.append(line)
                continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_registration_fees(lines):
    """Anonymize registration_fees phone numbers."""
    out = []
    in_table = False
    for line in lines:
        if "INSERT INTO `registration_fees`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                line = re.sub(r"'\+?\d[\d\s\-\(\)]{7,20}'", "'+260-000-000-000'", line)
                out.append(line)
                continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_user_sessions(lines):
    """Anonymize user_sessions IPs."""
    out = []
    in_table = False
    for line in lines:
        if "INSERT INTO `user_sessions`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                line = re.sub(r"'\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}'", "'127.0.0.1'", line)
                out.append(line)
                continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_lenco_webhook_logs(lines):
    """Anonymize lenco_webhook_logs - clear payload data."""
    out = []
    in_table = False
    for line in lines:
        if "INSERT INTO `lenco_webhook_logs`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                line = re.sub(r"'\{[^}]*\}'", "'{}'", line)
                out.append(line)
                continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_transactions(lines):
    """Anonymize transactions reference numbers."""
    out = []
    in_table = False
    idx = 1
    for line in lines:
        if "INSERT INTO `transactions`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                line = re.sub(r"'REF-[A-Z0-9]+'", lambda m: f"'REF-TEST-{idx:06d}'", line)
                line = re.sub(r"'TXN-[A-Z0-9]+'", lambda m: f"'TXN-TEST-{idx:06d}'", line)
                idx += 1
                out.append(line)
                continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_discussions(lines):
    """Anonymize discussions - redact content."""
    out = []
    in_table = False
    idx = 1
    for line in lines:
        if "INSERT INTO `discussions`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                m = re.match(r"\((\d+),\s*(\d+),\s*(\d+),\s*'([^']*)',\s*'([^']*)',\s*(\d),\s*(\d),\s*(\d+),\s*(\d+),\s*'([^']*)',\s*('[^']*'|NULL)\)(,|;)?", line.strip())
                if m:
                    did = m.group(1)
                    course = m.group(2)
                    user = m.group(3)
                    title = f"Discussion Topic {idx}"
                    content = "This is a test discussion topic for development."
                    pinned = m.group(6)
                    locked = m.group(7)
                    views = m.group(8)
                    replies = m.group(9)
                    created = m.group(10)
                    updated = m.group(11)
                    ending = m.group(12) if m.group(12) else ""
                    new_line = f"({did}, {course}, {user}, '{title}', '{content}', {pinned}, {locked}, {views}, {replies}, '{created}', {updated}){ending}\n"
                    out.append(new_line)
                    idx += 1
                    continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def anonymize_discussion_replies(lines):
    """Anonymize discussion_replies - redact content."""
    out = []
    in_table = False
    idx = 1
    for line in lines:
        if "INSERT INTO `discussion_replies`" in line:
            in_table = True
            out.append(line)
            continue
        if in_table:
            if line.strip().startswith('('):
                m = re.match(r"\((\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*'([^']*)',\s*(\d),\s*(\d),\s*'([^']*)',\s*('[^']*'|NULL)\)(,|;)?", line.strip())
                if m:
                    rid = m.group(1)
                    disc = m.group(2)
                    parent = m.group(3)
                    user = m.group(4)
                    content = "This is a test reply for development."
                    is_inst = m.group(6)
                    best = m.group(7)
                    created = m.group(8)
                    updated = m.group(9)
                    ending = m.group(10) if m.group(10) else ""
                    new_line = f"({rid}, {disc}, {parent}, {user}, '{content}', {is_inst}, {best}, '{created}', {updated}){ending}\n"
                    out.append(new_line)
                    idx += 1
                    continue
            if line.strip().endswith(';') or not line.strip().endswith(','):
                in_table = False
            out.append(line)
        else:
            out.append(line)
    return out

def main():
    base = Path(__file__).parent.parent
    src = base / "laravel" / "database" / "production_db.sql"
    dst = base / "laravel" / "database" / "test_db.sql"

    if not src.exists():
        print(f"Error: {src} not found")
        sys.exit(1)

    print(f"Reading {src} ...")
    with open(src, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    print(f"Processing {len(lines)} lines...")

    # Update header
    header = f"""-- SANITIZED TEST DATABASE DUMP
-- Generated from production_db.sql for development/testing ONLY
-- ALL real user data has been anonymized
-- Course content and structure preserved
--
-- Host: 127.0.0.1
-- Generation Time: {__import__('datetime').datetime.now().strftime('%b %d, %Y at %I:%M %p')}
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edutrack_test`
--

"""

    # Find where the actual content starts (after the original header)
    content_start = 0
    for i, line in enumerate(lines):
        if "CREATE TABLE `" in line:
            content_start = i
            break

    body = lines[content_start:]

    # Apply sanitizers in sequence
    body = anonymize_users(body)
    body = anonymize_user_profiles(body)
    body = anonymize_contacts(body)
    body = anonymize_payments(body)
    body = anonymize_activity_logs(body)
    body = anonymize_email_queue(body)
    body = anonymize_messages(body)
    body = anonymize_students(body)
    body = anonymize_registration_fees(body)
    body = anonymize_user_sessions(body)
    body = anonymize_lenco_webhook_logs(body)
    body = anonymize_transactions(body)
    body = anonymize_discussions(body)
    body = anonymize_discussion_replies(body)

    # Remove Google credentials from any config-like data
    body = [re.sub(r"'[0-9a-zA-Z_-]{20,}\.apps\.googleusercontent\.com'", "'REDACTED.apps.googleusercontent.com'", line) for line in body]
    body = [re.sub(r"'[0-9a-zA-Z_-]{20,}'", lambda m: "'REDACTED'" if len(m.group(0)) > 30 else m.group(0), line) for line in body]

    print(f"Writing sanitized dump to {dst} ...")
    with open(dst, 'w', encoding='utf-8') as f:
        f.write(header)
        f.writelines(body)

    # Verify
    src_size = src.stat().st_size
    dst_size = dst.stat().st_size
    print(f"Done!")
    print(f"  Source: {src_size:,} bytes")
    print(f"  Output: {dst_size:,} bytes")
    print(f"  Tables preserved: courses, lessons, categories, quizzes, assignments, events, etc.")
    print(f"  Tables sanitized: users, user_profiles, contacts, payments, activity_logs, messages, etc.")

if __name__ == "__main__":
    main()
