<?php

session_start();

require_once "../database/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please log in."
    ]);
    exit;
}

$userId = (int) $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit;
}

// Get updated profile data
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$city = trim($_POST["city"] ?? "");

if ($name === "" || $email === "" || $mobile === "") {
    echo json_encode([
        "success" => false,
        "message" => "Name, email, and phone are required."
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

// Check if email already exists for another user
$checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
$checkStmt->bind_param("si", $email, $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "This email is already in use by another account."
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Update profile
$stmt = $conn->prepare(
    "UPDATE users SET name = ?, email = ?, mobile = ?, city = ? WHERE user_id = ?"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to update profile: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param(
    "ssssi",
    $name,
    $email,
    $mobile,
    $city,
    $userId
);

if ($stmt->execute()) {
    // Update session data
    $_SESSION["name"] = $name;
    $_SESSION["email"] = $email;

    echo json_encode([
        "success" => true,
        "message" => "Profile updated successfully!",
        "name" => $name,
        "email" => $email,
        "mobile" => $mobile,
        "city" => $city
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update profile."
    ]);
}

$stmt->close();
$conn->close();

?>
