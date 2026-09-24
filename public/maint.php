<?php
/**
 * DueTap maintenance page for shared hosting without SSH.
 *
 * Runs artisan tasks in-process (no shell access needed). Disabled unless
 * MAINT_KEY is set in .env. Call it as:
 *
 *   /maint.php?key=YOUR_KEY&action=check          diagnostics (PHP, DB, migrations, permissions)
 *   /maint.php?key=YOUR_KEY&action=setup          storage link + migrate + cache (first install / after update)
 *   /maint.php?key=YOUR_KEY&action=migrate        run pending migrations
 *   /maint.php?key=YOUR_KEY&action=cache          config/route/view cache
 *   /maint.php?key=YOUR_KEY&action=clear          clear all caches
 *   /maint.php?key=YOUR_KEY&action=storage-link   create public/storage link
 *   /maint.php?key=YOUR_KEY&action=schedule       run due scheduled jobs (use from a cPanel cron with curl)
 */

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex');

$base = dirname(__DIR__);
$envFile = $base . '/.env';

// --- key check happens BEFORE booting Laravel so a broken install can still be diagnosed
$expected = null;
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (preg_match('/^\s*MAINT_KEY\s*=\s*"?([^"#]+?)"?\s*$/', $line, $m)) {
            $expected = trim($m[1]);
            break;
        }
    }
}

if ($expected === null || $expected === '') {
    http_response_code(403);
    exit("Disabled. Add MAINT_KEY=<long random string> to .env to enable this page.\n");
}

$given = (string) ($_GET['key'] ?? '');
if ($given === '' || !hash_equals($expected, $given)) {
    http_response_code(403);
    exit("Access denied.\n");
}

$action = (string) ($_GET['action'] ?? 'check');
$allowed = ['check', 'setup', 'migrate', 'cache', 'clear', 'storage-link', 'schedule'];
if (!in_array($action, $allowed, true)) {
    http_response_code(400);
    exit("Unknown action. Allowed: " . implode(', ', $allowed) . "\n");
}

echo "=== DueTap maintenance: {$action} ===\n";
echo 'Time: ' . date('Y-m-d H:i:s') . "\n\n";

// --- pre-boot diagnostics (work even if Laravel cannot boot)
if ($action === 'check') {
    echo "PHP version: " . PHP_VERSION . (version_compare(PHP_VERSION, '8.2.0', '>=') ? "  OK" : "  TOO OLD (need 8.2+)") . "\n";
    foreach (['pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo', 'gd', 'zip', 'curl', 'bcmath'] as $ext) {
        echo str_pad("ext {$ext}:", 16) . (extension_loaded($ext) ? "OK" : "MISSING") . "\n";
    }
    echo "\n";
    foreach (['.env' => $envFile, 'vendor/autoload.php' => "$base/vendor/autoload.php", 'public/build/manifest.json' => "$base/public/build/manifest.json"] as $label => $path) {
        echo str_pad("{$label}:", 28) . (is_file($path) ? "present" : "MISSING") . "\n";
    }
    foreach (['storage', 'storage/logs', 'storage/framework/views', 'storage/framework/sessions', 'storage/framework/cache', 'bootstrap/cache'] as $dir) {
        $p = "$base/$dir";
        echo str_pad("{$dir}:", 28) . (is_dir($p) ? (is_writable($p) ? "writable" : "NOT WRITABLE (chmod 775)") : "MISSING") . "\n";
    }
    echo str_pad("public/storage link:", 28) . (is_link("$base/public/storage") || is_dir("$base/public/storage") ? "present" : "missing (run action=storage-link)") . "\n";
    echo str_pad("public/.htaccess:", 28) . (is_file("$base/public/.htaccess") ? "present" : "MISSING") . "\n";
    echo str_pad("root .htaccess:", 28) . (is_file("$base/.htaccess") ? "present" : "none (fine if document root is /public)") . "\n";
    // Live routing test: does a pretty URL reach Laravel?
    if (function_exists('curl_init')) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $probe = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/login';
        $ch = curl_init($probe);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_TIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => false]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        echo str_pad("URL rewriting (/login):", 28) . ($code === 200 ? "OK" : "FAILING (HTTP {$code}) - see CPANEL_DEPLOYMENT.md, Step 5 Option B") . "\n";
    }
    echo "\n";
}

// --- boot Laravel
try {
    require $base . '/vendor/autoload.php';
    $app = require $base . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
} catch (Throwable $e) {
    http_response_code(500);
    exit("Laravel failed to boot: " . get_class($e) . ": " . $e->getMessage() . "\n");
}

$artisan = static function (string $command, array $params = []): void {
    echo "\n$ php artisan {$command}" . ($params ? ' ' . implode(' ', array_keys($params)) : '') . "\n";
    try {
        Illuminate\Support\Facades\Artisan::call($command, $params);
        echo trim(Illuminate\Support\Facades\Artisan::output()) . "\n";
    } catch (Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
};

switch ($action) {
    case 'check':
        echo "APP_ENV: " . config('app.env') . "   APP_DEBUG: " . (config('app.debug') ? 'true (turn off in production)' : 'false') . "\n";
        echo "APP_URL: " . config('app.url') . "   timezone: " . config('app.timezone') . "\n";
        echo "APP_KEY: " . (config('app.key') ? 'set' : 'MISSING') . "\n";
        $smsKey = class_exists(App\Models\Setting::class) ? App\Models\Setting::smsApiKey() : config('services.sms.api_key');
        echo "Platform SMS key: " . ($smsKey ? 'set' : 'NOT SET - Admin > SMS Gateway') . "\n";
        echo "DB: " . config('database.default') . " " . config('database.connections.' . config('database.default') . '.database') . "\n";
        try {
            $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
            echo "DB connection: OK (" . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . ")\n";
            $tables = count(Illuminate\Support\Facades\Schema::getTableListing());
            echo "Tables: {$tables}\n";
            if ($tables > 0) {
                echo "Users: " . Illuminate\Support\Facades\DB::table('users')->count()
                    . ", markets: " . Illuminate\Support\Facades\DB::table('markets')->count()
                    . ", plans: " . Illuminate\Support\Facades\DB::table('plans')->count() . "\n";
            }
        } catch (Throwable $e) {
            echo "DB connection: FAILED - " . $e->getMessage() . "\n";
        }
        $artisan('migrate:status');
        $artisan('schedule:list');
        break;

    case 'setup':
        $artisan('storage:link', ['--force' => true]);
        $artisan('migrate', ['--force' => true]);
        $artisan('config:cache');
        $artisan('route:cache');
        $artisan('view:cache');
        echo "\nDone. Open the site and log in.\n";
        break;

    case 'migrate':
        $artisan('migrate', ['--force' => true]);
        $artisan('config:clear');
        break;

    case 'cache':
        $artisan('config:cache');
        $artisan('route:cache');
        $artisan('view:cache');
        break;

    case 'clear':
        $artisan('config:clear');
        $artisan('route:clear');
        $artisan('view:clear');
        $artisan('cache:clear');
        break;

    case 'storage-link':
        $artisan('storage:link', ['--force' => true]);
        break;

    case 'schedule':
        $artisan('schedule:run');
        break;
}

echo "\n=== finished ===\n";
