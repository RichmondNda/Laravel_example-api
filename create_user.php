<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::factory()->create();

echo "Utilisateur créé avec succès!\n";
echo "ID: {$user->id}\n";
echo "Nom: {$user->name}\n";
echo "Email: {$user->email}\n";
