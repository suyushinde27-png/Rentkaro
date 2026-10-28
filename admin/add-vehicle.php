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

$name = trim($_POST["vehicle_name"] ?? "");
$type = trim($_POST["vehicle_type"] ?? "");
$seats = filter_input(INPUT_POST, "seats", FILTER_VALIDATE_INT);
$transmission = trim($_POST["transmission"] ?? "");
$fuel = trim($_POST["fuel_type"] ?? "");
$price = filter_input(INPUT_POST, "price_per_day", FILTER_VALIDATE_FLOAT);
$location = trim($_POST["location"] ?? "");

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

// Handle image upload (optional)
$imagePath = "images/hero-car.png"; // Default image
if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
    $uploadDir = "../images/";
    $allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
    $fileType = $_FILES["image"]["type"];

    if (in_array($fileType, $allowedTypes)) {
        $newFileName = "vehicle_" . time() . "." . pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $uploadPath)) {
            $imagePath = "images/" . $newFileName;
        }
    }
}

$stmt = $conn->prepare(
    "INSERT INTO vehicles
     (vehicle_name, vehicle_type, seats, transmission, fuel_type, price_per_day, location, image, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available')"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to add vehicle: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param(
    "ssissss",
    $name,
    $type,
    $seats,
    $transmission,
    $fuel,
    $price,
    $location,
    $imagePath
);

if ($stmt->execute()) {
    $vehicleId = $conn->insert_id;
    echo json_encode([
        "success" => true,
        "message" => "Vehicle added successfully!",
        "vehicle_id" => $vehicleId
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to add vehicle. It may already exist."
    ]);
}

$stmt->close();
$conn->close();

?>
