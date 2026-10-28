<?php

// Use the shared session bootstrap so cookie params match login/session.php.
// Without this, logout can start a *different* session and fail to clear the real one.
require_once __DIR__ . "/../database/database.php";

// Unset all session variables
$_SESSION = [];

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../login.html", true, 302);
exit;

?>