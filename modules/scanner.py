#!/usr/bin/env python3
"""
Network Scanner Module for Quantum TrackIP
Advanced scanning capabilities
"""

import socket
import struct
import threading
import time
import ipaddress
from queue import Queue
import sys

class NetworkScanner:
    def __init__(self):
        self.timeout = 1
        self.threads = 50
        self.open_ports = {}
        self.scan_queue = Queue()
        
    def ping_host(self, ip):
        """Check if host is alive using ICMP ping"""
        try:
            # Create raw socket
            sock = socket.socket(socket.AF_INET, socket.SOCK_RAW, socket.IPPROTO_ICMP)
            sock.settimeout(self.timeout)
            
            # Create ICMP packet
            packet = self.create_icmp_packet()
            
            # Send packet
            sock.sendto(packet, (ip, 0))
            
            # Wait for response
            start_time = time.time()
            sock.recvfrom(1024)
            end_time = time.time()
            
            sock.close()
            return True, round((end_time - start_time) * 1000, 2)
        except:
            return False, 0
    
    def create_icmp_packet(self):
        """Create ICMP echo request packet"""
        # Header checksum
        checksum = 0
        # Header is type (8), code (0), checksum (0), id (1), seq (1)
        header = struct.pack('bbHHh', 8, 0, checksum, 1, 1)
        data = b'PING'
        
        # Calculate checksum
        checksum = self.calculate_checksum(header + data)
        header = struct.pack('bbHHh', 8, 0, checksum, 1, 1)
        
        return header + data
    
    def calculate_checksum(self, data):
        """Calculate internet checksum"""
        if len(data) % 2:
            data += b'\x00'
        
        s = 0
        for i in range(0, len(data), 2):
            w = (data[i] << 8) + data[i+1]
            s += w
        
        s = (s >> 16) + (s & 0xffff)
        s = ~s & 0xffff
        return socket.htons(s)
    
    def scan_port(self, ip, port):
        """Scan a single port"""
        try:
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(self.timeout)
            result = sock.connect_ex((ip, port))
            sock.close()
            
            if result == 0:
                return True
        except:
            pass
        return False
    
    def scan_worker(self):
        """Worker thread for port scanning"""
        while True:
            try:
                ip, port = self.scan_queue.get(timeout=1)
                if self.scan_port(ip, port):
                    if ip not in self.open_ports:
                        self.open_ports[ip] = []
                    self.open_ports[ip].append(port)
                self.scan_queue.task_done()
            except:
                break
    
    def scan_ports_range(self, ip, start_port=1, end_port=1024):
        """Scan range of ports"""
        print(f"[*] Scanning {ip} from port {start_port} to {end_port}")
        
        # Add ports to queue
        for port in range(start_port, end_port + 1):
            self.scan_queue.put((ip, port))
        
        # Start worker threads
        threads = []
        for _ in range(self.threads):
            thread = threading.Thread(target=self.scan_worker)
            thread.daemon = True
            thread.start()
            threads.append(thread)
        
        # Wait for completion
        self.scan_queue.join()
        
        return self.open_ports.get(ip, [])
    
    def scan_network(self, network_cidr, ports=[80, 443, 22, 21, 25]):
        """Scan network for hosts and open ports"""
        network = ipaddress.ip_network(network_cidr, strict=False)
        alive_hosts = []
        
        print(f"[*] Scanning network {network_cidr}")
        
        for ip in network.hosts():
            ip_str = str(ip)
            print(f"[*] Checking {ip_str}...", end='\r')
            
            is_alive, response_time = self.ping_host(ip_str)
            if is_alive:
                print(f"[+] {ip_str} is alive ({response_time}ms)")
                alive_hosts.append((ip_str, response_time))
                
                # Scan common ports
                open_ports = []
                for port in ports:
                    if self.scan_port(ip_str, port):
                        open_ports.append(port)
                
                if open_ports:
                    print(f"    Open ports: {open_ports}")
        
        return alive_hosts
    
    def get_service_name(self, port):
        """Get service name for port"""
        common_services = {
            20: 'FTP-Data',
            21: 'FTP',
            22: 'SSH',
            23: 'Telnet',
            25: 'SMTP',
            53: 'DNS',
            80: 'HTTP',
            110: 'POP3',
            143: 'IMAP',
            443: 'HTTPS',
            465: 'SMTPS',
            587: 'SMTP-Submission',
            993: 'IMAPS',
            995: 'POP3S',
            3306: 'MySQL',
            3389: 'RDP',
            5432: 'PostgreSQL',
            8080: 'HTTP-Alt'
        }
        return common_services.get(port, 'Unknown')
    
    def get_banner(self, ip, port):
        """Get service banner if available"""
        try:
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(2)
            sock.connect((ip, port))
            
            # Try to receive banner
            banner = sock.recv(1024)
            sock.close()
            
            if banner:
                return banner.decode('utf-8', errors='ignore').strip()
        except:
            pass
        return None

def main():
    if len(sys.argv) < 2:
        print("Usage:")
        print("  python scanner.py ping <ip>")
        print("  python scanner.py scan <ip> [start-port] [end-port]")
        print("  python scanner.py network <network-cidr>")
        sys.exit(1)
    
    scanner = NetworkScanner()
    
    if sys.argv[1] == 'ping':
        ip = sys.argv[2]
        is_alive, response_time = scanner.ping_host(ip)
        if is_alive:
            print(f"[+] {ip} is alive - Response time: {response_time}ms")
        else:
            print(f"[-] {ip} is not responding")
    
    elif sys.argv[1] == 'scan':
        ip = sys.argv[2]
        start_port = int(sys.argv[3]) if len(sys.argv) > 3 else 1
        end_port = int(sys.argv[4]) if len(sys.argv) > 4 else 1024
        
        open_ports = scanner.scan_ports_range(ip, start_port, end_port)
        
        if open_ports:
            print(f"\n[+] Open ports on {ip}:")
            for port in sorted(open_ports):
                service = scanner.get_service_name(port)
                print(f"  Port {port}: {service}")
                
                # Try to get banner
                banner = scanner.get_banner(ip, port)
                if banner:
                    print(f"    Banner: {banner}")
        else:
            print(f"\n[-] No open ports found on {ip}")
    
    elif sys.argv[1] == 'network':
        network = sys.argv[2]
        alive_hosts = scanner.scan_network(network)
        
        print(f"\n[+] Found {len(alive_hosts)} alive hosts:")
        for ip, response_time in alive_hosts:
            print(f"  {ip} ({response_time}ms)")

if __name__ == "__main__":
    main()
