<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/database.php";

$vehicleId = filter_input(INPUT_GET, "vehicle_id", FILTER_VALIDATE_INT);

if (!$vehicleId || $vehicleId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid vehicle ID."
    ]);
    exit;
}

$sql = "SELECT vehicle_id, vehicle_name, vehicle_type, seats,
               transmission, fuel_type, price_per_day, location, image, status
        FROM vehicles
        WHERE vehicle_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database query could not be prepared."
    ]);
    exit;
}

$stmt->bind_param("i", $vehicleId);
$stmt->execute();

$stmt->bind_result(
    $id,
    $name,
    $type,
    $seats,
    $transmission,
    $fuel,
    $price,
    $location,
    $image,
    $status
);

if (!$stmt->fetch()) {
    echo json_encode([
        "success" => false,
        "message" => "Vehicle not found."
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "vehicle" => [
        "vehicle_id" => $id,
        "vehicle_name" => $name,
        "vehicle_type" => $type,
        "seats" => $seats,
        "transmission" => $transmission,
        "fuel_type" => $fuel,
        "price_per_day" => $price,
        "location" => $location,
        "image" => $image,
        "status" => $status
    ]
]);

$stmt->close();
$conn->close();
?>