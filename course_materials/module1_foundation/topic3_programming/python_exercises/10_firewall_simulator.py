#!/usr/bin/env python3
"""
Scenario 7.3: Simple Firewall Rule Checker
Topic 3: Introduction to Programming Logic
"""


def ip_to_int(ip_string):
    """Convert IP address to integer for comparison"""
    try:
        octets = [int(o) for o in ip_string.split(".")]
        return (octets[0] << 24) + (octets[1] << 16) + (octets[2] << 8) + octets[3]
    except:
        return None


def is_ip_in_range(ip, network, mask):
    """Check if IP is within a network range"""
    ip_int = ip_to_int(ip)
    network_int = ip_to_int(network)
    
    if ip_int is None or network_int is None:
        return False
    
    mask_bits = (0xFFFFFFFF << (32 - mask)) & 0xFFFFFFFF
    return (ip_int & mask_bits) == (network_int & mask_bits)


class SimpleFirewall:
    """Simulate a simple firewall with allow/block rules"""
    
    def __init__(self):
        self.rules = []
        self.log = []
    
    def add_rule(self, action, source=None, destination=None, port=None, protocol="any"):
        """Add a firewall rule"""
        rule = {
            "action": action,
            "source": source,
            "destination": destination,
            "port": port,
            "protocol": protocol
        }
        self.rules.append(rule)
        print(f"✓ Added rule: {action.upper()} {source} → {destination}:{port} ({protocol})")
    
    def check_packet(self, src_ip, dst_ip, dst_port, protocol="tcp"):
        """Check if a packet should be allowed or blocked"""
        
        for rule in self.rules:
            match = True
            
            # Check source
            if rule["source"] and rule["source"] != "any":
                if "/" in rule["source"]:
                    network, mask = rule["source"].split("/")
                    if not is_ip_in_range(src_ip, network, int(mask)):
                        match = False
                elif rule["source"] != src_ip:
                    match = False
            
            # Check destination
            if match and rule["destination"] and rule["destination"] != "any":
                if "/" in rule["destination"]:
                    network, mask = rule["destination"].split("/")
                    if not is_ip_in_range(dst_ip, network, int(mask)):
                        match = False
                elif rule["destination"] != dst_ip:
                    match = False
            
            # Check port
            if match and rule["port"] and rule["port"] != "any":
                if rule["port"] != dst_port:
                    match = False
            
            # Check protocol
            if match and rule["protocol"] != "any":
                if rule["protocol"] != protocol:
                    match = False
            
            if match:
                result = {
                    "action": rule["action"],
                    "src_ip": src_ip,
                    "dst_ip": dst_ip,
                    "port": dst_port,
                    "protocol": protocol,
                    "matched_rule": rule
                }
                self.log.append(result)
                return result
        
        # Default action: block if no rule matches
        return {
            "action": "block",
            "src_ip": src_ip,
            "dst_ip": dst_ip,
            "port": dst_port,
            "protocol": protocol,
            "matched_rule": None,
            "reason": "No matching rule"
        }
    
    def print_rules(self):
        """Display all firewall rules"""
        print("\n📋 FIREWALL RULES")
        print("-" * 70)
        print(f"{'#':<3} {'Action':<8} {'Source':<20} {'Destination':<20} {'Port':<8} {'Protocol':<8}")
        print("-" * 70)
        
        for i, rule in enumerate(self.rules, 1):
            src = rule["source"] or "any"
            dst = rule["destination"] or "any"
            port = str(rule["port"]) if rule["port"] else "any"
            proto = rule["protocol"]
            print(f"{i:<3} {rule['action'].upper():<8} {src:<20} {dst:<20} {port:<8} {proto:<8}")


# Demonstration
if __name__ == "__main__":
    print("=" * 70)
    print("FIREWALL SIMULATOR")
    print("=" * 70)
    
    # Create firewall and add rules
    fw = SimpleFirewall()
    
    # Allow internal traffic
    fw.add_rule("allow", "192.168.1.0/24", "192.168.1.0/24", "any")
    
    # Allow outbound web traffic
    fw.add_rule("allow", "192.168.1.0/24", "any", 80, "tcp")
    fw.add_rule("allow", "192.168.1.0/24", "any", 443, "tcp")
    
    # Allow SSH from specific admin IP
    fw.add_rule("allow", "10.0.0.10", "any", 22, "tcp")
    
    # Block everything else
    fw.add_rule("block", "any", "any", "any")
    
    # Show rules
    fw.print_rules()
    
    # Test some connections
    print("\n🧪 TESTING CONNECTIONS")
    print("-" * 50)
    
    test_packets = [
        ("192.168.1.50", "192.168.1.1", 80, "tcp"),
        ("192.168.1.50", "8.8.8.8", 443, "tcp"),
        ("192.168.1.50", "8.8.8.8", 22, "tcp"),
        ("10.0.0.10", "192.168.1.5", 22, "tcp"),
        ("10.0.0.20", "192.168.1.5", 22, "tcp"),
    ]
    
    for src, dst, port, proto in test_packets:
        result = fw.check_packet(src, dst, port, proto)
        action_emoji = "✅" if result["action"] == "allow" else "❌"
        print(f"{action_emoji} {src} → {dst}:{port} ({proto}) = {result['action'].upper()}")
