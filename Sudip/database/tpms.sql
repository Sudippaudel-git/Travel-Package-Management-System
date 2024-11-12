-- Table structure for table `Tourists`
CREATE TABLE `Tourists` (
  `tourist_id` INT AUTO_INCREMENT PRIMARY KEY,
  `Fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `Profile_image` Text(255),
  `contact` VARCHAR(100),
  `address` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `email_index` (`email`)
);

-- Table structure for table `Admins`
CREATE TABLE `Admins` (
  `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
INSERT INTO `Admins` (`username`, `password`)
VALUES ('admin', '$2y$10$x3UXmUy/p4GGxZGIJK2PgeWQIE1ZFs6/s2VfF1ow.IATNLZRy82ha');
-- Table structure for table `Categories`


CREATE TABLE `Categories` (
  `category_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `name_index` (`name`)
);




-- Table structure for table `Packages`
CREATE TABLE `Packages` (
  `package_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(15, 2) NOT NULL,
  `duration` VARCHAR(50),
  `location` VARCHAR(100),
  `image` VARCHAR(255),
  `itinerary` TEXT,
  `includes` TEXT,
  `excludes` TEXT,
  `max_travelers` INT,
  `featured` BOOLEAN DEFAULT FALSE,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `category_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `Categories` (`category_id`) ON DELETE SET NULL,
  INDEX `name_index` (`name`),
  INDEX `category_id_index` (`category_id`)
);


-- Table structure for table `Bookings`
CREATE TABLE `Bookings` (
  `booking_id` INT AUTO_INCREMENT PRIMARY KEY,
  `tourist_id` INT,
  `package_id` INT,
  `booking_date` DATE NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `number_of_travelers` INT NOT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    --  is_read TINYINT(1) DEFAULT 0, -- New column for tracking notifications
     `cancellation_allowed` BOOLEAN DEFAULT TRUE;
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tourist_id`) REFERENCES `Tourists` (`tourist_id`) ON DELETE CASCADE,
  FOREIGN KEY (`package_id`) REFERENCES `Packages` (`package_id`) ON DELETE CASCADE,
  INDEX `tourist_id_index` (`tourist_id`),
  INDEX `package_id_index` (`package_id`)
);

-- Table structure for table `Contactus`
CREATE TABLE IF NOT EXISTS contact_us (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- Table structure for table `Comments`
CREATE TABLE `Comments` (
  `comment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `tourist_id` INT,
  `package_id` INT,
  `admin_id` INT,
  `content` TEXT,
  `comment_status` ENUM('published', 'unpublished') DEFAULT 'unpublished',
  `comment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tourist_id`) REFERENCES `Tourists` (`tourist_id`) ON DELETE CASCADE,
  FOREIGN KEY (`package_id`) REFERENCES `Packages` (`package_id`) ON DELETE CASCADE,
  FOREIGN KEY (`admin_id`) REFERENCES `Admins` (`admin_id`) ON DELETE SET NULL,
  INDEX `tourist_id_index` (`tourist_id`),
  INDEX `package_id_index` (`package_id`),
  INDEX `admin_id_index` (`admin_id`)
);
