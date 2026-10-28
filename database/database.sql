CREATE DATABASE IF NOT EXISTS rentkaro;
USE rentkaro;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `mobile` varchar(15) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `city` varchar(50) DEFAULT NULL,
  `reward_points` int(11) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Vehicles table
CREATE TABLE IF NOT EXISTS `vehicles` (
  `vehicle_id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_name` varchar(100) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `seats` int(11) NOT NULL,
  `transmission` varchar(20) NOT NULL,
  `fuel_type` varchar(20) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `location` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT 'images/hero-car.png',
  `status` enum('available','booked','maintenance') DEFAULT 'available',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `vehicles`

INSERT INTO `vehicles` (`vehicle_id`, `vehicle_name`, `vehicle_type`, `seats`, `transmission`, `fuel_type`, `price_per_day`, `location`, `image`, `status`, `created_at`) VALUES
(1, 'Maruti Swift', 'Hatchback', 4, 'Manual', 'Petrol', 1500.00, 'Mumbai', 'images/maruti-swift.png', 'available', '2026-10-22 16:59:28'),
(2, 'Honda City', 'Sedan', 5, 'Automatic', 'Petrol', 2200.00, 'Mumbai', 'images/honda-city.png', 'available', '2026-10-22 16:59:28'),
(3, 'Hyundai Creta', 'SUV', 5, 'Automatic', 'Petrol', 2800.00, 'Pune', 'images/hyundai-creta.png', 'available', '2026-10-22 16:59:28'),
(4, 'KTM Duke 390', 'Bike', 2, 'Manual', 'Petrol', 999.00, 'Goa', 'images/ktm-duke-390.png', 'available', '2026-10-22 16:59:28'),
(5, 'Bajaj Dominar', 'Bike', 2, 'Automatic', 'Petrol', 1299.00, 'Mumbai', 'images/bajaj_dominar.png', 'available', '2026-10-22 16:59:28'),
(6, 'Tata Nexon', 'SUV', 5, 'Manual', 'Petrol', 2300.00, 'Pune', 'images/tata-nexon.png', 'available', '2026-10-22 16:59:28'),
(7, 'Toyota Innova', 'MPV', 7, 'Automatic', 'Diesel', 3499.00, 'Mumbai', 'images/toyota_innova.png', 'available', '2026-10-22 16:59:28'),
(8, 'Royal Enfield', 'Bike', 2, 'Manual', 'Petrol', 899.00, 'Goa', 'images/royal-enfield.png', 'available', '2026-10-22 16:59:28'),
(9, 'Maruti Suzuki Baleno', 'Hatchback', 5, 'Manual', 'Petrol', 1800.00, 'Mumbai', 'images/maruti-baleno.png', 'available', '2026-10-24 16:56:14'),
(10, 'Honda Glamour', 'Bike', 2, 'Manual', 'Petrol', 700.00, 'Mumbai', 'images/honda-glamour.png', 'available', '2026-10-24 17:00:18'),
(11, 'TVS Apache RTR 160', 'Bike', 2, 'Manual', 'Petrol', 900.00, 'Mumbai', 'images/tvs-apache-rtr-160.png', 'available', '2026-10-24 17:01:47'),
(12, 'Honda Activa 6G', 'Scooter', 2, 'Automatic', 'Petrol', 500.00, 'Mumbai', 'images/honda-activa-6g.png', 'available', '2026-10-24 17:03:02'),
(13, 'Maruti Swift', 'Hatchback', 4, 'Manual', 'Petrol', 1500.00, 'Pune', 'images/maruti-swift.png', 'available', '2026-10-24 17:41:00'),
(14, 'Honda City', 'Sedan', 5, 'Automatic', 'Petrol', 2200.00, 'Pune', 'images/honda-city.png', 'available', '2026-10-24 17:42:21'),
(15, 'Maruti Suzuki Baleno', 'Hatchback', 5, 'Manual', 'Petrol', 1800.00, 'Pune', 'images/maruti-baleno.png', 'available', '2026-10-24 17:43:21'),
(16, 'Bajaj Dominar', 'Bike', 2, 'Manual', 'Petrol', 1400.00, 'Pune', 'images/bajaj_dominar.png', 'available', '2026-10-24 17:44:24'),
(17, 'KTM Duke 390', 'Bike', 2, 'Manual', 'Petrol', 1600.00, 'Pune', 'images/ktm-duke-390.png', 'available', '2026-10-24 17:45:41'),
(18, 'Royal Enfield', 'Bike', 2, 'Manual', 'Petrol', 1500.00, 'Pune', 'images/royal-enfield.png', 'available', '2026-10-24 17:46:44'),
(19, 'Maruti Swift', 'Hatchback', 4, 'Manual', 'Petrol', 1500.00, 'Goa', 'images/maruti-swift.png', 'available', '2026-10-24 17:49:00'),
(20, 'Honda Activa 6G', 'Scooter', 2, 'Automatic', 'Petrol', 500.00, 'Goa', 'images/honda-activa-6g.png', 'available', '2026-10-24 17:49:54'),
(21, 'Honda City', 'Sedan', 5, 'Automatic', 'Petrol', 2200.00, 'Goa', 'images/honda-city.png', 'available', '2026-10-24 17:50:52'),
(22, 'Ford EcoSport', 'SUV', 5, 'Automatic', 'Petrol', 2500.00, 'Mumbai', 'images/ford-ecosport.png', 'available', '2026-10-28 18:00:00'),
(23, 'Chevy Spark', 'Hatchback', 4, 'Manual', 'Petrol', 800.00, 'Pune', 'images/chevy-spark.png', 'available', '2026-10-28 18:00:00'),
(24, 'Hyundai Verna', 'Sedan', 5, 'Automatic', 'Petrol', 1800.00, 'Goa', 'images/hyundai-verna.png', 'available', '2026-10-28 18:00:00'),
(25, 'Mahindra Thar', 'SUV', 4, 'Manual', 'Petrol', 3200.00, 'Mumbai', 'images/mahindra-thar.png', 'available', '2026-10-28 18:00:00'),
(26, 'Tata Tiago', 'Hatchback', 5, 'Manual', 'Petrol', 750.00, 'Pune', 'images/tata-tiago.png', 'available', '2026-10-28 18:00:00'),
(27, 'Maruti Alto', 'Hatchback', 2, 'Manual', 'Petrol', 600.00, 'Mumbai', 'images/maruti-alto.png', 'available', '2026-10-28 18:00:00'),
(28, 'Honda CR-V', 'SUV', 5, 'Automatic', 'Diesel', 3500.00, 'Goa', 'images/honda-cr-v.png', 'available', '2026-10-28 18:00:00'),
(29, 'Ford Figo', 'Hatchback', 4, 'Manual', 'Petrol', 700.00, 'Pune', 'images/ford-figo.png', 'available', '2026-10-28 18:00:00'),
(30, 'Renault Kwid', 'Hatchback', 2, 'Manual', 'Petrol', 500.00, 'Mumbai', 'images/renault-kwid.png', 'available', '2026-10-28 18:00:00');

-- Bookings table
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `booking_code` varchar(50) NOT NULL UNIQUE,
  `pickup_date` date NOT NULL,
  `return_date` date NOT NULL,
  `pickup_location` varchar(100) NOT NULL,
  `dropoff_location` varchar(100) NOT NULL,
  `rental_cost` decimal(10,2) NOT NULL,
  `booking_status` enum('pending','confirmed','active','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `vehicle_id` (`vehicle_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;