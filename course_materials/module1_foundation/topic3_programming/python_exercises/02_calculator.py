#!/usr/bin/env python3
"""
Exercise 1.2: Python as a Calculator
Topic 3: Introduction to Programming Logic
"""

print("=" * 50)
print("PYTHON CALCULATOR FOR SECURITY TASKS")
print("=" * 50)

# Network security calculations
print("\n--- Network Calculation ---")
ip_addresses_per_subnet = 256
subnets_needed = 4
total_ips = ip_addresses_per_subnet * subnets_needed

print(f"IP addresses per subnet: {ip_addresses_per_subnet}")
print(f"Subnets needed: {subnets_needed}")
print(f"Total IP addresses: {total_ips}")

# File size calculations
print("\n--- Storage Calculation ---")
file_size_mb = 50
num_files = 120
total_storage_gb = (file_size_mb * num_files) / 1024

print(f"Average file size: {file_size_mb} MB")
print(f"Number of log files: {num_files}")
print(f"Total storage needed: {total_storage_gb:.2f} GB")

# Modulo operator
print("\n--- Modulo Examples ---")
print(f"256 % 16 = {256 % 16}")  # Used in IP addressing
print(f"100 % 7 = {100 % 7}")    # Used in checksum calculations

# Exponentiation
print("\n--- Exponentiation ---")
print(f"2^8 = {2**8}")         # IPv4 address space per octet
print(f"2^16 = {2**16}")       # Class B network addresses
print(f"2^32 = {2**32}")       # Total IPv4 addresses

# EXERCISE: Calculate password cracking time
print("\n--- EXERCISE: Password Cracking Time ---")
print("How long would it take to crack a password?")

# An 8-character password from 95 printable ASCII characters
password_space = 95 ** 8
attempts_per_second = 1_000_000  # 1 million per second

seconds_to_crack = password_space / attempts_per_second
minutes_to_crack = seconds_to_crack / 60
hours_to_crack = minutes_to_crack / 60
days_to_crack = hours_to_crack / 24
years_to_crack = days_to_crack / 365

print(f"Password space: {password_space:,}")
print(f"Attempts per second: {attempts_per_second:,}")
print(f"Time to crack (worst case):")
print(f"  - Seconds: {seconds_to_crack:,.0f}")
print(f"  - Minutes: {minutes_to_crack:,.0f}")
print(f"  - Hours: {hours_to_crack:,.0f}")
print(f"  - Days: {days_to_crack:,.0f}")
print(f"  - Years: {years_to_crack:,.0f}")
