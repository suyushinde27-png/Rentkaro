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

// Get optional filters
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$city = isset($_GET["city"]) ? trim($_GET["city"]) : "";

// Build query
$sql = "SELECT u.user_id, u.name, u.email, u.mobile, u.city, u.reward_points, u.is_admin,
               u.created_at, CASE WHEN u.created_at IS NOT NULL THEN 1 ELSE 0 END as is_active
        FROM users u WHERE 1=1";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
    $types .= "ss";
}

if ($city !== "" && $city !== "All Cities") {
    $sql .= " AND u.city = ?";
    $params[] = $city;
    $types .= "s";
}

$sql .= " ORDER BY u.user_id DESC";

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

$users = [];
while ($row = $result->fetch_assoc()) {
    // Get booking count for each user
    $bookingStmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
    $bookingStmt->bind_param("i", $row["user_id"]);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result()->fetch_assoc();
    $row["booking_count"] = $bookingResult["count"];
    $bookingStmt->close();

    $users[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "users" => $users,
    "count" => count($users)
]);

?>
