<?php
// Geolocation Module
error_reporting(0);

define('RED', "\033[0;31m");
define('GREEN', "\033[0;32m");
define('YELLOW', "\033[1;33m");
define('BLUE', "\033[0;34m");
define('CYAN', "\033[0;36m");
define('NC', "\033[0m");
define('WHITE', "\033[1;37m");

function get_geolocation($ip) {
    if ($ip == 'self') {
        $ip = trim(@file_get_contents('https://api.ipify.org'));
    }
    
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        echo RED . "[!] Invalid IP address\n" . NC;
        return;
    }
    
    echo CYAN . "════════════════════════════════════════\n" . NC;
    echo WHITE . "         GEOLOCATION RESULTS\n" . NC;
    echo CYAN . "════════════════════════════════════════\n\n" . NC;
    
    echo YELLOW . "[*] Target IP: " . GREEN . $ip . "\n\n" . NC;
    
    // Multiple API sources
    $apis = [
        'ip-api' => "http://ip-api.com/json/$ip",
        'ipapi' => "https://ipapi.co/$ip/json/",
        'ipinfo' => "https://ipinfo.io/$ip/json"
    ];
    
    $all_data = [];
    
    foreach ($apis as $name => $url) {
        echo YELLOW . "[*] Querying $name API...\n" . NC;
        $data = @file_get_contents($url);
        
        if ($data) {
            $json = json_decode($data, true);
            $all_data[$name] = $json;
            
            if ($name == 'ip-api' && isset($json['status']) && $json['status'] == 'success') {
                display_geolocation($json, 'ip-api');
            } elseif ($name == 'ipapi' && isset($json['ip'])) {
                display_geolocation($json, 'ipapi');
            } elseif ($name == 'ipinfo' && isset($json['ip'])) {
                display_geolocation($json, 'ipinfo');
            }
            
            echo "\n";
            sleep(1);
        }
    }
    
    // Log the query
    $log = date('Y-m-d H:i:s') . " - Geolocation: $ip\n";
    @file_put_contents('logs/ip_logs.txt', $log, FILE_APPEND);
}

function display_geolocation($data, $source) {
    echo BLUE . "════════════════════════════════════════\n" . NC;
    echo CYAN . "Source: " . strtoupper($source) . "\n" . NC;
    echo BLUE . "════════════════════════════════════════\n" . NC;
    
    switch ($source) {
        case 'ip-api':
            echo WHITE . "Country       : " . GREEN . ($data['country'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Country Code  : " . CYAN . ($data['countryCode'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Region        : " . CYAN . ($data['regionName'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "City          : " . CYAN . ($data['city'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "ZIP Code      : " . BLUE . ($data['zip'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Latitude      : " . YELLOW . ($data['lat'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Longitude     : " . YELLOW . ($data['lon'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Timezone      : " . BLUE . ($data['timezone'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "ISP           : " . WHITE . ($data['isp'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Organization  : " . WHITE . ($data['org'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "ASN           : " . WHITE . ($data['as'] ?? 'N/A') . "\n" . NC;
            break;
            
        case 'ipapi':
            echo WHITE . "Country       : " . GREEN . ($data['country_name'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Country Code  : " . CYAN . ($data['country_code'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Region        : " . CYAN . ($data['region'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "City          : " . CYAN . ($data['city'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Postal Code   : " . BLUE . ($data['postal'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Latitude      : " . YELLOW . ($data['latitude'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Longitude     : " . YELLOW . ($data['longitude'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Timezone      : " . BLUE . ($data['timezone'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Currency      : " . WHITE . ($data['currency'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Languages     : " . WHITE . ($data['languages'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Calling Code  : +" . WHITE . ($data['country_calling_code'] ?? 'N/A') . "\n" . NC;
            break;
            
        case 'ipinfo':
            $loc = explode(',', $data['loc'] ?? '');
            echo WHITE . "Country       : " . GREEN . ($data['country'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Region        : " . CYAN . ($data['region'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "City          : " . CYAN . ($data['city'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Postal Code   : " . BLUE . ($data['postal'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Coordinates   : " . YELLOW . (isset($loc[0]) ? $loc[0] : 'N/A') . ", " . (isset($loc[1]) ? $loc[1] : 'N/A') . "\n" . NC;
            echo WHITE . "Timezone      : " . BLUE . ($data['timezone'] ?? 'N/A') . "\n" . NC;
            echo WHITE . "Organization  : " . WHITE . ($data['org'] ?? 'N/A') . "\n" . NC;
            break;
    }
}

// Show map link
function show_map_link($lat, $lon) {
    if ($lat && $lon && $lat != 'N/A' && $lon != 'N/A') {
        echo "\n" . YELLOW . "[*] Map Links:\n" . NC;
        echo WHITE . "Google Maps   : " . BLUE . "https://maps.google.com/?q=$lat,$lon\n" . NC;
        echo WHITE . "OpenStreetMap : " . BLUE . "https://www.openstreetmap.org/?mlat=$lat&mlon=$lon\n" . NC;
    }
}

// Main
if (php_sapi_name() === 'cli') {
    $ip = $argv[1] ?? 'self';
    get_geolocation($ip);
    
    // Try to get coordinates for map
    $url = "http://ip-api.com/json/$ip";
    $data = @file_get_contents($url);
    if ($data) {
        $json = json_decode($data, true);
        if ($json['status'] == 'success') {
            show_map_link($json['lat'] ?? null, $json['lon'] ?? null);
        }
    }
}
?>
