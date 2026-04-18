#!/usr/bin/env python3
"""
Exercise 4.1: Control Flow - Conditionals
Topic 3: Introduction to Programming Logic
"""

print("=" * 50)
print("CONTROL FLOW - CONDITIONAL STATEMENTS")
print("=" * 50)

# Basic if statement
print("\n--- Alert Severity Check ---")
alert_severity = "HIGH"

if alert_severity == "CRITICAL":
    print("🚨 IMMEDIATE ACTION REQUIRED!")
    print("   - Notify security team immediately")
    print("   - Begin incident response procedure")

if alert_severity == "HIGH":
    print("⚠️  HIGH PRIORITY ALERT")
    print("   - Investigate within 1 hour")

# If-else statement
print("\n--- Port Status Check ---")
port = 22
allowed_ports = [80, 443, 8080]

if port in allowed_ports:
    print(f"Port {port}: ALLOWED")
else:
    print(f"Port {port}: BLOCKED - Not in allowed list")

# If-elif-else chain
print("\n--- Risk Level Assessment ---")
cvss_score = 7.5

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
print("\n--- Access Control Decision ---")
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

# YOUR EXERCISE: Incident Categorization
print("\n--- YOUR TURN: Incident Categorization ---")
# Create a script that categorizes incidents:
# - "malware" → CRITICAL
# - "unauthorized_access" → HIGH
# - "policy_violation" → MEDIUM
# - "suspicious_activity" → LOW
# Print appropriate response actions for each

incident_type = "malware"  # Change this to test different types
