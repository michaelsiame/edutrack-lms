#!/usr/bin/env python3
"""
Scenario 7.2: Password Generator
Topic 3: Introduction to Programming Logic
"""

import random
import string


def generate_password(length=12, use_uppercase=True, use_numbers=True, use_special=True):
    """
    Generate a secure random password
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
    required_chars = [random.choice(lowercase)]
    
    if use_uppercase:
        char_pool += uppercase
        required_chars.append(random.choice(uppercase))
    
    if use_numbers:
        char_pool += numbers
        required_chars.append(random.choice(numbers))
    
    if use_special:
        char_pool += special
        required_chars.append(random.choice(special))
    
    # Fill remaining length
    remaining_length = length - len(required_chars)
    password_chars = required_chars + [random.choice(char_pool) for _ in range(remaining_length)]
    
    # Shuffle to randomize position of required characters
    random.shuffle(password_chars)
    
    return ''.join(password_chars)


def generate_passphrase(num_words=4, separator="-"):
    """
    Generate a passphrase (easier to remember, still secure)
    """
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
if __name__ == "__main__":
    print("=" * 50)
    print("SECURE PASSWORD GENERATOR")
    print("=" * 50)
    
    print("\n🔐 Random Passwords:")
    print(f"   Basic (12 chars):       {generate_password(12)}")
    print(f"   Strong (16 chars):      {generate_password(16)}")
    print(f"   Very Strong (20 chars): {generate_password(20)}")
    print(f"   Numbers only:           {generate_password(8, False, True, False)}")
    
    print("\n📝 Passphrases (easier to remember):")
    for _ in range(3):
        print(f"   {generate_passphrase()}")
    
    print("\n💡 Password Security Tips:")
    print("   - Use at least 12 characters for important accounts")
    print("   - Combine letters, numbers, and special characters")
    print("   - Use a unique password for each account")
    print("   - Consider using a password manager")
    print("   - Passphrases are often easier to remember")
