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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit;
}

$vehicleId = filter_input(INPUT_POST, "vehicle_id", FILTER_VALIDATE_INT);
$name = trim($_POST["vehicle_name"] ?? "");
$type = trim($_POST["vehicle_type"] ?? "");
$seats = filter_input(INPUT_POST, "seats", FILTER_VALIDATE_INT);
$transmission = trim($_POST["transmission"] ?? "");
$fuel = trim($_POST["fuel_type"] ?? "");
$price = filter_input(INPUT_POST, "price_per_day", FILTER_VALIDATE_FLOAT);
$location = trim($_POST["location"] ?? "");
$status = trim($_POST["status"] ?? "");

if ($vehicleId === false || $vehicleId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid vehicle ID."
    ]);
    exit;
}

if ($name === "" || $type === "" || $seats === false || $transmission === "" || $fuel === "" || $price === false || $location === "") {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);
    exit;
}

if ($seats < 1 || $seats > 20) {
    echo json_encode([
        "success" => false,
        "message" => "Seats must be between 1 and 20."
    ]);
    exit;
}

if ($price < 0) {
    echo json_encode([
        "success" => false,
        "message" => "Price must be a positive number."
    ]);
    exit;
}

// Validate status if provided
if ($status !== "" && !in_array($status, ["available", "booked", "maintenance"])) {
    $status = "available";
} elseif ($status === "") {
    $status = "available";
}

$stmt = $conn->prepare(
    "UPDATE vehicles
     SET vehicle_name = ?, vehicle_type = ?, seats = ?, transmission = ?,
         fuel_type = ?, price_per_day = ?, location = ?, status = ?
     WHERE vehicle_id = ?"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to update vehicle: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param(
    "ssisssssi",
    $name,
    $type,
    $seats,
    $transmission,
    $fuel,
    $price,
    $location,
    $status,
    $vehicleId
);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Vehicle updated successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No changes made or vehicle not found."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update vehicle."
    ]);
}

$stmt->close();
$conn->close();

?>
