<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Get the test user
$user = User::where('email', 'test@example.com')->first();

if (!$user) {
    echo "Test user not found. Creating one...\n";
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
}

echo "User: {$user->name} ({$user->email})\n";
echo "User ID: {$user->id}\n\n";

// Create a new token
$token = $user->createToken('web-app');

echo "✅ New token created!\n\n";
echo "Full token string (use this in your requests):\n";
echo $token->plainTextToken . "\n\n";

echo "To test the API, use this curl command:\n";
echo "curl -X POST http://localhost:8000/api/logout \\\n";
echo "  -H \"Authorization: Bearer {$token->plainTextToken}\" \\\n";
echo "  -H \"Content-Type: application/json\"\n";
