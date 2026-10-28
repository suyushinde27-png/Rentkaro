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

$stmt = $conn->prepare(
    "SELECT user_id, name, email, password_hash FROM users WHERE email = ?"
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
        "message" => "Invalid email or password."
    ]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password_hash"])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);
    exit;
}

$_SESSION["user_id"] = $user["user_id"];
$_SESSION["name"] = $user["name"];
$_SESSION["email"] = $user["email"];

echo json_encode([
    "success" => true,
    "message" => "Login successful!",
    "user_id" => $user["user_id"],
    "name" => $user["name"],
    "email" => $user["email"]
]);

$stmt->close();
$conn->close();

?>