# Lost & Found Application - Setup Instructions

## Requirements
- XAMPP (with Apache and MySQL)
- Web browser

## Setup Steps

### 1. Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache** server
3. Start **MySQL** server

### 2. Create Database
1. Open your web browser and go to: `http://localhost/phpmyadmin`
2. Click on "New" in the left sidebar
3. Or use the SQL tab and import the `setup_database.sql` file:
   - Click on "Import" tab
   - Click "Choose File" and select `setup_database.sql`
   - Click "Go" at the bottom

### 3. Configure Application
The database configuration is already set in `db_config.php`:
- Host: localhost
- Username: root
- Password: (empty)
- Database: lost_and_found

If your XAMPP MySQL settings are different, edit `db_config.php` accordingly.

### 4. Setup Complete!
Your application is now ready to use.

## How to Use

### Access the Application
1. Make sure XAMPP Apache and MySQL are running
2. Open your web browser and go to: `http://localhost/kalri/registration.html`
3. Create a new account
4. Login and start using the application!

## Application Features

### User Features
- **Registration**: Create a new account with email and password
- **Login**: Secure login with session management
- **Dashboard**: View statistics and recent activity
- **Post Lost Item**: Report items you have lost
- **Post Found Item**: Report items you have found
- **View Lost Items**: Browse all lost items posted by community
- **View Found Items**: Browse all found items posted by community
- **My Posts**: Manage all your posted items
- **Delete Items**: Remove your posted items when found
- **Photo Upload**: Add photos to your item listings
- **Contact Users**: Send email to item posters

### Database Structure
- **users**: Stores user account information
- **items**: Stores all lost and found item listings
- **uploads/**: Directory for uploaded photos

## Troubleshooting

### Database Connection Error
- Make sure MySQL is running in XAMPP
- Check if database `lost_and_found` exists in phpMyAdmin
- Verify database credentials in `db_config.php`

### Apache Not Starting
- Check if port 80 is not being used by another program
- Try changing Apache port in XAMPP config

### Photos Not Uploading
- Make sure `uploads/` directory has write permissions
- The directory will be created automatically on first upload

### Session Issues / Not Logged In
- Make sure you registered an account first
- Clear browser cookies and try again
- Check if PHP sessions are enabled in XAMPP

## File Structure

```
kalri/
├── setup_database.sql      # Database setup script
├── db_config.php          # Database configuration
├── register.php           # Registration API
├── login.php              # Login API
├── logout.php             # Logout script
├── add_item.php           # Add item API
├── get_items.php          # Get items API
├── delete_item.php        # Delete item API
├── index.html             # Main dashboard
├── login.html             # Login page
├── registration.html      # Registration page
├── admin.html             # Admin page (if exists)
├── uploads/               # Photo uploads directory (auto-created)
└── README.md              # This file
```

## Default Test Account
After setting up, you need to register your own account. There are no default accounts.

## Security Notes
- Passwords are hashed using PHP's password_hash() function
- Sessions are used for authentication
- Photo uploads are validated for file type
- SQL injection prevention using PDO prepared statements
- XSS protection through HTML escaping

## Support
For issues or questions, check the troubleshooting section above.
