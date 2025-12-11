# TravelHubBD

A comprehensive travel and tourism website for Bangladesh, featuring tourist spots, hotel bookings, and destination information.

## Features

- 🏨 Hotel booking system with search and filtering
- 📍 Tourist spot information across all divisions of Bangladesh
- 🗺️ Categorized destinations (Sea, Mountain, Forest, Historical Places, River, Worship Places)
- 💝 Wishlist functionality
- 👤 User profile management
- 📧 Contact and support ticket system
- ⭐ Hotel review system
- 👨‍💼 Admin dashboard for booking management

## Technologies Used

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: XAMPP (Apache + MySQL)

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/TravelHubBD.git
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
