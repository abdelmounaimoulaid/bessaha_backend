<?php
/**
 * Diagnostic Script for Hostinger Deployment
 * Upload this to: public/diagnose.php
 * Visit: https://aqua-locust-289318.hostingersite.com/diagnose.php
 * DELETE THIS FILE AFTER DIAGNOSING!
 */

echo "<h1>🔍 Food Checker Backend Diagnostics</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} pre{background:#f4f4f4;padding:10px;border-radius:5px;}</style>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
$phpVersion = phpversion();
echo $phpVersion >= '8.2' 
    ? "<p class='success'>✅ PHP $phpVersion (Compatible)</p>" 
    : "<p class='error'>❌ PHP $phpVersion (Need 8.2+)</p>";

// Test 2: Required Extensions
echo "<h2>2. PHP Extensions</h2>";
$required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo'];
foreach ($required as $ext) {
    echo extension_loaded($ext) 
        ? "<p class='success'>✅ $ext</p>" 
        : "<p class='error'>❌ $ext (MISSING)</p>";
}

// Test 3: Laravel Files
echo "<h2>3. Laravel Files</h2>";
$files = [
    '../vendor/autoload.php' => 'Composer autoload',
    '../bootstrap/app.php' => 'Laravel bootstrap',
    '../.env' => 'Environment file',
    '../routes/api.php' => 'API routes',
];
foreach ($files as $file => $name) {
    echo file_exists(__DIR__ . '/' . $file)
        ? "<p class='success'>✅ $name</p>"
        : "<p class='error'>❌ $name (MISSING)</p>";
}

// Test 4: .env Configuration
echo "<h2>4. Environment Configuration</h2>";
if (file_exists(__DIR__ . '/../.env')) {
    $env = file_get_contents(__DIR__ . '/../.env');
    $hasAppKey = strpos($env, 'APP_KEY=base64:') !== false;
    $hasDbConfig = strpos($env, 'DB_DATABASE=') !== false;
    
    echo $hasAppKey 
        ? "<p class='success'>✅ APP_KEY is set</p>" 
        : "<p class='error'>❌ APP_KEY is missing</p>";
    echo $hasDbConfig 
        ? "<p class='success'>✅ DB_DATABASE is configured</p>" 
        : "<p class='error'>❌ DB_DATABASE is missing</p>";
} else {
    echo "<p class='error'>❌ .env file not found</p>";
}

// Test 5: Database Connection
echo "<h2>5. Database Connection</h2>";
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $config = $app['config'];
    $dbHost = $config->get('database.connections.mysql.host');
    $dbName = $config->get('database.connections.mysql.database');
    $dbUser = $config->get('database.connections.mysql.username');
    $dbPass = $config->get('database.connections.mysql.password');
    
    echo "<p>Host: <code>$dbHost</code></p>";
    echo "<p>Database: <code>$dbName</code></p>";
    echo "<p>Username: <code>$dbUser</code></p>";
    echo "<p>Password: <code>" . (empty($dbPass) ? 'EMPTY' : str_repeat('*', strlen($dbPass))) . "</code></p>";
    
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName",
        $dbUser,
        $dbPass
    );
    echo "<p class='success'>✅ Database connection successful!</p>";
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables found: " . count($tables) . "</p>";
    if (count($tables) > 0) {
        echo "<pre>" . implode("\n", $tables) . "</pre>";
    } else {
        echo "<p class='warning'>⚠️ No tables found. Run migrations!</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 6: Storage Permissions
echo "<h2>6. Storage Permissions</h2>";
$storagePath = __DIR__ . '/../storage/logs';
echo is_writable($storagePath)
    ? "<p class='success'>✅ Storage is writable</p>"
    : "<p class='error'>❌ Storage is not writable (chmod 755 needed)</p>";

// Test 7: Routes
echo "<h2>7. API Routes</h2>";
try {
    $routes = file_get_contents(__DIR__ . '/../routes/api.php');
    echo strpos($routes, 'register') !== false
        ? "<p class='success'>✅ Register route exists</p>"
        : "<p class='error'>❌ Register route missing</p>";
    echo strpos($routes, 'login') !== false
        ? "<p class='success'>✅ Login route exists</p>"
        : "<p class='error'>❌ Login route missing</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Cannot read routes: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 8: .htaccess
echo "<h2>8. .htaccess Files</h2>";
echo file_exists(__DIR__ . '/.htaccess')
    ? "<p class='success'>✅ public/.htaccess exists</p>"
    : "<p class='error'>❌ public/.htaccess missing</p>";
echo file_exists(__DIR__ . '/../.htaccess')
    ? "<p class='success'>✅ root/.htaccess exists</p>"
    : "<p class='warning'>⚠️ root/.htaccess missing (optional)</p>";

// Test 9: Recent Errors
echo "<h2>9. Recent Laravel Errors</h2>";
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lastError = substr($logs, -2000); // Last 2000 characters
    echo "<pre>" . htmlspecialchars($lastError) . "</pre>";
} else {
    echo "<p class='warning'>⚠️ No log file found</p>";
}

echo "<hr>";
echo "<h2>⚠️ IMPORTANT</h2>";
echo "<p style='color:red;font-weight:bold;'>DELETE THIS FILE (diagnose.php) AFTER REVIEWING!</p>";
echo "<p>This file exposes sensitive information about your server.</p>";
