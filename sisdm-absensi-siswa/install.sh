#!/bin/bash
# SISDM Absensi Siswa - Automated Installation Script
# Instalasi Otomatis Sistem Absensi Siswa

set -e

echo "=============================================="
echo "  SISDM Absensi Siswa - Installation Script"
echo "=============================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
DB_NAME="sisdm_absensi"
DB_USER="root"
DB_PASS=""
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${YELLOW}Starting installation...${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

# Check for required tools
echo "Checking requirements..."

# Check PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Installing...${NC}"
    apt-get update && apt-get install -y php php-mysql php-gd php-mbstring php-xml php-curl
fi

# Check MySQL/MariaDB
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}MySQL/MariaDB is not installed. Installing...${NC}"
    apt-get update && apt-get install -y mariadb-server mariadb-client
    systemctl start mariadb
    systemctl enable mariadb
else
    echo -e "${GREEN}✓ MySQL/MariaDB found${NC}"
fi

# Check if MySQL is running
if ! systemctl is-active --quiet mariadb && ! systemctl is-active --quiet mysql; then
    echo "Starting database server..."
    systemctl start mariadb 2>/dev/null || systemctl start mysql 2>/dev/null || true
fi

# Create database
echo ""
echo "Creating database..."
mysql -u $DB_USER -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
    echo -e "${YELLOW}Could not create database automatically. Please create manually:${NC}"
    echo "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
}

# Import SQL file
echo "Importing database schema and sample data..."
SQL_FILE="$PROJECT_DIR/sql/database.sql"
if [ -f "$SQL_FILE" ]; then
    mysql -u $DB_USER -p"$DB_PASS" $DB_NAME < "$SQL_FILE" 2>/dev/null || {
        echo -e "${YELLOW}Could not import SQL automatically.${NC}"
        echo "Please import manually: mysql -u $DB_USER -p $DB_NAME < $SQL_FILE"
    }
    echo -e "${GREEN}✓ Database imported successfully${NC}"
else
    echo -e "${RED}SQL file not found: $SQL_FILE${NC}"
    exit 1
fi

# Set permissions
echo ""
echo "Setting permissions..."
chown -R www-data:www-data "$PROJECT_DIR" 2>/dev/null || true
chmod -R 755 "$PROJECT_DIR"
chmod -R 777 "$PROJECT_DIR/assets/images" 2>/dev/null || mkdir -p "$PROJECT_DIR/assets/images/students"

# Create upload directories
mkdir -p "$PROJECT_DIR/assets/images/students"
chmod -R 777 "$PROJECT_DIR/assets/images"

echo -e "${GREEN}✓ Permissions set${NC}"

# Configure database connection
echo ""
echo "Configuring database connection..."
CONFIG_FILE="$PROJECT_DIR/config/database.php"
if [ -f "$CONFIG_FILE" ]; then
    sed -i "s/'dbname' => '.*'/'dbname' => '$DB_NAME'/" "$CONFIG_FILE"
    sed -i "s/'username' => '.*'/'username' => '$DB_USER'/" "$CONFIG_FILE"
    sed -i "s/'password' => '.*'/'password' => '$DB_PASS'/" "$CONFIG_FILE"
    echo -e "${GREEN}✓ Database configured${NC}"
fi

# Apache configuration (optional)
echo ""
read -p "Configure Apache virtual host? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    VHOST_FILE="/etc/apache2/sites-available/sisdm-absensi.conf"
    cat > "$VHOST_FILE" << EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot $PROJECT_DIR
    
    <Directory $PROJECT_DIR>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/sisdm-error.log
    CustomLog \${APACHE_LOG_DIR}/sisdm-access.log combined
</VirtualHost>
EOF
    
    a2ensite sisdm-absensi.conf 2>/dev/null || true
    a2enmod rewrite 2>/dev/null || true
    systemctl reload apache2 2>/dev/null || true
    
    echo -e "${GREEN}✓ Apache configured${NC}"
    echo "Access the application at: http://localhost/"
fi

# Nginx configuration (optional)
echo ""
read -p "Configure Nginx instead? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    NGINX_FILE="/etc/nginx/sites-available/sisdm-absensi"
    cat > "$NGINX_FILE" << EOF
server {
    listen 80;
    server_name localhost;
    root $PROJECT_DIR;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath\$root;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF
    
    ln -sf "$NGINX_FILE" /etc/nginx/sites-enabled/ 2>/dev/null || true
    nginx -t 2>/dev/null && systemctl reload nginx 2>/dev/null || true
    
    echo -e "${GREEN}✓ Nginx configured${NC}"
    echo "Access the application at: http://localhost/"
fi

# Final summary
echo ""
echo "=============================================="
echo -e "${GREEN}Installation Complete!${NC}"
echo "=============================================="
echo ""
echo "Database: $DB_NAME"
echo "Database User: $DB_USER"
echo ""
echo "Default Login Credentials:"
echo "  Admin: username=admin, password=admin123"
echo "  Officer: username=petugas1, password=officer123"
echo ""
echo "Features:"
echo "  ✓ Multi-theme support (Fluent UI, Material UI, Glassmorphism, Cyberpunk)"
echo "  ✓ Light/Dark mode (White, Light Gray, Dark Gray, Black)"
echo "  ✓ Full CRUD for Students and Attendance"
echo "  ✓ Monthly and Weekly Reports"
echo "  ✓ Daily Officer Assignment"
echo "  ✓ Logo and Background Image Upload"
echo "  ✓ Transparency and Blur Effects"
echo ""
echo -e "${YELLOW}Please ensure your web server is running!${NC}"
echo ""
