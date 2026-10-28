<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "rentkaro";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    // Log error without exposing sensitive info to user
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

$conn->set_charset("utf8mb4");

// Session security settings
if (session_status() === PHP_SESSION_NONE) {
    // Strip port from host — cookie domain must NOT include :80 or :443
    // For localhost, use empty string so the browser sends the cookie back correctly
    $cookieDomain = preg_replace('/:\d+$/', '', $_SERVER["HTTP_HOST"] ?? "");
    if ($cookieDomain === "localhost") $cookieDomain = "";

    session_set_cookie_params([
        "lifetime" => 0,        // Cookie lives until browser closes
        "path"     => "/",
        "domain"   => $cookieDomain,
        "secure"   => false,    // Set to true in production with HTTPS
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();

    // Regenerate session ID periodically to prevent fixation attacks
    if (!isset($_SESSION["created"])) {
        $_SESSION["created"] = time();
    } elseif (time() - $_SESSION["created"] > 600) {
        session_regenerate_id(true);
        $_SESSION["created"] = time();
    }
}

?>