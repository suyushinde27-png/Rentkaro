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

// Get optional status filter
$statusFilter = isset($_GET["status"]) ? trim($_GET["status"]) : "";

// Build base query
$sql = "SELECT b.booking_id, b.booking_code, b.pickup_date, b.return_date,
               b.pickup_location, b.dropoff_location, b.rental_cost, b.booking_status,
               v.vehicle_name, v.vehicle_type, v.image
        FROM bookings b
        INNER JOIN vehicles v ON v.vehicle_id = b.vehicle_id
        WHERE b.user_id = ?";

$params = [$userId];
$types = "i";

if ($statusFilter !== "") {
    $sql .= " AND b.booking_status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= " ORDER BY b.pickup_date DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to load bookings: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    // Calculate rental days and total
    $pickup = DateTime::createFromFormat("Y-m-d", $row["pickup_date"]);
    $return = DateTime::createFromFormat("Y-m-d", $row["return_date"]);
    $days = $pickup->diff($return)->days;
    if ($days === 0) $days = 1;

    $row["rental_days"] = $days;
    $row["total_cost"] = $row["rental_cost"];
    $bookings[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "bookings" => $bookings,
    "count" => count($bookings)
]);

?>
