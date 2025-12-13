# TravelHub Bangladesh - Web Platform 🇧🇩

**Discover, Explore, and Book Your Perfect Bangladesh Adventure**

TravelHub Bangladesh is a comprehensive travel and tourism platform designed to showcase the natural beauty, cultural heritage, and hospitality of Bangladesh. From the serene beaches of Cox's Bazar to the lush tea gardens of Sylhet, from the historic sites of Dhaka to the majestic Sundarbans - explore it all in one place!

## ✨ Key Features

### 🏨 **Hotel Booking System**

- Advanced search and filtering by location, price, and amenities
- Real-time availability checking
- Detailed hotel information with multiple room types
- Secure booking and confirmation process
- Booking management dashboard

### 📍 **Comprehensive Destination Guide**

- **8 Divisions Covered**: Dhaka, Chittagong, Sylhet, Rajshahi, Khulna, Barisal, Rangpur, Mymensingh
- **200+ Tourist Spots** with detailed information, images, and visitor reviews
- **6 Categories**: Sea, Mountain, Forest, Historical Places, River, Worship Places
- Interactive spot details with location, best visiting time, and activities

### 👤 **User Experience**

- 💝 Wishlist functionality to save favorite spots and hotels
- ⭐ Review and rating system for hotels and destinations
- 📧 Support ticket system for customer assistance
- 🔐 Secure user authentication and profile management
- 📱 Responsive design for mobile and desktop

### 👨‍💼 **Admin Dashboard**

- Complete booking management system
- Customer message handling and response
- Booking status updates and cancellation management
- User activity monitoring

## Technologies Used

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: XAMPP (Apache + MySQL)

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/ug2102047/TravelHubBD-Web.git
   ```

2. **Move to XAMPP htdocs**

   ```bash
   cd C:\xampp\htdocs\
   ```

3. **Configure Database**

   - Start XAMPP and run Apache and MySQL services
   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Create a new database named `travellers`
   - Import `DDL.sql` first (creates tables)
   - Then import `DML.sql` (inserts data)

4. **Configure Database Connection**

   - Copy `config.example.php` to `config.php`
   - Update database credentials in `config.php`:
     ```php
     $host = 'localhost';
     $dbname = 'travellers';
     $username = 'root';
     $password = '';
     ```

5. **Access the Website**
   - Open your browser and navigate to `http://localhost/TravelHubBD`

## Project Structure

```
TravelHubBD/
├── CSS/                    # Stylesheets
├── js/                     # JavaScript files
├── images/                 # Image assets
├── *.php                   # PHP pages and scripts
├── DDL.sql                # Database schema
├── DML.sql                # Database initial data
└── README.md              # This file
```

## Database Setup

The project uses two SQL files:

- **DDL.sql**: Creates database tables and structure
- **DML.sql**: Inserts initial data (spots, hotels, categories, etc.)

## Pages

- **Home**: Main landing page
- **Destinations**: Browse by division (Dhaka, Chittagong, Sylhet, etc.)
- **Categories**: Browse by type (Sea, Mountain, Forest, etc.)
- **Hotels**: Search and book hotels
- **User Dashboard**: Manage bookings, profile, wishlist
- **Admin Panel**: Manage bookings and customer messages

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Server (XAMPP)
- Modern web browser

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open source and available under the [MIT License](LICENSE).

## Contact

For any queries or support, please use the contact form on the website.

---

Made with ❤️ for Bangladesh Tourism
