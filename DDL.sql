--
-- Table structure for table `bookings`
--
CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `room_type_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nid_passport` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `nights` int(11) NOT NULL,
  `room_price_at_booking` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_option` varchar(20) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `booking_status` enum('pending','confirmed','failed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) 
--
-- Table structure for table `contact_submissions`
--
CREATE TABLE `contact_submissions` (
  `submission_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID of the logged-in user, if any',
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','closed') NOT NULL DEFAULT 'new' COMMENT 'Status of the submission',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_notes` text DEFAULT NULL COMMENT 'Internal notes by admin regarding this submission'
) 
--
-- Table structure for table `gallery`
--
CREATE TABLE `gallery` (
  `image_id` int(11) NOT NULL,
  `spot_id` int(11) DEFAULT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) 
--
-- Table structure for table `hotels`
--
CREATE TABLE `hotels` (
  `hotel_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(100) DEFAULT NULL COMMENT 'e.g., Luxury Hotel, Boutique Hotel, Guest House, Eco-Resort, Family Hotels, Standard Hotel, Resort, Apartment',
  `country` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amenities` text DEFAULT NULL COMMENT 'Comma-separated list of amenities e.g., Wi-Fi,Parking,Restaurant,AC',
  `image_url` varchar(255) DEFAULT NULL COMMENT 'Main image for the hotel',
  `price_per_night` decimal(10,2) DEFAULT NULL,
  `star_rating` tinyint(4) DEFAULT NULL COMMENT 'e.g., 1 to 5',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) 
--
-- Table structure for table `hotel_images`
--
CREATE TABLE `hotel_images` (
  `image_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL
) 
--
-- Table structure for table `message_replies`
--
CREATE TABLE `message_replies` (
  `reply_id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL COMMENT 'Which submission this reply is for',
  `admin_user_id` int(11) NOT NULL COMMENT 'Which admin sent the reply',
  `reply_message` text NOT NULL,
  `replied_at` timestamp NOT NULL DEFAULT current_timestamp()
) 
--
-- Table structure for table `reviews`
--
CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `spot_id` int(11) DEFAULT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) 
--
-- Table structure for table `room_types`
--
CREATE TABLE `room_types` (
  `room_type_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `capacity` int(11) DEFAULT NULL,
  `beds` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) 
--
-- Table structure for table `tourist_spots`
--
CREATE TABLE `tourist_spots` (
  `spot_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Sea','Mountain','Historical','River','Forest','Worship','Other') NOT NULL,
  `description` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `division` varchar(100) NOT NULL DEFAULT 'Unknown',
  `location` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `historical_significance` text DEFAULT NULL,
  `best_time_to_visit` varchar(255) DEFAULT NULL COMMENT 'e.g., October to March',
  `entry_fee` varchar(100) DEFAULT NULL COMMENT 'e.g., BDT 50 for locals, BDT 200 for foreigners',
  `opening_hours` varchar(255) DEFAULT NULL COMMENT 'e.g., 9:00 AM - 5:00 PM, Closed on Sundays',
  `things_to_do` text DEFAULT NULL COMMENT 'e.g., Sightseeing,Photography,Boating,Hiking',
  `contact_info` varchar(255) DEFAULT NULL COMMENT 'e.g., Phone number or email for inquiries',
  `how_to_go` text DEFAULT NULL COMMENT 'Brief direction guide',
  `special_tips` text DEFAULT NULL COMMENT 'Any special advice for visitors',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) 
--
-- Table structure for table `tour_plans`
--
CREATE TABLE `tour_plans` (
  `plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `duration_days` int(11) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) 
--
-- Table structure for table `tour_plan_items`
--
CREATE TABLE `tour_plan_items` (
  `item_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `day_number` int(11) NOT NULL COMMENT 'Which day of the tour this item belongs to (e.g., 1, 2, 3)',
  `item_type` enum('spot','hotel','activity','note') NOT NULL DEFAULT 'activity',
  `spot_id` int(11) DEFAULT NULL COMMENT 'Reference to tourist_spots table if item_type is spot',
  `hotel_id` int(11) DEFAULT NULL COMMENT 'Reference to hotels table if item_type is hotel stay',
  `item_title` varchar(255) NOT NULL COMMENT 'Title for the activity/note, or can be pre-filled for spot/hotel',
  `item_description` text DEFAULT NULL COMMENT 'Details for the activity/note, or custom notes for spot/hotel',
  `start_time` time DEFAULT NULL COMMENT 'Optional start time for the item within the day',
  `end_time` time DEFAULT NULL COMMENT 'Optional end time for the item',
  `sequence_order` int(11) NOT NULL DEFAULT 0 COMMENT 'To order items within a day',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) 
--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `district` varchar(100) NOT NULL,
  `is_traveler` enum('Yes','No') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','admin') DEFAULT 'user'
) 
--
-- Table structure for table `wishlist`
--
CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `spot_id` int(11) DEFAULT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hotel_id` (`hotel_id`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- Indexes for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`hotel_id`);

--
-- Indexes for table `hotel_images`
--
ALTER TABLE `hotel_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_hotel_id_hotel_images` (`hotel_id`);

--
-- Indexes for table `message_replies`
--
ALTER TABLE `message_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `submission_id` (`submission_id`),
  ADD KEY `admin_user_id` (`admin_user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `spot_id` (`spot_id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`room_type_id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  ADD PRIMARY KEY (`spot_id`);

--
-- Indexes for table `tour_plans`
--
ALTER TABLE `tour_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tour_plan_items`
--
ALTER TABLE `tour_plan_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `spot_id` (`spot_id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `spot_id` (`spot_id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `hotel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT for table `hotel_images`
--
ALTER TABLE `hotel_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=511;

--
-- AUTO_INCREMENT for table `message_replies`
--
ALTER TABLE `message_replies`
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `room_types`
--
ALTER TABLE `room_types`
  MODIFY `room_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=618;

--
-- AUTO_INCREMENT for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  MODIFY `spot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `tour_plans`
--
ALTER TABLE `tour_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tour_plan_items`
--
ALTER TABLE `tour_plan_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`room_type_id`) ON DELETE SET NULL;

--
-- Constraints for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD CONSTRAINT `contact_submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `message_replies`
--
ALTER TABLE `message_replies`
  ADD CONSTRAINT `message_replies_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `contact_submissions` (`submission_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_replies_ibfk_2` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`spot_id`) REFERENCES `tourist_spots` (`spot_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE SET NULL;

--
-- Constraints for table `room_types`
--
ALTER TABLE `room_types`
  ADD CONSTRAINT `room_types_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE;

--
-- Constraints for table `tour_plans`
--
ALTER TABLE `tour_plans`
  ADD CONSTRAINT `tour_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tour_plan_items`
--
ALTER TABLE `tour_plan_items`
  ADD CONSTRAINT `tour_plan_items_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `tour_plans` (`plan_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tour_plan_items_ibfk_2` FOREIGN KEY (`spot_id`) REFERENCES `tourist_spots` (`spot_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tour_plan_items_ibfk_3` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`spot_id`) REFERENCES `tourist_spots` (`spot_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wishlist_ibfk_3` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE SET NULL;
COMMIT;