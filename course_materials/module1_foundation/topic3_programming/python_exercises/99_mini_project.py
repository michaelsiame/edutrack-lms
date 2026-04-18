#!/usr/bin/env python3
"""
MINI PROJECT: Network Security Analyzer
Topic 3: Introduction to Programming Logic - Capstone Exercise

This mini project brings together all the concepts learned:
- Variables and data types
- Operators
- Conditionals
- Loops
- Functions
- Data structures (lists, dictionaries)

Your task: Complete the functions marked with "TODO"
"""


def analyze_network_traffic(packets):
    """
    Analyze network traffic packets and identify suspicious activity.
    
    Args:
        packets: List of dictionaries with keys:
                 - src_ip: source IP address
                 - dst_ip: destination IP address  
                 - port: destination port
                 - size: packet size in bytes
    
    Returns:
        Dictionary with analysis results
    """
    print("=" * 60)
    print("NETWORK TRAFFIC ANALYZER")
    print("=" * 60)
    
    # TODO 1: Initialize counters
    # Create variables to count:
    # - total_packets
    # - total_bytes
    # - unique_source_ips (use a set)
    # - unique_destination_ips (use a set)
    
    total_packets = 0  # Replace with your code
    total_bytes = 0    # Replace with your code
    unique_source_ips = set()  # Replace with your code
    unique_destination_ips = set()  # Replace with your code
    
    # Suspicious activity tracking
    large_packets = []  # Packets > 1000 bytes
    suspicious_ports = []  # Packets to ports: 23 (Telnet), 21 (FTP)
    
    # TODO 2: Loop through packets and analyze each one
    # For each packet:
    # - Increment total_packets
    # - Add packet size to total_bytes
    # - Add src_ip to unique_source_ips
    # - Add dst_ip to unique_destination_ips
    # - If packet size > 1000, add to large_packets
    # - If port is 23 or 21, add to suspicious_ports
    
    for packet in packets:
        # Your code here
        pass  # Remove this line when you add your code
    
    # TODO 3: Calculate statistics
    avg_packet_size = 0  # Replace: total_bytes / total_packets (if > 0)
    
    # Build result dictionary
    results = {
        "total_packets": total_packets,
        "total_bytes": total_bytes,
        "avg_packet_size": avg_packet_size,
        "unique_sources": len(unique_source_ips),
        "unique_destinations": len(unique_destination_ips),
        "large_packet_count": len(large_packets),
        "suspicious_port_count": len(suspicious_ports),
        "suspicious_activity": large_packets + suspicious_ports
    }
    
    return results


def generate_security_report(analysis_results, network_name="Corporate Network"):
    """
    Generate a formatted security report from analysis results.
    
    Args:
        analysis_results: Dictionary from analyze_network_traffic()
        network_name: Name of the network being analyzed
    
    Returns:
        Formatted report string
    """
    # TODO 4: Create a formatted report string
    # Include:
    # - Network name
    # - All statistics from analysis_results
    # - Risk level assessment (HIGH if suspicious_port_count > 0)
    
    report = f"""
{'='*60}
SECURITY ANALYSIS REPORT
{'='*60}
Network: {network_name}

STATISTICS:
- Total Packets: {analysis_results['total_packets']}
- Total Data: {analysis_results['total_bytes']} bytes
- Average Packet Size: {analysis_results['avg_packet_size']:.2f} bytes
- Unique Source IPs: {analysis_results['unique_sources']}
- Unique Destination IPs: {analysis_results['unique_destinations']}

SECURITY FINDINGS:
- Large Packets (>1000 bytes): {analysis_results['large_packet_count']}
- Suspicious Port Activity: {analysis_results['suspicious_port_count']}

RISK LEVEL: {'HIGH' if analysis_results['suspicious_port_count'] > 0 else 'LOW'}
{'='*60}
"""
    return report


def check_compliance(password, policy):
    """
    Check if a password meets compliance policy requirements.
    
    Args:
        password: The password to check
        policy: Dictionary with policy requirements:
                - min_length: minimum character length
                - require_uppercase: True/False
                - require_numbers: True/False
                - require_special: True/False
    
    Returns:
        Tuple of (is_compliant: bool, violations: list)
    """
    # TODO 5: Implement password compliance checking
    # Check each policy requirement and collect violations
    
    violations = []
    
    # Check min_length
    # Your code here
    
    # Check require_uppercase (if policy says it's required)
    # Hint: use any(char.isupper() for char in password)
    # Your code here
    
    # Check require_numbers
    # Your code here
    
    # Check require_special
    # Hint: special_chars = "!@#$%^&*()_+-=[]{}|;:,.<>?"
    # Your code here
    
    is_compliant = len(violations) == 0
    return is_compliant, violations


def main():
    """Main function to run the network security analyzer"""
    
    # Sample network traffic data
    sample_packets = [
        {"src_ip": "192.168.1.10", "dst_ip": "8.8.8.8", "port": 443, "size": 512},
        {"src_ip": "192.168.1.11", "dst_ip": "8.8.8.8", "port": 443, "size": 1024},
        {"src_ip": "192.168.1.12", "dst_ip": "10.0.0.5", "port": 23, "size": 64},   # Telnet!
        {"src_ip": "192.168.1.10", "dst_ip": "172.16.0.1", "port": 22, "size": 2048},
        {"src_ip": "10.0.0.50", "dst_ip": "192.168.1.5", "port": 21, "size": 1500},   # FTP!
        {"src_ip": "192.168.1.15", "dst_ip": "8.8.8.8", "port": 53, "size": 256},
        {"src_ip": "192.168.1.16", "dst_ip": "8.8.4.4", "port": 443, "size": 1800},
        {"src_ip": "192.168.1.10", "dst_ip": "192.168.1.20", "port": 445, "size": 4096},
    ]
    
    # Sample password policy
    password_policy = {
        "min_length": 8,
        "require_uppercase": True,
        "require_numbers": True,
        "require_special": True
    }
    
    # Sample passwords to check
    passwords_to_check = [
        "password",
        "Password1",
        "MyStr0ng!Pass",
        "admin123",
        "Short1!"
    ]
    
    print("\n" + "=" * 60)
    print("NETWORK TRAFFIC ANALYSIS")
    print("=" * 60)
    
    # Run network analysis
    analysis = analyze_network_traffic(sample_packets)
    
    # Print report
    report = generate_security_report(analysis)
    print(report)
    
    # Check password compliance
    print("\n" + "=" * 60)
    print("PASSWORD COMPLIANCE CHECK")
    print("=" * 60)
    
    for pwd in passwords_to_check:
        compliant, issues = check_compliance(pwd, password_policy)
        status = "✓ COMPLIANT" if compliant else "✗ NON-COMPLIANT"
        print(f"\nPassword: {'*' * len(pwd)}")
        print(f"Status: {status}")
        if issues:
            print(f"Issues: {', '.join(issues)}")
    
    print("\n" + "=" * 60)
    print("YOUR TASK")
    print("=" * 60)
    print("""
Complete the following functions in this file:

1. analyze_network_traffic() - Count and categorize network packets
2. generate_security_report() - Create formatted security report
3. check_compliance() - Validate passwords against policy

Each function has TODO comments to guide you.

HINTS:
- Use len() to get the size of lists and sets
- Use set.add() to add items to a set
- Use list.append() to add items to a list
- Use 'in' to check if an item exists in a list
- Use if/elif/else for decision making
- Use for loops to iterate through data

Once complete, run this script to test your implementation!
    """)


# SOLUTION (for instructor reference - students should not look until attempting!)
# ============================================================================

def analyze_network_traffic_solution(packets):
    """Solution for TODO 1-3"""
    total_packets = len(packets)
    total_bytes = 0
    unique_source_ips = set()
    unique_destination_ips = set()
    
    large_packets = []
    suspicious_ports = []
    
    for packet in packets:
        total_bytes += packet["size"]
        unique_source_ips.add(packet["src_ip"])
        unique_destination_ips.add(packet["dst_ip"])
        
        if packet["size"] > 1000:
            large_packets.append(packet)
        
        if packet["port"] in [23, 21]:  # Telnet or FTP
            suspicious_ports.append(packet)
    
    avg_packet_size = total_bytes / total_packets if total_packets > 0 else 0
    
    return {
        "total_packets": total_packets,
        "total_bytes": total_bytes,
        "avg_packet_size": avg_packet_size,
        "unique_sources": len(unique_source_ips),
        "unique_destinations": len(unique_destination_ips),
        "large_packet_count": len(large_packets),
        "suspicious_port_count": len(suspicious_ports),
        "suspicious_activity": large_packets + suspicious_ports
    }


def check_compliance_solution(password, policy):
    """Solution for TODO 5"""
    violations = []
    
    if len(password) < policy["min_length"]:
        violations.append(f"Minimum length is {policy['min_length']} characters")
    
    if policy["require_uppercase"] and not any(c.isupper() for c in password):
        violations.append("Must contain uppercase letter")
    
    if policy["require_numbers"] and not any(c.isdigit() for c in password):
        violations.append("Must contain a number")
    
    if policy["require_special"]:
        special_chars = "!@#$%^&*()_+-=[]{}|;:,.<>?"
        if not any(c in special_chars for c in password):
            violations.append("Must contain special character")
    
    return len(violations) == 0, violations


if __name__ == "__main__":
    main()
