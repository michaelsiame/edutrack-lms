# Module 1: Instructor Lesson Plan
## Foundations of Computing and Mathematics

---

> **Program:** Certificate in Cybersecurity
> **Audience:** Instructors / Facilitators
> **Duration:** 3 Weeks (Weeks 1–3) | ~72 contact hours

---

## Module Overview

This module lays the foundation for everything that follows in the program. Students arrive with varying computer literacy levels. The goal is to bring everyone to a common baseline — understanding how computers work, how to navigate operating systems, how to think logically, and how networks function — before introducing actual cybersecurity content in Module 2.

**Key principle for delivery:** Connect every concept to cybersecurity. Never let students think they are in a "basic computing class." Every topic taught here has a direct security implication — make that explicit at every step.

---

## Learning Outcomes

By the end of this module, students will be able to:

1. Identify and describe core hardware components and explain their security relevance
2. Navigate Windows and Linux operating systems using the command line
3. Write basic pseudocode and simple Python/Bash scripts
4. Convert between binary, decimal, and hexadecimal number systems
5. Explain the OSI and TCP/IP models and identify where common attacks occur
6. Describe IP addressing, subnetting basics, and key network protocols

---

## Week-by-Week Schedule

### Week 1: Computer Fundamentals and Operating Systems

| Day | Duration | Topic | Activity | Resources |
|---|---|---|---|---|
| Day 1 | 3 hrs | Introduction, IPOS model, Hardware | Lecture + open computer case demo | Lecture notes §1 |
| Day 2 | 3 hrs | Windows OS, CMD navigation | Lab 1A + Lab 2A | Lab exercises |
| Day 3 | 3 hrs | Linux OS, Terminal basics | Lab 1B + Lab 2B | Lab exercises |
| Day 4 | 3 hrs | Linux file permissions, security directories | Lab 2B continued | Lab exercises |
| Day 5 | 3 hrs | Week 1 review + Q&A | Group discussion, pair exercises | Notes §1–2 |

**Week 1 Total: ~15 hours**

---

### Week 2: Programming Logic and Mathematics

| Day | Duration | Topic | Activity | Resources |
|---|---|---|---|---|
| Day 6 | 3 hrs | Variables, data types, pseudocode | Lecture + Python REPL exercises | Notes §3 |
| Day 7 | 3 hrs | Control flow (if/else, while loops) | Coding exercises — login system | Notes §3 |
| Day 8 | 3 hrs | Functions, Bash scripting intro | Lab 4D (script writing) | Lab exercises |
| Day 9 | 3 hrs | Number systems — binary, hex | Lab 3A + Lab 3B | Notes §4, Lab 3 |
| Day 10 | 3 hrs | Boolean algebra, XOR in crypto | Lab 3C + Lab 3D, Python demo | Notes §4 |

**Week 2 Total: ~15 hours**

---

### Week 3: Networking Essentials and Assessment

| Day | Duration | Topic | Activity | Resources |
|---|---|---|---|---|
| Day 11 | 3 hrs | OSI model, TCP/IP model | Lecture, layer-mapping game | Notes §5 |
| Day 12 | 3 hrs | IP addressing, DHCP, DNS | Lab 4A + Lab 4B | Lab exercises |
| Day 13 | 3 hrs | Subnetting basics, key protocols/ports | Lab 4C + subnet worksheet | Notes §5 |
| Day 14 | 3 hrs | Capstone lab exercise (Lab 5) | Lab 5 — full system check | Lab exercises |
| Day 15 | 3 hrs | **MODULE 1 QUIZ** | Exam (90 minutes) + debrief | Quiz paper |

**Week 3 Total: ~15 hours**

---

## Detailed Teaching Notes

### Topic 1: Computer Hardware — Teaching Tips

**Common student misconception:** "More RAM = faster computer, always."
- Clarify: RAM helps with multitasking. A computer with 16GB RAM but a slow CPU won't encrypt files fast. A computer with a fast CPU but 2GB RAM will struggle running a SIEM tool.

**Security angle to emphasise:**
- Physical access = game over. If an attacker can touch the machine, they can boot from USB, bypass passwords, pull the RAM and read it (cold boot attack). This is why physical security matters.
- Every component has a security implication — RAM (fileless malware), storage (forensic evidence), NIC (MAC spoofing), BIOS (bootkit attacks).

**Suggested demo:** Open up a non-working computer and identify each component. Let students touch and identify RAM, CPU, hard drive, NIC.

---

### Topic 2: Operating Systems — Teaching Tips

**Windows:**
- Students are likely most familiar with Windows GUI but rarely with CMD.
- Spend time making CMD feel natural — run `ipconfig`, `netstat`, `tasklist` repeatedly until students are comfortable.
- Highlight that most malware investigation on Windows desktops uses these exact commands.

**Linux:**
- Many students will feel intimidated. Reassure them: "If you can type, you can use Linux."
- Zambian context: Most Zambian servers (ZICTA, banks, mobile network operators) run Linux. Learning it is directly employable.
- Start with basic navigation before permissions.

**Common misconception:** "Linux is only for experts."
- Counter: Android phones run Linux. Most of the internet runs on Linux servers. It is everywhere.

**Suggested demo:** Show the same task done in both Windows and Linux — e.g., "show IP address" → `ipconfig` vs `ip a`. Students see the parallel.

---

### Topic 3: Programming Logic — Teaching Tips

**Start with pseudocode before any real code.** Students who have never programmed will freeze when they see code syntax. Pseudocode lets them think logically first.

**Use cybersecurity examples exclusively:**
- Variables → storing passwords, IPs, user IDs
- Loops → login attempt counters, port scanners
- Conditionals → access control logic
- Functions → reusable security checks

**Python REPL for instant feedback:** Have students type directly into the Python interactive shell (`python3`) first. No files, no saving — just seeing immediate results builds confidence.

**Bash scripting:** Keep it simple — focus on the network health check script (Lab 4D). The goal is automation thinking, not becoming a developer.

---

### Topic 4: Number Systems — Teaching Tips

**Students who struggle:** Work through decimal-to-binary division step by step on the board multiple times. Use a table with column headers (128 | 64 | 32 | 16 | 8 | 4 | 2 | 1) and have students "fill in" bits.

**Make it visual:**

```
Is 45 ≥ 128? No → 0
Is 45 ≥  64? No → 0
Is 45 ≥  32? Yes → 1, remainder = 45-32 = 13
Is 13 ≥  16? No → 0
Is 13 ≥   8? Yes → 1, remainder = 13-8 = 5
Is  5 ≥   4? Yes → 1, remainder = 5-4 = 1
Is  1 ≥   2? No → 0
Is  1 ≥   1? Yes → 1

Result: 0010 1101
```

**Hexadecimal shortcut:** Emphasise the "group into 4 bits" method. This is the fastest real-world technique.

**XOR:** Do not go deep into cryptographic theory. The key insight: "XOR with the same key twice = original value." Show it in Python. That's enough for Module 1.

---

### Topic 5: Networking — Teaching Tips

**OSI model — the hardest part:** Students memorise the layers but struggle to understand them. Use the analogy of sending a letter in Zambia:

| OSI Layer | Letter Analogy |
|---|---|
| Application | What you write in the letter |
| Presentation | Language you write it in |
| Session | The conversation / correspondence thread |
| Transport | Splitting a long letter into multiple envelopes |
| Network | Routing from Lusaka → Kitwe → destination |
| Data Link | The Zampost truck route between cities |
| Physical | The roads and vehicles |

**TCP vs UDP:** Use the analogy of:
- TCP = Registered mail (confirmation required, retries if not received)
- UDP = Dropping a flyer through the door (no confirmation, fast)

**Zambian context for ports:**
- Port 443 (HTTPS) — when students use Airtel Money online, Zanaco internet banking, or buy data bundles, this port protects their data
- Port 80 (HTTP) — any site using HTTP exposes their data to anyone on the same network (e.g., shared WiFi at a café in Lusaka)

---

## Differentiation Strategies

### For Advanced Students
- Ask them to explain concepts back to the class (peer teaching)
- Give extension tasks: "Write a Bash script that scans ports 20–443 and reports which are open"
- Introduce Wireshark for packet capture as a preview of Module 2

### For Struggling Students
- Pair with stronger students during labs
- Provide the reference tables (port numbers, conversion tables) during assessments
- Allow extra time on number conversion exercises
- Focus on practical comfort with CLI over theoretical depth

---

## Equipment and Preparation Checklist

| Item | Required | Notes |
|---|---|---|
| Computers with Windows | Yes | At least one per student, or pairs |
| VirtualBox + Ubuntu ISO | Yes | Pre-install if possible to save time |
| Python 3 installed | Yes | Test before class: `python3 --version` |
| Network access (internet) | Preferred | For DNS labs and traceroute |
| Printed lab sheets | Recommended | Students write answers on paper |
| Printed quiz papers | Yes | Week 3 Day 15 |
| Whiteboard/blackboard | Yes | For manual binary conversion demos |
| A non-working computer to open | Optional | Very effective for hardware demo |

---

## Assessment Summary

| Assessment | Weight | When |
|---|---|---|
| Lab 1–4 participation and submission | 20% | Weeks 1–3 ongoing |
| Lab 5 Capstone Report | 20% | Week 3 Day 14 |
| Module 1 Quiz | 60% | Week 3 Day 15 |

**Pass mark:** 50% overall

---

## Module 1 to Module 2 Bridge

On the final day (after the quiz), introduce Module 2 with a hook:

> "Now that you know how computers work, how operating systems run, and how networks communicate — in Module 2, we are going to start looking at how all of this gets attacked. Every concept we covered here is something an attacker will try to exploit."

Preview the key Module 2 topics and connect them back:
- "Remember port 23 Telnet? That's a vulnerability."
- "Remember XOR? That's how basic encryption works — and how some malware hides."
- "Remember the `/tmp` and `C:\Temp` directories? Malware loves those."

---

*Lesson plan prepared for EduTrack Cybersecurity Certificate Program — Zambia*
