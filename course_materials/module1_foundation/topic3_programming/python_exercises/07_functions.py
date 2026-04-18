#!/usr/bin/env python3
"""
Exercise 6.1: Functions - Organizing Your Code
Topic 3: Introduction to Programming Logic
"""

print("=" * 50)
print("FUNCTIONS - REUSABLE CODE BLOCKS")
print("=" * 50)


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
    host_bits = 32 - subnet_mask
    num_hosts = 2 ** host_bits - 2
    return num_hosts


print("\n--- Subnet Calculator ---")
for mask in [24, 16, 8]:
    hosts = calculate_subnet_hosts(mask)
    print(f"/{mask} subnet: {hosts:,} usable hosts")


# Function with multiple parameters and return values
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


print("\n--- Password Strength Checker ---")
test_passwords = ["abc", "Password1", "MyStr0ng!Pass", "admin123"]

for pwd in test_passwords:
    strong, msg = check_password_strength(pwd)
    status = "✓" if strong else "✗"
    print(f"{status} '{pwd}': {msg}")


# Function with default parameters
def generate_report(title, author="Security System", severity="MEDIUM"):
    """Generate a standardized security report header"""
    from datetime import datetime
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


# Function returning dictionary
def analyze_ip_address(ip_string):
    """Analyze an IP address and return its properties"""
    try:
        octets = [int(o) for o in ip_string.split(".")]
        if len(octets) != 4 or not all(0 <= o <= 255 for o in octets):
            return None, "Invalid IP format"
    except ValueError:
        return None, "Invalid octet values"
    
    first_octet = octets[0]
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
    
    is_private = (
        (first_octet == 10) or
        (first_octet == 172 and 16 <= octets[1] <= 31) or
        (first_octet == 192 and octets[1] == 168)
    )
    
    return {
        "ip": ip_string,
        "class": ip_class,
        "is_private": is_private,
        "octets": octets
    }, "Analysis complete"


print("\n--- IP Address Analyzer ---")
test_ips = ["192.168.1.1", "8.8.8.8", "10.0.0.1", "256.1.1.1"]

for ip in test_ips:
    result, message = analyze_ip_address(ip)
    if result:
        scope = "Private" if result["is_private"] else "Public"
        print(f"✓ {result['ip']}: Class {result['class']}, {scope}")
    else:
        print(f"✗ {ip}: {message}")


# YOUR EXERCISE: Event Summary Function
print("\n--- YOUR TURN: Event Summary Function ---")
# Create a function that takes a list of security events
# Returns: counts by type, most common source IP

events = [
    {"type": "login", "ip": "192.168.1.1"},
    {"type": "login", "ip": "192.168.1.5"},
    {"type": "error", "ip": "192.168.1.1"},
    {"type": "warning", "ip": "10.0.0.1"},
    {"type": "login", "ip": "192.168.1.1"},
]

# Define your function here:
# def summarize_events(event_list):
#     # Your code
#     return summary
