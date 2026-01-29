<?php
// DNS Lookup Module
error_reporting(0);

define('RED', "\033[0;31m");
define('GREEN', "\033[0;32m");
define('YELLOW', "\033[1;33m");
define('BLUE', "\033[0;34m");
define('CYAN', "\033[0;36m");
define('NC', "\033[0m");
define('WHITE', "\033[1;37m");

function dns_lookup($domain) {
    echo CYAN . "════════════════════════════════════════\n" . NC;
    echo WHITE . "           DNS LOOKUP RESULTS\n" . NC;
    echo CYAN . "════════════════════════════════════════\n\n" . NC;
    
    // Clean domain
    $domain = strtolower(trim($domain));
    $domain = preg_replace('/^https?:\/\//', '', $domain);
    $domain = preg_replace('/^www\./', '', $domain);
    
    if (!preg_match('/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,}$/', $domain)) {
        echo RED . "[!] Invalid domain format\n" . NC;
        return;
    }
    
    echo YELLOW . "[*] Domain: " . GREEN . $domain . "\n" . NC;
    echo YELLOW . "[*] Performing DNS lookup...\n\n" . NC;
    
    // Log the query
    $log = date('Y-m-d H:i:s') . " - DNS Lookup: $domain\n";
    @file_put_contents('logs/ip_logs.txt', $log, FILE_APPEND);
    
    // Get all DNS records
    $dns_types = [
        'A' => DNS_A,
        'AAAA' => DNS_AAAA,
        'MX' => DNS_MX,
        'NS' => DNS_NS,
        'TXT' => DNS_TXT,
        'SOA' => DNS_SOA,
        'CNAME' => DNS_CNAME,
        'PTR' => DNS_PTR,
        'SRV' => DNS_SRV
    ];
    
    $all_records = [];
    
    foreach ($dns_types as $type => $const) {
        if (defined($const)) {
            $records = @dns_get_record($domain, $const);
            if ($records) {
                $all_records[$type] = $records;
            }
        }
    }
    
    if (empty($all_records)) {
        // Try with checkdnsrr
        echo YELLOW . "[*] Trying alternative DNS lookup...\n" . NC;
        check_dns_manually($domain);
    } else {
        display_dns_records($all_records, $domain);
    }
    
    // Additional checks
    perform_dns_checks($domain);
}

function display_dns_records($records, $domain) {
    // A Records (IPv4)
    if (isset($records['A'])) {
        echo BLUE . "════════════════════════════════════════\n" . NC;
        echo CYAN . "A Records (IPv4):\n" . NC;
        echo BLUE . "════════════════════════════════════════\n" . NC;
        
        foreach ($records['A'] as $record) {
            echo WHITE . "• " . GREEN . $record['ip'] . NC;
            echo " (TTL: " . YELLOW . $record['ttl'] . ")\n" . NC;
        }
    }
    
    // AAAA Records (IPv6)
    if (isset($records['AAAA'])) {
        echo "\n" . BLUE . "════════════════════════════════════════\n" . NC;
        echo CYAN . "AAAA Records (IPv6):\n" . NC;
        echo BLUE . "════════════════════════════════════════\n" . NC;
        
        foreach ($records['AAAA'] as $record) {
            echo WHITE . "• " . CYAN . $record['ipv6'] . NC;
            echo " (TTL: " . YELLOW . $record['ttl'] . ")\n" . NC;
        }
    }
    
    // MX Records
    if (isset($records['MX'])) {
        echo "\n" . BLUE . "════════════════════════════════════════\n" . NC;
        echo CYAN . "MX Records (Mail Servers):\n" . NC;
        echo BLUE . "════════════════════════════════════════\n" . NC;
        
        usort($records['MX'], function($a, $b) {
            return $a['pri'] - $b['pri'];
        });
        
        foreach ($records['MX'] as $record) {
            echo WHITE . "• Priority " . YELLOW . $record['pri'] . ": " . GREEN . $record['target'] . "\n" . NC;
        }
    }
    
    // NS Records
    if (isset($records['NS'])) {
        echo "\n" . BLUE . "════════════════════════════════════════\n" . NC;
        echo CYAN . "NS Records (Name Servers):\n" . NC;
        echo BLUE . "════════════════════════════════════════\n" . NC;
        
        foreach ($records['NS'] as $record) {
            echo WHITE . "• " . BLUE . $record['target'] . "\n" . NC;
        }
    }
    
    // TXT Records
    if (isset($records['TXT'])) {
        echo "\n" . BLUE . "════════════════════════════════════════\n" . NC;
        echo CYAN . "TXT Records:\n" . NC;
        echo BLUE . "════════════════════════════════════════\n" . NC;
        
        foreach ($records['TXT'] as $record) {
            echo WHITE . "• " . CYAN . $record['txt'] . "\n" . NC;
        }
    }
    
    // CNAME Records
    if (isset($records['CNAME'])) {
        echo "\n" . BLUE . "════════════════════════════════════════\n" . NC;
        echo CYAN . "CNAME Records:\n" . NC;
        echo BLUE . "════════════════════════════════════════\n" . NC;
        
        foreach ($records['CNAME'] as $record) {
            echo WHITE . "• " . $domain . " → " . GREEN . $record['target'] . "\n" . NC;
        }
    }
    
    // SOA Record
    if (isset($records['SOA'])) {
        echo "\n" . BLUE . "════════════════════════════════════════\n" . NC;
        echo CYAN . "SOA Record (Start of Authority):\n" . NC;
        echo BLUE . "════════════════════════════════════════\n" . NC;
        
        $soa = $records['SOA'][0];
        echo WHITE . "Primary NS   : " . GREEN . $soa['mname'] . "\n" . NC;
        echo WHITE . "Admin Email  : " . CYAN . $soa['rname'] . "\n" . NC;
        echo WHITE . "Serial       : " . YELLOW . $soa['serial'] . "\n" . NC;
        echo WHITE . "Refresh      : " . BLUE . $soa['refresh'] . " sec\n" . NC;
        echo WHITE . "Retry        : " . BLUE . $soa['retry'] . " sec\n" . NC;
        echo WHITE . "Expire       : " . BLUE . $soa['expire'] . " sec\n" . NC;
        echo WHITE . "Minimum TTL  : " . BLUE . $soa['minimum-ttl'] . " sec\n" . NC;
    }
}

function check_dns_manually($domain) {
    // Check A record
    $ipv4 = gethostbyname($domain);
    if ($ipv4 && $ipv4 != $domain) {
        echo GREEN . "[✓] A Record found: " . $ipv4 . "\n" . NC;
    } else {
        echo RED . "[!] No A Record found\n" . NC;
    }
    
    // Check MX records
    if (getmxrr($domain, $mxhosts, $mxweight)) {
        echo GREEN . "[✓] MX Records found:\n" . NC;
        for ($i = 0; $i < count($mxhosts); $i++) {
            echo "  Priority " . $mxweight[$i] . ": " . $mxhosts[$i] . "\n";
        }
    }
    
    // Check NS records
    if (checkdnsrr($domain, 'NS')) {
        $ns = dns_get_record($domain, DNS_NS);
        echo GREEN . "[✓] NS Records found:\n" . NC;
        foreach ($ns as $record) {
            echo "  • " . $record['target'] . "\n";
        }
    }
}

function perform_dns_checks($domain) {
    echo "\n" . YELLOW . "[*] Performing DNS security checks...\n" . NC;
    echo BLUE . "════════════════════════════════════════\n" . NC;
    
    // Check for DNSSEC
    $dnssec = @dns_get_record($domain, DNS_DNSKEY);
    if ($dnssec) {
        echo GREEN . "[✓] DNSSEC is enabled\n" . NC;
    } else {
        echo YELLOW . "[!] DNSSEC not detected\n" . NC;
    }
    
    // Check for DMARC
    $dmarc = @dns_get_record('_dmarc.' . $domain, DNS_TXT);
    $has_dmarc = false;
    if ($dmarc) {
        foreach ($dmarc as $record) {
            if (strpos($record['txt'], 'v=DMARC1') !== false) {
                $has_dmarc = true;
                break;
            }
        }
    }
    echo $has_dmarc ? GREEN . "[✓] DMARC record found\n" : YELLOW . "[!] No DMARC record found\n" . NC;
    
    // Check for SPF
    $spf = @dns_get_record($domain, DNS_TXT);
    $has_spf = false;
    if ($spf) {
        foreach ($spf as $record) {
            if (strpos($record['txt'], 'v=spf1') !== false) {
                $has_spf = true;
                break;
            }
        }
    }
    echo $has_spf ? GREEN . "[✓] SPF record found\n" : YELLOW . "[!] No SPF record found\n" . NC;
    
    // Check for DKIM (common selectors)
    $common_dkim = ['default', 'dkim', 'google', 'selector1', 'selector2'];
    $has_dkim = false;
    foreach ($common_dkim as $selector) {
        $dkim = @dns_get_record($selector . '._domainkey.' . $domain, DNS_TXT);
        if ($dkim) {
            $has_dkim = true;
            break;
        }
    }
    echo $has_dkim ? GREEN . "[✓] DKIM record found\n" : YELLOW . "[!] No DKIM record found\n" . NC;
    
    // Check for CAA
    $caa = @dns_get_record($domain, DNS_CAA);
    echo $caa ? GREEN . "[✓] CAA records found\n" : CYAN . "[*] No CAA records (normal for many domains)\n" . NC;
    
    // Check if domain resolves
    if (gethostbyname($domain) == $domain) {
        echo RED . "[!] Domain does not resolve to any IP\n" . NC;
    } else {
        echo GREEN . "[✓] Domain resolves correctly\n" . NC;
    }
}

// Main
if (php_sapi_name() === 'cli') {
    if ($argc < 2) {
        echo "Usage: php dns.php [domain]\n";
        echo "Example: php dns.php example.com\n";
        exit(1);
    }
    
    $domain = $argv[1];
    dns_lookup($domain);
}
?>
