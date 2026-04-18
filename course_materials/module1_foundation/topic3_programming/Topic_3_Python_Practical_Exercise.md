# Topic 3: Python Programming Practical Exercise
## Module 1: Foundations of Computing and Mathematics

---

## Learning Objectives

By completing this practical exercise, you will be able to:
1. Write and execute basic Python commands
2. Work with variables and different data types
3. Use operators for calculations and comparisons
4. Implement control flow with conditionals and loops
5. Create functions to organize code
6. Apply Python basics to simple cybersecurity scenarios

---

## Part 1: Getting Started with Python

### 1.1 Your First Python Program

Create a file called `hello_security.py`:

```python
#!/usr/bin/env python3
# hello_security.py - My first Python program for cybersecurity

# The print() function displays output to the screen
print("Welcome to Python for Cybersecurity!")
print("-" * 40)

# You can print multiple items
print("Course:", "Cybersecurity Certificate")
print("Module:", 1)
print("Topic:", 3)

# Using f-strings (formatted strings) - Python 3.6+
student_name = "Your Name"
print(f"\nHello, {student_name}! Ready to learn Python?")
```

**Exercise 1.1:** Run the program and modify it to print your actual name and today's date.

---

### 1.2 Python as a Calculator

Python can perform mathematical operations - useful for calculating subnet masks, IP ranges, and encryption:

```python
#!/usr/bin/env python3
# calculator.py - Basic calculations for security tasks

# Network security calculations
ip_addresses_per_subnet = 256
subnets_needed = 4
total_ips = ip_addresses_per_subnet * subnets_needed

print("=== Network Calculation ===")
print(f"IP addresses per subnet: {ip_addresses_per_subnet}")
print(f"Subnets needed: {subnets_needed}")
print(f"Total IP addresses: {total_ips}")

# File size calculations (important for storage planning)
file_size_mb = 50
num_files = 120
total_storage_gb = (file_size_mb * num_files) / 1024

print("\n=== Storage Calculation ===")
print(f"Average file size: {file_size_mb} MB")
print(f"Number of log files: {num_files}")
print(f"Total storage needed: {total_storage_gb:.2f} GB")

# Modulo operator - useful for hashing and cryptography
print("\n=== Modulo Examples ===")
print(f"256 % 16 = {256 % 16}")  # Used in IP addressing
print(f"100 % 7 = {100 % 7}")    # Used in checksum calculations

# Exponentiation - used in encryption key calculations
print(f"\n2^8 = {2**8}")         # IPv4 address space per octet
print(f"2^32 = {2**32}")        # Total IPv4 addresses
```

**Exercise 1.2:** Calculate how many days it would take to crack a password if you can test 1 million passwords per second, and the password space is 95^8 (8 characters from 95 printable ASCII characters).

---

## Part 2: Variables and Data Types

### 2.1 Understanding Variables

Variables store data that your program needs to work with:

```python
#!/usr/bin/env python3
# variables_demo.py - Understanding data types in security context

# String - text data (usernames, domain names, hashes)
username = "admin"
email = "security@company.com"
hash_example = "5f4dcc3b5aa765d61d8327deb882cf99"  # MD5 hash example

print("=== String Variables ===")
print(f"Username: {username}")
print(f"Email: {email}")
print(f"Hash: {hash_example}")
print(f"Hash length: {len(hash_example)} characters")

# Integer - whole numbers (port numbers, counts)
port_number = 443
failed_attempts = 5
max_retries = 3

print("\n=== Integer Variables ===")
print(f"HTTPS Port: {port_number}")
print(f"Failed login attempts: {failed_attempts}")
print(f"Max retries allowed: {max_retries}")

# Float - decimal numbers (percentages, time measurements)
cpu_usage = 45.7
network_latency = 23.5
encryption_strength = 256.0

print("\n=== Float Variables ===")
print(f"CPU Usage: {cpu_usage}%")
print(f"Network Latency: {network_latency}ms")
print(f"Encryption Key Size: {encryption_strength} bits")

# Boolean - True/False values (access control, status checks)
is_authenticated = False
is_admin = True
firewall_enabled = True

print("\n=== Boolean Variables ===")
print(f"User authenticated: {is_authenticated}")
print(f"User is admin: {is_admin}")
print(f"Firewall enabled: {firewall_enabled}")
```

### 2.2 Lists and Dictionaries for Security Data

```python
#!/usr/bin/env python3
# data_structures.py - Organizing security data

# List - ordered collection (IP blacklists, port lists)
blocked_ips = ["192.168.1.100", "10.0.0.50", "172.16.0.25"]
common_ports = [22, 80, 443, 3389, 8080]

print("=== Working with Lists ===")
print(f"Blocked IPs: {blocked_ips}")
print(f"Number of blocked IPs: {len(blocked_ips)}")

# Accessing list items by index (starts at 0)
print(f"First blocked IP: {blocked_ips[0]}")
print(f"Last blocked IP: {blocked_ips[-1]}")

# Adding to a list
blocked_ips.append("192.168.1.200")
print(f"Updated blocked IPs: {blocked_ips}")

# Dictionary - key-value pairs (user records, configuration)
user_account = {
    "username": "jdoe",
    "email": "jdoe@company.com",
    "role": "analyst",
    "active": True,
    "last_login": "2026-04-09",
    "failed_attempts": 0
}

print("\n=== Working with Dictionaries ===")
print(f"User: {user_account['username']}")
print(f"Role: {user_account['role']}")

# Adding/updating dictionary entries
user_account["department"] = "Security Operations"
user_account["failed_attempts"] = 2

print(f"Updated user data: {user_account}")

# List of dictionaries - representing multiple security events
security_events = [
    {"timestamp": "10:00:15", "type": "login_failed", "ip": "192.168.1.50"},
    {"timestamp": "10:05:22", "type": "firewall_block", "ip": "10.0.0.25"},
    {"timestamp": "10:12:08", "type": "suspicious_activity", "ip": "172.16.0.10"}
]

print("\n=== Security Events ===")
for event in security_events:
    print(f"[{event['timestamp']}] {event['type']} from {event['ip']}")
```

**Exercise 2.1:** Create a list of 5 common malware file extensions and a dictionary containing information about a security incident (date, type, severity, affected_systems).

---

## Part 3: Operators in Python

### 3.1 Arithmetic, Comparison, and Logical Operators

```python
#!/usr/bin/env python3
# operators_demo.py - Operators for security logic

# ARITHMETIC OPERATORS
print("=== Arithmetic Operators ===")
packets_in = 1500
packets_out = 1420
packet_loss = packets_in - packets_out
loss_percentage = (packet_loss / packets_in) * 100

print(f"Packets sent: {packets_in}")
print(f"Packets received: {packets_out}")
print(f"Packet loss: {packet_loss}")
print(f"Loss percentage: {loss_percentage:.2f}%")

# COMPARISON OPERATORS - Return True or False
print("\n=== Comparison Operators ===")
failed_logins = 5
threshold = 3

print(f"Failed logins ({failed_logins}) > threshold ({threshold}): {failed_logins > threshold}")
print(f"Failed logins ({failed_logins}) == threshold ({threshold}): {failed_logins == threshold}")
print(f"Failed logins ({failed_logins}) <= 5: {failed_logins <= 5}")

# LOGICAL OPERATORS - Combine conditions
print("\n=== Logical Operators ===")
is_business_hours = True
is_weekend = False
has_admin_privileges = False

# AND - Both conditions must be True
should_allow_access = is_business_hours and not is_weekend
print(f"Allow normal access: {should_allow_access}")

# OR - At least one condition must be True
needs_review = failed_logins > 3 or not is_business_hours
print(f"Account needs review: {needs_review}")

# NOT - Inverts the condition
can_delete_logs = has_admin_privileges and is_business_hours
print(f"Can delete logs: {can_delete_logs}")

# Real-world scenario: Account lockout logic
print("\n=== Account Lockout Logic ===")
failed_attempts = 4
last_attempt_time = 2  # minutes ago
account_locked = False

# Lock account if 3+ failed attempts within 5 minutes
if failed_attempts >= 3 and last_attempt_time <= 5:
    account_locked = True
    
print(f"Account locked: {account_locked}")
```

**Exercise 3.1:** Write a Python script that checks if a password meets minimum requirements: at least 8 characters long, contains at least one number, and is not equal to "password123".

---

## Part 4: Control Flow - Conditionals

### 4.1 If, Elif, Else Statements

```python
#!/usr/bin/env python3
# conditionals.py - Making decisions in security scripts

# Basic if statement
alert_severity = "HIGH"

print("=== Alert Severity Check ===")
if alert_severity == "CRITICAL":
    print("🚨 IMMEDIATE ACTION REQUIRED!")
    print("   - Notify security team immediately")
    print("   - Begin incident response procedure")

if alert_severity == "HIGH":
    print("⚠️  HIGH PRIORITY ALERT")
    print("   - Investigate within 1 hour")

# If-else statement
print("\n=== Port Status Check ===")
port = 22
allowed_ports = [80, 443, 8080]

if port in allowed_ports:
    print(f"Port {port}: ALLOWED")
else:
    print(f"Port {port}: BLOCKED - Not in allowed list")

# If-elif-else chain
print("\n=== Risk Level Assessment ===")
cvss_score = 7.5  # Common Vulnerability Scoring System (0-10)

if cvss_score >= 9.0:
    risk_level = "CRITICAL"
    action = "Patch within 24 hours"
elif cvss_score >= 7.0:
    risk_level = "HIGH"
    action = "Patch within 1 week"
elif cvss_score >= 4.0:
    risk_level = "MEDIUM"
    action = "Patch within 1 month"
else:
    risk_level = "LOW"
    action = "Schedule for next maintenance"

print(f"CVSS Score: {cvss_score}")
print(f"Risk Level: {risk_level}")
print(f"Required Action: {action}")

# Nested conditionals
print("\n=== Access Control Decision ===")
user_role = "analyst"
is_authenticated = True
security_clearance = 2
required_clearance = 3

if is_authenticated:
    print("User is authenticated")
    
    if user_role == "admin":
        print("Full system access granted")
    elif user_role == "analyst":
        if security_clearance >= required_clearance:
            print("Access granted to classified data")
        else:
            print("Access denied - Insufficient clearance")
            print(f"Required: Level {required_clearance}, Current: Level {security_clearance}")
    else:
        print("Read-only access granted")
else:
    print("Access denied - Please log in")
```

**Exercise 4.1:** Create a script that categorizes security incidents based on type: "malware" (critical), "unauthorized_access" (high), "policy_violation" (medium), "suspicious_activity" (low). Print appropriate response actions for each.

---

## Part 5: Loops - Repeating Actions

### 5.1 For Loops

```python
#!/usr/bin/env python3
# loops_demo.py - Automating repetitive security tasks

# Loop through a list
print("=== Port Scan Simulation ===")
ports_to_check = [22, 80, 443, 3306, 8080, 8443]

for port in ports_to_check:
    # Simulate checking each port
    status = "OPEN" if port in [80, 443, 8080] else "CLOSED"
    print(f"Port {port}: {status}")

# Loop with range() - useful for IP addresses, iterations
print("\n=== Password Policy Check ===")
passwords = ["pass", "password123", "Secure1!", "admin", "MyStr0ngP@ss"]
min_length = 8

for i, password in enumerate(passwords, 1):
    if len(password) >= min_length:
        strength = "STRONG"
    else:
        strength = "WEAK"
    print(f"Password {i}: {'*' * len(password)} - {strength}")

# Loop through dictionary
print("\n=== System Health Check ===")
system_metrics = {
    "CPU Usage": 45,
    "Memory Usage": 72,
    "Disk Usage": 85,
    "Network Load": 30
}

for metric, value in system_metrics.items():
    if value > 80:
        status = "⚠️  WARNING"
    elif value > 60:
        status = "⚡ ELEVATED"
    else:
        status = "✅ NORMAL"
    print(f"{metric}: {value}% {status}")

# Nested loops - checking IP ranges
print("\n=== Network Segment Check ===")
subnets = ["192.168.1", "192.168.2"]
host_ids = [1, 2, 3, 254]  # .254 is usually gateway

for subnet in subnets:
    print(f"\nScanning subnet {subnet}.0/24:")
    for host in host_ids:
        ip = f"{subnet}.{host}"
        device_type = "Gateway" if host == 254 else f"Host-{host}"
        print(f"  - {ip}: {device_type} responding")
```

### 5.2 While Loops

```python
#!/usr/bin/env python3
# while_loops.py - Event-driven and conditional repetition

# Basic while loop - login attempt simulation
print("=== Login Attempt Simulation ===")
max_attempts = 3
attempts = 0
password_correct = False

while attempts < max_attempts and not password_correct:
    attempts += 1
    print(f"Attempt {attempts} of {max_attempts}")
    
    # Simulate password check (in real scenario, this would be actual verification)
    if attempts == 2:  # Simulate correct password on 2nd try
        password_correct = True
        print("  ✓ Login successful!")
    else:
        print("  ✗ Invalid password")

if not password_correct:
    print("\n⚠️  Account locked - Maximum attempts exceeded")

# While with user input simulation
print("\n=== Security Menu System ===")
choice = ""
menu_options = [
    "1. View Security Logs",
    "2. Check System Status", 
    "3. Run Vulnerability Scan",
    "4. Exit"
]

# Simulate menu loop (normally would use input())
simulated_choices = ["2", "3", "4"]
choice_index = 0

while choice != "4":
    print("\n--- Security Management Menu ---")
    for option in menu_options:
        print(option)
    
    choice = simulated_choices[choice_index]
    choice_index += 1
    print(f"Selection: {choice}")
    
    if choice == "1":
        print("→ Loading security logs...")
    elif choice == "2":
        print("→ System status: All systems operational")
    elif choice == "3":
        print("→ Starting vulnerability scan...")
    elif choice == "4":
        print("→ Exiting system. Goodbye!")
    else:
        print("→ Invalid option")

# While with break - searching for specific event
print("\n=== Event Log Search ===")
events = [
    {"time": "10:01", "type": "info", "message": "System started"},
    {"time": "10:05", "type": "warning", "message": "High memory usage"},
    {"time": "10:12", "type": "error", "message": "Connection failed"},
    {"time": "10:15", "type": "info", "message": "User logged in"},
]

index = 0
found_error = False

while index < len(events):
    event = events[index]
    print(f"Checking event at {event['time']}: {event['type']}")
    
    if event['type'] == "error":
        print(f"  ⚠️  ERROR FOUND: {event['message']}")
        found_error = True
        break  # Exit loop immediately
    
    index += 1

if not found_error:
    print("No errors found in event log")
```

**Exercise 5.1:** Write a script that simulates monitoring a log file. Use a loop to check 10 log entries and count how many are "ERROR", "WARNING", or "INFO" level.

---

## Part 6: Functions - Organizing Your Code

### 6.1 Defining and Using Functions

```python
#!/usr/bin/env python3
# functions_demo.py - Reusable security utilities

# Simple function - no parameters
def show_banner():
    """Display a welcome banner for security tools"""
    print("=" * 50)
    print("  🔒 CYBERSECURITY TOOLKIT v1.0")
    print("  Edutrack Computer Training College")
    print("=" * 50)

show_banner()

# Function with parameters
def calculate_subnet_hosts(subnet_mask):
    """Calculate number of host addresses from subnet mask"""
    # /24 = 256 addresses, /16 = 65536 addresses, etc.
    host_bits = 32 - subnet_mask
    num_hosts = 2 ** host_bits - 2  # Subtract network and broadcast addresses
    return num_hosts

print("\n=== Subnet Calculator ===")
for mask in [24, 16, 8]:
    hosts = calculate_subnet_hosts(mask)
    print(f"/{mask} subnet: {hosts} usable hosts")

# Function with multiple parameters
def check_password_strength(password, min_length=8):
    """
    Check if password meets security requirements
    Returns: (is_strong, feedback_message)
    """
    score = 0
    feedback = []
    
    # Check length
    if len(password) >= min_length:
        score += 1
    else:
        feedback.append(f"Must be at least {min_length} characters")
    
    # Check for numbers
    has_number = any(char.isdigit() for char in password)
    if has_number:
        score += 1
    else:
        feedback.append("Must contain at least one number")
    
    # Check for uppercase
    has_upper = any(char.isupper() for char in password)
    if has_upper:
        score += 1
    else:
        feedback.append("Must contain at least one uppercase letter")
    
    # Check for special characters
    special_chars = "!@#$%^&*()_+-=[]{}|;:,.<>?"
    has_special = any(char in special_chars for char in password)
    if has_special:
        score += 1
    else:
        feedback.append("Must contain at least one special character")
    
    is_strong = score >= 3
    message = "Strong password!" if is_strong else "Weak password: " + ", ".join(feedback)
    
    return is_strong, message

print("\n=== Password Strength Checker ===")
test_passwords = ["abc", "Password1", "MyStr0ng!Pass", "admin123"]

for pwd in test_passwords:
    strong, msg = check_password_strength(pwd)
    status = "✓" if strong else "✗"
    print(f"{status} '{pwd}': {msg}")

# Function with default parameters
def generate_report(title, author="Security System", date=None, severity="MEDIUM"):
    """Generate a standardized security report header"""
    from datetime import datetime
    
    if date is None:
        date = datetime.now().strftime("%Y-%m-%d %H:%M")
    
    report = f"""
{'='*50}
SECURITY INCIDENT REPORT
{'='*50}
Title:    {title}
Severity: {severity}
Author:   {author}
Date:     {date}
{'='*50}
"""
    return report

print(generate_report("Suspicious Login Activity", severity="HIGH"))

# Function returning multiple values
def analyze_ip_address(ip_string):
    """Analyze an IP address and return its properties"""
    octets = ip_string.split(".")
    
    if len(octets) != 4:
        return None, "Invalid IP format"
    
    try:
        octet_values = [int(o) for o in octets]
        if not all(0 <= o <= 255 for o in octet_values):
            return None, "Octets must be 0-255"
    except ValueError:
        return None, "Invalid octet values"
    
    # Determine IP class
    first_octet = octet_values[0]
    if first_octet < 128:
        ip_class = "A"
    elif first_octet < 192:
        ip_class = "B"
    elif first_octet < 224:
        ip_class = "C"
    elif first_octet < 240:
        ip_class = "D (Multicast)"
    else:
        ip_class = "E (Experimental)"
    
    # Check if private
    is_private = (
        (first_octet == 10) or
        (first_octet == 172 and 16 <= octet_values[1] <= 31) or
        (first_octet == 192 and octet_values[1] == 168)
    )
    
    return {
        "ip": ip_string,
        "class": ip_class,
        "is_private": is_private,
        "octets": octet_values
    }, "Analysis complete"

print("\n=== IP Address Analyzer ===")
test_ips = ["192.168.1.1", "8.8.8.8", "10.0.0.1", "256.1.1.1", "invalid"]

for ip in test_ips:
    result, message = analyze_ip_address(ip)
    if result:
        scope = "Private" if result["is_private"] else "Public"
        print(f"✓ {result['ip']}: Class {result['class']}, {scope}")
    else:
        print(f"✗ {ip}: {message}")
```

**Exercise 6.1:** Create a function that takes a list of security events and returns a summary with counts of each event type and the most common source IP address.

---

## Part 7: Practical Cybersecurity Scenarios

### 7.1 Scenario: Log File Analyzer

```python
#!/usr/bin/env python3
# log_analyzer.py - Analyze security logs for suspicious activity

def analyze_auth_log(log_entries):
    """
    Analyze authentication logs for brute force attempts
    
    log_entries format: [{"timestamp": "", "user": "", "ip": "", "status": "success/failed"}]
    """
    print("=" * 60)
    print("AUTHENTICATION LOG ANALYZER")
    print("=" * 60)
    
    # Initialize counters
    stats = {
        "total_attempts": len(log_entries),
        "successful": 0,
        "failed": 0,
        "unique_ips": set(),
        "unique_users": set()
    }
    
    # Track failed attempts per IP
    failed_by_ip = {}
    
    for entry in log_entries:
        ip = entry["ip"]
        user = entry["user"]
        status = entry["status"]
        
        # Update statistics
        stats["unique_ips"].add(ip)
        stats["unique_users"].add(user)
        
        if status == "success":
            stats["successful"] += 1
        else:
            stats["failed"] += 1
            failed_by_ip[ip] = failed_by_ip.get(ip, 0) + 1
    
    # Display summary
    print(f"\n📊 SUMMARY")
    print(f"   Total attempts: {stats['total_attempts']}")
    print(f"   Successful: {stats['successful']}")
    print(f"   Failed: {stats['failed']}")
    print(f"   Success rate: {(stats['successful']/stats['total_attempts']*100):.1f}%")
    print(f"   Unique IPs: {len(stats['unique_ips'])}")
    print(f"   Unique users: {len(stats['unique_users'])}")
    
    # Identify potential brute force attacks (3+ failed from same IP)
    print(f"\n🚨 POTENTIAL BRUTE FORCE ATTACKS")
    brute_force_threshold = 3
    attacks_detected = 0
    
    for ip, failed_count in failed_by_ip.items():
        if failed_count >= brute_force_threshold:
            attacks_detected += 1
            print(f"   IP {ip}: {failed_count} failed attempts")
    
    if attacks_detected == 0:
        print("   No brute force attacks detected")
    else:
        print(f"\n   ⚠️  {attacks_detected} IP(s) exceeded threshold of {brute_force_threshold} failures")
    
    return stats, failed_by_ip


# Sample log data (normally this would come from a file)
sample_logs = [
    {"timestamp": "08:00:01", "user": "admin", "ip": "192.168.1.10", "status": "success"},
    {"timestamp": "08:05:23", "user": "jdoe", "ip": "192.168.1.25", "status": "success"},
    {"timestamp": "08:15:10", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "08:15:15", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "08:15:22", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "08:15:30", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "08:15:35", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "08:30:00", "user": "asmith", "ip": "192.168.1.30", "status": "success"},
    {"timestamp": "09:00:00", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "09:45:12", "user": "guest", "ip": "172.16.0.5", "status": "failed"},
]

# Run the analysis
stats, failures = analyze_auth_log(sample_logs)
```

### 7.2 Scenario: Password Generator

```python
#!/usr/bin/env python3
# password_generator.py - Generate secure passwords

import random
import string

def generate_password(length=12, use_uppercase=True, use_numbers=True, use_special=True):
    """
    Generate a secure random password
    
    Parameters:
        length: Password length (default 12, minimum 8)
        use_uppercase: Include A-Z
        use_numbers: Include 0-9
        use_special: Include special characters
    """
    if length < 8:
        length = 8
        print("⚠️  Minimum password length is 8 characters")
    
    # Define character sets
    lowercase = string.ascii_lowercase
    uppercase = string.ascii_uppercase
    numbers = string.digits
    special = "!@#$%^&*()_+-=[]{}|;:,.<>?"
    
    # Build character pool
    char_pool = lowercase
    required_chars = [random.choice(lowercase)]  # At least one lowercase
    
    if use_uppercase:
        char_pool += uppercase
        required_chars.append(random.choice(uppercase))
    
    if use_numbers:
        char_pool += numbers
        required_chars.append(random.choice(numbers))
    
    if use_special:
        char_pool += special
        required_chars.append(random.choice(special))
    
    # Fill remaining length with random choices
    remaining_length = length - len(required_chars)
    password_chars = required_chars + [random.choice(char_pool) for _ in range(remaining_length)]
    
    # Shuffle to randomize position of required characters
    random.shuffle(password_chars)
    
    return ''.join(password_chars)


def generate_passphrase(num_words=4, separator="-"):
    """
    Generate a passphrase (easier to remember, still secure)
    Example: correct-horse-battery-staple
    """
    # Common words list (in production, use a larger word list)
    word_list = [
        "apple", "banana", "cherry", "dragon", "eagle", "forest", "garden", "house",
        "island", "jungle", "kitchen", "lemon", "mountain", "night", "orange", "piano",
        "queen", "river", "sunset", "tiger", "umbrella", "valley", "water", "yellow",
        "zebra", "bridge", "castle", "desert", "engine", "flower", "guitar", "harbor",
        "iceberg", "jacket", "kite", "laptop", "mirror", "notebook", "ocean", "planet"
    ]
    
    words = [random.choice(word_list) for _ in range(num_words)]
    return separator.join(words)


# Demonstration
print("=" * 50)
print("SECURE PASSWORD GENERATOR")
print("=" * 50)

print("\n🔐 Random Passwords:")
print(f"   Basic (12 chars):      {generate_password(12)}")
print(f"   Strong (16 chars):     {generate_password(16)}")
print(f"   Very Strong (20 chars): {generate_password(20)}")
print(f"   Numbers only:          {generate_password(8, False, True, False)}")

print("\n📝 Passphrases (easier to remember):")
for _ in range(3):
    print(f"   {generate_passphrase()}")

print("\n💡 Password Security Tips:")
print("   - Use at least 12 characters for important accounts")
print("   - Combine letters, numbers, and special characters")
print("   - Use a unique password for each account")
print("   - Consider using a password manager")
print("   - Passphrases are often easier to remember than random passwords")
```

### 7.3 Scenario: Simple Firewall Rule Checker

```python
#!/usr/bin/env python3
# firewall_simulator.py - Simulate basic firewall functionality

def ip_to_int(ip_string):
    """Convert IP address to integer for comparison"""
    try:
        octets = [int(o) for o in ip_string.split(".")]
        return (octets[0] << 24) + (octets[1] << 16) + (octets[2] << 8) + octets[3]
    except:
        return None

def is_ip_in_range(ip, network, mask):
    """Check if IP is within a network range"""
    ip_int = ip_to_int(ip)
    network_int = ip_to_int(network)
    
    if ip_int is None or network_int is None:
        return False
    
    # Calculate subnet mask
    mask_bits = (0xFFFFFFFF << (32 - mask)) & 0xFFFFFFFF
    
    # Check if IP is in network
    return (ip_int & mask_bits) == (network_int & mask_bits)


class SimpleFirewall:
    """Simulate a simple firewall with allow/block rules"""
    
    def __init__(self):
        self.rules = []
        self.log = []
    
    def add_rule(self, action, source=None, destination=None, port=None, protocol="any"):
        """
        Add a firewall rule
        action: "allow" or "block"
        source: IP, network (e.g., "192.168.1.0/24"), or "any"
        destination: IP, network, or "any"
        port: port number or "any"
        protocol: "tcp", "udp", "icmp", or "any"
        """
        rule = {
            "action": action,
            "source": source,
            "destination": destination,
            "port": port,
            "protocol": protocol
        }
        self.rules.append(rule)
        print(f"✓ Added rule: {action.upper()} {source} → {destination}:{port} ({protocol})")
    
    def check_packet(self, src_ip, dst_ip, dst_port, protocol="tcp"):
        """Check if a packet should be allowed or blocked"""
        
        for rule in self.rules:
            match = True
            
            # Check source
            if rule["source"] and rule["source"] != "any":
                if "/" in rule["source"]:
                    network, mask = rule["source"].split("/")
                    if not is_ip_in_range(src_ip, network, int(mask)):
                        match = False
                elif rule["source"] != src_ip:
                    match = False
            
            # Check destination
            if match and rule["destination"] and rule["destination"] != "any":
                if "/" in rule["destination"]:
                    network, mask = rule["destination"].split("/")
                    if not is_ip_in_range(dst_ip, network, int(mask)):
                        match = False
                elif rule["destination"] != dst_ip:
                    match = False
            
            # Check port
            if match and rule["port"] and rule["port"] != "any":
                if rule["port"] != dst_port:
                    match = False
            
            # Check protocol
            if match and rule["protocol"] != "any":
                if rule["protocol"] != protocol:
                    match = False
            
            if match:
                result = {
                    "action": rule["action"],
                    "src_ip": src_ip,
                    "dst_ip": dst_ip,
                    "port": dst_port,
                    "protocol": protocol,
                    "matched_rule": rule
                }
                self.log.append(result)
                return result
        
        # Default action: block if no rule matches
        return {
            "action": "block",
            "src_ip": src_ip,
            "dst_ip": dst_ip,
            "port": dst_port,
            "protocol": protocol,
            "matched_rule": None,
            "reason": "No matching rule"
        }
    
    def print_rules(self):
        """Display all firewall rules"""
        print("\n📋 FIREWALL RULES")
        print("-" * 70)
        print(f"{'#':<3} {'Action':<8} {'Source':<20} {'Destination':<20} {'Port':<8} {'Protocol':<8}")
        print("-" * 70)
        
        for i, rule in enumerate(self.rules, 1):
            src = rule["source"] or "any"
            dst = rule["destination"] or "any"
            port = str(rule["port"]) if rule["port"] else "any"
            proto = rule["protocol"]
            print(f"{i:<3} {rule['action'].upper():<8} {src:<20} {dst:<20} {port:<8} {proto:<8}")


# Demonstration
print("=" * 70)
print("FIREWALL SIMULATOR")
print("=" * 70)

# Create firewall and add rules
fw = SimpleFirewall()

# Allow internal traffic
fw.add_rule("allow", "192.168.1.0/24", "192.168.1.0/24", "any")

# Allow outbound web traffic
fw.add_rule("allow", "192.168.1.0/24", "any", 80, "tcp")
fw.add_rule("allow", "192.168.1.0/24", "any", 443, "tcp")

# Allow SSH from specific admin IP
fw.add_rule("allow", "10.0.0.10", "any", 22, "tcp")

# Block everything else
fw.add_rule("block", "any", "any", "any")

# Show rules
fw.print_rules()

# Test some connections
print("\n🧪 TESTING CONNECTIONS")
print("-" * 50)

test_packets = [
    ("192.168.1.50", "192.168.1.1", 80, "tcp"),    # Internal HTTP - should allow
    ("192.168.1.50", "8.8.8.8", 443, "tcp"),        # External HTTPS - should allow
    ("192.168.1.50", "8.8.8.8", 22, "tcp"),         # External SSH - should block
    ("10.0.0.10", "192.168.1.5", 22, "tcp"),        # Admin SSH - should allow
    ("10.0.0.20", "192.168.1.5", 22, "tcp"),        # Non-admin SSH - should block
]

for src, dst, port, proto in test_packets:
    result = fw.check_packet(src, dst, port, proto)
    action_emoji = "✅" if result["action"] == "allow" else "❌"
    print(f"{action_emoji} {src} → {dst}:{port} ({proto}) = {result['action'].upper()}")
```

---

## Part 8: Practice Exercises

### Exercise 1: Calculator for Security Metrics
Create a program that calculates:
1. Time to crack a password given attempts per second and password space
2. Storage requirements for log files over time
3. Network bandwidth usage

### Exercise 2: Security Event Logger
Create a program that:
1. Takes user input for security events (type, description, severity)
2. Stores them in a list of dictionaries
3. Displays a summary report with counts by severity
4. Filters and shows only high/critical events

### Exercise 3: Port Scanner Simulation
Create a program that:
1. Has a list of common ports and their services
2. Checks each port (simulated with random status)
3. Reports which ports are "open"
4. Identifies potential security risks (e.g., Telnet port 23 open)

### Exercise 4: User Access Review
Create a program that:
1. Has a list of users with their roles and last login dates
2. Identifies users who haven't logged in for 30+ days
3. Identifies admin accounts for review
4. Generates a cleanup report

### Exercise 5: Hash Verifier
Create a program that:
1. Takes a file path and expected hash
2. Calculates MD5 or SHA256 hash of the file (simulated)
3. Compares with expected hash
4. Reports if file integrity is verified

---

## Summary Checklist

After completing this practical exercise, you should be comfortable with:

- [ ] Running Python scripts and using print()
- [ ] Creating and using variables of different types
- [ ] Working with strings, lists, and dictionaries
- [ ] Using arithmetic, comparison, and logical operators
- [ ] Writing if/elif/else conditional statements
- [ ] Using for and while loops
- [ ] Defining and calling functions
- [ ] Applying Python to basic security scenarios

---

## Additional Resources

1. **Python Official Tutorial**: https://docs.python.org/3/tutorial/
2. **Practice Online**: https://www.hackerrank.com/domains/python
3. **Cybersecurity Python Projects**: https://github.com/topics/cybersecurity-python
4. **Automate the Boring Stuff**: https://automatetheboringstuff.com/

---

*Created for Edutrack Computer Training College - Cybersecurity Certificate Program*
*Topic 3: Introduction to Programming Logic*
