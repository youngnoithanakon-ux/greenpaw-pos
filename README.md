# GreenPaw POS & Inventory System 🐾

GreenPaw is a modern, lightweight Point of Sale (POS) and inventory management system built with Laravel. It is optimized for low-resource environments and can be seamlessly deployed on microcomputers like the **Raspberry Pi Zero W**.

## 🛠 Tech Stack
- **Framework:** Laravel 13.x
- **Language:** PHP 8.4
- **Database:** SQLite (No external database service required, saving RAM and CPU)
- **Frontend:** Blade, TailwindCSS, Alpine.js, Vite

---

## 🚀 Deployment Guide for Raspberry Pi Zero W

The Raspberry Pi Zero W has limited RAM (512MB) and a single-core CPU. To run Laravel smoothly, we use **SQLite** as the database and PHP's built-in server or a lightweight web server like Nginx.

### 1. System Requirements & Preparation
Ensure your Raspberry Pi Zero W is running the latest Raspberry Pi OS (Lite version is recommended to save RAM).

Update the system:
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install PHP 8.4 & SQLite
Install PHP 8.4 and the required extensions for Laravel and SQLite.

```bash
# Add SURY PHP PPA (for Debian/Raspbian)
sudo apt install -y lsb-release apt-transport-https ca-certificates wget
sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
sudo apt update

# Install PHP 8.4 and extensions
sudo apt install -y php8.4-cli php8.4-sqlite3 php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip unzip git
```

### 3. Clone and Setup the Project
```bash
git clone https://github.com/youngnoithanakon-ux/greenpaw-pos.git
cd greenpaw-pos
git checkout raspberry-pi-zero

# Note: The vendor directory is pre-included in this branch. No composer install is needed!
```

### 4. Environment Configuration
```bash
cp .env.example .env
```
The `.env` file is already pre-configured to use `sqlite`. 

Generate the application key and create the SQLite database file:
```bash
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
```

### 5. File Permissions
Ensure the storage and cache directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

### 6. Running the Application
For a Raspberry Pi Zero W, the most resource-efficient way to run the application is using Laravel's built-in server (or Octane if you install Swoole/FrankenPHP). 

**Option A: Built-in Server (Easiest)**
```bash
php artisan serve --host=0.0.0.0 --port=80
```
*(You may need `sudo` to bind to port 80)*

**Option B: Nginx + PHP-FPM (For Production Stability)**
```bash
sudo apt install nginx php8.4-fpm
```
Configure Nginx `/etc/nginx/sites-available/default` to point to `/path/to/greenpaw-pos/public` and route PHP requests to `unix:/var/run/php/php8.4-fpm.sock`.

### 7. Access the POS
Open a web browser on any device connected to the same Wi-Fi network and navigate to the Raspberry Pi's IP address:
`http://<RASPBERRY_PI_IP>`

**Default Credentials:**
- Username: `admin`
- Password: `password`

---

## 💡 Optimizations for Pi Zero W
- **Session & Cache:** Driven by `file` to avoid Redis overhead.
- **Client-Side QR Codes:** QR codes for receipts are generated via JavaScript in the browser (`qrcode.js`), offloading CPU work from the Pi.
- **Native CSV Export:** Reports are generated via streaming `StreamedResponse` natively instead of using heavy Excel libraries, preventing Out-Of-Memory (OOM) crashes.
