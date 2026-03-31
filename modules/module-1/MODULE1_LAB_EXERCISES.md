# Module 1: Lab Exercises
## Foundations of Computing and Mathematics

---

> **Program:** Certificate in Cybersecurity
> **Labs:** Weeks 1–3
> **Requirements:** A computer with Windows or Linux (or VirtualBox with Ubuntu)

---

## Lab 1: Exploring Your Computer (Week 1)

### Objective
Identify hardware components, gather system information, and understand what is running on your computer.

### Tools Required
- Windows CMD or Linux Terminal
- Your physical computer

---

### Lab 1A: System Information (Windows)

**Step 1:** Open Command Prompt (press `Win + R`, type `cmd`, press Enter)

**Step 2:** Run the following commands and record the output in the table below:

```cmd
systeminfo
```

| Information | Your Result |
|---|---|
| OS Name | |
| OS Version | |
| Total Physical Memory (RAM) | |
| Number of Processors | |
| Time Zone | |

**Step 3:** Check your network configuration:
```cmd
ipconfig /all
```

Record the following:

| Information | Your Result |
|---|---|
| IPv4 Address | |
| Subnet Mask | |
| Default Gateway | |
| DNS Server(s) | |
| Physical (MAC) Address | |

**Step 4:** List running processes:
```cmd
tasklist | more
```
- How many processes are running? ___________
- Can you identify any suspicious-looking process names? ___________

**Step 5:** Check open network connections:
```cmd
netstat -an | more
```
- List 3 ports you see in the LISTENING state:
  1. ___________
  2. ___________
  3. ___________

---

### Lab 1B: System Information (Linux/Ubuntu)

**Step 1:** Open Terminal

**Step 2:** Gather system information:
```bash
uname -a
```
Record the kernel version: ___________

**Step 3:** Check CPU and RAM:
```bash
cat /proc/cpuinfo | grep "model name" | head -1
free -h
df -h
```

| Information | Your Result |
|---|---|
| CPU Model | |
| Total RAM | |
| Available RAM | |
| Disk Usage (root `/`) | |

**Step 4:** Check network information:
```bash
ip a
ip route
cat /etc/resolv.conf
```

| Information | Your Result |
|---|---|
| IP Address | |
| Default Gateway | |
| DNS Server | |
| MAC Address | |

**Step 5:** Check who is logged in and what is running:
```bash
whoami
who
ps aux | head -20
```

---

### Lab 1 Questions

1. What is the difference between RAM and hard disk storage? Why does it matter for cybersecurity?

   _____________________________________________

2. You found a process called `svch0st.exe` (with a zero, not an 'O') in tasklist. Why is this suspicious?

   _____________________________________________

3. Your `netstat` output shows port 3389 in LISTENING state. What protocol is this and what is the security risk?

   _____________________________________________

---

## Lab 2: Operating System Navigation and Security (Week 1-2)

### Objective
Navigate both Windows and Linux file systems, manage users, and identify security-relevant directories.

---

### Lab 2A: Windows File System Exploration

**Step 1:** Using CMD, navigate the file system:
```cmd
cd C:\
dir
cd Windows
dir
cd System32
dir | more
```

**Step 2:** Check for recently modified files in Temp (common malware location):
```cmd
dir C:\Windows\Temp /O:D
dir %TEMP% /O:D
```
- Were there any files? What were they? ___________

**Step 3:** View user accounts on the system:
```cmd
net user
net localgroup administrators
```
- List the user accounts: ___________
- Who is in the Administrators group? ___________

**Step 4:** Check startup programs (malware often adds itself here):
```cmd
reg query HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Run
```
- List what you find: ___________

---

### Lab 2B: Linux Permissions Exercise

**Step 1:** Create a directory and files for practice:
```bash
mkdir ~/security_lab
cd ~/security_lab
touch secret_data.txt public_info.txt script.sh
ls -la
```

**Step 2:** Modify permissions:
```bash
# Make secret_data.txt readable only by owner
chmod 600 secret_data.txt

# Make public_info.txt readable by everyone
chmod 644 public_info.txt

# Make script.sh executable
chmod 755 script.sh

ls -la
```

**Complete the table:**

| File | Permission String | Numeric | Who can read? | Who can write? | Who can execute? |
|---|---|---|---|---|---|
| `secret_data.txt` | | 600 | | | |
| `public_info.txt` | | 644 | | | |
| `script.sh` | | 755 | | | |

**Step 3:** Explore the critical `/etc` directory:
```bash
ls /etc | head -20
cat /etc/passwd | head -5
cat /etc/hostname
cat /etc/os-release
```

> **Note:** `/etc/passwd` is a list of user accounts. In older systems it contained password hashes — now those are in `/etc/shadow` which requires root to read.

**Step 4:** Check the log files (crucial for security investigations):
```bash
ls /var/log
# On Ubuntu:
sudo tail -20 /var/log/auth.log
# Or on older systems:
sudo tail -20 /var/log/secure
```
- What types of events do you see? ___________

---

### Lab 2 Questions

1. What does `chmod 777` do to a file and why is it a security risk?

   _____________________________________________

2. In Linux, what is the difference between `/etc/passwd` and `/etc/shadow`?

   _____________________________________________

3. Why should you regularly check `C:\Windows\Temp` and startup registry keys on a Windows computer?

   _____________________________________________

---

## Lab 3: Number Systems Conversion (Week 2)

### Objective
Convert between decimal, binary, and hexadecimal number systems by hand and verify with Python.

---

### Lab 3A: Manual Conversions

**Convert the following Decimal numbers to Binary:**

| Decimal | Binary | Working (show your division steps) |
|---|---|---|
| 10 | | |
| 27 | | |
| 65 | | |
| 128 | | |
| 200 | | |

**Convert the following Binary numbers to Decimal:**

| Binary | Decimal | Working (show positional values) |
|---|---|---|
| 0000 1010 | | |
| 0001 1001 | | |
| 0100 0001 | | |
| 1000 0000 | | |
| 1100 1000 | | |

**Convert Decimal to Hexadecimal:**

| Decimal | Hexadecimal |
|---|---|
| 10 | |
| 16 | |
| 255 | |
| 160 | |
| 172 | |

**Convert Binary to Hexadecimal (group into 4-bit nibbles):**

| Binary | Grouped | Hexadecimal |
|---|---|---|
| 10101010 | 1010 1010 | |
| 11001101 | | |
| 11111111 | | |
| 00001010 | | |

---

### Lab 3B: Real-World Application — IP Addresses in Binary

An IPv4 address like `192.168.10.5` is actually four 8-bit binary numbers.

**Convert this IP address to binary:**

| Octet | Decimal | Binary |
|---|---|---|
| 1st | 192 | |
| 2nd | 168 | |
| 3rd | 10 | |
| 4th | 5 | |

**Full binary IP:** __________.__________.__________.__________

---

### Lab 3C: Verify with Python

Open Python (type `python3` in terminal) and verify your answers:

```python
# Decimal to Binary
print(bin(27))       # Should show 0b11011
print(bin(200))      # Should show 0b11001000

# Binary to Decimal
print(int('00011001', 2))  # Should show 25

# Decimal to Hexadecimal
print(hex(255))      # Should show 0xff
print(hex(172))      # Should show 0xac

# Hexadecimal to Decimal
print(int('ff', 16)) # Should show 255

# IP address in binary
ip = "192.168.10.5"
for octet in ip.split('.'):
    print(f"{octet} = {int(octet):08b}")
```

**Did your manual answers match Python's output?** ___________

**If not, identify and correct your mistakes:**

_____________________________________________

---

### Lab 3D: Boolean Logic

**Complete these truth tables:**

**NOT:**
| A | NOT A |
|---|---|
| 0 | |
| 1 | |

**AND:**
| A | B | A AND B |
|---|---|---|
| 0 | 0 | |
| 0 | 1 | |
| 1 | 0 | |
| 1 | 1 | |

**OR:**
| A | B | A OR B |
|---|---|---|
| 0 | 0 | |
| 0 | 1 | |
| 1 | 0 | |
| 1 | 1 | |

**XOR:**
| A | B | A XOR B |
|---|---|---|
| 0 | 0 | |
| 0 | 1 | |
| 1 | 0 | |
| 1 | 1 | |

**XOR Encryption Exercise:**

```python
# Run this in Python to see XOR encryption in action
message = ord('Z')          # ASCII value of letter 'Z' = 90
key = 0b10101010            # Our encryption key

encrypted = message ^ key   # XOR to encrypt
decrypted = encrypted ^ key # XOR again to decrypt

print(f"Original:  {message} ({chr(message)})")
print(f"Key:       {key} ({bin(key)})")
print(f"Encrypted: {encrypted} ({bin(encrypted)})")
print(f"Decrypted: {decrypted} ({chr(decrypted)})")
```

**What did you observe?** ___________

---

## Lab 4: Networking Fundamentals (Week 3)

### Objective
Analyse network traffic, understand IP addressing, and use network diagnostic tools.

---

### Lab 4A: Network Diagnostics

**Step 1: Ping — test connectivity**
```bash
# Test connectivity to Google's DNS (requires internet)
ping -c 4 8.8.8.8

# Test your own machine (loopback)
ping -c 4 127.0.0.1

# Test your gateway
ping -c 4 [your default gateway IP]
```

Record results:

| Target | Packets Sent | Packets Received | Average Response Time |
|---|---|---|---|
| 8.8.8.8 | | | |
| 127.0.0.1 | | | |
| Default Gateway | | | |

**Step 2: Traceroute — see the path packets take**
```bash
# Linux
traceroute 8.8.8.8

# Windows
tracert 8.8.8.8
```

- How many hops to reach 8.8.8.8? ___________
- What do you notice about response times as hops increase? ___________

**Step 3: DNS Lookup**
```bash
# Linux
nslookup zamtel.co.zm
dig zamtel.co.zm

# Windows
nslookup zamtel.co.zm
```

| Domain | IP Address Returned |
|---|---|
| zamtel.co.zm | |
| google.com | |
| zicta.org.zm | |

---

### Lab 4B: Port and Service Investigation

**Step 1:** Check what services are listening on your machine:

```bash
# Linux
netstat -tuln
# or
ss -tuln

# Windows
netstat -an | findstr LISTENING
```

| Port | State | Service (guess or research) |
|---|---|---|
| | LISTENING | |
| | LISTENING | |
| | LISTENING | |

**Step 2:** Identify suspicious ports

Research these ports online and fill in the table:

| Port | Protocol | Legitimate Use | Potential Misuse |
|---|---|---|---|
| 4444 | | | |
| 1337 | | | |
| 31337 | | | |
| 8080 | | | |

---

### Lab 4C: Subnet Calculation

Given the IP address `192.168.50.0/24`:

1. What is the subnet mask? ___________
2. What is the network address? ___________
3. What is the broadcast address? ___________
4. How many usable host addresses are there? ___________
5. What is the first usable host address? ___________
6. What is the last usable host address? ___________

Given the IP address `10.0.0.0/8`:

1. What is the subnet mask? ___________
2. How many host addresses are possible? ___________

---

### Lab 4D: Scripting — Network Health Check Script

Write a Bash script that:
1. Displays your IP address and MAC address
2. Pings the default gateway and reports if it is reachable
3. Lists all open listening ports
4. Saves the output to a file called `network_report.txt`

**Template to complete:**

```bash
#!/bin/bash
# Network Health Check Script
# Student: ________________
# Date: ________________

echo "===== NETWORK HEALTH REPORT ====="
echo "Date: $(date)"
echo ""

echo "--- IP Configuration ---"
# ADD COMMAND TO SHOW IP ADDRESS HERE


echo ""
echo "--- Gateway Connectivity ---"
GATEWAY=$(ip route | grep default | awk '{print $3}')
echo "Default Gateway: $GATEWAY"
# ADD PING COMMAND HERE (ping 4 times)


echo ""
echo "--- Open Listening Ports ---"
# ADD COMMAND TO SHOW LISTENING PORTS HERE


echo "===== END OF REPORT =====" 

# Save to file
# ADD COMMAND TO SAVE THIS REPORT TO network_report.txt
```

**Completed script:**

```bash
#!/bin/bash
# Write your completed version here
```

---

### Lab 4 Questions

1. What is the purpose of the loopback address `127.0.0.1`?

   _____________________________________________

2. Why is it important to know which ports are open on a computer?

   _____________________________________________

3. During a traceroute to 8.8.8.8, one of the hops showed `* * *` instead of a response time. What could this mean?

   _____________________________________________

4. A company in Lusaka uses the IP range `10.10.50.0/24` for its internal network. Can this IP range be seen on the public internet? Why or why not?

   _____________________________________________

---

## Lab 5: Capstone Mini-Exercise — Putting It All Together (Week 3)

### Scenario

> You have just been hired as an IT Security Assistant at a small company in Lusaka. Your manager asks you to do a basic security check on a new Windows workstation before it goes into production.

### Your Tasks

**Task 1: System Inventory**
Run commands to document:
- OS version and patch level
- Amount of RAM and disk space
- IP address, MAC address, default gateway
- All user accounts and which are administrators

**Task 2: Running Services Check**
- List all listening ports
- Identify any ports that should NOT be open on a normal workstation (e.g., Telnet port 23, RDP 3389 if not needed)

**Task 3: Suspicious Files Check**
- Check `C:\Windows\Temp` for unusual files
- Check the startup registry key for unexpected entries

**Task 4: Network Connectivity**
- Ping the default gateway — record response time
- Perform a DNS lookup for `google.com`

**Task 5: Report**

Write a one-page security report covering:
1. System details (from Task 1)
2. Open ports — any concerns?
3. Startup entries — any concerns?
4. Temp directory — anything suspicious?
5. Overall recommendation: Is this workstation ready for production use?

---

*Labs designed for EduTrack Cybersecurity Certificate Program — Zambia*
*Next: Module 1 Quiz → `MODULE1_QUIZ.md`*
