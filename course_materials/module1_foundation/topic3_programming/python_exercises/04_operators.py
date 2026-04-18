#!/usr/bin/env python3
"""
Exercise 3.1: Operators in Python
Topic 3: Introduction to Programming Logic
"""

print("=" * 50)
print("OPERATORS FOR SECURITY LOGIC")
print("=" * 50)

# Arithmetic Operators
print("\n--- Arithmetic Operators ---")
packets_in = 1500
packets_out = 1420
packet_loss = packets_in - packets_out
loss_percentage = (packet_loss / packets_in) * 100

print(f"Packets sent: {packets_in}")
print(f"Packets received: {packets_out}")
print(f"Packet loss: {packet_loss}")
print(f"Loss percentage: {loss_percentage:.2f}%")

# Comparison Operators
print("\n--- Comparison Operators ---")
failed_logins = 5
threshold = 3

print(f"Failed logins ({failed_logins}) > threshold ({threshold}): {failed_logins > threshold}")
print(f"Failed logins ({failed_logins}) == threshold ({threshold}): {failed_logins == threshold}")
print(f"Failed logins ({failed_logins}) <= 5: {failed_logins <= 5}")

# Logical Operators
print("\n--- Logical Operators ---")
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

# Account lockout logic
print("\n--- Account Lockout Logic ---")
failed_attempts = 4
last_attempt_time = 2  # minutes ago
account_locked = False

if failed_attempts >= 3 and last_attempt_time <= 5:
    account_locked = True

print(f"Failed attempts: {failed_attempts}")
print(f"Last attempt: {last_attempt_time} minutes ago")
print(f"Account locked: {account_locked}")

# YOUR EXERCISE: Password validation
print("\n--- YOUR TURN: Password Validator ---")
# Write code to check if a password:
# - Is at least 8 characters long
# - Contains at least one number
# - Is not equal to "password123"

test_password = "MyP@ssw0rd"
# Your validation code here:
