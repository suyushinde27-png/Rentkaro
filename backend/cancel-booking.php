<?php

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

$bookingId = filter_input(INPUT_POST, "booking_id", FILTER_VALIDATE_INT);

if ($bookingId === false || $bookingId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid booking ID."
    ]);
    exit;
}

// Check if booking belongs to user
$stmt = $conn->prepare(
    "SELECT b.booking_id, b.booking_status, b.vehicle_id, v.status as vehicle_status
     FROM bookings b
     INNER JOIN vehicles v ON v.vehicle_id = b.vehicle_id
     WHERE b.booking_id = ? AND b.user_id = ?"
);

$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Booking not found."
    ]);
    $stmt->close();
    exit;
}

$booking = $result->fetch_assoc();
$stmt->close();

// Check if booking can be cancelled (not already completed)
if ($booking["booking_status"] === "completed") {
    echo json_encode([
        "success" => false,
        "message" => "Cannot cancel a completed booking."
    ]);
    exit;
}

if ($booking["booking_status"] === "cancelled") {
    echo json_encode([
        "success" => false,
        "message" => "This booking has already been cancelled."
    ]);
    exit;
}

// Update booking status
$updateStmt = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = ?");
$updateStmt->bind_param("i", $bookingId);

if ($updateStmt->execute()) {
    // Release the vehicle
    $vehicleStmt = $conn->prepare("UPDATE vehicles SET status = 'available' WHERE vehicle_id = ?");
    $vehicleStmt->bind_param("i", $booking["vehicle_id"]);
    $vehicleStmt->execute();
    $vehicleStmt->close();

    echo json_encode([
        "success" => true,
        "message" => "Booking cancelled successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to cancel booking."
    ]);
}

$updateStmt->close();
$conn->close();

?>
