<?php
// WHOIS Lookup Module
error_reporting(0);

define('RED', "\033[0;31m");
define('GREEN', "\033[0;32m");
define('YELLOW', "\033[1;33m");
define('BLUE', "\033[0;34m");
define('CYAN', "\033[0;36m");
define('NC', "\033[0m");
define('WHITE', "\033[1;37m");

function whois_lookup($type, $target) {
    echo CYAN . "════════════════════════════════════════\n" . NC;
    echo WHITE . "           WHOIS RESULTS\n" . NC;
    echo CYAN . "════════════════════════════════════════\n\n" . NC;
    
    echo YELLOW . "[*] Target: " . GREEN . $target . "\n" . NC;
    echo YELLOW . "[*] Type: " . CYAN . $type . "\n\n" . NC;
    
    if ($type == 'ip') {
        whois_ip($target);
    } elseif ($type == 'domain') {
        whois_domain($target);
    } else {
        echo RED . "[!] Invalid type. Use 'ip' or 'domain'\n" . NC;
    }
    
    // Log the query
    $log = date('Y-m-d H:i:s') . " - WHOIS ($type): $target\n";
    @file_put_contents('logs/ip_logs.txt', $log, FILE_APPEND);
}

function whois_ip($ip) {
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        echo RED . "[!] Invalid IP address\n" . NC;
        return;
    }
    
    // Get RIR information based on IP range
    $rir = get_rir($ip);
    echo YELLOW . "[*] RIR: " . CYAN . $rir . "\n\n" . NC;
    
    // Try whois command
    echo YELLOW . "[*] Querying WHOIS servers...\n" . NC;
    echo BLUE . "════════════════════════════════════════\n" . NC;
    
    $output = [];
    exec("whois $ip 2>/dev/null", $output);
    
    if (empty($output)) {
        // Fallback to API
        whois_api($ip, 'ip');
    } else {
        $relevant_info = extract_whois_info($output);
        display_whois_info($relevant_info);
    }
}

function whois_domain($domain) {
    // Clean domain
    $domain = strtolower(trim($domain));
    $domain = preg_replace('/^https?:\/\//', '', $domain);
    $domain = preg_replace('/^www\./', '', $domain);
    
    if (!preg_match('/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,}$/', $domain)) {
        echo RED . "[!] Invalid domain format\n" . NC;
        return;
    }
    
    echo YELLOW . "[*] Domain: " . GREEN . $domain . "\n" . NC;
    echo YELLOW . "[*] TLD: " . CYAN . get_tld($domain) . "\n\n" . NC;
    
    // Try whois command
    echo YELLOW . "[*] Querying WHOIS servers...\n" . NC;
    echo BLUE . "════════════════════════════════════════\n" . NC;
    
    $output = [];
    exec("whois $domain 2>/dev/null", $output);
    
    if (empty($output)) {
        // Fallback to API
        whois_api($domain, 'domain');
    } else {
        $relevant_info = extract_domain_info($output);
        display_domain_info($relevant_info, $domain);
    }
}

function get_rir($ip) {
    $ip_long = ip2long($ip);
    
    // RIR ranges
    $rirs = [
        'APNIC' => [
            ['1.0.0.0', '1.255.255.255'],
            ['14.0.0.0', '14.255.255.255'],
            ['27.0.0.0', '27.255.255.255'],
            ['36.0.0.0', '36.255.255.255'],
            ['39.0.0.0', '39.255.255.255'],
            ['42.0.0.0', '42.255.255.255'],
            ['49.0.0.0', '49.255.255.255'],
            ['58.0.0.0', '58.255.255.255'],
            ['59.0.0.0', '59.255.255.255'],
            ['60.0.0.0', '60.255.255.255'],
            ['61.0.0.0', '61.255.255.255'],
            ['101.0.0.0', '101.255.255.255'],
            ['103.0.0.0', '103.255.255.255'],
            ['106.0.0.0', '106.255.255.255'],
            ['110.0.0.0', '110.255.255.255'],
            ['111.0.0.0', '111.255.255.255'],
            ['112.0.0.0', '112.255.255.255'],
            ['113.0.0.0', '113.255.255.255'],
            ['114.0.0.0', '114.255.255.255'],
            ['115.0.0.0', '115.255.255.255'],
            ['116.0.0.0', '116.255.255.255'],
            ['117.0.0.0', '117.255.255.255'],
            ['118.0.0.0', '118.255.255.255'],
            ['119.0.0.0', '119.255.255.255'],
            ['120.0.0.0', '120.255.255.255'],
            ['121.0.0.0', '121.255.255.255'],
            ['122.0.0.0', '122.255.255.255'],
            ['123.0.0.0', '123.255.255.255'],
            ['124.0.0.0', '124.255.255.255'],
            ['125.0.0.0', '125.255.255.255'],
            ['126.0.0.0', '126.255.255.255'],
            ['133.0.0.0', '133.255.255.255'],
            ['139.0.0.0', '139.255.255.255'],
            ['140.0.0.0', '140.255.255.255'],
            ['143.0.0.0', '143.255.255.255'],
            ['144.0.0.0', '144.255.255.255'],
            ['150.0.0.0', '150.255.255.255'],
            ['153.0.0.0', '153.255.255.255'],
            ['155.0.0.0', '155.255.255.255'],
            ['157.0.0.0', '157.255.255.255'],
            ['159.0.0.0', '159.255.255.255'],
            ['160.0.0.0', '160.255.255.255'],
            ['161.0.0.0', '161.255.255.255'],
            ['162.0.0.0', '162.255.255.255'],
            ['163.0.0.0', '163.255.255.255'],
            ['164.0.0.0', '164.255.255.255'],
            ['165.0.0.0', '165.255.255.255'],
            ['166.0.0.0', '166.255.255.255'],
            ['167.0.0.0', '167.255.255.255'],
            ['168.0.0.0', '168.255.255.255'],
            ['169.0.0.0', '169.255.255.255'],
            ['170.0.0.0', '170.255.255.255'],
            ['171.0.0.0', '171.255.255.255'],
            ['172.0.0.0', '172.255.255.255'],
            ['173.0.0.0', '173.255.255.255'],
            ['174.0.0.0', '174.255.255.255'],
            ['175.0.0.0', '175.255.255.255'],
            ['180.0.0.0', '180.255.255.255'],
            ['182.0.0.0', '182.255.255.255'],
            ['183.0.0.0', '183.255.255.255'],
            ['202.0.0.0', '202.255.255.255'],
            ['203.0.0.0', '203.255.255.255'],
            ['210.0.0.0', '210.255.255.255'],
            ['211.0.0.0', '211.255.255.255'],
            ['218.0.0.0', '218.255.255.255'],
            ['219.0.0.0', '219.255.255.255'],
            ['220.0.0.0', '220.255.255.255'],
            ['221.0.0.0', '221.255.255.255'],
            ['222.0.0.0', '222.255.255.255'],
            ['223.0.0.0', '223.255.255.255']
        ],
        'ARIN' => [
            ['7.0.0.0', '7.255.255.255'],
            ['23.0.0.0', '23.255.255.255'],
            ['24.0.0.0', '24.255.255.255'],
            ['32.0.0.0', '32.255.255.255'],
            ['45.0.0.0', '45.255.255.255'],
            ['50.0.0.0', '50.255.255.255'],
            ['63.0.0.0', '63.255.255.255'],
            ['64.0.0.0', '64.255.255.255'],
            ['65.0.0.0', '65.255.255.255'],
            ['66.0.0.0', '66.255.255.255'],
            ['67.0.0.0', '67.255.255.255'],
            ['68.0.0.0', '68.255.255.255'],
            ['69.0.0.0', '69.255.255.255'],
            ['70.0.0.0', '70.255.255.255'],
            ['71.0.0.0', '71.255.255.255'],
            ['72.0.0.0', '72.255.255.255'],
            ['73.0.0.0', '73.255.255.255'],
            ['74.0.0.0', '74.255.255.255'],
            ['75.0.0.0', '75.255.255.255'],
            ['76.0.0.0', '76.255.255.255'],
            ['96.0.0.0', '96.255.255.255'],
            ['97.0.0.0', '97.255.255.255'],
            ['98.0.0.0', '98.255.255.255'],
            ['99.0.0.0', '99.255.255.255'],
            ['100.0.0.0', '100.255.255.255'],
            ['104.0.0.0', '104.255.255.255'],
            ['107.0.0.0', '107.255.255.255'],
            ['108.0.0.0', '108.255.255.255'],
            ['128.0.0.0', '128.255.255.255'],
            ['129.0.0.0', '129.255.255.255'],
            ['130.0.0.0', '130.255.255.255'],
            ['131.0.0.0', '131.255.255.255'],
            ['132.0.0.0', '132.255.255.255'],
            ['134.0.0.0', '134.255.255.255'],
            ['135.0.0.0', '135.255.255.255'],
            ['136.0.0.0', '136.255.255.255'],
            ['137.0.0.0', '137.255.255.255'],
            ['138.0.0.0', '138.255.255.255'],
            ['142.0.0.0', '142.255.255.255'],
            ['147.0.0.0', '147.255.255.255'],
            ['148.0.0.0', '148.255.255.255'],
            ['152.0.0.0', '152.255.255.255'],
            ['156.0.0.0', '156.255.255.255'],
            ['158.0.0.0', '158.255.255.255'],
            ['162.0.0.0', '162.255.255.255'],
            ['172.0.0.0', '172.255.255.255'],
            ['173.0.0.0', '173.255.255.255'],
            ['174.0.0.0', '174.255.255.255'],
            ['184.0.0.0', '184.255.255.255'],
            ['192.0.0.0', '192.255.255.255'],
            ['198.0.0.0', '198.255.255.255'],
            ['199.0.0.0', '199.255.255.255'],
            ['204.0.0.0', '204.255.255.255'],
            ['205.0.0.0', '205.255.255.255'],
            ['206.0.0.0', '206.255.255.255'],
            ['207.0.0.0', '207.255.255.255'],
            ['208.0.0.0', '208.255.255.255'],
            ['209.0.0.0', '209.255.255.255'],
            ['216.0.0.0', '216.255.255.255']
        ],
        'RIPE' => [
            ['5.0.0.0', '5.255.255.255'],
            ['31.0.0.0', '31.255.255.255'],
            ['37.0.0.0', '37.255.255.255'],
            ['46.0.0.0', '46.255.255.255'],
            ['51.0.0.0', '51.255.255.255'],
            ['52.0.0.0', '52.255.255.255'],
            ['53.0.0.0', '53.255.255.255'],
            ['54.0.0.0', '54.255.255.255'],
            ['55.0.0.0', '55.255.255.255'],
            ['57.0.0.0', '57.255.255.255'],
            ['62.0.0.0', '62.255.255.255'],
            ['77.0.0.0', '77.255.255.255'],
            ['78.0.0.0', '78.255.255.255'],
            ['79.0.0.0', '79.255.255.255'],
            ['80.0.0.0', '80.255.255.255'],
            ['81.0.0.0', '81.255.255.255'],
            ['82.0.0.0', '82.255.255.255'],
            ['83.0.0.0', '83.255.255.255'],
            ['84.0.0.0', '84.255.255.255'],
            ['85.0.0.0', '85.255.255.255'],
            ['86.0.0.0', '86.255.255.255'],
            ['87.0.0.0', '87.255.255.255'],
            ['88.0.0.0', '88.255.255.255'],
            ['89.0.0.0', '89.255.255.255'],
            ['90.0.0.0', '90.255.255.255'],
            ['91.0.0.0', '91.255.255.255'],
            ['92.0.0.0', '92.255.255.255'],
            ['93.0.0.0', '93.255.255.255'],
            ['94.0.0.0', '94.255.255.255'],
            ['95.0.0.0', '95.255.255.255'],
            ['109.0.0.0', '109.255.255.255'],
            ['141.0.0.0', '141.255.255.255'],
            ['145.0.0.0', '145.255.255.255'],
            ['146.0.0.0', '146.255.255.255'],
            ['149.0.0.0', '149.255.255.255'],
            ['151.0.0.0', '151.255.255.255'],
            ['176.0.0.0', '176.255.255.255'],
            ['178.0.0.0', '178.255.255.255'],
            ['179.0.0.0', '179.255.255.255'],
            ['185.0.0.0', '185.255.255.255'],
            ['188.0.0.0', '188.255.255.255'],
            ['193.0.0.0', '193.255.255.255'],
            ['194.0.0.0', '194.255.255.255'],
            ['195.0.0.0', '195.255.255.255'],
            ['212.0.0.0', '212.255.255.255'],
            ['213.0.0.0', '213.255.255.255'],
            ['217.0.0.0', '217.255.255.255']
        ],
        'LACNIC' => [
            ['177.0.0.0', '177.255.255.255'],
            ['179.0.0.0', '179.255.255.255'],
            ['181.0.0.0', '181.255.255.255'],
            ['186.0.0.0', '186.255.255.255'],
            ['187.0.0.0', '187.255.255.255'],
            ['189.0.0.0', '189.255.255.255'],
            ['190.0.0.0', '190.255.255.255'],
            ['191.0.0.0', '191.255.255.255'],
            ['200.0.0.0', '200.255.255.255'],
            ['201.0.0.0', '201.255.255.255']
        ],
        'AFRINIC' => [
            ['41.0.0.0', '41.255.255.255'],
            ['102.0.0.0', '102.255.255.255'],
            ['105.0.0.0', '105.255.255.255'],
            ['154.0.0.0', '154.255.255.255'],
            ['196.0.0.0', '196.255.255.255'],
            ['197.0.0.0', '197.255.255.255']
        ]
    ];
    
    foreach ($rirs as $rir_name => $ranges) {
        foreach ($ranges as $range) {
            $start = ip2long($range[0]);
            $end = ip2long($range[1]);
            if ($ip_long >= $start && $ip_long <= $end) {
                return $rir_name;
            }
        }
    }
    
    return 'Unknown';
}

function extract_whois_info($output) {
    $info = [
        'inetnum' => 'Not found',
        'netname' => 'Not found',
        'country' => 'Not found',
        'org-name' => 'Not found',
        'admin-c' => 'Not found',
        'tech-c' => 'Not found',
        'status' => 'Not found',
        'created' => 'Not found',
        'last-modified' => 'Not found'
    ];
    
    foreach ($output as $line) {
        $line = trim($line);
        
        foreach ($info as $key => $value) {
            if (stripos($line, $key . ':') === 0) {
                $parts = explode(':', $line, 2);
                if (isset($parts[1])) {
                    $info[$key] = trim($parts[1]);
                }
            }
        }
        
        // Also check for common variations
        if (stripos($line, 'inetnum') === 0 && $info['inetnum'] == 'Not found') {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) $info['inetnum'] = trim($parts[1]);
        }
        if (stripos($line, 'OrgName') === 0 && $info['org-name'] == 'Not found') {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) $info['org-name'] = trim($parts[1]);
        }
        if (stripos($line, 'Country') === 0 && $info['country'] == 'Not found') {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) $info['country'] = trim($parts[1]);
        }
    }
    
    return $info;
}

function extract_domain_info($output) {
    $info = [
        'domain' => 'Not found',
        'registrar' => 'Not found',
        'creation_date' => 'Not found',
        'expiration_date' => 'Not found',
        'updated_date' => 'Not found',
        'status' => 'Not found',
        'name_servers' => [],
        'registrant' => 'Not found',
        'admin' => 'Not found',
        'tech' => 'Not found'
    ];
    
    foreach ($output as $line) {
        $line = trim($line);
        
        if (stripos($line, 'Domain Name:') === 0) {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) $info['domain'] = trim($parts[1]);
        }
        elseif (stripos($line, 'Registrar:') === 0 || stripos($line, 'Registered through:') === 0) {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) $info['registrar'] = trim($parts[1]);
        }
        elseif (stripos($line, 'Creation Date:') === 0 || stripos($line, 'Registered on:') === 0 || stripos($line, 'Created:') === 0) {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) $info['creation_date'] = trim($parts[1]);
        }
        elseif (stripos($line, 'Expiration Date:') === 0 || stripos($line, 'Expires on:') === 0 || stripos($line, 'Registry Expiry Date:') === 0) {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) $info['expiration_date'] = trim($parts[1]);
        }
        elseif (stripos($line, 'Updated Date:') === 0 || stripos($line, 'Last updated:') === 0) {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) $info['updated_date'] = trim($parts[1]);
        }
        elseif (stripos($line, 'Status:') === 0) {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) $info['status'] = trim($parts[1]);
        }
        elseif (stripos($line, 'Name Server:') === 0 || stripos($line, 'nserver:') === 0 || stripos($line, 'Nameservers:') === 0) {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) {
                $ns = trim($parts[1]);
                if (!in_array($ns, $info['name_servers'])) {
                    $info['name_servers'][] = $ns;
                }
            }
        }
        elseif (stripos($line, 'Registrant:') === 0 || stripos($line, 'Registrant Name:') === 0) {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) $info['registrant'] = trim($parts[1]);
        }
    }
    
    return $info;
}

function display_whois_info($info) {
    echo WHITE . "Network Range  : " . GREEN . $info['inetnum'] . "\n" . NC;
    echo WHITE . "Network Name   : " . CYAN . $info['netname'] . "\n" . NC;
    echo WHITE . "Country        : " . YELLOW . $info['country'] . "\n" . NC;
    echo WHITE . "Organization   : " . WHITE . $info['org-name'] . "\n" . NC;
    echo WHITE . "Admin Contact  : " . BLUE . $info['admin-c'] . "\n" . NC;
    echo WHITE . "Tech Contact   : " . BLUE . $info['tech-c'] . "\n" . NC;
    echo WHITE . "Status         : " . ($info['status'] == 'Not found' ? RED : GREEN) . $info['status'] . "\n" . NC;
    echo WHITE . "Created        : " . CYAN . $info['created'] . "\n" . NC;
    echo WHITE . "Last Modified  : " . CYAN . $info['last-modified'] . "\n" . NC;
}

function display_domain_info($info, $domain) {
    echo WHITE . "Domain         : " . GREEN . $info['domain'] . "\n" . NC;
    echo WHITE . "Registrar      : " . CYAN . $info['registrar'] . "\n" . NC;
    echo WHITE . "Creation Date  : " . YELLOW . $info['creation_date'] . "\n" . NC;
    echo WHITE . "Expiration Date: " . YELLOW . $info['expiration_date'] . "\n" . NC;
    echo WHITE . "Updated Date   : " . BLUE . $info['updated_date'] . "\n" . NC;
    echo WHITE . "Status         : " . ($info['status'] == 'Not found' ? RED : GREEN) . $info['status'] . "\n" . NC;
    echo WHITE . "Registrant     : " . WHITE . $info['registrant'] . "\n" . NC;
    
    if (!empty($info['name_servers'])) {
        echo WHITE . "Name Servers   :\n" . NC;
        foreach ($info['name_servers'] as $ns) {
            echo "  " . BLUE . "• " . CYAN . $ns . "\n" . NC;
        }
    }
    
    // Calculate days until expiration
    if ($info['expiration_date'] != 'Not found') {
        $exp_date = strtotime($info['expiration_date']);
        if ($exp_date) {
            $days_left = ceil(($exp_date - time()) / (60 * 60 * 24));
            echo WHITE . "Days Left     : " . ($days_left > 30 ? GREEN : ($days_left > 7 ? YELLOW : RED)) . $days_left . " days\n" . NC;
        }
    }
}

function whois_api($target, $type) {
    echo YELLOW . "[*] Using WHOIS API...\n" . NC;
    
    if ($type == 'ip') {
        $url = "https://whois.arin.net/rest/ip/$target.json";
    } else {
        $url = "https://api.whoisproxy.info/whois/$target";
    }
    
    $data = @file_get_contents($url);
    
    if ($data) {
        $json = json_decode($data, true);
        if ($json) {
            echo GREEN . "[✓] Data retrieved from API\n" . NC;
            
            if ($type == 'ip') {
                echo BLUE . "════════════════════════════════════════\n" . NC;
                if (isset($json['net'])) {
                    $net = $json['net'];
                    echo WHITE . "Network       : " . GREEN . ($net['name']['$'] ?? 'N/A') . "\n" . NC;
                    echo WHITE . "Start Address : " . CYAN . ($net['startAddress']['$'] ?? 'N/A') . "\n" . NC;
                    echo WHITE . "End Address   : " . CYAN . ($net['endAddress']['$'] ?? 'N/A') . "\n" . NC;
                    echo WHITE . "Org Name      : " . WHITE . ($net['orgRef']['@name'] ?? 'N/A') . "\n" . NC;
                    echo WHITE . "Handle        : " . BLUE . ($net['handle']['$'] ?? 'N/A') . "\n" . NC;
                }
            } else {
                echo BLUE . "════════════════════════════════════════\n" . NC;
                if (isset($json['whois'])) {
                    $whois = $json['whois'];
                    echo WHITE . "Domain        : " . GREEN . ($whois['domain'] ?? 'N/A') . "\n" . NC;
                    echo WHITE . "Registrar     : " . CYAN . ($whois['registrar'] ?? 'N/A') . "\n" . NC;
                    echo WHITE . "Created       : " . YELLOW . ($whois['created'] ?? 'N/A') . "\n" . NC;
                    echo WHITE . "Expires       : " . YELLOW . ($whois['expires'] ?? 'N/A') . "\n" . NC;
                }
            }
            return;
        }
    }
    
    echo RED . "[!] Could not retrieve WHOIS information\n" . NC;
}

function get_tld($domain) {
    $parts = explode('.', $domain);
    return end($parts);
}

// Main
if (php_sapi_name() === 'cli') {
    if ($argc < 3) {
        echo "Usage: php whois.php [ip|domain] [target]\n";
        echo "Example: php whois.php ip 8.8.8.8\n";
        echo "Example: php whois.php domain example.com\n";
        exit(1);
    }
    
    $type = $argv[1];
    $target = $argv[2];
    
    whois_lookup($type, $target);
}
?>
