# BazarBill cPanel Deployment Guide

## Prerequisites

- cPanel hosting with PHP 8.3+
- MySQL 8.0+ database
- SSH access (recommended) or File Manager
- Composer available on server

---

## Step 1: Prepare Files Locally

### 1.1 Build Frontend Assets
```bash
npm install
npm run build
```

### 1.2 Install Production Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### 1.3 Create Deployment Package
Create a ZIP of your project (exclude these):
- `node_modules/`
- `.git/`
- `tests/`
- `.env`
- `storage/logs/*`

---

## Step 2: cPanel Database Setup

### 2.1 Create MySQL Database
1. Login to cPanel
2. Go to **MySQL Databases**
3. Create new database: `yourusername_bazarbill`
4. Create new user with strong password
5. Add user to database with **ALL PRIVILEGES**

### 2.2 Import Database Schema
1. Go to **phpMyAdmin**
2. Select your new database
3. Click **Import** tab
4. Upload `database/bazarbill_init.sql`
5. Click **Go**

---

## Step 3: Upload Files

### Option A: File Manager (Simple)
1. Go to **File Manager** in cPanel
2. Navigate to `public_html` (or subdomain folder)
3. Upload your ZIP file
4. Extract the ZIP
5. Move all contents to the root

### Option B: SSH (Recommended)
```bash
# Connect via SSH
ssh username@yourdomain.com

# Navigate to public_html
cd public_html

# Upload via SCP from local machine
scp bazarbill.zip username@yourdomain.com:~/public_html/

# Extract
unzip bazarbill.zip
```

---

## Step 4: Configure Laravel

### 4.1 Create .env File
Create `.env` in your project root:

```env
APP_NAME=BazarBill
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

APP_LOCALE=bn
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yourusername_bazarbill
DB_USERNAME=yourusername_dbuser
DB_PASSWORD=your_secure_password

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database
QUEUE_CONNECTION=database

# Platform SMS gateway (your bulksmsbd.net account). Markets without their own
# key send through this account and consume the SMS credits you assign them.
SMS_API_KEY=your_bulksmsbd_api_key
SMS_SENDER_ID=8809617642636
SMS_LOW_CREDIT_THRESHOLD=20

# Shown to market owners on the "subscription expired" and renewal screens
SUPPORT_PHONE=01XXXXXXXXX
SUPPORT_EMAIL=support@yourdomain.com
```

### 4.2 Generate Application Key
Via SSH:
```bash
php artisan key:generate
```

Or manually generate and add to `.env`:
```bash
# Run locally
php artisan key:generate --show
# Copy the output to APP_KEY in .env
```

### 4.3 Set Permissions
```bash
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
```

---

## Step 5: Configure Document Root

### Option A: Subdomain/Addon Domain
1. Go to **Subdomains** or **Addon Domains** in cPanel
2. Set document root to: `/public_html/bazarbill/public`

### Option B: Document root is the app folder (.htaccess method)
Create `.htaccess` in the app folder (next to `artisan`). This version routes everything itself and works on LiteSpeed hosts where `public/.htaccess` is ignored after a forward:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Already inside public/ (after an internal rewrite): stop here
    RewriteRule ^public/ - [L]

    # Existing file or folder inside public/ (assets, uploads, maint.php): serve it
    RewriteCond %{DOCUMENT_ROOT}/public/$1 -f [OR]
    RewriteCond %{DOCUMENT_ROOT}/public/$1 -d
    RewriteRule ^(.*)$ public/$1 [L]

    # Everything else: Laravel front controller
    RewriteRule ^ public/index.php [L]
</IfModule>

<FilesMatch "^(\.env|composer\.(json|lock)|artisan|package(-lock)?\.json|vite\.config\.js|tailwind\.config\.js|postcss\.config\.js)$">
    Require all denied
</FilesMatch>
```

Symptom this fixes: `/` redirects to `/login` but `/login` shows the server's own 404 page, while `/public/index.php/login` works.

### Option C: Move public folder contents (Alternative)
1. Move contents of `public/` to `public_html/`
2. Edit `public_html/index.php`:

```php
// Change these lines
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// To (assuming app is in a folder like 'bazarbill')
require __DIR__.'/../bazarbill/vendor/autoload.php';
$app = require_once __DIR__.'/../bazarbill/bootstrap/app.php';
```

---

## Step 6: Final Setup Commands

### No SSH or Terminal? Use the browser maintenance page

1. Add a long random line to `.env` (File Manager → Edit):
   ```
   MAINT_KEY=paste-40-random-characters-here
   ```
2. Open `https://yourdomain.com/maint.php?key=YOUR_KEY&action=check` and fix anything reported as MISSING or NOT WRITABLE.
3. Open `https://yourdomain.com/maint.php?key=YOUR_KEY&action=setup` — this creates the storage link, runs migrations and builds the caches.
4. After every future upload, open the same `action=setup` URL again.

Other actions: `clear` (clear caches), `migrate`, `cache`, `storage-link`, `schedule` (runs due jobs; use it from cron with `curl`, see 6b). Remove `MAINT_KEY` from `.env` to disable the page.

### With SSH / Terminal

```bash
# Apply any new migrations (SMS credits, plans, subscriptions, reminder tracking)
php artisan migrate --force

# Seed the three starter subscription plans (safe to re-run; edit them in Admin → Plans)
php artisan db:seed --class=PlanSeeder --force

# Clear and cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Create storage link
php artisan storage:link

# Optimize
php artisan optimize
```

---

## Step 6b: Cron Job for Scheduled Tasks (REQUIRED)

BazarBill runs four background jobs through Laravel's scheduler:

| Job | When | What it does |
|-----|------|--------------|
| `subscriptions:process` | daily 00:10 | Expires finished trials/subscriptions, activates pre-paid renewals, allocates monthly SMS credits on yearly plans |
| `invoices:mark-overdue` | daily 00:20 | Flags unpaid invoices past their due date |
| `invoices:generate-monthly` | 1st of month 06:00 | Creates rent invoices for markets with "auto-generate" enabled |
| `invoices:send-reminders` | daily 10:00 | SMS payment reminders for markets with "automatic reminders" enabled |

None of these run unless cron calls the scheduler every minute.

1. In cPanel go to **Advanced → Cron Jobs**.
2. Add a new cron job with **Common Settings: Once Per Minute** (`* * * * *`).
3. Command. Without SSH, the simplest is to call the maintenance page:

```bash
curl -s "https://yourdomain.com/maint.php?key=YOUR_KEY&action=schedule" > /dev/null 2>&1
```

   Or, if you know your PHP binary path:

```bash
cd /home/USERNAME/public_html/bazarbill && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Find your PHP binary with `which php` over SSH, or use cPanel's **MultiPHP Manager** path such as `/opt/cpanel/ea-php83/root/usr/bin/php`.

4. Verify from SSH:

```bash
php artisan schedule:list
```

If the server timezone differs from Bangladesh, set `APP_TIMEZONE=Asia/Dhaka` in `.env` (and `'timezone' => env('APP_TIMEZONE', 'UTC')` in `config/app.php`) so the 10:00 reminder actually goes out at 10:00 local time.

---

## Step 7: Verify Installation

### 7.1 Access Your Site
Visit: `https://yourdomain.com`

### 7.2 Login with Default Admin
- **Email:** admin@bazarbill.com
- **Password:** password

**IMPORTANT:** Change the admin password immediately after first login!

### 7.3 SaaS Checklist
- **Admin → Plans**: review prices, shop limits, SMS allowance and which plan is the default for signups.
- **Admin → SMS Credits**: after a market pays for a recharge (bKash / Nagad / cash), add the credits here with the TrxID as reference.
- **Admin → Subscriptions**: record paid activations and renewals; the market is locked out automatically when the period ends.
- **Public signup** is at `/register`: it creates the market, the owner login and a free trial on the default plan.

---

## Troubleshooting

### 500 Internal Server Error
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Fix permissions
chmod -R 775 storage bootstrap/cache
```

### Blank Page
- Ensure `APP_DEBUG=true` temporarily to see errors
- Check PHP version: `php -v` (needs 8.3+)

### Database Connection Error
- Verify database credentials in `.env`
- Check if database user has proper privileges
- Try `localhost` vs `127.0.0.1` for DB_HOST

### Storage Link Issues
```bash
php artisan storage:link --force
```

### Class Not Found Errors
```bash
composer dump-autoload
php artisan clear-compiled
```

---

## Cron Job Setup (Optional)

For scheduled tasks, add to cPanel Cron Jobs:

```
* * * * * cd /home/username/public_html/bazarbill && php artisan schedule:run >> /dev/null 2>&1
```

---

## Queue Worker Setup (Optional)

If using queues, set up Supervisor or use cPanel's cron:

```
* * * * * cd /home/username/public_html/bazarbill && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

---

## Security Checklist

- [ ] Change default admin password
- [ ] Set `APP_DEBUG=false`
- [ ] Ensure `.env` is not accessible via web
- [ ] Enable HTTPS (SSL certificate)
- [ ] Set proper file permissions (755 for folders, 644 for files)
- [ ] Remove default `bazarbill_init.sql` from public access

---

## File Structure Reference

```
public_html/
├── bazarbill/           # Laravel app root
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/          # Document root should point here
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── artisan
```
