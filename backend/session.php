<?php

require_once "../database/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "User is not logged in."
    ]);
    exit;
}

$userId = (int) $_SESSION["user_id"];
$userStmt = $conn->prepare(
    "SELECT name, email, mobile, city, reward_points FROM users WHERE user_id = ?"
);

if (!$userStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to load user details."
    ]);
    exit;
}

$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "User account was not found."
    ]);
    $conn->close();
    exit;
}

$statsStmt = $conn->prepare(
    "SELECT
        SUM(booking_status IN ('pending', 'confirmed', 'active')) AS active_bookings,
        SUM(booking_status = 'completed') AS total_trips
     FROM bookings
     WHERE user_id = ?"
);
$statsStmt->bind_param("i", $userId);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();

// Check if user is admin
$isAdminStmt = $conn->prepare("SELECT is_admin FROM users WHERE user_id = ?");
$isAdminStmt->bind_param("i", $userId);
$isAdminStmt->execute();
$isAdminResult = $isAdminStmt->get_result()->fetch_assoc();
$isAdmin = $isAdminResult ? $isAdminResult["is_admin"] : 0;
$isAdminStmt->close();

$bookingStmt = $conn->prepare(
    "SELECT b.booking_code, b.pickup_date, b.return_date, b.pickup_location,
            b.booking_status, v.vehicle_name
     FROM bookings b
     INNER JOIN vehicles v ON v.vehicle_id = b.vehicle_id
     WHERE b.user_id = ? AND b.booking_status <> 'cancelled'
     ORDER BY b.pickup_date DESC, b.booking_id DESC
     LIMIT 1"
);
$bookingStmt->bind_param("i", $userId);
$bookingStmt->execute();
$booking = $bookingStmt->get_result()->fetch_assoc();
$bookingStmt->close();

echo json_encode([
    "success" => true,
    "user_id" => $userId,
    "name" => $user["name"],
    "email" => $user["email"],
    "mobile" => $user["mobile"] ?? "",
    "city" => $user["city"] ?? "",
    "active_bookings" => (int) ($stats["active_bookings"] ?? 0),
    "total_trips" => (int) ($stats["total_trips"] ?? 0),
    "saved_vehicles" => 0,
    "reward_points" => (int) $user["reward_points"],
    "is_admin" => (int) $isAdmin,
    "current_booking" => $booking ?: null
]);

$conn->close();

?>