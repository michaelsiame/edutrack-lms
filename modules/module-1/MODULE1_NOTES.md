# Module 1: Foundations of Computing and Mathematics
## Student Notes | Weeks 1–3

---

> **Program:** Certificate in Cybersecurity
> **Institution:** EduTrack Learning Management System
> **Duration:** 3 Weeks (Weeks 1–3)
> **Country:** Zambia

---

## Table of Contents

1. [Computer Fundamentals](#1-computer-fundamentals)
2. [Operating Systems](#2-operating-systems)
3. [Introduction to Programming Logic](#3-introduction-to-programming-logic)
4. [Mathematics for Cybersecurity](#4-mathematics-for-cybersecurity)
5. [Networking Essentials](#5-networking-essentials)
6. [Revision Summary](#6-revision-summary)

---

## 1. Computer Fundamentals

### 1.1 What is a Computer?

A computer is an electronic device that accepts input, processes it, stores the result, and produces output. Everything a cybersecurity professional protects — data, systems, networks — runs on computers.

**The IPOS Model:**

| Stage | Meaning | Example |
|---|---|---|
| **I**nput | Data entered into the system | Typing a password |
| **P**rocessing | CPU performs operations | Checking the password against a database |
| **O**utput | Result shown to the user | "Login successful" or "Access denied" |
| **S**torage | Data saved for later use | Password hash saved in a database |

---

### 1.2 Computer Hardware Components

Understanding hardware is essential — you cannot secure what you do not understand.

#### Central Processing Unit (CPU)
- The "brain" of the computer
- Executes instructions from programs
- Speed measured in GHz (gigahertz)
- Modern CPUs have multiple **cores** (dual-core, quad-core, etc.)
- **Cybersecurity relevance:** Cryptographic operations (encrypting/decrypting data) are CPU-intensive

#### Memory (RAM — Random Access Memory)
- Temporary storage used while the computer is running
- Faster than a hard drive but loses data when power is off
- Measured in GB (e.g., 4GB, 8GB, 16GB)
- **Cybersecurity relevance:** Malware often runs entirely in RAM to avoid leaving traces on disk (called "fileless malware")

#### Storage Devices
| Type | Speed | Capacity | Use Case |
|---|---|---|---|
| HDD (Hard Disk Drive) | Slow | Large (1TB+) | Storing large files |
| SSD (Solid State Drive) | Fast | Medium (256GB–2TB) | Operating system, apps |
| USB Flash Drive | Medium | Small (8GB–256GB) | Portable storage |
| CD/DVD | Slow | Very small | Software distribution (older) |

> **Zambian context:** In many Zambian organisations (banks, government offices, ZICTA), data is stored on servers. Understanding storage helps you protect that data from theft or corruption.

#### Motherboard
- The main circuit board connecting all components
- Contains: CPU socket, RAM slots, storage connectors, expansion slots
- **BIOS/UEFI** chip on the motherboard controls startup — an attacker with physical access can tamper with it

#### Input Devices
- Keyboard, mouse, touchscreen, biometric scanner, card reader

#### Output Devices
- Monitor, printer, speakers

#### Network Interface Card (NIC)
- Connects the computer to a network (wired or wireless)
- Every NIC has a unique **MAC address** (e.g., `00:1A:2B:3C:4D:5E`)
- **Cybersecurity relevance:** MAC addresses can be used to identify devices on a network

#### Power Supply Unit (PSU)
- Converts mains electricity (240V in Zambia) to voltages the computer needs
- Physical security includes protecting power sources from tampering

---

### 1.3 Types of Computers

| Type | Description | Example Use in Zambia |
|---|---|---|
| Personal Computer (PC) | Desktop or laptop for individual use | Office workstations at Zanaco, ZESCO |
| Server | Powerful computer serving many users | MTN Zambia billing servers |
| Embedded System | Computer inside a device | ATM machines, traffic lights in Lusaka |
| Mobile Device | Smartphone, tablet | Mobile money (Airtel Money, MTN MoMo) |
| IoT Device | Internet-connected smart device | Smart meters, security cameras |

---

### 1.4 System Architecture

**32-bit vs 64-bit Systems:**
- A 32-bit system can address up to 4GB of RAM
- A 64-bit system can address up to 16 exabytes of RAM
- Modern cybersecurity tools require 64-bit systems

**Von Neumann Architecture** (how most computers are built):
```
Input → [CPU (ALU + Control Unit)] ↔ [Memory (RAM)] → Output
                    ↕
                [Storage]
```

---

## 2. Operating Systems

### 2.1 What is an Operating System?

An Operating System (OS) is software that manages hardware resources and provides services for programs. It sits between the user/applications and the hardware.

```
User
  ↓
Applications (Word, Browser, Wireshark)
  ↓
Operating System (Windows, Linux, macOS)
  ↓
Hardware (CPU, RAM, Disk, Network)
```

**Key functions of an OS:**
- Process management (running programs)
- Memory management (allocating RAM)
- File system management (organising files)
- Security and access control (users and permissions)
- Device management (drivers for hardware)

---

### 2.2 Windows Operating System

Windows is the most common OS in Zambian homes, schools, and businesses.

#### Windows File System

```
C:\
├── Windows\          ← OS system files (do not delete)
├── Program Files\    ← Installed applications
├── Users\
│   └── Chanda\       ← Your user profile
│       ├── Desktop\
│       ├── Documents\
│       └── Downloads\
└── Temp\             ← Temporary files (malware often hides here)
```

#### Important Windows Directories for Security

| Path | Purpose | Security Note |
|---|---|---|
| `C:\Windows\System32` | Core OS files | Common target for malware |
| `C:\Users\[name]\AppData` | Hidden app data | Malware often stores itself here |
| `C:\Temp` or `C:\Windows\Temp` | Temporary files | Check here during investigations |
| `C:\Program Files` | Installed software | Unauthorised software detection |

#### Windows Command Prompt (CMD) Basics

Open CMD: Press `Win + R`, type `cmd`, press Enter

| Command | What It Does | Example |
|---|---|---|
| `dir` | List files in a directory | `dir C:\Users` |
| `cd` | Change directory | `cd C:\Windows` |
| `ipconfig` | Show IP address info | `ipconfig /all` |
| `ping` | Test network connectivity | `ping 8.8.8.8` |
| `netstat` | Show network connections | `netstat -an` |
| `tasklist` | Show running processes | `tasklist` |
| `systeminfo` | Show system information | `systeminfo` |
| `net user` | Manage user accounts | `net user` |

> **Practice:** Open CMD on your computer and run `ipconfig`. Note your IP address and default gateway.

#### Windows User Accounts and Permissions

- **Administrator** — Full system access (use sparingly)
- **Standard User** — Limited access (recommended for daily use)
- **Guest** — Very limited, no persistent changes

**Principle of Least Privilege:** Only give users the minimum permissions they need to do their job. This is a core cybersecurity principle.

---

### 2.3 Linux Operating System

Linux is the dominant OS in servers, cybersecurity tools, and cloud infrastructure. Kali Linux (used in ethical hacking) is built on it.

#### Why Linux for Cybersecurity?
- Free and open source
- Highly configurable
- Most cybersecurity tools are Linux-first
- Powers most web servers globally
- Stable and secure

#### Linux File System Structure

```
/                       ← Root directory (top of the tree)
├── /home/              ← User home directories
│   └── /home/mulenga/  ← Mulenga's files
├── /etc/               ← Configuration files (very important)
├── /var/log/           ← Log files (critical for security)
├── /tmp/               ← Temporary files
├── /usr/               ← User programs
├── /bin/               ← Essential system commands
└── /root/              ← Root (admin) user's home
```

#### Essential Linux Commands

| Command | Purpose | Example |
|---|---|---|
| `ls` | List files | `ls -la /etc` |
| `pwd` | Show current directory | `pwd` |
| `cd` | Change directory | `cd /var/log` |
| `cat` | View file contents | `cat /etc/passwd` |
| `grep` | Search text in files | `grep "Failed" /var/log/auth.log` |
| `ifconfig` / `ip a` | Show network info | `ip a` |
| `ping` | Test connectivity | `ping 8.8.8.8` |
| `ps aux` | Show running processes | `ps aux` |
| `sudo` | Run as administrator | `sudo apt update` |
| `chmod` | Change file permissions | `chmod 600 file.txt` |
| `whoami` | Show current user | `whoami` |
| `history` | Show command history | `history` |

#### Linux File Permissions

Every file in Linux has permissions for three groups:

```
-rwxr-xr--  1  mulenga  staff  1024  Mar 2026  script.sh
 ↑↑↑↑↑↑↑↑↑
 │└──┬──┘└──┬──┘└──┬──┘
 │  Owner  Group  Others
 │  (rwx)  (r-x)  (r--)
 │
 └─ File type (- = file, d = directory)
```

| Symbol | Meaning | Numeric Value |
|---|---|---|
| `r` | Read | 4 |
| `w` | Write | 2 |
| `x` | Execute | 1 |
| `-` | No permission | 0 |

**Example:** `chmod 755 file.sh` = Owner: rwx (7), Group: r-x (5), Others: r-x (5)

---

## 3. Introduction to Programming Logic

### 3.1 Why Programming Logic Matters in Cybersecurity

You do not need to be a software developer, but understanding logic helps you:
- Read and write basic scripts for automation
- Understand how malware and exploits work
- Analyse code for vulnerabilities
- Write simple security tools

---

### 3.2 Variables and Data Types

A **variable** is a named container that stores a value.

```python
# Python examples
student_name = "Chanda Mwale"     # String (text)
age = 22                           # Integer (whole number)
gpa = 3.5                          # Float (decimal number)
is_enrolled = True                 # Boolean (True/False)
```

**Data Types relevant to cybersecurity:**

| Type | Example | Security Use |
|---|---|---|
| String | `"password123"` | Storing usernames, passwords, IPs |
| Integer | `443`, `80`, `22` | Port numbers |
| Boolean | `True`, `False` | Access granted/denied |
| List | `["192.168.1.1", "10.0.0.1"]` | List of IP addresses |

---

### 3.3 Control Structures

#### If/Else (Decision Making)
```python
password = input("Enter password: ")

if password == "Secure@2026":
    print("Access granted")
else:
    print("Access denied")
```

#### Loops (Repetition)
```python
# Check 5 login attempts
attempts = 0

while attempts < 3:
    password = input("Enter password: ")
    if password == "Secure@2026":
        print("Access granted")
        break
    else:
        attempts += 1
        print(f"Wrong password. Attempt {attempts} of 3")

if attempts == 3:
    print("Account locked. Too many failed attempts.")
```

> This is exactly how account lockout policies work in real systems!

#### Functions (Reusable Code Blocks)
```python
def check_password_strength(password):
    if len(password) < 8:
        return "Weak - too short"
    elif password.isalpha():
        return "Weak - letters only"
    elif password.isnumeric():
        return "Weak - numbers only"
    else:
        return "Strong"

result = check_password_strength("Zambia@2026")
print(result)  # Output: Strong
```

---

### 3.4 Pseudocode

Pseudocode is plain-English logic before writing real code. It helps plan your solution.

**Example — Login System:**
```
START
  INPUT username
  INPUT password
  IF username EXISTS in database THEN
      IF password MATCHES stored hash THEN
          GRANT access
          LOG successful login
      ELSE
          INCREMENT failed_attempts
          IF failed_attempts >= 3 THEN
              LOCK account
              SEND alert to admin
          ENDIF
      ENDIF
  ELSE
      DISPLAY "Invalid credentials"
  ENDIF
END
```

---

### 3.5 Basic Bash Scripting (Linux)

```bash
#!/bin/bash
# Simple network check script

echo "Checking connectivity..."
ping -c 4 8.8.8.8

echo ""
echo "Your IP address:"
ip a | grep "inet " | grep -v "127.0.0.1"

echo ""
echo "Open network connections:"
netstat -tuln
```

Save as `check.sh`, then run: `chmod +x check.sh && ./check.sh`

---

## 4. Mathematics for Cybersecurity

### 4.1 Number Systems

Computers operate using **binary** (0s and 1s). Understanding number systems is essential for understanding IP addresses, cryptography, and data encoding.

#### Decimal (Base 10) — Human System
- Digits: 0, 1, 2, 3, 4, 5, 6, 7, 8, 9
- Each position is a power of 10

#### Binary (Base 2) — Computer System
- Digits: 0 and 1 only
- Each position is a power of 2
- 1 bit = one binary digit; 8 bits = 1 byte

#### Hexadecimal (Base 16) — Compact Computer Notation
- Digits: 0–9, then A=10, B=11, C=12, D=13, E=14, F=15
- Used in MAC addresses, color codes, memory addresses, hashes

---

### 4.2 Binary Conversion

#### Decimal to Binary

Divide by 2 repeatedly, collect remainders from bottom to top.

**Example: Convert 45 to binary**

```
45 ÷ 2 = 22 remainder 1
22 ÷ 2 = 11 remainder 0
11 ÷ 2 =  5 remainder 1
 5 ÷ 2 =  2 remainder 1
 2 ÷ 2 =  1 remainder 0
 1 ÷ 2 =  0 remainder 1

Read remainders bottom to top: 101101
So 45 in decimal = 101101 in binary
```

#### Binary to Decimal

Multiply each bit by its positional value (powers of 2) and add.

**Example: Convert 1010 1100 to decimal**

```
Position: 128  64  32  16   8   4   2   1
Bit:        1   0   1   0   1   1   0   0

= (1×128) + (0×64) + (1×32) + (0×16) + (1×8) + (1×4) + (0×2) + (0×1)
= 128 + 0 + 32 + 0 + 8 + 4 + 0 + 0
= 172
```

> **Cybersecurity use:** The IP address `172.16.0.1` — each number (172, 16, 0, 1) is an 8-bit binary value called an **octet**.

---

### 4.3 Hexadecimal Conversion

#### Decimal to Hexadecimal

Divide by 16, collect remainders.

**Example: Convert 255 to hex**
```
255 ÷ 16 = 15 remainder 15 → F
 15 ÷ 16 =  0 remainder 15 → F

255 decimal = FF hexadecimal
```

#### Binary to Hexadecimal (Shortcut)

Group binary digits into sets of 4, convert each group.

```
Binary:  1010  1100
Hex:      A     C

So 10101100 binary = AC hexadecimal = 172 decimal
```

#### Quick Reference Table

| Decimal | Binary | Hexadecimal |
|---|---|---|
| 0 | 0000 | 0 |
| 5 | 0101 | 5 |
| 9 | 1001 | 9 |
| 10 | 1010 | A |
| 15 | 1111 | F |
| 16 | 0001 0000 | 10 |
| 255 | 1111 1111 | FF |

---

### 4.4 Boolean Algebra and Logic Gates

Boolean algebra uses only two values: **TRUE (1)** and **FALSE (0)**.

#### Logic Operations

| Operation | Symbol | Rule | Example |
|---|---|---|---|
| AND | `∧` or `&&` | Both must be true | `1 AND 1 = 1` |
| OR | `∨` or `\|\|` | At least one must be true | `0 OR 1 = 1` |
| NOT | `¬` or `!` | Inverts the value | `NOT 1 = 0` |
| XOR | `⊕` | One OR the other, not both | `1 XOR 1 = 0` |

#### Truth Tables

**AND Gate:**
| A | B | A AND B |
|---|---|---|
| 0 | 0 | 0 |
| 0 | 1 | 0 |
| 1 | 0 | 0 |
| 1 | 1 | 1 |

**OR Gate:**
| A | B | A OR B |
|---|---|---|
| 0 | 0 | 0 |
| 0 | 1 | 1 |
| 1 | 0 | 1 |
| 1 | 1 | 1 |

**XOR (used in encryption!):**
| A | B | A XOR B |
|---|---|---|
| 0 | 0 | 0 |
| 0 | 1 | 1 |
| 1 | 0 | 1 |
| 1 | 1 | 0 |

> **Why XOR matters in cybersecurity:** XOR is the foundation of many encryption algorithms. If you XOR a value with a key, you can XOR it again with the same key to get back the original — this is the basis of stream ciphers.

---

## 5. Networking Essentials

### 5.1 What is a Network?

A network is two or more computers connected to share resources and communicate. Virtually all cybersecurity work involves networks.

**Types of Networks:**

| Type | Full Name | Coverage | Example |
|---|---|---|---|
| LAN | Local Area Network | Single building | School computer lab |
| WAN | Wide Area Network | Large geographic area | ZAMTEL national backbone |
| MAN | Metropolitan Area Network | City-wide | Lusaka city network |
| WLAN | Wireless LAN | Local wireless | Your home WiFi |
| VPN | Virtual Private Network | Secure tunnel over internet | Remote work in Zambia |

---

### 5.2 The OSI Model

The **OSI (Open Systems Interconnection) model** breaks networking into 7 layers. Think of it as a stack — data travels down the layers on the sender's side and up the layers on the receiver's side.

```
SENDER                          RECEIVER
  │                                │
7 │ Application  ←────────────→  Application  │ 7
6 │ Presentation ←────────────→  Presentation │ 6
5 │ Session      ←────────────→  Session      │ 5
4 │ Transport    ←────────────→  Transport    │ 4
3 │ Network      ←────────────→  Network      │ 3
2 │ Data Link    ←────────────→  Data Link    │ 2
1 │ Physical     ──────────────  Physical     │ 1
```

#### OSI Layers Explained

| Layer | Name | Function | Protocol/Example |
|---|---|---|---|
| 7 | Application | Interface for apps | HTTP, HTTPS, DNS, FTP, SMTP |
| 6 | Presentation | Data formatting, encryption | SSL/TLS, JPEG, ASCII |
| 5 | Session | Manages sessions/connections | NetBIOS, RPC |
| 4 | Transport | End-to-end delivery, ports | TCP, UDP |
| 3 | Network | Routing, IP addressing | IP, ICMP, routing |
| 2 | Data Link | MAC addressing, frames | Ethernet, WiFi (802.11) |
| 1 | Physical | Bits over cables/wireless | Cables, switches, radio |

**Memory Aid:** "**A**ll **P**eople **S**eem **T**o **N**eed **D**ata **P**rocessing"

> **Cybersecurity relevance:** Attacks happen at specific OSI layers. Firewalls operate at Layer 3–4. IDS/IPS at Layer 4–7. DDoS attacks often target Layer 3 (network flooding) or Layer 7 (application flooding).

---

### 5.3 TCP/IP Model

In practice, the **TCP/IP model** (4 layers) is more commonly used:

| TCP/IP Layer | Corresponds to OSI | Protocols |
|---|---|---|
| Application | Layers 5, 6, 7 | HTTP, HTTPS, DNS, FTP, SSH |
| Transport | Layer 4 | TCP, UDP |
| Internet | Layer 3 | IP, ICMP, ARP |
| Network Access | Layers 1, 2 | Ethernet, WiFi |

#### TCP vs UDP

| Feature | TCP | UDP |
|---|---|---|
| Connection | Connection-oriented (handshake) | Connectionless |
| Reliability | Guaranteed delivery | No guarantee |
| Speed | Slower | Faster |
| Use case | Web browsing, email, file transfer | Video streaming, DNS, VoIP |
| Cybersecurity note | SYN flood attacks exploit TCP handshake | UDP used in amplification DDoS attacks |

---

### 5.4 IP Addressing

An **IP address** uniquely identifies a device on a network.

#### IPv4

- 32-bit address written as 4 octets separated by dots
- Format: `XXX.XXX.XXX.XXX`
- Example: `192.168.1.100`
- Each octet is 0–255 (8 bits)

**Private IP Ranges** (used inside networks — not routable on the internet):

| Range | Use |
|---|---|
| `10.0.0.0 – 10.255.255.255` | Large enterprise networks |
| `172.16.0.0 – 172.31.255.255` | Medium networks |
| `192.168.0.0 – 192.168.255.255` | Home/small office networks |
| `127.0.0.1` | Loopback (your own machine) |

> **Zambian context:** When you connect to MTN or Airtel data, your phone gets a private IP (e.g., `192.168.1.x`) from the router, and a public IP is assigned by the ISP.

#### IPv6

- 128-bit address (IPv4 exhaustion led to this)
- Format: `2001:0db8:85a3:0000:0000:8a2e:0370:7334`
- Virtually unlimited addresses

---

### 5.5 Subnetting Basics

A **subnet mask** divides an IP address into the **network** part and the **host** part.

**Example:**
```
IP Address:    192.168.1.100
Subnet Mask:   255.255.255.0  (or /24)
Network:       192.168.1.0
Host Range:    192.168.1.1 – 192.168.1.254
Broadcast:     192.168.1.255
```

**CIDR Notation:**
- `/24` means the first 24 bits are the network (255.255.255.0)
- `/16` means the first 16 bits are the network (255.255.0.0)

---

### 5.6 Key Protocols

#### DNS (Domain Name System)
Translates human-friendly names to IP addresses.
```
You type: www.zamtel.co.zm
DNS returns: 196.32.X.X (the actual IP)
```
> **Security threat:** DNS poisoning — attacker redirects you to a fake website

#### DHCP (Dynamic Host Configuration Protocol)
Automatically assigns IP addresses to devices on a network.
> **Security threat:** Rogue DHCP server — attacker sets up a fake DHCP server to redirect traffic

#### HTTP vs HTTPS
- **HTTP** (port 80): Unencrypted — anyone on the network can read your data
- **HTTPS** (port 443): Encrypted using TLS — safe for banking, passwords, personal data
> Always check for `https://` and the padlock icon before entering passwords online.

#### SSH (Secure Shell) — Port 22
Encrypted remote access to servers. Replaces insecure Telnet.
```bash
ssh mulenga@192.168.1.50   # Connect to server at 192.168.1.50
```

#### Common Port Numbers

| Port | Protocol | Use |
|---|---|---|
| 20, 21 | FTP | File transfer |
| 22 | SSH | Secure remote access |
| 23 | Telnet | Insecure remote access (avoid) |
| 25 | SMTP | Sending email |
| 53 | DNS | Domain name resolution |
| 80 | HTTP | Web (unencrypted) |
| 110 | POP3 | Receiving email |
| 143 | IMAP | Email sync |
| 443 | HTTPS | Web (encrypted) |
| 3389 | RDP | Windows remote desktop |
| 3306 | MySQL | Database |

> **Cybersecurity use:** Attackers scan for open ports to find ways in. Knowing these ports helps you understand what's normal vs. suspicious on a network.

---

## 6. Revision Summary

### Key Concepts Checklist

**Computer Fundamentals**
- [ ] Explain the IPOS model
- [ ] Identify CPU, RAM, storage, NIC, and motherboard
- [ ] Explain the difference between RAM and storage
- [ ] Explain what a MAC address is

**Operating Systems**
- [ ] Navigate Windows using CMD (dir, cd, ipconfig, netstat, tasklist)
- [ ] Navigate Linux using terminal (ls, cd, cat, grep, ifconfig, chmod)
- [ ] Explain Linux file permissions (rwx and numeric notation)
- [ ] Identify security-relevant directories in Windows and Linux

**Programming Logic**
- [ ] Write pseudocode for a login system with lockout
- [ ] Write a Python if/else statement
- [ ] Write a while loop for login attempts
- [ ] Explain what a function is and why it is useful

**Number Systems**
- [ ] Convert decimal to binary and binary to decimal
- [ ] Convert binary to hexadecimal using the grouping method
- [ ] Complete AND, OR, NOT, XOR truth tables
- [ ] Explain why XOR is used in encryption

**Networking**
- [ ] Name and describe all 7 OSI layers
- [ ] Explain the difference between TCP and UDP
- [ ] Identify private IP address ranges
- [ ] Explain what DNS and DHCP do and their security risks
- [ ] List 10 common port numbers

---

### Glossary

| Term | Definition |
|---|---|
| **Bit** | The smallest unit of data (0 or 1) |
| **Byte** | 8 bits |
| **Bandwidth** | The amount of data that can be transferred per second |
| **Firewall** | Software or hardware that controls network traffic |
| **IP Address** | Unique numerical label for a device on a network |
| **MAC Address** | Hardware address burned into a NIC |
| **Packet** | A unit of data transmitted over a network |
| **Port** | A virtual endpoint for network communication |
| **Protocol** | A set of rules for how data is transmitted |
| **Server** | A computer that provides services to other computers |
| **Subnet** | A portion of a network divided from a larger network |
| **VPN** | Encrypted tunnel over the internet for secure access |

---

*Notes prepared for EduTrack Cybersecurity Certificate Program — Zambia*
*Next: Module 1 Lab Exercises → `MODULE1_LAB_EXERCISES.md`*
