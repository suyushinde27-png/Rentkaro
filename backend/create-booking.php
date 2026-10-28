<?php

require_once "../database/database.php";

header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please log in to make a booking."
    ]);
    exit;
}

// Validate request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit;
}

// Get and validate input data
$userId = (int) $_SESSION["user_id"];
$vehicleId = filter_input(INPUT_POST, "vehicle_id", FILTER_VALIDATE_INT);
$pickupDate = trim($_POST["pickup_date"] ?? "");
$returnDate = trim($_POST["return_date"] ?? "");
$pickupLocation = trim($_POST["pickup_location"] ?? "");
$dropoffLocation = trim($_POST["dropoff_location"] ?? "");

if ($vehicleId === false || $vehicleId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid vehicle selected."
    ]);
    exit;
}

if ($pickupDate === "" || $returnDate === "" || $pickupLocation === "" || $dropoffLocation === "") {
    echo json_encode([
        "success" => false,
        "message" => "All booking fields are required."
    ]);
    exit;
}

// Validate dates
$pdate = DateTime::createFromFormat("Y-m-d", $pickupDate);
$rdate = DateTime::createFromFormat("Y-m-d", $returnDate);

if (!$pdate || !$rdate) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid date format."
    ]);
    exit;
}

if ($rdate <= $pdate) {
    echo json_encode([
        "success" => false,
        "message" => "Return date must be after pickup date."
    ]);
    exit;
}

// Check vehicle availability
$vehicleStmt = $conn->prepare(
    "SELECT vehicle_id, vehicle_name, price_per_day, status
     FROM vehicles WHERE vehicle_id = ?"
);

if (!$vehicleStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to check vehicle availability."
    ]);
    exit;
}

$vehicleStmt->bind_param("i", $vehicleId);
$vehicleStmt->execute();
$vehicleResult = $vehicleStmt->get_result();

if ($vehicleResult->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Vehicle not found."
    ]);
    exit;
}

$vehicle = $vehicleResult->fetch_assoc();
$vehicleStmt->close();

// Calculate rental cost
$days = $pdate->diff($rdate)->days;
if ($days === 0) {
    $days = 1;
}
$rentalCost = $days * $vehicle["price_per_day"];

// Generate unique booking code
$bookingCode = "RK" . strtoupper(substr(md5(uniqid()), 0, 8));

// Insert booking
$insertStmt = $conn->prepare(
    "INSERT INTO bookings
     (user_id, vehicle_id, booking_code, pickup_date, return_date,
      pickup_location, dropoff_location, rental_cost, booking_status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$insertStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to create booking: " . $conn->error
    ]);
    exit;
}

$bookingStatus = "pending";
$insertStmt->bind_param(
    "iisssssds",
    $userId,
    $vehicleId,
    $bookingCode,
    $pickupDate,
    $returnDate,
    $pickupLocation,
    $dropoffLocation,
    $rentalCost,
    $bookingStatus
);

if ($insertStmt->execute()) {
    $bookingId = $conn->insert_id;

    echo json_encode([
        "success" => true,
        "message" => "Booking confirmed!",
        "booking_id" => $bookingId,
        "booking_code" => $bookingCode,
        "vehicle_name" => $vehicle["vehicle_name"],
        "total_amount" => $rentalCost,
        "pickup_date" => $pickupDate,
        "return_date" => $returnDate
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Booking failed. Please try again."
    ]);
}

$insertStmt->close();
$conn->close();

?>
