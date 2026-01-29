<?php
// TrackIP Quantum - IP Tracking Module
error_reporting(0);

// Colors for terminal
define('RED', "\033[0;31m");
define('GREEN', "\033[0;32m");
define('YELLOW', "\033[1;33m");
define('BLUE', "\033[0;34m");
define('CYAN', "\033[0;36m");
define('NC', "\033[0m");
define('WHITE', "\033[1;37m");

function get_public_ip() {
    $services = [
        'https://api.ipify.org',
        'https://icanhazip.com',
        'https://checkip.amazonaws.com',
        'https://ifconfig.me/ip'
    ];
    
    foreach ($services as $service) {
        try {
            $ip = trim(@file_get_contents($service));
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        } catch (Exception $e) {
            continue;
        }
    }
    return false;
}

function track_ip($ip) {
    echo CYAN . "════════════════════════════════════════\n" . NC;
    echo WHITE . "           IP TRACKING RESULTS\n" . NC;
    echo CYAN . "════════════════════════════════════════\n\n" . NC;
    
    if (!$ip || $ip == 'self') {
        $ip = get_public_ip();
        if (!$ip) {
            echo RED . "[!] Could not get public IP\n" . NC;
            return;
        }
        echo YELLOW . "[*] Your Public IP: " . GREEN . $ip . "\n" . NC;
    }
    
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        echo RED . "[!] Invalid IP address: $ip\n" . NC;
        return;
    }
    
    // Log the query
    $log_entry = date('Y-m-d H:i:s') . " - IP Tracked: $ip\n";
    file_put_contents('logs/ip_logs.txt', $log_entry, FILE_APPEND);
    
    // Get IP information from multiple sources
    $info = get_ip_info($ip);
    
    display_ip_info($info);
}

function get_ip_info($ip) {
    $info = [
        'ip' => $ip,
        'country' => 'Unknown',
        'city' => 'Unknown',
        'region' => 'Unknown',
        'isp' => 'Unknown',
        'asn' => 'Unknown',
        'timezone' => 'Unknown',
        'coordinates' => 'Unknown'
    ];
    
    // Try ip-api.com
    $url = "http://ip-api.com/json/$ip";
    $data = @file_get_contents($url);
    
    if ($data) {
        $json = json_decode($data, true);
        if ($json && $json['status'] == 'success') {
            $info['country'] = $json['country'] ?? 'Unknown';
            $info['city'] = $json['city'] ?? 'Unknown';
            $info['region'] = $json['regionName'] ?? 'Unknown';
            $info['isp'] = $json['isp'] ?? 'Unknown';
            $info['asn'] = $json['as'] ?? 'Unknown';
            $info['timezone'] = $json['timezone'] ?? 'Unknown';
            $info['coordinates'] = ($json['lat'] ?? '') . ', ' . ($json['lon'] ?? '');
        }
    }
    
    // Additional info from ipinfo.io
    $url2 = "https://ipinfo.io/$ip/json";
    $data2 = @file_get_contents($url2);
    
    if ($data2) {
        $json2 = json_decode($data2, true);
        if ($json2) {
            if (empty($info['city']) && !empty($json2['city'])) $info['city'] = $json2['city'];
            if (empty($info['region']) && !empty($json2['region'])) $info['region'] = $json2['region'];
            if (empty($info['country']) && !empty($json2['country'])) $info['country'] = $json2['country'];
            if (empty($info['isp']) && !empty($json2['org'])) $info['isp'] = $json2['org'];
        }
    }
    
    return $info;
}

function display_ip_info($info) {
    echo "\n" . BLUE . "════════════════════════════════════════\n" . NC;
    echo YELLOW . "IP Address Information:\n" . NC;
    echo BLUE . "════════════════════════════════════════\n" . NC;
    
    echo WHITE . "IP Address    : " . GREEN . $info['ip'] . "\n" . NC;
    echo WHITE . "Country       : " . CYAN . $info['country'] . "\n" . NC;
    echo WHITE . "City          : " . CYAN . $info['city'] . "\n" . NC;
    echo WHITE . "Region        : " . CYAN . $info['region'] . "\n" . NC;
    echo WHITE . "ISP           : " . YELLOW . $info['isp'] . "\n" . NC;
    echo WHITE . "ASN           : " . YELLOW . $info['asn'] . "\n" . NC;
    echo WHITE . "Timezone      : " . BLUE . $info['timezone'] . "\n" . NC;
    echo WHITE . "Coordinates   : " . BLUE . $info['coordinates'] . "\n" . NC;
    
    echo "\n" . YELLOW . "[*] Additional Information:\n" . NC;
    echo WHITE . "IP Type       : " . (is_private_ip($info['ip']) ? "Private" : "Public") . "\n" . NC;
    echo WHITE . "IP Version    : " . (filter_var($info['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? "IPv6" : "IPv4") . "\n" . NC;
    
    // Check if IP is blacklisted
    check_blacklist($info['ip']);
}

function is_private_ip($ip) {
    $private_ranges = [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.0/8'
    ];
    
    foreach ($private_ranges as $range) {
        if (ip_in_range($ip, $range)) {
            return true;
        }
    }
    return false;
}

function ip_in_range($ip, $range) {
    list($subnet, $bits) = explode('/', $range);
    $ip_long = ip2long($ip);
    $subnet_long = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet_long &= $mask;
    return ($ip_long & $mask) == $subnet_long;
}

function check_blacklist($ip) {
    echo YELLOW . "\n[*] Checking blacklist status...\n" . NC;
    
    $blacklists = [
        'zen.spamhaus.org',
        'bl.spamcop.net',
        'dnsbl.sorbs.net'
    ];
    
    $listed = false;
    $reverse_ip = implode('.', array_reverse(explode('.', $ip)));
    
    foreach ($blacklists as $bl) {
        $lookup = $reverse_ip . '.' . $bl;
        $result = gethostbyname($lookup);
        
        if ($result != $lookup) {
            echo RED . "[!] Listed in $bl: $result\n" . NC;
            $listed = true;
        }
    }
    
    if (!$listed) {
        echo GREEN . "[✓] IP not found in major blacklists\n" . NC;
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $ip = $argv[1] ?? 'self';
    track_ip($ip);
}
?>
