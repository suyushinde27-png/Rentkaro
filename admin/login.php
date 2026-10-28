<?php

require_once "../database/database.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required."
    ]);
    exit;
}

// Check for admin credentials
// Admins are users with is_admin = 1 in the database
// For production, you should have a separate admin_users table or role column
$stmt = $conn->prepare(
    "SELECT user_id, name, email, password_hash FROM users WHERE email = ? AND is_admin = 1"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to process login."
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid admin credentials."
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password_hash"])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid admin credentials."
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$_SESSION["admin_id"] = $user["user_id"];
$_SESSION["admin_name"] = $user["name"];
$_SESSION["admin_email"] = $user["email"];

echo json_encode([
    "success" => true,
    "message" => "Admin login successful!",
    "user_id" => $user["user_id"],
    "name" => $user["name"],
    "email" => $user["email"]
]);

$stmt->close();
$conn->close();

?>
