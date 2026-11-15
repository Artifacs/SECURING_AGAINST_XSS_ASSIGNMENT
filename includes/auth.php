<?php
/**
 * includes/auth.php - session, login guard and CSRF helpers.
 * Needs includes/functions.php to be loaded first (for redirect()).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Is someone logged in? */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/** The logged-in user's id (used for added_by / updated_by). */
function current_user_id(): int {
    return (int) ($_SESSION['user_id'] ?? 0);
}

/**
 * Call this at the TOP of every protected page.
 * Not logged in -> sent to login.php. Also checks the CSRF token on POST requests.
 */
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
