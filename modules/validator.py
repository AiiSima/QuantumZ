#!/usr/bin/env python3
"""
Input Validator Module for Quantum TrackIP
Validate and sanitize all inputs
"""

import re
import socket
import ipaddress
from urllib.parse import urlparse

class InputValidator:
    
    @staticmethod
    def validate_ip(ip):
        """Validate IP address (IPv4 or IPv6)"""
        try:
            ipaddress.ip_address(ip)
            return True, "Valid IP address"
        except ValueError:
            return False, "Invalid IP address format"
    
    @staticmethod
    def validate_domain(domain):
        """Validate domain name"""
        # Remove protocol and www
        domain = re.sub(r'^https?://', '', domain)
        domain = re.sub(r'^www\.', '', domain)
        domain = domain.strip().lower()
        
        # Domain regex pattern
        pattern = r'^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,}$'
        
        if re.match(pattern, domain):
            # Check if domain resolves
            try:
                socket.gethostbyname(domain)
                return True, "Valid domain", domain
            except socket.gaierror:
                return False, "Domain does not resolve", domain
        else:
            return False, "Invalid domain format", domain
    
    @staticmethod
    def validate_url(url):
        """Validate URL"""
        try:
            result = urlparse(url)
            if all([result.scheme, result.netloc]):
                # Check if it's HTTP or HTTPS
                if result.scheme in ['http', 'https']:
                    return True, "Valid URL", url
            return False, "Invalid URL format", url
        except:
            return False, "Invalid URL", url
    
    @staticmethod
    def validate_port(port):
        """Validate port number"""
        try:
            port_num = int(port)
            if 1 <= port_num <= 65535:
                return True, "Valid port", port_num
            else:
                return False, "Port must be between 1 and 65535", port_num
        except ValueError:
            return False, "Port must be a number", port
    
    @staticmethod
    def validate_port_range(start_port, end_port):
        """Validate port range"""
        start_valid, start_msg, start = InputValidator.validate_port(start_port)
        end_valid, end_msg, end = InputValidator.validate_port(end_port)
        
        if not start_valid:
            return False, f"Start port: {start_msg}", (start, end)
        if not end_valid:
            return False, f"End port: {end_msg}", (start, end)
        
        if start > end:
            return False, "Start port must be less than or equal to end port", (start, end)
        
        return True, "Valid port range", (start, end)
    
    @staticmethod
    def validate_email(email):
        """Validate email address"""
        pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        if re.match(pattern, email):
            return True, "Valid email", email
        return False, "Invalid email format", email
    
    @staticmethod
    def validate_phone(phone):
        """Validate phone number (basic international format)"""
        # Remove spaces, dashes, parentheses
        phone = re.sub(r'[\s\-\(\)]', '', phone)
        
        # Check if it's all digits and has reasonable length
        if phone.isdigit() and 8 <= len(phone) <= 15:
            return True, "Valid phone number", phone
        return False, "Invalid phone number", phone
    
    @staticmethod
    def sanitize_input(input_str, max_length=255):
        """Sanitize input string"""
        if not input_str:
            return ""
        
        # Remove null bytes
        input_str = input_str.replace('\0', '')
        
        # Truncate if too long
        if len(input_str) > max_length:
            input_str = input_str[:max_length]
        
        # Remove potentially dangerous characters for command injection
        dangerous = [';', '|', '&', '`', '$', '(', ')', '{', '}', '[', ']', '<', '>']
        for char in dangerous:
            input_str = input_str.replace(char, '')
        
        # Strip whitespace
        input_str = input_str.strip()
        
        return input_str
    
    @staticmethod
    def is_private_ip(ip):
        """Check if IP is in private range"""
        try:
            ip_obj = ipaddress.ip_address(ip)
            return ip_obj.is_private
        except:
            return False
    
    @staticmethod
    def is_loopback_ip(ip):
        """Check if IP is loopback"""
        try:
            ip_obj = ipaddress.ip_address(ip)
            return ip_obj.is_loopback
        except:
            return False
    
    @staticmethod
    def is_reserved_ip(ip):
        """Check if IP is reserved"""
        try:
            ip_obj = ipaddress.ip_address(ip)
            return ip_obj.is_reserved
        except:
            return False
    
    @staticmethod
    def get_ip_version(ip):
        """Get IP version (4 or 6)"""
        try:
            ip_obj = ipaddress.ip_address(ip)
            return 4 if ip_obj.version == 4 else 6
        except:
            return 0
    
    @staticmethod
    def extract_domain_from_url(url):
        """Extract domain from URL"""
        try:
            parsed = urlparse(url)
            domain = parsed.netloc
            
            # Remove port if present
            if ':' in domain:
                domain = domain.split(':')[0]
            
            # Remove www prefix
            if domain.startswith('www.'):
                domain = domain[4:]
            
            return domain
        except:
            return ""
    
    @staticmethod
    def validate_mac_address(mac):
        """Validate MAC address"""
        patterns = [
            r'^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$',
            r'^([0-9A-Fa-f]{4}\.){2}([0-9A-Fa-f]{4})$'
        ]
        
        for pattern in patterns:
            if re.match(pattern, mac):
                return True, "Valid MAC address", mac
        
        return False, "Invalid MAC address format", mac

def main():
    """Test function"""
    validator = InputValidator()
    
    test_cases = [
        ("IP", "192.168.1.1"),
        ("IP", "256.256.256.256"),
        ("Domain", "example.com"),
        ("Domain", "example"),
        ("URL", "https://example.com"),
        ("URL", "example.com"),
        ("Port", "80"),
        ("Port", "99999"),
        ("Email", "test@example.com"),
        ("Email", "test@example"),
        ("Phone", "+1234567890"),
        ("Phone", "123"),
        ("MAC", "00:1A:2B:3C:4D:5E"),
        ("MAC", "00-1A-2B-3C-4D-5E")
    ]
    
    for test_type, value in test_cases:
        if test_type == "IP":
            valid, msg = validator.validate_ip(value)
        elif test_type == "Domain":
            valid, msg, _ = validator.validate_domain(value)
        elif test_type == "URL":
            valid, msg, _ = validator.validate_url(value)
        elif test_type == "Port":
            valid, msg, _ = validator.validate_port(value)
        elif test_type == "Email":
            valid, msg, _ = validator.validate_email(value)
        elif test_type == "Phone":
            valid, msg, _ = validator.validate_phone(value)
        elif test_type == "MAC":
            valid, msg, _ = validator.validate_mac_address(value)
        
        status = "✓" if valid else "✗"
        print(f"{status} {test_type}: {value} - {msg}")

if __name__ == "__main__":
    main()
