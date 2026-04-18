#!/usr/bin/env python3
"""
Exercise 5.1: Loops - For and While
Topic 3: Introduction to Programming Logic
"""

print("=" * 50)
print("LOOPS - REPEATING ACTIONS")
print("=" * 50)

# For loop through list
print("\n--- Port Scan Simulation ---")
ports_to_check = [22, 80, 443, 3306, 8080, 8443]

for port in ports_to_check:
    status = "OPEN" if port in [80, 443, 8080] else "CLOSED"
    print(f"Port {port}: {status}")

# For loop with enumerate
print("\n--- Password Policy Check ---")
passwords = ["pass", "password123", "Secure1!", "admin", "MyStr0ngP@ss"]
min_length = 8

for i, password in enumerate(passwords, 1):
    if len(password) >= min_length:
        strength = "STRONG"
    else:
        strength = "WEAK"
    print(f"Password {i}: {'*' * len(password)} - {strength}")

# Loop through dictionary
print("\n--- System Health Check ---")
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

# Nested loops
print("\n--- Network Segment Check ---")
subnets = ["192.168.1", "192.168.2"]
host_ids = [1, 2, 3, 254]

for subnet in subnets:
    print(f"\nScanning subnet {subnet}.0/24:")
    for host in host_ids:
        ip = f"{subnet}.{host}"
        device_type = "Gateway" if host == 254 else f"Host-{host}"
        print(f"  - {ip}: {device_type} responding")

# While loop - login simulation
print("\n--- Login Attempt Simulation ---")
max_attempts = 3
attempts = 0
password_correct = False

while attempts < max_attempts and not password_correct:
    attempts += 1
    print(f"Attempt {attempts} of {max_attempts}")
    
    # Simulate correct password on 2nd try
    if attempts == 2:
        password_correct = True
        print("  ✓ Login successful!")
    else:
        print("  ✗ Invalid password")

if not password_correct:
    print("\n⚠️  Account locked - Maximum attempts exceeded")

# YOUR EXERCISE: Log monitoring
print("\n--- YOUR TURN: Log Level Counter ---")
# Create a loop that processes 10 log entries
# Count how many are ERROR, WARNING, or INFO level

log_entries = [
    {"level": "INFO", "message": "System started"},
    {"level": "WARNING", "message": "High memory"},
    {"level": "ERROR", "message": "Connection failed"},
    {"level": "INFO", "message": "User login"},
    {"level": "ERROR", "message": "File not found"},
    {"level": "INFO", "message": "Backup complete"},
    {"level": "WARNING", "message": "Disk space low"},
    {"level": "INFO", "message": "Scan started"},
    {"level": "ERROR", "message": "Access denied"},
    {"level": "INFO", "message": "Scan complete"},
]

error_count = 0
warning_count = 0
info_count = 0

# Your code here to count each type

print(f"ERROR: {error_count}, WARNING: {warning_count}, INFO: {info_count}")
