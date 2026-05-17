<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Do NOT clear the remembered_email or last_login cookies —
// those are for UX convenience and should persist across sessions.

// Redirect to login
header('Location: login.php');
exit;
?>
