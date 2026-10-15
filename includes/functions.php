<?php

// Escape output before displaying it in HTML
function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


// Create a CSRF token
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf_token'];
}


// Check a submitted CSRF token
function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token'])
        && hash_equals(
            $_SESSION['csrf_token'],
            $token
        );
}

?>