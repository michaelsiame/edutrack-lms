#!/usr/bin/env python3
"""
Exercise 2.1: Understanding Variables and Data Types
Topic 3: Introduction to Programming Logic
"""

print("=" * 50)
print("VARIABLES AND DATA TYPES")
print("=" * 50)

# String - text data
print("\n--- String Variables ---")
username = "admin"
email = "security@company.com"
hash_example = "5f4dcc3b5aa765d61d8327deb882cf99"

print(f"Username: {username}")
print(f"Email: {email}")
print(f"Hash: {hash_example}")
print(f"Hash length: {len(hash_example)} characters")

# Integer - whole numbers
print("\n--- Integer Variables ---")
port_number = 443
failed_attempts = 5
max_retries = 3

print(f"HTTPS Port: {port_number}")
print(f"Failed login attempts: {failed_attempts}")
print(f"Max retries allowed: {max_retries}")

# Float - decimal numbers
print("\n--- Float Variables ---")
cpu_usage = 45.7
network_latency = 23.5
encryption_strength = 256.0

print(f"CPU Usage: {cpu_usage}%")
print(f"Network Latency: {network_latency}ms")
print(f"Encryption Key Size: {encryption_strength} bits")

# Boolean - True/False values
print("\n--- Boolean Variables ---")
is_authenticated = False
is_admin = True
firewall_enabled = True

print(f"User authenticated: {is_authenticated}")
print(f"User is admin: {is_admin}")
print(f"Firewall enabled: {firewall_enabled}")

# Lists
print("\n--- Lists ---")
blocked_ips = ["192.168.1.100", "10.0.0.50", "172.16.0.25"]
common_ports = [22, 80, 443, 3389, 8080]

print(f"Blocked IPs: {blocked_ips}")
print(f"Number of blocked IPs: {len(blocked_ips)}")
print(f"First blocked IP: {blocked_ips[0]}")
print(f"Last blocked IP: {blocked_ips[-1]}")

# Add to list
blocked_ips.append("192.168.1.200")
print(f"Updated blocked IPs: {blocked_ips}")

# Dictionaries
print("\n--- Dictionaries ---")
user_account = {
    "username": "jdoe",
    "email": "jdoe@company.com",
    "role": "analyst",
    "active": True,
    "last_login": "2026-04-09",
    "failed_attempts": 0
}

print(f"User: {user_account['username']}")
print(f"Role: {user_account['role']}")

user_account["department"] = "Security Operations"
user_account["failed_attempts"] = 2

print(f"Updated user data: {user_account}")

# List of dictionaries
print("\n--- List of Dictionaries ---")
security_events = [
    {"timestamp": "10:00:15", "type": "login_failed", "ip": "192.168.1.50"},
    {"timestamp": "10:05:22", "type": "firewall_block", "ip": "10.0.0.25"},
    {"timestamp": "10:12:08", "type": "suspicious_activity", "ip": "172.16.0.10"}
]

for event in security_events:
    print(f"[{event['timestamp']}] {event['type']} from {event['ip']}")

# YOUR EXERCISE: Create a list of 5 malware file extensions
# and a dictionary with security incident information
print("\n--- YOUR TURN ---")
malware_extensions = []  # Add 5 extensions like ".exe", ".bat", etc.
incident = {}  # Create dictionary with: date, type, severity, affected_systems
