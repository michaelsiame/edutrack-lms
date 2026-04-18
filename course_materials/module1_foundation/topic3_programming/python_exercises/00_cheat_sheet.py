#!/usr/bin/env python3
"""
PYTHON CHEAT SHEET FOR CYBERSECURITY
Topic 3: Introduction to Programming Logic

This file serves as a quick reference for Python syntax and concepts.
Run it to see examples of all major Python features.
"""

print("=" * 60)
print("PYTHON CHEAT SHEET FOR CYBERSECURITY STUDENTS")
print("=" * 60)

# =============================================================================
# 1. VARIABLES AND DATA TYPES
# =============================================================================
print("\n" + "=" * 60)
print("1. VARIABLES AND DATA TYPES")
print("=" * 60)

# Strings (text)
username = "admin"
password_hash = "5f4dcc3b5aa765d61d8327deb882cf99"
print(f"String: {username}, Hash: {password_hash}")

# Integers (whole numbers)
port = 443
attempts = 5
print(f"Integer: Port {port}, Attempts {attempts}")

# Floats (decimals)
cpu_percent = 45.7
risk_score = 8.5
print(f"Float: CPU {cpu_percent}%, Risk {risk_score}")

# Booleans (True/False)
is_secure = True
is_blocked = False
print(f"Boolean: Secure={is_secure}, Blocked={is_blocked}")

# None (no value)
result = None
print(f"None: {result}")

# =============================================================================
# 2. DATA STRUCTURES
# =============================================================================
print("\n" + "=" * 60)
print("2. DATA STRUCTURES")
print("=" * 60)

# Lists (ordered, mutable)
ports = [22, 80, 443, 8080]
ports.append(8443)  # Add item
print(f"List: {ports}")
print(f"  First: {ports[0]}, Last: {ports[-1]}, Count: {len(ports)}")

# Dictionaries (key-value pairs)
user = {
    "name": "jdoe",
    "role": "analyst",
    "active": True
}
user["department"] = "Security"  # Add key
print(f"Dictionary: {user}")
print(f"  Name: {user['name']}, Role: {user.get('role')}")

# Sets (unique values)
unique_ips = {"192.168.1.1", "10.0.0.1", "192.168.1.1"}  # Duplicates removed
print(f"Set: {unique_ips} (duplicates automatically removed)")

# =============================================================================
# 3. OPERATORS
# =============================================================================
print("\n" + "=" * 60)
print("3. OPERATORS")
print("=" * 60)

# Arithmetic
a, b = 10, 3
print(f"Arithmetic: {a} + {b} = {a+b}, {a} - {b} = {a-b}, {a} * {b} = {a*b}")
print(f"            {a} / {b} = {a/b:.2f}, {a} // {b} = {a//b} (floor), {a} % {b} = {a%b} (mod)")
print(f"            {a} ** {b} = {a**b} (power)")

# Comparison
print(f"Comparison: {a} > {b} = {a > b}, {a} == {b} = {a == b}, {a} != {b} = {a != b}")

# Logical
x, y = True, False
print(f"Logical: {x} and {y} = {x and y}, {x} or {y} = {x or y}, not {x} = {not x}")

# Membership
print(f"Membership: 80 in {ports} = {80 in ports}")
print(f"            'name' in user = {'name' in user}")

# =============================================================================
# 4. CONTROL FLOW
# =============================================================================
print("\n" + "=" * 60)
print("4. CONTROL FLOW")
print("=" * 60)

# If / Elif / Else
score = 75
if score >= 90:
    grade = "A"
elif score >= 80:
    grade = "B"
elif score >= 70:
    grade = "C"
else:
    grade = "F"
print(f"If-elif-else: Score {score} = Grade {grade}")

# Ternary operator (one-line if)
status = "PASS" if score >= 70 else "FAIL"
print(f"Ternary: Score {score} = {status}")

# =============================================================================
# 5. LOOPS
# =============================================================================
print("\n" + "=" * 60)
print("5. LOOPS")
print("=" * 60)

# For loop with list
print("For loop over list:")
for port in [80, 443, 8080]:
    print(f"  Checking port {port}")

# For loop with range
print("For loop with range:")
for i in range(3):
    print(f"  Iteration {i}")

# For loop with enumerate (get index and value)
print("For loop with enumerate:")
for i, port in enumerate([22, 80, 443], 1):
    print(f"  {i}. Port {port}")

# While loop
print("While loop:")
count = 0
while count < 3:
    print(f"  Count: {count}")
    count += 1

# Loop control: break and continue
print("Loop with break (stop at 5):")
for i in range(10):
    if i == 5:
        break
    print(f"  {i}", end=" ")
print()

# =============================================================================
# 6. FUNCTIONS
# =============================================================================
print("\n" + "=" * 60)
print("6. FUNCTIONS")
print("=" * 60)

# Basic function
def greet(name):
    """Return a greeting message"""
    return f"Hello, {name}!"

print(f"Basic function: {greet('Security Analyst')}")

# Function with default parameter
def scan_port(port, protocol="tcp"):
    return f"Scanning port {port}/{protocol}"

print(f"Default param: {scan_port(80)}")
print(f"Custom param:  {scan_port(53, 'udp')}")

# Function with multiple return values
def get_stats(numbers):
    return min(numbers), max(numbers), sum(numbers)/len(numbers)

min_val, max_val, avg = get_stats([10, 20, 30, 40, 50])
print(f"Multiple returns: min={min_val}, max={max_val}, avg={avg}")

# Lambda (anonymous function)
square = lambda x: x ** 2
print(f"Lambda: square(5) = {square(5)}")

# =============================================================================
# 7. STRING OPERATIONS
# =============================================================================
print("\n" + "=" * 60)
print("7. STRING OPERATIONS")
print("=" * 60)

text = "  CyberSecurity is Important!  "
print(f"Original: '{text}'")
print(f"Upper: '{text.upper()}'")
print(f"Lower: '{text.lower()}'")
print(f"Strip: '{text.strip()}'")
print(f"Replace: '{text.replace('Important', 'Critical')}'")
print(f"Split: {text.split()}")
print(f"Starts with 'Cyber': {text.strip().startswith('Cyber')}")

# String formatting
ip = "192.168.1.1"
port = 443
print(f"F-string: Connecting to {ip}:{port}")
print("Format:   Connecting to {}:{}".format(ip, port))

# =============================================================================
# 8. FILE OPERATIONS (Basics)
# =============================================================================
print("\n" + "=" * 60)
print("8. FILE OPERATIONS")
print("=" * 60)

# Writing to a file
with open("temp_example.txt", "w") as f:
    f.write("Line 1: Security log entry\n")
    f.write("Line 2: Another entry\n")
print("Written to temp_example.txt")

# Reading from a file
with open("temp_example.txt", "r") as f:
    content = f.read()
print(f"File content:\n{content}")

# Reading line by line
with open("temp_example.txt", "r") as f:
    for line_num, line in enumerate(f, 1):
        print(f"  Line {line_num}: {line.strip()}")

# Clean up
import os
os.remove("temp_example.txt")
print("Cleaned up temp file")

# =============================================================================
# 9. ERROR HANDLING
# =============================================================================
print("\n" + "=" * 60)
print("9. ERROR HANDLING")
print("=" * 60)

# Try / Except
def safe_divide(a, b):
    try:
        result = a / b
        return result
    except ZeroDivisionError:
        return "Error: Cannot divide by zero"
    except TypeError:
        return "Error: Invalid input type"

print(f"Safe divide 10/2: {safe_divide(10, 2)}")
print(f"Safe divide 10/0: {safe_divide(10, 0)}")

# Try / Except / Else / Finally
try:
    value = int("123")
except ValueError:
    print("Could not convert to integer")
else:
    print(f"Conversion successful: {value}")
finally:
    print("This always executes")

# =============================================================================
# 10. USEFUL BUILT-IN FUNCTIONS
# =============================================================================
print("\n" + "=" * 60)
print("10. USEFUL BUILT-IN FUNCTIONS")
print("=" * 60)

numbers = [3, 1, 4, 1, 5, 9, 2, 6]
print(f"List: {numbers}")
print(f"len(): {len(numbers)}")
print(f"sum(): {sum(numbers)}")
print(f"min(): {min(numbers)}")
print(f"max(): {max(numbers)}")
print(f"sorted(): {sorted(numbers)}")
print(f"any(x > 5): {any(x > 5 for x in numbers)}")
print(f"all(x > 0): {all(x > 0 for x in numbers)}")

# Type conversion
print(f"\nType conversion:")
print(f"int('42') = {int('42')}")
print(f"str(42) = {str(42)}")
print(f"float('3.14') = {float('3.14')}")
print(f"list('abc') = {list('abc')}")

# =============================================================================
# QUICK REFERENCE SUMMARY
# =============================================================================
print("\n" + "=" * 60)
print("QUICK REFERENCE SUMMARY")
print("=" * 60)

summary = """
COMMENTS:
  # Single line comment
  # Multi-line comments use multiple # lines

VARIABLES:
  name = "value"           # String
  count = 10               # Integer
  price = 19.99            # Float
  active = True            # Boolean

DATA STRUCTURES:
  my_list = [1, 2, 3]                    # List
  my_dict = {"key": "value"}             # Dictionary
  my_set = {1, 2, 3}                     # Set

CONDITIONALS:
  if condition:
      do_something()
  elif other_condition:
      do_other()
  else:
      do_default()

LOOPS:
  for item in items:
      process(item)
  
  while condition:
      do_something()

FUNCTIONS:
  def my_func(param1, param2="default"):
      # Docstring here
      return result

STRING FORMATTING:
  f"Hello {name}"          # f-string (recommended)
  "Hello {}".format(name)  # format method

FILE OPERATIONS:
  with open("file.txt", "r") as f:
      data = f.read()

ERROR HANDLING:
  try:
      risky_operation()
  except Exception as e:
      handle_error(e)
"""

print(summary)

print("=" * 60)
print("END OF CHEAT SHEET")
print("=" * 60)
