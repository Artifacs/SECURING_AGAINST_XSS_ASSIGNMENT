<?php

if (!headers_sent()) {
    //Security headers 
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
    header('X-Content-Type-Options: nosniff');   
    header('X-Frame-Options: DENY');             
    header('Referrer-Policy: same-origin');
}

//Session cookie flags
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,      
        'samesite' => 'Lax',     
        'secure'   => !empty($_SERVER['HTTPS']),   
    ]);
    session_start();
}


function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}


function current_user_id(): int {
    return (int) ($_SESSION['user_id'] ?? 0);
}


function require_login(): void {
    if (!is_logged_in()) {
        redirect('login.php');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
    }
}

/** Create (once per session) a random token that proves a form came from our site. */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Hidden input to put inside every <form method="post">. */
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Stop the request if the posted token doesn't match the session token. */
function csrf_check(): void {
    $sent     = $_POST['csrf'] ?? '';
    $expected = $_SESSION['csrf'] ?? '';
    // an empty token never counts as valid
    if ($expected === '' || !hash_equals($expected, $sent)) {
        http_response_code(403);
        die('Invalid or missing security token. Go back, refresh the page and try again.');
    }
}
