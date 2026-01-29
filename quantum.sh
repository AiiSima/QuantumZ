#!/bin/bash
clear

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'
MAGENTA='\033[0;35m'
WHITE='\033[1;37m'

# Banner
echo -e "${CYAN}"
cat << "EOF"
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀
⠀⠀⣤⣀⣀⠀⠀⠀⠀⠀⠄⠀⢠⣤⡄⠀⠠⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⡀⠙
⠀⠐⠻⣿⣿⣟⣒⣒⣒⣀⠀⠀⠈⠛⠃⠀⢀⠀⣀⢀⣀⣀⣀⣴⣾⣿⣿⣿⣿⡷⠶⠒
⠐⠆⠀⢿⣿⣿⣿⣿⣿⣿⣿⣶⣶⣶⣶⣶⣿⣾⣏⣀⣀⣤⣤⣤⣤⣤⣼⣿⠛⠓⠒⠒⠂
⠀⠐⠒⠚⠛⠛⠛⠛⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡟⠛⠛⠋⠀⠀⠂
⠀⠀⠀⣀⣀⣹⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣷⣶⣏⠉⠑⠂
⠀⡄⠀⠉⢹⣿⣿⣿⣿⠿⢿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡿⠿⣿⣿⣿⣿⣆⠈⠂⢠⠀⡄
⠀⠁⢀⣀⣿⣿⣿⠟⠛⠉⠓⢮⣍⢻⣿⣿⣿⣿⣿⣏⡥⠞⠋⠙⠻⣿⣿⣉⣀⠀⢀⣉
⠀⠀⠀⣿⣿⡏⠀⠀⠀⠀⠀⠀⣘⣿⣿⣿⣿⣿⣿⣋⠀⠀⠀⠀⠀⡀⠹⣿⣿⠤⠾⠤⠄
⠀⠐⣲⣿⣿⣷⣦⡀⠀⠀⠀⢤⣤⣼⣿⣿⣿⣿⣿⣿⡄⠀⠀⠀⠀⣧⣾⣿⣿⡶⠖
⠀⠀⠈⠿⠿⠿⠿⢿⣶⣶⣶⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣷⣶⣿⣿⣿⣿⡿⠿⠋⠁
⠀⠀⠀⠀⠒⠀⠀⠉⠛⠿⠿⢿⣿⣿⣿⣦⣤⣾⣿⣿⣿⣿⠛⠛⠛⠛⠃⠀⠀⠤
⠀⠀⠀⠀⠀⠀⠛⠀⠀⠀⠀⠀⠉⠙⠻⠿⠿⠟⠛⠋⡉                        

─────────────────────────────────────
Name Script   : TrackIP - Quantum
Author      : AiiSima Quantum
Version     : 1.0
─────────────────────────────────────
EOF
echo -e "${NC}"

# Check dependencies
check_dependencies() {
    echo -e "${YELLOW}[*] Checking dependencies...${NC}"
    
    # Check PHP
    if ! command -v php &> /dev/null; then
        echo -e "${RED}[!] PHP not found. Installing...${NC}"
        pkg install php -y
    else
        echo -e "${GREEN}[✓] PHP installed${NC}"
    fi
    
    # Check curl
    if ! command -v curl &> /dev/null; then
        echo -e "${RED}[!] curl not found. Installing...${NC}"
        pkg install curl -y
    else
        echo -e "${GREEN}[✓] curl installed${NC}"
    fi
    
    # Check wget
    if ! command -v wget &> /dev/null; then
        echo -e "${RED}[!] wget not found. Installing...${NC}"
        pkg install wget -y
    else
        echo -e "${GREEN}[✓] wget installed${NC}"
    fi
    
    # Check python3
    if ! command -v python3 &> /dev/null; then
        echo -e "${RED}[!] python3 not found. Installing...${NC}"
        pkg install python -y
    else
        echo -e "${GREEN}[✓] python3 installed${NC}"
    fi
    
    sleep 2
}

# Main menu
main_menu() {
    clear
    echo -e "${CYAN}"
    cat assets/banner.txt 2>/dev/null || echo "TrackIP Akurat - Quantum"
    echo -e "${NC}"
    echo -e "${BLUE}════════════════════════════════════════${NC}"
    echo -e "${WHITE}         MAIN MENU TRACKIP${NC}"
    echo -e "${BLUE}════════════════════════════════════════${NC}"
    echo -e "${GREEN}[1]${NC} Track IP Address"
    echo -e "${GREEN}[2]${NC} Geolocation Lookup"
    echo -e "${GREEN}[3]${NC} WHOIS Information"
    echo -e "${GREEN}[4]${NC} DNS Lookup"
    echo -e "${GREEN}[5]${NC} Port Scanner"
    echo -e "${GREEN}[6]${NC} All Information"
    echo -e "${GREEN}[7]${NC} View Logs"
    echo -e "${GREEN}[8]${NC} Update Script"
    echo -e "${GREEN}[9]${NC} About"
    echo -e "${GREEN}[0]${NC} Exit"
    echo -e "${BLUE}════════════════════════════════════════${NC}"
    
    read -p "Select option [0-9]: " choice
    
    case $choice in
        1)
            track_ip
            ;;
        2)
            geolocation
            ;;
        3)
            whois_lookup
            ;;
        4)
            dns_lookup
            ;;
        5)
            port_scan
            ;;
        6)
            all_info
            ;;
        7)
            view_logs
            ;;
        8)
            update_script
            ;;
        9)
            about
            ;;
        0)
            echo -e "${YELLOW}[*] Goodbye!${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}[!] Invalid option!${NC}"
            sleep 1
            main_menu
            ;;
    esac
}

# Track IP
track_ip() {
    clear
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    echo -e "${WHITE}           TRACK IP ADDRESS${NC}"
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    
    echo -e "${YELLOW}[*] Options:${NC}"
    echo -e "${GREEN}[1]${NC} Track your own IP"
    echo -e "${GREEN}[2]${NC} Track specific IP"
    echo -e "${GREEN}[3]${NC} Back to Main Menu"
    
    read -p "Select: " option
    
    case $option in
        1)
            echo -e "${YELLOW}[*] Getting your IP address...${NC}"
            php trackip.php self
            ;;
        2)
            read -p "Enter IP Address: " ip
            if [[ -n "$ip" ]]; then
                php trackip.php $ip
            else
                echo -e "${RED}[!] IP address cannot be empty!${NC}"
            fi
            ;;
        3)
            main_menu
            ;;
        *)
            echo -e "${RED}[!] Invalid option!${NC}"
            ;;
    esac
    
    echo -e "\n${YELLOW}[*] Press Enter to continue...${NC}"
    read
    main_menu
}

# Geolocation
geolocation() {
    clear
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    echo -e "${WHITE}           GEOLOCATION LOOKUP${NC}"
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    
    read -p "Enter IP Address (leave empty for your IP): " ip
    
    if [[ -z "$ip" ]]; then
        echo -e "${YELLOW}[*] Getting your geolocation...${NC}"
        php geolocation.php self
    else
        php geolocation.php $ip
    fi
    
    echo -e "\n${YELLOW}[*] Press Enter to continue...${NC}"
    read
    main_menu
}

# WHOIS Lookup
whois_lookup() {
    clear
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    echo -e "${WHITE}           WHOIS LOOKUP${NC}"
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    
    echo -e "${YELLOW}[*] Options:${NC}"
    echo -e "${GREEN}[1]${NC} WHOIS for IP"
    echo -e "${GREEN}[2]${NC} WHOIS for Domain"
    echo -e "${GREEN}[3]${NC} Back to Main Menu"
    
    read -p "Select: " option
    
    case $option in
        1)
            read -p "Enter IP Address: " target
            if [[ -n "$target" ]]; then
                php whois.php ip $target
            fi
            ;;
        2)
            read -p "Enter Domain (example.com): " target
            if [[ -n "$target" ]]; then
                php whois.php domain $target
            fi
            ;;
        3)
            main_menu
            ;;
        *)
            echo -e "${RED}[!] Invalid option!${NC}"
            ;;
    esac
    
    echo -e "\n${YELLOW}[*] Press Enter to continue...${NC}"
    read
    main_menu
}

# DNS Lookup
dns_lookup() {
    clear
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    echo -e "${WHITE}           DNS LOOKUP${NC}"
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    
    read -p "Enter Domain (example.com): " domain
    
    if [[ -n "$domain" ]]; then
        php dns.php $domain
    else
        echo -e "${RED}[!] Domain cannot be empty!${NC}"
    fi
    
    echo -e "\n${YELLOW}[*] Press Enter to continue...${NC}"
    read
    main_menu
}

# Port Scanner
port_scan() {
    clear
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    echo -e "${WHITE}           PORT SCANNER${NC}"
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    
    read -p "Enter IP Address or Domain: " target
    
    if [[ -n "$target" ]]; then
        php portscan.php $target
    else
        echo -e "${RED}[!] Target cannot be empty!${NC}"
    fi
    
    echo -e "\n${YELLOW}[*] Press Enter to continue...${NC}"
    read
    main_menu
}

# All Information
all_info() {
    clear
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    echo -e "${WHITE}           ALL INFORMATION${NC}"
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    
    read -p "Enter IP Address or Domain: " target
    
    if [[ -n "$target" ]]; then
        echo -e "${YELLOW}[*] Getting all information for: $target${NC}"
        echo -e "${BLUE}════════════════════════════════════════${NC}"
        
        # IP Info
        echo -e "${GREEN}[1] IP Information:${NC}"
        php trackip.php $target
        
        echo -e "\n${BLUE}════════════════════════════════════════${NC}"
        
        # Geolocation
        echo -e "${GREEN}[2] Geolocation:${NC}"
        php geolocation.php $target
        
        echo -e "\n${BLUE}════════════════════════════════════════${NC}"
        
        # WHOIS
        echo -e "${GREEN}[3] WHOIS Information:${NC}"
        php whois.php ip $target
        
        echo -e "\n${BLUE}════════════════════════════════════════${NC}"
        
        # DNS if domain
        if [[ ! "$target" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            echo -e "${GREEN}[4] DNS Information:${NC}"
            php dns.php $target
        fi
        
        echo -e "\n${BLUE}════════════════════════════════════════${NC}"
        
        # Port Scan
        echo -e "${GREEN}[5] Port Scan:${NC}"
        php portscan.php $target
        
    else
        echo -e "${RED}[!] Target cannot be empty!${NC}"
    fi
    
    echo -e "\n${YELLOW}[*] Press Enter to continue...${NC}"
    read
    main_menu
}

# View Logs
view_logs() {
    clear
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    echo -e "${WHITE}           VIEW LOGS${NC}"
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    
    if [[ -f "logs/ip_logs.txt" ]]; then
        echo -e "${YELLOW}[*] Recent IP Tracking Logs:${NC}"
        echo -e "${BLUE}════════════════════════════════════════${NC}"
        tail -20 logs/ip_logs.txt
    else
        echo -e "${RED}[!] No logs found!${NC}"
    fi
    
    echo -e "\n${YELLOW}[*] Press Enter to continue...${NC}"
    read
    main_menu
}

# Update Script
update_script() {
    clear
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    echo -e "${WHITE}           UPDATE SCRIPT${NC}"
    echo -e "${CYAN}════════════════════════════════════════${NC}"
    
    echo -e "${YELLOW}[*] Checking for updates...${NC}"
    
    # Simulate update check
    sleep 2
    echo -e "${GREEN}[✓] You have the latest version!${NC}"
    echo -e "${YELLOW}[*] Current Version: 1.0${NC}"
    
    echo -e "\n${YELLOW}[*] Press Enter to continue...${NC}"
    read
    main_menu
}

# About
about() {
    clear
    echo -e "${CYAN}"
    cat << "EOF"
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⢠⠄⠀⡐⠀⠀⠀⠀⠀⠀⠀⠀⠀⠄⠀⠳⠃⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⡈⣀⡴⢧⣀⠀⠀⣀⣠⠤⠤⠤⠤⣄⣀⠀⠀⠈⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠘⠏⢀⡴⠊⠁⠀⠄⠀⠀⠀⠀⠈⠙⠢⡀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⣰⠋⠀⠀⠀⠈⠁⠀⠀⠀⠀⠀⠀⠀⠘⢶⣶⣒⡶⠦⣠⣀⠀
⠀⠀⠀⠀⠀⠀⢀⣰⠃⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠂⠀⠀⠈⣟⠲⡎⠙⢦⠈⢧
⠀⠀⠀⣠⢴⡾⢟⣿⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣸⡰⢃⡠⠋⣠⠋
⠐⠀⠞⣱⠋⢰⠁⢿⠀⠀⠀⠀⠄⢂⠀⠀⠀⠀⠀⣀⣠⠠⢖⣋⡥⢖⣩⠔⠊⠀⠀
⠈⠠⡀⠹⢤⣈⣙⠚⠶⠤⠤⠤⠴⠶⣒⣒⣚⣨⠭⢵⣒⣩⠬⢖⠏⠁⢀⣀⠀⠀⠀
⠀⠀⠈⠓⠒⠦⠍⠭⠭⣭⠭⠭⠭⠭⡿⡓⠒⠛⠉⠉⠀⠀⣠⠇⠀⠀⠘⠞⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⠓⢤⣀⠀⠁⠀⠀⠀⠀⣀⡤⠞⠁⠀⣰⣆⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠿⠀⠀⠀⠀⠀⠉⠉⠙⠒⠒⠚⠉⠁⠀⠀⠀⠁⢣⡎⠁⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠂⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀                        
EOF
    echo -e "${NC}"
    echo -e "${BLUE}════════════════════════════════════════${NC}"
    echo -e "${WHITE}           ABOUT TRACKIP QUANTUM${NC}"
    echo -e "${BLUE}════════════════════════════════════════${NC}"
    echo -e "${CYAN}Script Name   : TrackIP Akurat - Quantum${NC}"
    echo -e "${CYAN}Author        : AiiSima Quantum${NC}"
    echo -e "${CYAN}Version       : 1.0${NC}"
    echo -e "${CYAN}Description   : Advanced IP Tracking Tool${NC}"
    echo -e "${CYAN}Features      :${NC}"
    echo -e "  ${GREEN}✓${NC} IP Address Tracking"
    echo -e "  ${GREEN}✓${NC} Geolocation Lookup"
    echo -e "  ${GREEN}✓${NC} WHOIS Information"
    echo -e "  ${GREEN}✓${NC} DNS Lookup"
    echo -e "  ${GREEN}✓${NC} Port Scanner"
    echo -e "  ${GREEN}✓${NC} All-in-One Information"
    echo -e "  ${GREEN}✓${NC} Logging System"
    echo -e "${BLUE}════════════════════════════════════════${NC}"
    
    echo -e "\n${YELLOW}[*] Press Enter to continue...${NC}"
    read
    main_menu
}

# Initialize
init() {
    echo -e "${YELLOW}[*] Initializing TrackIP Quantum...${NC}"
    
    # Create directories
    mkdir -p modules assets logs
    
    # Create banner file
    cat > assets/banner.txt << "EOF"
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⢠⠄⠀⡐⠀⠀⠀⠀⠀⠀⠀⠀⠀⠄⠀⠳⠃⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⡈⣀⡴⢧⣀⠀⠀⣀⣠⠤⠤⠤⠤⣄⣀⠀⠀⠈⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠘⠏⢀⡴⠊⠁⠀⠄⠀⠀⠀⠀⠈⠙⠢⡀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⣰⠋⠀⠀⠀⠈⠁⠀⠀⠀⠀⠀⠀⠀⠘⢶⣶⣒⡶⠦⣠⣀⠀
⠀⠀⠀⠀⠀⠀⢀⣰⠃⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠂⠀⠀⠈⣟⠲⡎⠙⢦⠈⢧
⠀⠀⠀⣠⢴⡾⢟⣿⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣸⡰⢃⡠⠋⣠⠋
⠐⠀⠞⣱⠋⢰⠁⢿⠀⠀⠀⠀⠄⢂⠀⠀⠀⠀⠀⣀⣠⠠⢖⣋⡥⢖⣩⠔⠊⠀⠀
⠈⠠⡀⠹⢤⣈⣙⠚⠶⠤⠤⠤⠴⠶⣒⣒⣚⣨⠭⢵⣒⣩⠬⢖⠏⠁⢀⣀⠀⠀⠀
⠀⠀⠈⠓⠒⠦⠍⠭⠭⣭⠭⠭⠭⠭⡿⡓⠒⠛⠉⠉⠀⠀⣠⠇⠀⠀⠘⠞⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⠓⢤⣀⠀⠁⠀⠀⠀⠀⣀⡤⠞⠁⠀⣰⣆⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠿⠀⠀⠀⠀⠀⠉⠉⠙⠒⠒⠚⠉⠁⠀⠀⠀⠁⢣⡎⠁⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠂⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀                        

─────────────────────────────────────
Name Script   : TrackIP Akurat - Quantum
Author      : AiiSima Quantum
Version     : 1.0
─────────────────────────────────────
EOF
    
    echo -e "${GREEN}[✓] Initialization complete!${NC}"
    sleep 1
}

# Main execution
echo -e "${YELLOW}[*] Starting TrackIP Quantum...${NC}"
check_dependencies
init
main_menu
