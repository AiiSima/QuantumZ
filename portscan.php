<?php
// Port Scanner Module
error_reporting(0);

define('RED', "\033[0;31m");
define('GREEN', "\033[0;32m");
define('YELLOW', "\033[1;33m");
define('BLUE', "\033[0;34m");
define('CYAN', "\033[0;36m");
define('NC', "\033[0m");
define('WHITE', "\033[1;37m");

function port_scan($target) {
    echo CYAN . "════════════════════════════════════════\n" . NC;
    echo WHITE . "           PORT SCAN RESULTS\n" . NC;
    echo CYAN . "════════════════════════════════════════\n\n" . NC;
    
    // Check if target is domain or IP
    if (!filter_var($target, FILTER_VALIDATE_IP)) {
        // Try to resolve domain to IP
        echo YELLOW . "[*] Resolving domain: " . $target . "\n" . NC;
        $ip = gethostbyname($target);
        if ($ip == $target) {
            echo RED . "[!] Could not resolve domain\n" . NC;
            return;
        }
        echo GREEN . "[✓] Resolved to IP: " . $ip . "\n\n" . NC;
        $target_ip = $ip;
    } else {
        $target_ip = $target;
    }
    
    echo YELLOW . "[*] Target: " . GREEN . $target . " (" . $target_ip . ")\n" . NC;
    echo YELLOW . "[*] Starting port scan...\n\n" . NC;
    
    // Log the query
    $log = date('Y-m-d H:i:s') . " - Port Scan: $target ($target_ip)\n";
    @file_put_contents('logs/ip_logs.txt', $log, FILE_APPEND);
    
    // Common ports to scan
    $common_ports = [
        // HTTP/HTTPS
        80 => 'HTTP',
        443 => 'HTTPS',
        8080 => 'HTTP-Alt',
        8443 => 'HTTPS-Alt',
        
        // FTP
        21 => 'FTP',
        22 => 'SSH',
        
        // Mail
        25 => 'SMTP',
        465 => 'SMTPS',
        587 => 'SMTP-Submission',
        110 => 'POP3',
        995 => 'POP3S',
        143 => 'IMAP',
        993 => 'IMAPS',
        
        // Database
        3306 => 'MySQL',
        5432 => 'PostgreSQL',
        27017 => 'MongoDB',
        6379 => 'Redis',
        
        // Remote Access
        3389 => 'RDP',
        5900 => 'VNC',
        22 => 'SSH',
        
        // DNS
        53 => 'DNS',
        
        // Other Services
        111 => 'RPCbind',
        135 => 'MS-RPC',
        139 => 'NetBIOS',
        445 => 'SMB',
        1433 => 'MSSQL',
        1521 => 'Oracle',
        2049 => 'NFS',
        3306 => 'MySQL',
        5432 => 'PostgreSQL',
        5900 => 'VNC',
        8080 => 'HTTP-Proxy',
        8443 => 'HTTPS-Alt',
        9000 => 'PHP-FPM',
        9200 => 'Elasticsearch',
        27017 => 'MongoDB'
    ];
    
    // Quick scan (top 20 ports)
    echo YELLOW . "[*] Quick scan (common ports):\n" . NC;
    echo BLUE . "════════════════════════════════════════\n" . NC;
    
    $open_ports = [];
    $timeout = 1; // seconds
    
    foreach ($common_ports as $port => $service) {
        echo WHITE . "Scanning port " . str_pad($port, 5) . " ($service)... " . NC;
        
        $start = microtime(true);
        $socket = @fsockopen($target_ip, $port, $errno, $errstr, $timeout);
        $end = microtime(true);
        
        if ($socket) {
            echo GREEN . "[OPEN]\n" . NC;
            fclose($socket);
            $open_ports[$port] = [
                'service' => $service,
                'response_time' => round(($end - $start) * 1000, 2) . 'ms'
            ];
        } else {
            echo RED . "[CLOSED]\n" . NC;
        }
        
        // Don't flood too fast
        usleep(50000); // 50ms delay
    }
    
    // Display results
    if (!empty($open_ports)) {
        echo "\n" . GREEN . "[✓] Open ports found:\n" . NC;
        echo BLUE . "════════════════════════════════════════\n" . NC;
        
        echo WHITE . str_pad("Port", 8) . str_pad("Service", 15) . "Response Time\n" . NC;
        echo BLUE . str_repeat("─", 40) . "\n" . NC;
        
        ksort($open_ports);
        foreach ($open_ports as $port => $info) {
            echo CYAN . str_pad($port, 8) . NC;
            echo WHITE . str_pad($info['service'], 15) . NC;
            echo YELLOW . $info['response_time'] . "\n" . NC;
        }
        
        // Service detection
        echo "\n" . YELLOW . "[*] Service detection:\n" . NC;
        detect_services($target_ip, $open_ports);
        
    } else {
        echo "\n" . RED . "[!] No open ports found on common ports\n" . NC;
    }
    
    // Offer full scan
    echo "\n" . YELLOW . "[*] Options:\n" . NC;
    echo WHITE . "[1] Quick scan (common ports) - Done\n" . NC;
    echo WHITE . "[2] Full scan (1-1000 ports)\n" . NC;
    echo WHITE . "[3] Custom port range\n" . NC;
    echo WHITE . "[4] Back to menu\n" . NC;
    
    echo "\n" . CYAN . "Select option: " . NC;
    $handle = fopen("php://stdin", "r");
    $option = trim(fgets($handle));
    
    switch ($option) {
        case '2':
            full_scan($target_ip, 1, 1000);
            break;
        case '3':
            echo CYAN . "Start port: " . NC;
            $start = trim(fgets($handle));
            echo CYAN . "End port: " . NC;
            $end = trim(fgets($handle));
            if (is_numeric($start) && is_numeric($end) && $start > 0 && $end <= 65535 && $start <= $end) {
                full_scan($target_ip, $start, $end);
            } else {
                echo RED . "[!] Invalid port range\n" . NC;
            }
            break;
        case '4':
            return;
    }
}

function full_scan($ip, $start, $end) {
    echo "\n" . YELLOW . "[*] Starting full scan from port $start to $end\n" . NC;
    echo YELLOW . "[*] This may take a while...\n\n" . NC;
    
    $open_ports = [];
    $batch_size = 100;
    $timeout = 1;
    
    $total = $end - $start + 1;
    $current = 0;
    
    for ($port = $start; $port <= $end; $port += $batch_size) {
        $batch_end = min($port + $batch_size - 1, $end);
        
        echo WHITE . "Scanning ports $port-$batch_end... " . NC;
        
        $batch_open = [];
        for ($p = $port; $p <= $batch_end; $p++) {
            $socket = @fsockopen($ip, $p, $errno, $errstr, $timeout);
            if ($socket) {
                $batch_open[] = $p;
                fclose($socket);
            }
        }
        
        if (!empty($batch_open)) {
            echo GREEN . "Found " . count($batch_open) . " open ports\n" . NC;
            $open_ports = array_merge($open_ports, $batch_open);
        } else {
            echo RED . "No open ports\n" . NC;
        }
        
        $current += $batch_size;
        $percent = min(100, round(($current / $total) * 100));
        echo YELLOW . "Progress: $percent%\n" . NC;
    }
    
    if (!empty($open_ports)) {
        echo "\n" . GREEN . "[✓] Open ports found: " . implode(', ', $open_ports) . "\n" . NC;
        
        // Get service names for open ports
        $services = get_service_names($open_ports);
        echo "\n" . BLUE . "════════════════════════════════════════\n" . NC;
        echo CYAN . "Open Ports Details:\n" . NC;
        echo BLUE . "════════════════════════════════════════\n" . NC;
        
        foreach ($open_ports as $port) {
            $service = $services[$port] ?? 'Unknown';
            echo WHITE . "Port " . str_pad($port, 6) . ": " . CYAN . $service . "\n" . NC;
        }
    } else {
        echo "\n" . RED . "[!] No open ports found in range $start-$end\n" . NC;
    }
}

function get_service_names($ports) {
    $common_services = [
        21 => 'FTP',
        22 => 'SSH',
        23 => 'Telnet',
        25 => 'SMTP',
        53 => 'DNS',
        80 => 'HTTP',
        110 => 'POP3',
        111 => 'RPC',
        135 => 'MS-RPC',
        139 => 'NetBIOS',
        143 => 'IMAP',
        443 => 'HTTPS',
        445 => 'SMB',
        993 => 'IMAPS',
        995 => 'POP3S',
        1723 => 'PPTP',
        3306 => 'MySQL',
        3389 => 'RDP',
        5900 => 'VNC',
        8080 => 'HTTP-Proxy'
    ];
    
    $result = [];
    foreach ($ports as $port) {
        $result[$port] = $common_services[$port] ?? 'Unknown';
    }
    return $result;
}

function detect_services($ip, $open_ports) {
    echo BLUE . "════════════════════════════════════════\n" . NC;
    
    foreach ($open_ports as $port => $info) {
        switch ($port) {
            case 80:
            case 8080:
                check_http_service($ip, $port);
                break;
            case 443:
            case 8443:
                check_https_service($ip, $port);
                break;
            case 21:
                check_ftp_service($ip);
                break;
            case 22:
                check_ssh_service($ip);
                break;
            case 25:
            case 587:
                check_smtp_service($ip, $port);
                break;
        }
    }
}

function check_http_service($ip, $port) {
    $url = ($port == 443 ? "https" : "http") . "://$ip" . ($port != 80 && $port != 443 ? ":$port" : "");
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 3,
            'header' => "User-Agent: TrackIP-Quantum/1.0\r\n"
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        // Get headers
        $headers = $http_response_header;
        echo "\n" . CYAN . "HTTP Service on port $port:\n" . NC;
        
        foreach ($headers as $header) {
            if (stripos($header, 'Server:') !== false) {
                echo GREEN . "  Server: " . trim(substr($header, 7)) . "\n" . NC;
            }
            if (stripos($header, 'X-Powered-By:') !== false) {
                echo GREEN . "  Powered By: " . trim(substr($header, 13)) . "\n" . NC;
            }
        }
        
        // Check for common web apps
        if (strpos($response, 'wordpress') !== false) {
            echo GREEN . "  Detected: WordPress\n" . NC;
        }
        if (strpos($response, 'joomla') !== false) {
            echo GREEN . "  Detected: Joomla\n" . NC;
        }
        if (strpos($response, 'drupal') !== false) {
            echo GREEN . "  Detected: Drupal\n" . NC;
        }
    }
}

function check_https_service($ip, $port) {
    echo "\n" . CYAN . "HTTPS Service on port $port:\n" . NC;
    
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'capture_peer_cert' => true
        ]
    ]);
    
    $client = @stream_socket_client("ssl://$ip:$port", $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $context);
    
    if ($client) {
        $params = stream_context_get_params($client);
        if (isset($params['options']['ssl']['peer_certificate'])) {
            $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
            if ($cert) {
                if (isset($cert['subject']['CN'])) {
                    echo GREEN . "  Certificate CN: " . $cert['subject']['CN'] . "\n" . NC;
                }
                if (isset($cert['validTo_time_t'])) {
                    $expiry = date('Y-m-d', $cert['validTo_time_t']);
                    $days_left = ceil(($cert['validTo_time_t'] - time()) / 86400);
                    echo GREEN . "  Expires: " . $expiry . " (" . $days_left . " days)\n" . NC;
                }
            }
        }
        fclose($client);
    }
}

function check_ftp_service($ip) {
    $socket = @fsockopen($ip, 21, $errno, $errstr, 3);
    if ($socket) {
        stream_set_timeout($socket, 3);
        $banner = fgets($socket);
        echo "\n" . CYAN . "FTP Service:\n" . NC;
        echo GREEN . "  Banner: " . trim($banner) . "\n" . NC;
        fclose($socket);
    }
}

function check_ssh_service($ip) {
    $socket = @fsockopen($ip, 22, $errno, $errstr, 3);
    if ($socket) {
        stream_set_timeout($socket, 3);
        $banner = fgets($socket);
        echo "\n" . CYAN . "SSH Service:\n" . NC;
        echo GREEN . "  Banner: " . trim($banner) . "\n" . NC;
        fclose($socket);
    }
}

function check_smtp_service($ip, $port) {
    $socket = @fsockopen($ip, $port, $errno, $errstr, 3);
    if ($socket) {
        stream_set_timeout($socket, 3);
        $banner = fgets($socket);
        echo "\n" . CYAN . "SMTP Service on port $port:\n" . NC;
        echo GREEN . "  Banner: " . trim($banner) . "\n" . NC;
        
        // Try EHLO
        fwrite($socket, "EHLO example.com\r\n");
        $response = fgets($socket);
        if (strpos($response, '250') === 0) {
            echo GREEN . "  EHLO accepted\n" . NC;
        }
        fclose($socket);
    }
}

// Main
if (php_sapi_name() === 'cli') {
    if ($argc < 2) {
        echo "Usage: php portscan.php [ip/domain]\n";
        echo "Example: php portscan.php 8.8.8.8\n";
        echo "Example: php portscan.php example.com\n";
        exit(1);
    }
    
    $target = $argv[1];
    port_scan($target);
}
?>
