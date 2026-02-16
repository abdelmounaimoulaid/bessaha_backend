<?php
/**
 * Email Test Script for Hostinger
 * Upload to: public/test-email.php
 * Visit: https://aqua-locust-289318.hostingersite.com/test-email.php
 * DELETE THIS FILE AFTER TESTING!
 */

echo "<h1>📧 Email Configuration Test</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;max-width:800px;} .success{color:green;} .error{color:red;} pre{background:#f4f4f4;padding:10px;border-radius:5px;}</style>";

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

// Display current configuration
echo "<h2>Current Email Configuration</h2>";
echo "<pre>";
echo "MAIL_MAILER: " . Config::get('mail.default') . "\n";
echo "MAIL_HOST: " . Config::get('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . Config::get('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . Config::get('mail.mailers.smtp.username') . "\n";
echo "MAIL_ENCRYPTION: " . Config::get('mail.mailers.smtp.encryption') . "\n";
echo "MAIL_FROM_ADDRESS: " . Config::get('mail.from.address') . "\n";
echo "</pre>";

// Get test email from URL parameter or use default
$testEmail = $_GET['email'] ?? 'oulaidabdelmonaim2@gmail.com';

echo "<h2>Sending Test Email</h2>";
echo "<p>Sending to: <strong>$testEmail</strong></p>";
echo "<p><small>To test with different email: ?email=your@email.com</small></p>";

try {
    Mail::raw('🎉 Success! Your email configuration is working correctly on Hostinger.

This is a test email from your Food Checker backend.

If you received this email, your SMTP settings are configured properly!

---
Food Checker App
Powered by Laravel', function ($message) use ($testEmail) {
        $message->to($testEmail)
                ->subject('✅ Test Email from Food Checker - Hostinger');
    });
    
    echo "<div class='success'>";
    echo "<h3>✅ Email Sent Successfully!</h3>";
    echo "<p>Check your inbox at: <strong>$testEmail</strong></p>";
    echo "<p>If you don't see it:</p>";
    echo "<ul>";
    echo "<li>Check your spam/junk folder</li>";
    echo "<li>Wait a few minutes (sometimes delayed)</li>";
    echo "<li>Verify the email address is correct</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Email Failed</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    
    echo "<h3>Common Issues & Solutions:</h3>";
    echo "<ul>";
    echo "<li><strong>Connection refused / Could not connect:</strong> Port blocked by Hostinger. Try Hostinger SMTP instead of Gmail.</li>";
    echo "<li><strong>Authentication failed:</strong> Wrong username or password. Check your .env file.</li>";
    echo "<li><strong>Invalid address:</strong> Check MAIL_FROM_ADDRESS in .env</li>";
    echo "</ul>";
    
    echo "<h3>Recommended Fix:</h3>";
    echo "<p>Use Hostinger's SMTP server:</p>";
    echo "<pre>";
    echo "MAIL_HOST=smtp.hostinger.com\n";
    echo "MAIL_PORT=465\n";
    echo "MAIL_USERNAME=noreply@aqua-locust-289318.hostingersite.com\n";
    echo "MAIL_PASSWORD=your_hostinger_email_password\n";
    echo "MAIL_ENCRYPTION=ssl\n";
    echo "</pre>";
}

echo "<hr>";
echo "<h2>⚠️ IMPORTANT</h2>";
echo "<p style='color:red;font-weight:bold;'>DELETE THIS FILE (test-email.php) AFTER TESTING!</p>";
echo "<p>This file should not be accessible in production.</p>";
?>
