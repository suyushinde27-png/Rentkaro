<?php

require_once "../database/database.php";

header("Content-Type: application/json");

// Prevent direct access
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed."
    ]);
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";

if ($name === "" || $email === "" || $mobile === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address."
    ]);
    exit;
}

// Validate mobile number (10 digits)
if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid 10-digit mobile number."
    ]);
    exit;
}

// Improved password validation: at least 6 chars with at least one number
if (strlen($password) < 6 || !preg_match("/[0-9]/", $password)) {
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters and contain at least one number."
    ]);
    exit;
}

/* Check if email already exists */
$check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");

if (!$check) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to check account details: " . $conn->error
    ]);
    exit;
}

$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "An account with this email already exists."
    ]);
    exit;
}

/* Securely hash password */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* Insert user with is_admin = 0 (regular user) */
$stmt = $conn->prepare(
    "INSERT INTO users (name, email, mobile, password_hash, is_admin) VALUES (?, ?, ?, ?, 0)"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to create account: " . $conn->error
    ]);
    $check->close();
    $conn->close();
    exit;
}

$stmt->bind_param(
    "ssss",
    $name,
    $email,
    $mobile,
    $hashedPassword
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Account created successfully!"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to create account."
    ]);
}

$stmt->close();
$check->close();
$conn->close();

?>