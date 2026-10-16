CREATE TABLE IF NOT EXISTS `meal_reservations` (
  `meal_reservation_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` bigint UNSIGNED DEFAULT NULL,
  `guest_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meal_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meal_date` date DEFAULT NULL,
  `meal_time` time DEFAULT NULL,
  `guests` int UNSIGNED NOT NULL DEFAULT '1',
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','confirmed','served','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `source` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'website',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`meal_reservation_id`),
  KEY `idx_meal_reservations_booking` (`booking_id`),
  KEY `idx_meal_reservations_date_status` (`meal_date`,`status`),
  CONSTRAINT `fk_meal_reservations_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;