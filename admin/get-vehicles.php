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

// Get optional filters
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$category = isset($_GET["category"]) ? trim($_GET["category"]) : "";
$statusFilter = isset($_GET["status"]) ? trim($_GET["status"]) : "";

// Build query
$sql = "SELECT vehicle_id, vehicle_name, vehicle_type, seats, transmission,
               fuel_type, price_per_day, location, image, status
        FROM vehicles WHERE 1=1";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND vehicle_name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

if ($category !== "" && $category !== "All") {
    $sql .= " AND vehicle_type = ?";
    $params[] = $category;
    $types .= "s";
}

if ($statusFilter !== "" && $statusFilter !== "All") {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= " ORDER BY vehicle_id DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to prepare query: " . $conn->error
    ]);
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$vehicles = [];
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "vehicles" => $vehicles,
    "count" => count($vehicles)
]);

?>
