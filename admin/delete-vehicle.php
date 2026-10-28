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

$vehicleId = filter_input(INPUT_POST, "vehicle_id", FILTER_VALIDATE_INT);

if ($vehicleId === false || $vehicleId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid vehicle ID."
    ]);
    exit;
}

// First check if vehicle exists
$checkStmt = $conn->prepare("SELECT vehicle_id, status FROM vehicles WHERE vehicle_id = ?");
$checkStmt->bind_param("i", $vehicleId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Vehicle not found."
    ]);
    $checkStmt->close();
    exit;
}

$vehicle = $checkResult->fetch_assoc();
$checkStmt->close();

// If vehicle is booked, cannot delete
if ($vehicle["status"] === "booked") {
    echo json_encode([
        "success" => false,
        "message" => "Cannot delete vehicle with active bookings. Set it to maintenance instead."
    ]);
    exit;
}

// Delete vehicle
$stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to delete vehicle: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $vehicleId);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Vehicle deleted successfully!"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to delete vehicle."
    ]);
}

$stmt->close();
$conn->close();

?>
