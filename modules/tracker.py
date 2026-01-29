#!/usr/bin/env python3
"""
IP Tracker Module for Quantum TrackIP
Advanced tracking and analysis functions
"""

import socket
import json
import requests
import time
from datetime import datetime
import sys
import os

class IPTracker:
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'TrackIP-Quantum/1.0'
        })
        
    def get_public_ip(self):
        """Get public IP address"""
        services = [
            'https://api.ipify.org',
            'https://icanhazip.com',
            'https://checkip.amazonaws.com',
            'https://ifconfig.me/ip'
        ]
        
        for service in services:
            try:
                response = self.session.get(service, timeout=5)
                ip = response.text.strip()
                if self.validate_ip(ip):
                    return ip
            except:
                continue
        return None
    
    def validate_ip(self, ip):
        """Validate IP address format"""
        try:
            socket.inet_aton(ip)
            return True
        except socket.error:
            try:
                socket.inet_pton(socket.AF_INET6, ip)
                return True
            except socket.error:
                return False
    
    def get_ip_info(self, ip):
        """Get comprehensive IP information"""
        info = {
            'ip': ip,
            'type': 'IPv4' if '.' in ip else 'IPv6',
            'is_private': self.is_private_ip(ip),
            'geolocation': {},
            'network': {},
            'security': {}
        }
        
        # Get geolocation
        info['geolocation'] = self.get_geolocation(ip)
        
        # Get network info
        info['network'] = self.get_network_info(ip)
        
        # Get security info
        info['security'] = self.get_security_info(ip)
        
        return info
    
    def is_private_ip(self, ip):
        """Check if IP is private"""
        if '.' in ip:  # IPv4
            octets = list(map(int, ip.split('.')))
            # Check private ranges
            if octets[0] == 10:
                return True
            elif octets[0] == 172 and 16 <= octets[1] <= 31:
                return True
            elif octets[0] == 192 and octets[1] == 168:
                return True
            elif octets[0] == 127:
                return True
            elif octets[0] == 169 and octets[1] == 254:
                return True
        return False
    
    def get_geolocation(self, ip):
        """Get geolocation data from multiple sources"""
        geolocation = {
            'country': 'Unknown',
            'city': 'Unknown',
            'region': 'Unknown',
            'isp': 'Unknown',
            'coordinates': 'Unknown',
            'timezone': 'Unknown'
        }
        
        # Try ip-api.com
        try:
            url = f"http://ip-api.com/json/{ip}"
            response = self.session.get(url, timeout=5)
            data = response.json()
            
            if data['status'] == 'success':
                geolocation['country'] = data.get('country', 'Unknown')
                geolocation['city'] = data.get('city', 'Unknown')
                geolocation['region'] = data.get('regionName', 'Unknown')
                geolocation['isp'] = data.get('isp', 'Unknown')
                geolocation['coordinates'] = f"{data.get('lat', '')}, {data.get('lon', '')}"
                geolocation['timezone'] = data.get('timezone', 'Unknown')
        except:
            pass
        
        return geolocation
    
    def get_network_info(self, ip):
        """Get network information"""
        network_info = {
            'hostname': 'Unknown',
            'asn': 'Unknown',
            'org': 'Unknown'
        }
        
        try:
            # Get hostname
            try:
                hostname = socket.gethostbyaddr(ip)[0]
                network_info['hostname'] = hostname
            except:
                pass
            
            # Get ASN info from ip-api
            url = f"http://ip-api.com/json/{ip}"
            response = self.session.get(url, timeout=5)
            data = response.json()
            
            if data['status'] == 'success':
                network_info['asn'] = data.get('as', 'Unknown')
                network_info['org'] = data.get('org', 'Unknown')
        except:
            pass
        
        return network_info
    
    def get_security_info(self, ip):
        """Get security information"""
        security_info = {
            'blacklisted': False,
            'threat_level': 'Low',
            'proxy': False,
            'vpn': False,
            'tor': False
        }
        
        # Check blacklists
        blacklists = [
            'zen.spamhaus.org',
            'bl.spamcop.net',
            'dnsbl.sorbs.net'
        ]
        
        listed = []
        reverse_ip = '.'.join(reversed(ip.split('.')))
        
        for bl in blacklists:
            lookup = f"{reverse_ip}.{bl}"
            try:
                result = socket.gethostbyname(lookup)
                if result != lookup:
                    listed.append(bl)
            except:
                pass
        
        if listed:
            security_info['blacklisted'] = True
            security_info['threat_level'] = 'Medium' if len(listed) < 2 else 'High'
        
        return security_info
    
    def check_port(self, ip, port, timeout=2):
        """Check if port is open"""
        try:
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(timeout)
            result = sock.connect_ex((ip, port))
            sock.close()
            return result == 0
        except:
            return False
    
    def scan_ports(self, ip, ports):
        """Scan multiple ports"""
        open_ports = []
        for port in ports:
            if self.check_port(ip, port):
                open_ports.append(port)
            time.sleep(0.01)  # Small delay
        return open_ports
    
    def get_dns_records(self, domain):
        """Get DNS records for domain"""
        import dns.resolver
        
        records = {}
        resolver = dns.resolver.Resolver()
        resolver.timeout = 3
        resolver.lifetime = 3
        
        record_types = ['A', 'AAAA', 'MX', 'NS', 'TXT', 'SOA', 'CNAME']
        
        for rtype in record_types:
            try:
                answers = resolver.resolve(domain, rtype)
                records[rtype] = [str(r) for r in answers]
            except:
                pass
        
        return records

def main():
    if len(sys.argv) > 1:
        tracker = IPTracker()
        
        if sys.argv[1] == 'track':
            ip = sys.argv[2] if len(sys.argv) > 2 else tracker.get_public_ip()
            if ip:
                info = tracker.get_ip_info(ip)
                print(json.dumps(info, indent=2))
            else:
                print("Could not get IP address")
        
        elif sys.argv[1] == 'scan':
            ip = sys.argv[2]
            ports = range(1, 100)  # Scan first 100 ports
            open_ports = tracker.scan_ports(ip, ports)
            print(f"Open ports: {open_ports}")
        
        elif sys.argv[1] == 'dns':
            domain = sys.argv[2]
            records = tracker.get_dns_records(domain)
            print(json.dumps(records, indent=2))

if __name__ == "__main__":
    main()
