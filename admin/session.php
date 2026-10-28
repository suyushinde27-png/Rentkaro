<?php

require_once "../database/database.php";

header("Content-Type: application/json");

// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Admin not logged in."
    ]);
    exit;
}

$adminId = (int) $_SESSION["admin_id"];

// Get admin details including is_admin check
$adminStmt = $conn->prepare("SELECT user_id, name, email, is_admin FROM users WHERE user_id = ?");
$adminStmt->bind_param("i", $adminId);
$adminStmt->execute();
$adminResult = $adminStmt->get_result()->fetch_assoc();
$adminStmt->close();

// Verify user is actually an admin
if (!$adminResult || !$adminResult["is_admin"]) {
    echo json_encode([
        "success" => false,
        "message" => "Admin access denied."
    ]);
    $conn->close();
    exit;
}

// Get statistics
// Total vehicles
$totalVehiclesStmt = $conn->prepare("SELECT COUNT(*) as total, SUM(status='available') as available, SUM(status='booked') as booked, SUM(status='maintenance') as maintenance FROM vehicles");
$totalVehiclesStmt->execute();
$totalVehicles = $totalVehiclesStmt->get_result()->fetch_assoc();
$totalVehiclesStmt->close();

// Total bookings (all time)
$totalBookingsStmt = $conn->prepare("SELECT COUNT(*) as total, SUM(booking_status='confirmed' OR booking_status='active') as active FROM bookings");
$totalBookingsStmt->execute();
$totalBookings = $totalBookingsStmt->get_result()->fetch_assoc();
$totalBookingsStmt->close();

// Total users
$totalUsersStmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$totalUsersStmt->execute();
$totalUsers = $totalUsersStmt->get_result()->fetch_assoc();
$totalUsersStmt->close();

// Monthly revenue (last 30 days)
$monthlyRevenueStmt = $conn->prepare("SELECT COALESCE(SUM(rental_cost), 0) as monthly FROM bookings WHERE booking_status IN ('confirmed', 'active', 'completed') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$monthlyRevenueStmt->execute();
$monthlyRevenue = $monthlyRevenueStmt->get_result()->fetch_assoc();
$monthlyRevenueStmt->close();

echo json_encode([
    "success" => true,
    "admin_name" => $adminResult["name"],
    "admin_email" => $adminResult["email"],
    "stats" => [
        "total_vehicles" => (int) $totalVehicles["total"],
        "vehicles_available" => (int) ($totalVehicles["available"] ?? 0),
        "vehicles_booked" => (int) ($totalVehicles["booked"] ?? 0),
        "vehicles_maintenance" => (int) ($totalVehicles["maintenance"] ?? 0),
        "total_bookings" => (int) $totalBookings["total"],
        "active_bookings" => (int) ($totalBookings["active"] ?? 0),
        "total_users" => (int) $totalUsers["total"],
        "monthly_revenue" => (float) $monthlyRevenue["monthly"]
    ]
]);

?>
