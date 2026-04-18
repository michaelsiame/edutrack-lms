#!/usr/bin/env python3
"""Generate educational diagrams for Module 1 PPTX using Pillow."""

from PIL import Image, ImageDraw, ImageFont
import os

ASSETS = "/Users/michael/Documents/Programming/edutrack-lms/module1_pptx_assets"
os.makedirs(ASSETS, exist_ok=True)

# Dimensions (16:9 friendly)
W, H = 1280, 720

def get_font(size):
    # Try common system fonts
    candidates = [
        "/System/Library/Fonts/Helvetica.ttc",
        "/System/Library/Fonts/HelveticaNeue.ttc",
        "/Library/Fonts/Arial.ttf",
        "/System/Library/Fonts/SFNS.ttf",
    ]
    for c in candidates:
        if os.path.exists(c):
            return ImageFont.truetype(c, size)
    return ImageFont.load_default()

def save(img, name):
    img.save(os.path.join(ASSETS, name))
    print(f"Saved {name}")

# ---------- 1. IPOS Cycle ----------
img = Image.new("RGB", (W, H), "#F5F7FA")
draw = ImageDraw.Draw(img)
font_big = get_font(36)
font_small = get_font(24)
font_note = get_font(20)

centers = [(200, 360), (500, 200), (800, 200), (1100, 360)]
colors = ["#1A237E", "#F44C00", "#1A237E", "#F44C00"]
labels = ["INPUT", "PROCESSING", "OUTPUT", "STORAGE"]
extras = ["Keyboard, Mouse", "CPU executes", "Monitor, Printer", "HDD, SSD, USB"]

for (x, y), col, lab, extra in zip(centers, colors, labels, extras):
    r = 70
    draw.ellipse([x-r, y-r, x+r, y+r], fill=col, outline="white", width=4)
    draw.text((x, y-15), lab, font=font_small, fill="white", anchor="mm")
    draw.text((x, y+45), extra, font=font_note, fill="#333333", anchor="mm")

# arrows
for i in range(3):
    x1, y1 = centers[i][0]+70, centers[i][1]
    x2, y2 = centers[i+1][0]-70, centers[i+1][1]
    draw.line([(x1, y1), (x2, y2)], fill="#333", width=4)
    # arrowhead
    draw.polygon([(x2, y2), (x2-10, y2-5), (x2-10, y2+5)], fill="#333")

# close the loop
x1, y1 = centers[3][0], centers[3][1]+70
x2, y2 = centers[0][0], centers[0][1]+70
draw.line([(x1, y1), (x2, y2)], fill="#333", width=4)
draw.polygon([(x2, y2), (x2+5, y2-10), (x2-5, y2-10)], fill="#333")

draw.text((W//2, 600), "Every stage is an attack surface in cybersecurity", font=font_note, fill="#1A237E", anchor="mm")
save(img, "ipos_cycle.png")

# ---------- 2. Hardware Components ----------
img = Image.new("RGB", (W, H), "#F5F7FA")
draw = ImageDraw.Draw(img)
font_title = get_font(32)
font_body = get_font(22)

# Title
draw.text((W//2, 30), "Computer Hardware Components & Security Relevance", font=font_title, fill="#1A237E", anchor="mm")

boxes = [
    (100, 100, "CPU", "Brain of the computer", "Spectre/Meltdown\nside-channel attacks", "#1A237E"),
    (680, 100, "RAM", "Temporary storage", "Fileless malware lives here", "#F44C00"),
    (100, 400, "Storage", "HDD / SSD / USB", "Encryption at rest\nprotects stolen data", "#1A237E"),
    (680, 400, "NIC", "Network Interface Card", "MAC spoofing\n& network ID", "#F44C00"),
]

for x, y, title, subtitle, threat, col in boxes:
    draw.rounded_rectangle([x, y, x+500, y+260], radius=15, fill=col, outline="white", width=4)
    draw.text((x+250, y+40), title, font=font_big, fill="white", anchor="mm")
    draw.text((x+250, y+90), subtitle, font=font_body, fill="#EEEEEE", anchor="mm")
    draw.text((x+250, y+160), "⚠ " + threat, font=font_body, fill="#FFD180", anchor="mm")

save(img, "hardware_components.png")

# ---------- 3. Von Neumann Architecture ----------
img = Image.new("RGB", (W, H), "#F5F7FA")
draw = ImageDraw.Draw(img)

def draw_box(x, y, w, h, text, col):
    draw.rounded_rectangle([x, y, x+w, y+h], radius=8, fill=col, outline="white", width=3)
    draw.text((x+w//2, y+h//2), text, font=font_body, fill="white", anchor="mm")

def draw_arrow(x1, y1, x2, y2):
    draw.line([(x1, y1), (x2, y2)], fill="#333", width=3)
    # simple arrowhead
    dx, dy = x2-x1, y2-y1
    if abs(dx) > abs(dy):
        sign = 1 if dx > 0 else -1
        draw.polygon([(x2, y2), (x2-8*sign, y2-4), (x2-8*sign, y2+4)], fill="#333")
    else:
        sign = 1 if dy > 0 else -1
        draw.polygon([(x2, y2), (x2-4, y2-8*sign), (x2+4, y2-8*sign)], fill="#333")

draw.text((W//2, 30), "Von Neumann Architecture", font=font_title, fill="#1A237E", anchor="mm")

# CPU box
draw_box(540, 120, 200, 80, "CPU\n(ALU + Control Unit)", "#1A237E")
# Memory box
draw_box(540, 280, 200, 80, "Memory (RAM)", "#F44C00")
# Storage box
draw_box(540, 440, 200, 80, "Storage", "#1A237E")
# Input box
draw_box(100, 280, 150, 80, "Input", "#555")
# Output box
draw_box(1030, 280, 150, 80, "Output", "#555")

# arrows
draw_arrow(250, 320, 540, 160)   # input -> cpu
draw_arrow(640, 200, 640, 280)   # cpu -> memory
draw_arrow(540, 320, 250, 320)   # memory -> input (bidirectional feel)
draw_arrow(740, 320, 1030, 320)  # memory -> output
draw_arrow(640, 360, 640, 440)   # memory -> storage
draw_arrow(640, 440, 640, 360)   # storage -> memory

draw.text((W//2, 580), "Data and instructions share the same memory → Buffer Overflow attacks possible", font=font_note, fill="#1A237E", anchor="mm")
save(img, "von_neumann.png")

# ---------- 4. Windows File System Tree ----------
img = Image.new("RGB", (W, H), "#F5F7FA")
draw = ImageDraw.Draw(img)
draw.text((W//2, 30), "Windows File System Hierarchy", font=font_title, fill="#1A237E", anchor="mm")

tree = [
    ("C:\\", 100, 100, "#1A237E"),
    ("├── Windows\\", 140, 150, "#F44C00"),
    ("├── Program Files\\", 140, 190, "#555"),
    ("├── Users\\", 140, 230, "#555"),
    ("│   └── Chanda\\", 180, 270, "#1A237E"),
    ("│       ├── Desktop\\", 220, 310, "#555"),
    ("│       ├── Documents\\", 220, 350, "#555"),
    ("│       └── Downloads\\", 220, 390, "#F44C00"),
    ("└── Temp\\", 140, 430, "#F44C00"),
]

for text, x, y, col in tree:
    draw.text((x, y), text, font=font_body, fill=col)

notes = [
    ("🔴 System32: malware target", 700, 150),
    ("🔴 AppData: hidden malware persistence", 700, 230),
    ("🔴 Downloads: common delivery point", 700, 350),
    ("🔴 Temp: classic malware hideout", 700, 430),
]
for text, x, y in notes:
    draw.text((x, y), text, font=font_note, fill="#333")

save(img, "windows_fs.png")

# ---------- 5. Linux File System Tree ----------
img = Image.new("RGB", (W, H), "#F5F7FA")
draw = ImageDraw.Draw(img)
draw.text((W//2, 30), "Linux File System Hierarchy", font=font_title, fill="#1A237E", anchor="mm")

tree = [
    ("/", 100, 100, "#1A237E"),
    ("├── /home/", 140, 150, "#555"),
    ("│   └── /home/mulenga/", 180, 190, "#1A237E"),
    ("├── /etc/", 140, 230, "#F44C00"),
    ("├── /var/log/", 140, 270, "#F44C00"),
    ("├── /tmp/", 140, 310, "#F44C00"),
    ("├── /usr/", 140, 350, "#555"),
    ("├── /bin/", 140, 390, "#555"),
    ("└── /root/", 140, 430, "#1A237E"),
]

for text, x, y, col in tree:
    draw.text((x, y), text, font=font_body, fill=col)

notes = [
    ("🔴 Config files & passwords", 700, 230),
    ("🔴 Security goldmine: logs", 700, 270),
    ("🔴 Malware favorite: world-writable", 700, 310),
    ("🔴 Root-only admin home", 700, 430),
]
for text, x, y in notes:
    draw.text((x, y), text, font=font_note, fill="#333")

save(img, "linux_fs.png")

# ---------- 6. Linux Permissions ----------
img = Image.new("RGB", (W, H), "#F5F7FA")
draw = ImageDraw.Draw(img)
draw.text((W//2, 30), "Linux File Permissions", font=font_title, fill="#1A237E", anchor="mm")

# Permission string breakdown
draw.text((100, 100), "-rwxr-xr--  1  mulenga  staff  1024  Mar 2026  script.sh", font=font_body, fill="#1A237E")

# Underlines
draw.rectangle([110, 130, 130, 135], fill="#F44C00")   # -
draw.rectangle([140, 130, 200, 135], fill="#1A237E")   # rwx
draw.rectangle([210, 130, 260, 135], fill="#F44C00")   # r-x
draw.rectangle([270, 130, 310, 135], fill="#1A237E")   # r--

labels = [
    ("File type", 120, 150, "#F44C00"),
    ("Owner (rwx)", 170, 150, "#1A237E"),
    ("Group (r-x)", 235, 150, "#F44C00"),
    ("Others (r--)", 290, 150, "#1A237E"),
]

for text, x, y, col in labels:
    draw.text((x, y), text, font=font_note, fill=col)

# Table
table_y = 220
row_h = 50
cols_x = [100, 300, 500, 700]
headers = ["Permission", "Symbol", "Value", "Example"]
rows = [
    ["Read", "r", "4", "View file contents"],
    ["Write", "w", "2", "Modify file"],
    ["Execute", "x", "1", "Run program/script"],
    ["No permission", "-", "0", "Denied"],
]

for i, h in enumerate(headers):
    draw.rectangle([cols_x[i], table_y, cols_x[i]+180, table_y+row_h], fill="#1A237E", outline="white")
    draw.text((cols_x[i]+90, table_y+row_h//2), h, font=font_note, fill="white", anchor="mm")

for r_idx, row in enumerate(rows):
    y = table_y + (r_idx+1)*row_h
    for c_idx, cell in enumerate(row):
        col = "#FFFFFF" if c_idx % 2 == 0 else "#E8EAF6"
        draw.rectangle([cols_x[c_idx], y, cols_x[c_idx]+180, y+row_h], fill=col, outline="#CCC")
        draw.text((cols_x[c_idx]+90, y+row_h//2), cell, font=font_note, fill="#333", anchor="mm")

draw.text((100, 520), "Example: chmod 755 script.sh", font=font_body, fill="#1A237E")
draw.text((100, 560), "Owner=7 (rwx)  |  Group=5 (r-x)  |  Others=5 (r-x)", font=font_note, fill="#333")

save(img, "linux_permissions.png")

# ---------- 7. Binary Hex Conversion Table ----------
img = Image.new("RGB", (W, H), "#F5F7FA")
draw = ImageDraw.Draw(img)
draw.text((W//2, 30), "Number Systems Quick Reference", font=font_title, fill="#1A237E", anchor="mm")

table_y = 100
row_h = 60
cols_x = [150, 400, 700, 1000]
headers = ["Decimal", "Binary", "Hexadecimal", "Security Use"]
rows = [
    ["0", "0000", "0", "—"],
    ["5", "0101", "5", "—"],
    ["9", "1001", "9", "—"],
    ["10", "1010", "A", "Color codes"],
    ["15", "1111", "F", "MAC addresses"],
    ["16", "0001 0000", "10", "Port ranges"],
    ["255", "1111 1111", "FF", "Max octet / IP"],
]

for i, h in enumerate(headers):
    draw.rectangle([cols_x[i], table_y, cols_x[i]+230, table_y+row_h], fill="#1A237E", outline="white")
    draw.text((cols_x[i]+115, table_y+row_h//2), h, font=font_note, fill="white", anchor="mm")

for r_idx, row in enumerate(rows):
    y = table_y + (r_idx+1)*row_h
    for c_idx, cell in enumerate(row):
        col = "#FFFFFF" if r_idx % 2 == 0 else "#E8EAF6"
        draw.rectangle([cols_x[c_idx], y, cols_x[c_idx]+230, y+row_h], fill=col, outline="#CCC")
        draw.text((cols_x[c_idx]+115, y+row_h//2), cell, font=font_note, fill="#333", anchor="mm")

draw.text((W//2, 620), "Binary → Hex shortcut: Group into 4 bits  |  Example: 1010 1100 = AC", font=font_body, fill="#1A237E", anchor="mm")
save(img, "binary_hex_table.png")

# ---------- 8. OSI vs TCP/IP Comparison ----------
img = Image.new("RGB", (W, H), "#F5F7FA")
draw = ImageDraw.Draw(img)
draw.text((W//2, 30), "OSI Model vs TCP/IP Model", font=font_title, fill="#1A237E", anchor="mm")

# OSI Stack
osi_layers = [
    ("Application", "HTTP, DNS, FTP"),
    ("Presentation", "SSL/TLS, JPEG"),
    ("Session", "NetBIOS, RPC"),
    ("Transport", "TCP, UDP"),
    ("Network", "IP, ICMP, ARP"),
    ("Data Link", "Ethernet, WiFi"),
    ("Physical", "Cables, Radio"),
]

# TCP/IP Stack
tcp_layers = [
    ("Application", "HTTP, HTTPS, DNS, SSH"),
    ("Transport", "TCP, UDP"),
    ("Internet", "IP, ICMP, ARP"),
    ("Network Access", "Ethernet, WiFi"),
]

box_w, box_h = 420, 60
start_y = 120
gap = 10

# Draw OSI
draw.text((310, 80), "OSI Model (7 Layers)", font=font_body, fill="#1A237E", anchor="mm")
for i, (layer, proto) in enumerate(osi_layers):
    y = start_y + i*(box_h+gap)
    col = "#1A237E" if i % 2 == 0 else "#3949AB"
    draw.rounded_rectangle([100, y, 100+box_w, y+box_h], radius=6, fill=col, outline="white", width=2)
    draw.text((110, y+box_h//2), f"L{7-i}. {layer}", font=font_note, fill="white", anchor="lm")
    draw.text((500, y+box_h//2), proto, font=font_note, fill="white", anchor="rm")

# Draw TCP/IP
draw.text((970, 80), "TCP/IP Model (4 Layers)", font=font_body, fill="#F44C00", anchor="mm")
# Map TCP/IP to approximate OSI positions
mapping = [
    (0, 0),    # Application -> L7
    (3, 1),    # Transport -> L4
    (4, 2),    # Internet -> L3
    (5, 3),    # Network Access -> L2-L1
]
for osi_idx, tcp_idx in mapping:
    layer, proto = tcp_layers[tcp_idx]
    y = start_y + osi_idx*(box_h+gap)
    h = box_h * (2 if tcp_idx == 0 else 1) + (gap if tcp_idx == 0 else 0)
    col = "#F44C00" if tcp_idx % 2 == 0 else "#FF7043"
    draw.rounded_rectangle([760, y, 760+box_w, y+h], radius=6, fill=col, outline="white", width=2)
    draw.text((770, y+h//2), layer, font=font_note, fill="white", anchor="lm")
    draw.text((1160, y+h//2), proto, font=font_note, fill="white", anchor="rm")

# Brackets showing mapping
draw.line([(520, 150), (750, 150)], fill="#333", width=2)
draw.line([(520, 390), (750, 390)], fill="#333", width=2)
draw.line([(520, 460), (750, 460)], fill="#333", width=2)
draw.line([(520, 540), (750, 540)], fill="#333", width=2)

draw.text((W//2, 650), "OSI = Theory & Teaching    |    TCP/IP = Real-world Implementation", font=font_note, fill="#333", anchor="mm")
save(img, "osi_tcpip_comparison.png")

# ---------- 9. Network Types ----------
img = Image.new("RGB", (W, H), "#F5F7FA")
draw = ImageDraw.Draw(img)
draw.text((W//2, 30), "Network Types & Security Relevance", font=font_title, fill="#1A237E", anchor="mm")

networks = [
    ("LAN", "Local Area Network", "Office, School lab", "Segment to limit breach spread", "#1A237E"),
    ("WAN", "Wide Area Network", "National backbone", "Encryption essential", "#F44C00"),
    ("WLAN", "Wireless LAN", "Home WiFi, Cafes", "Vulnerable to eavesdropping", "#1A237E"),
    ("VPN", "Virtual Private Network", "Remote work tunnel", "Misconfig = major breach risk", "#F44C00"),
]

for idx, (abbr, full, example, risk, col) in enumerate(networks):
    x = 80 + (idx % 2) * 600
    y = 100 + (idx // 2) * 300
    draw.rounded_rectangle([x, y, x+560, y+260], radius=12, fill=col, outline="white", width=4)
    draw.text((x+280, y+50), abbr, font=font_big, fill="white", anchor="mm")
    draw.text((x+280, y+100), full, font=font_body, fill="#EEEEEE", anchor="mm")
    draw.text((x+280, y+150), f"e.g. {example}", font=font_note, fill="#FFD180", anchor="mm")
    draw.text((x+280, y+200), f"⚠ {risk}", font=font_note, fill="#FFCCBC", anchor="mm")

save(img, "network_types.png")

print("\nAll images generated successfully!")
