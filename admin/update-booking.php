<?php

session_start();

require_once "../database/database.php";

header("Content-Type: application/json");

// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please log in as admin."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit;
}

$bookingId = filter_input(INPUT_POST, "booking_id", FILTER_VALIDATE_INT);
$status = trim($_POST["status"] ?? "");

if ($bookingId === false || $bookingId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid booking ID."
    ]);
    exit;
}

if (!in_array($status, ["confirmed", "active", "completed", "cancelled"])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid status."
    ]);
    exit;
}

// Update booking status
$updateStmt = $conn->prepare("UPDATE bookings SET booking_status = ? WHERE booking_id = ?");

// Track previous status for vehicle status management
$prevStatusStmt = $conn->prepare("SELECT booking_status, vehicle_id FROM bookings WHERE booking_id = ?");
$prevStatusStmt->bind_param("i", $bookingId);
$prevStatusStmt->execute();
$prevStatusResult = $prevStatusStmt->get_result()->fetch_assoc();
$prevStatusStmt->close();

if (!$prevStatusResult) {
    echo json_encode([
        "success" => false,
        "message" => "Booking not found."
    ]);
    exit;
}

$updateStmt->bind_param("si", $status, $bookingId);

if ($updateStmt->execute()) {
    // Update vehicle status based on booking status
    $vehicleStmt = $conn->prepare("UPDATE vehicles SET status = ? WHERE vehicle_id = ?");
    $vehicleId = $prevStatusResult["vehicle_id"];

    if ($status === "cancelled") {
        $vehicleStmt->bind_param("si", "available", $vehicleId);
    } elseif ($status === "completed") {
        $vehicleStmt->bind_param("si", "available", $vehicleId);
    } elseif ($status === "confirmed") {
        $vehicleStmt->bind_param("si", "booked", $vehicleId);
    } elseif ($status === "active") {
        $vehicleStmt->bind_param("si", "booked", $vehicleId);
    } else {
        $vehicleStmt->bind_param("si", "available", $vehicleId);
    }
    $vehicleStmt->execute();
    $vehicleStmt->close();

    echo json_encode([
        "success" => true,
        "message" => "Booking status updated successfully!"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update booking status."
    ]);
}

$updateStmt->close();
$conn->close();

?>
