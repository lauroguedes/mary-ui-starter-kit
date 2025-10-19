<?php

declare(strict_types=1);

dataset('required_fields', [
    'name' => ['name', 'required'],
    'email' => ['email', 'required'],
]);

dataset('email_validation', [
    'invalid format' => ['invalid-email', 'email'],
    'missing @' => ['invalidemail.com', 'email'],
    'missing domain' => ['invalid@', 'email'],
]);

dataset('max_length_fields', [
    'name max 100' => ['name', 100],
    'name max 256' => ['name', 256],
    'email max 255' => ['email', 255],
]);

dataset('unique_fields', [
    'email' => 'email',
    'name' => 'name',
]);
