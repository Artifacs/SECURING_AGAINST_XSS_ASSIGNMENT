<?php
/** logout.php - USER LOGOUT: ends the session completely. */
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    csrf_check();
    $_SESSION = [];                                  // clear all session data
    if (ini_get('session.use_cookies')) {            // delete the session cookie
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    session_start();                                 // fresh session just to show the message
    redirect('login.php', 'success', 'You have been logged out.');
}
redirect('login.php');
