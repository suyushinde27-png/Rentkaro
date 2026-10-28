<?php

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

// Get optional status filter
$statusFilter = isset($_GET["status"]) ? trim($_GET["status"]) : "";

// Build base query
$sql = "SELECT b.booking_id, b.booking_code, b.pickup_date, b.return_date,
               b.pickup_location, b.dropoff_location, b.rental_cost, b.booking_status,
               v.vehicle_name, u.name as customer_name, u.email as customer_email
        FROM bookings b
        INNER JOIN vehicles v ON v.vehicle_id = b.vehicle_id
        INNER JOIN users u ON u.user_id = b.user_id
        WHERE 1=1";

$params = [];
$types = "";

if ($statusFilter !== "" && $statusFilter !== "All Status") {
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

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
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
