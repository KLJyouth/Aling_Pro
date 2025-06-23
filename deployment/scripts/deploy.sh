#!/bin/bash

# AlingAi Pro - 三完编译 (Three Complete Compilation) Deployment Script
# Complete Production Deployment for Enhanced PHP 8.0+ Architecture
# 
# System Requirements:
# - PHP 8.0+ with extensions: pdo, pdo_mysql, curl, json, mbstring, openssl, fileinfo
# - MySQL 8.0+ 
# - Nginx 1.20+
# - Linux (CentOS 8+/Ubuntu 20.04+)
# 
# @package AlingAi\Pro
# @version 3.0.0

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="AlingAi Pro Enhanced"
PROJECT_VERSION="3.0.0"
PHP_MIN_VERSION="8.0"
MYSQL_MIN_VERSION="8.0"
NGINX_MIN_VERSION="1.20"

echo -e "${BLUE}🚀 ${PROJECT_NAME} v${PROJECT_VERSION} Deployment${NC}"
echo -e "${BLUE}=====================================================${NC}"
echo -e "${BLUE}三完编译 (Three Complete Compilation) Installation${NC}"
echo ""

# Function to print status messages
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check system requirements
check_system_requirements() {
    print_status "Checking system requirements..."
    
    # Check PHP version
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -r "echo PHP_VERSION;")
        print_status "PHP Version: $PHP_VERSION"
        
        if php -r "exit(version_compare(PHP_VERSION, '$PHP_MIN_VERSION', '<') ? 1 : 0);"; then
            print_error "PHP $PHP_MIN_VERSION or higher is required. Found: $PHP_VERSION"
            exit 1
        fi
    else
        print_error "PHP is not installed"
        exit 1
    fi
    
    # Check required PHP extensions
    REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "curl" "json" "mbstring" "openssl" "fileinfo")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if php -m | grep -q "^$ext$"; then
            print_status "PHP Extension $ext: ✅ Available"
        else
            print_error "Required PHP extension '$ext' is not installed"
            exit 1
        fi
    done
    
    # Check MySQL
    if command -v mysql &> /dev/null; then
        MYSQL_VERSION=$(mysql --version | grep -oP '\d+\.\d+\.\d+' | head -1)
        print_status "MySQL Version: $MYSQL_VERSION"
    else
        print_warning "MySQL client not found in PATH"
    fi
    
    # Check Nginx
    if command -v nginx &> /dev/null; then
        NGINX_VERSION=$(nginx -v 2>&1 | grep -oP '\d+\.\d+\.\d+')
        print_status "Nginx Version: $NGINX_VERSION"
    else
        print_warning "Nginx not found in PATH"
    fi
    
    print_status "✅ System requirements check passed"
}

# Function to setup directories and permissions
setup_directories() {
    print_status "Setting up directories and permissions..."
    
    # Create required directories
    mkdir -p storage/logs
    mkdir -p storage/cache
    mkdir -p storage/sessions
    mkdir -p storage/uploads
    mkdir -p storage/backups
    mkdir -p public/assets
    mkdir -p public/uploads
    
    # Set permissions
    chmod -R 755 storage/
    chmod -R 755 public/
    
    # Set web server ownership (adjust as needed)
    if id "nginx" &>/dev/null; then
        chown -R nginx:nginx storage/
        chown -R nginx:nginx public/
        print_status "Set ownership to nginx user"
    elif id "www-data" &>/dev/null; then
        chown -R www-data:www-data storage/
        chown -R www-data:www-data public/
        print_status "Set ownership to www-data user"
    else
        print_warning "Could not determine web server user. Please set ownership manually."
    fi
    
    print_status "✅ Directories and permissions configured"
}

# Function to install Composer dependencies
install_dependencies() {
    print_status "Installing Composer dependencies..."
    
    if ! command -v composer &> /dev/null; then
        print_error "Composer is not installed. Please install Composer first."
        exit 1
    fi
    
    # Install production dependencies
    composer install --no-dev --optimize-autoloader --no-interaction
    
    print_status "✅ Dependencies installed"
}

# Function to setup database
setup_database() {
    print_status "Setting up database..."
    
    # Check if .env file exists
    if [ ! -f ".env" ]; then
        print_error ".env file not found. Please create it first."
        exit 1
    fi
    
    # Run database migrations
    print_status "Running database migrations..."
    php migrate_database.php
    
    print_status "✅ Database setup completed"
}

# Function to configure web server
configure_nginx() {
    print_status "Configuring Nginx..."
    
    # Get current directory
    CURRENT_DIR=$(pwd)
    
    cat > /tmp/alingai_nginx.conf << EOF
server {
    listen 80;
    server_name localhost;
    root ${CURRENT_DIR}/public;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Hide Nginx version
    server_tokens off;
    
    # Main location block
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
        
        # Prevent PHP scripts in uploads
        location ~ ^/uploads/.*\.php$ {
            deny all;
        }
    }
    
    # Static assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /(vendor|storage|database|\.env) {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # API rate limiting (if needed)
    location ^~ /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
}

# Rate limiting zone
limit_req_zone \$binary_remote_addr zone=api:10m rate=60r/m;
EOF
    
    print_status "Nginx configuration created at /tmp/alingai_nginx.conf"
    print_warning "Please copy this configuration to your Nginx sites directory and restart Nginx"
    print_status "Example: sudo cp /tmp/alingai_nginx.conf /etc/nginx/sites-available/alingai"
    print_status "Example: sudo ln -s /etc/nginx/sites-available/alingai /etc/nginx/sites-enabled/"
    print_status "Example: sudo nginx -t && sudo systemctl restart nginx"
}

# Function to create systemd service for background workers
create_worker_service() {
    print_status "Creating systemd service for AI agents..."
    
    CURRENT_DIR=$(pwd)
    
    cat > /tmp/alingai-workers.service << EOF
[Unit]
Description=AlingAi Pro AI Agent Workers
After=network.target mysql.service

[Service]
Type=simple
User=nginx
Group=nginx
WorkingDirectory=${CURRENT_DIR}
ExecStart=/usr/bin/php ${CURRENT_DIR}/worker.php
Restart=always
RestartSec=5
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=alingai-workers

[Install]
WantedBy=multi-user.target
EOF
    
    print_status "Systemd service created at /tmp/alingai-workers.service"
    print_warning "To install: sudo cp /tmp/alingai-workers.service /etc/systemd/system/"
    print_warning "Then run: sudo systemctl daemon-reload && sudo systemctl enable alingai-workers"
}

# Function to run system tests
run_tests() {
    print_status "Running system tests..."
    
    # Test PHP syntax
    find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \; > /dev/null
    print_status "✅ PHP syntax check passed"
    
    # Test database connection
    php -r "
    require_once 'vendor/autoload.php';
    use AlingAi\Services\DatabaseService;
    if (file_exists('.env')) {
        \$dotenv = Dotenv\Dotenv::createImmutable('.');
        \$dotenv->load();
    }
    try {
        \$db = new DatabaseService();
        \$db->getPdo();
        echo 'Database connection: OK' . PHP_EOL;
    } catch (Exception \$e) {
        echo 'Database connection failed: ' . \$e->getMessage() . PHP_EOL;
        exit(1);
    }
    "
    
    print_status "✅ System tests passed"
}

# Function to optimize system
optimize_system() {
    print_status "Optimizing system performance..."
    
    # Generate optimized autoloader
    composer dump-autoload --optimize --no-dev
    
    # Clear any existing caches
    rm -rf storage/cache/*
    
    # Set optimal file permissions
    find . -type f -name "*.php" -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    
    print_status "✅ System optimization completed"
}

# Function to create backup script
create_backup_script() {
    print_status "Creating backup script..."
    
    cat > backup.sh << 'EOF'
#!/bin/bash
# AlingAi Pro Backup Script

BACKUP_DIR="storage/backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="alingai_backup_${DATE}.tar.gz"

echo "Creating backup: $BACKUP_FILE"

# Create backup directory
mkdir -p $BACKUP_DIR

# Create application backup (excluding logs and cache)
tar -czf "${BACKUP_DIR}/${BACKUP_FILE}" \
    --exclude='storage/logs/*' \
    --exclude='storage/cache/*' \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='.git' \
    .

echo "Backup created successfully: ${BACKUP_DIR}/${BACKUP_FILE}"

# Keep only last 7 backups
cd $BACKUP_DIR
ls -t alingai_backup_*.tar.gz | tail -n +8 | xargs -r rm --

echo "Cleanup completed"
EOF
    
    chmod +x backup.sh
    print_status "✅ Backup script created: ./backup.sh"
}

# Main deployment function
main() {
    echo -e "${BLUE}Starting deployment process...${NC}"
    echo ""
    
    # Check if running as root (some operations require elevated privileges)
    if [[ $EUID -eq 0 ]]; then
        print_warning "Running as root. Be careful with file permissions."
    fi
    
    # Run deployment steps
    check_system_requirements
    echo ""
    
    setup_directories
    echo ""
    
    install_dependencies
    echo ""
    
    setup_database
    echo ""
    
    configure_nginx
    echo ""
    
    create_worker_service
    echo ""
    
    run_tests
    echo ""
    
    optimize_system
    echo ""
    
    create_backup_script
    echo ""
    
    # Final status
    echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
    echo -e "${GREEN}=====================================================${NC}"
    echo -e "${GREEN}${PROJECT_NAME} v${PROJECT_VERSION} is ready!${NC}"
    echo ""
    echo -e "${YELLOW}Next steps:${NC}"
    echo "1. Copy Nginx configuration: sudo cp /tmp/alingai_nginx.conf /etc/nginx/sites-available/"
    echo "2. Enable site: sudo ln -s /etc/nginx/sites-available/alingai_nginx.conf /etc/nginx/sites-enabled/"
    echo "3. Test Nginx: sudo nginx -t"
    echo "4. Restart Nginx: sudo systemctl restart nginx"
    echo "5. Install worker service: sudo cp /tmp/alingai-workers.service /etc/systemd/system/"
    echo "6. Enable workers: sudo systemctl enable alingai-workers && sudo systemctl start alingai-workers"
    echo "7. Test the application in your browser"
    echo ""
    echo -e "${BLUE}三完编译 (Three Complete Compilation) deployment complete! 🚀${NC}"
}

# Run main function
main "$@"
