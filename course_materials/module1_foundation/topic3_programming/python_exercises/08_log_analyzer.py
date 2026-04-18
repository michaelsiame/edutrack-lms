#!/usr/bin/env python3
"""
Scenario 7.1: Log File Analyzer
Topic 3: Introduction to Programming Logic
"""


def analyze_auth_log(log_entries):
    """
    Analyze authentication logs for brute force attempts
    """
    print("=" * 60)
    print("AUTHENTICATION LOG ANALYZER")
    print("=" * 60)
    
    # Initialize counters
    stats = {
        "total_attempts": len(log_entries),
        "successful": 0,
        "failed": 0,
        "unique_ips": set(),
        "unique_users": set()
    }
    
    # Track failed attempts per IP
    failed_by_ip = {}
    
    for entry in log_entries:
        ip = entry["ip"]
        user = entry["user"]
        status = entry["status"]
        
        # Update statistics
        stats["unique_ips"].add(ip)
        stats["unique_users"].add(user)
        
        if status == "success":
            stats["successful"] += 1
        else:
            stats["failed"] += 1
            failed_by_ip[ip] = failed_by_ip.get(ip, 0) + 1
    
    # Display summary
    print(f"\n📊 SUMMARY")
    print(f"   Total attempts: {stats['total_attempts']}")
    print(f"   Successful: {stats['successful']}")
    print(f"   Failed: {stats['failed']}")
    if stats['total_attempts'] > 0:
        print(f"   Success rate: {(stats['successful']/stats['total_attempts']*100):.1f}%")
    print(f"   Unique IPs: {len(stats['unique_ips'])}")
    print(f"   Unique users: {len(stats['unique_users'])}")
    
    # Identify potential brute force attacks
    print(f"\n🚨 POTENTIAL BRUTE FORCE ATTACKS")
    brute_force_threshold = 3
    attacks_detected = 0
    
    for ip, failed_count in failed_by_ip.items():
        if failed_count >= brute_force_threshold:
            attacks_detected += 1
            print(f"   IP {ip}: {failed_count} failed attempts")
    
    if attacks_detected == 0:
        print("   No brute force attacks detected")
    else:
        print(f"\n   ⚠️  {attacks_detected} IP(s) exceeded threshold of {brute_force_threshold} failures")
    
    return stats, failed_by_ip


# Sample log data
sample_logs = [
    {"timestamp": "08:00:01", "user": "admin", "ip": "192.168.1.10", "status": "success"},
    {"timestamp": "08:05:23", "user": "jdoe", "ip": "192.168.1.25", "status": "success"},
    {"timestamp": "08:15:10", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "08:15:15", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "08:15:22", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "08:15:30", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "08:15:35", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "08:30:00", "user": "asmith", "ip": "192.168.1.30", "status": "success"},
    {"timestamp": "09:00:00", "user": "admin", "ip": "10.0.0.50", "status": "failed"},
    {"timestamp": "09:45:12", "user": "guest", "ip": "172.16.0.5", "status": "failed"},
]

# Run the analysis
if __name__ == "__main__":
    stats, failures = analyze_auth_log(sample_logs)
