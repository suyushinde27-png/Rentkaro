<?php

require_once "../database/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please log in to make a payment."
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
$paymentMethod = trim($_POST["payment_method"] ?? "");

if ($bookingId === false || $bookingId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid booking ID."
    ]);
    exit;
}

if ($paymentMethod === "") {
    echo json_encode([
        "success" => false,
        "message" => "Please select a payment method."
    ]);
    exit;
}

// Validate payment method
$validMethods = ["upi", "card", "cash"];
if (!in_array($paymentMethod, $validMethods)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid payment method."
    ]);
    exit;
}

// Check booking exists and belongs to user
$stmt = $conn->prepare(
    "SELECT b.booking_id, b.booking_code, b.booking_status, b.rental_cost, b.vehicle_id,
            v.vehicle_name
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

// Check booking status
if ($booking["booking_status"] !== "pending") {
    echo json_encode([
        "success" => false,
        "message" => "This booking cannot be paid for. Status: " . $booking["booking_status"]
    ]);
    exit;
}

// Process payment (simulated - in production, integrate with payment gateway)
$transactionId = "TXN" . strtoupper(substr(md5(uniqid()), 0, 8));
$paymentSuccessful = true; // Simulated

if ($paymentSuccessful) {
    // Update booking status to confirmed
    $updateStmt = $conn->prepare(
        "UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = ?"
    );
    $updateStmt->bind_param("i", $bookingId);
    $updateStmt->execute();
    $updateStmt->close();

    // Update vehicle status to booked
    $vehicleStmt = $conn->prepare("UPDATE vehicles SET status = 'booked' WHERE vehicle_id = ?");
    $vehicleStmt->bind_param("i", $booking["vehicle_id"]);
    $vehicleStmt->execute();
    $vehicleStmt->close();

    // Add reward points (1 point for every ₹100)
    $rewardPoints = (int) floor($booking["rental_cost"] / 100);
    $pointsStmt = $conn->prepare("UPDATE users SET reward_points = reward_points + ? WHERE user_id = ?");
    $pointsStmt->bind_param("ii", $rewardPoints, $userId);
    $pointsStmt->execute();
    $pointsStmt->close();

    echo json_encode([
        "success" => true,
        "message" => "Payment successful!",
        "transaction_id" => $transactionId,
        "booking_id" => $bookingId,
        "booking_code" => $booking["booking_code"],
        "amount_paid" => $booking["rental_cost"],
        "payment_method" => $paymentMethod,
        "reward_points_earned" => $rewardPoints
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Payment processing failed. Please try again."
    ]);
}

$conn->close();

?>
