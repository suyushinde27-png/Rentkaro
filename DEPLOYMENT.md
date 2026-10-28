# RentKaro - Deployment Guide

## Pre-Deployment Checklist

### 1. Database Setup
```bash
# Create the database
mysql -u root -p < database/database.sql

# If you're upgrading from an existing installation, run the migration
mysql -u root -p rentkaro < database/add_is_admin_column.sql
```

### 2. Configuration
Update `database/database.php` with production credentials:
```php
$host = "localhost";
$username = "root";
$password = "Suyash@0527";
$database = "rentkaro";
```

### 3. Environment Setup

#### XAMPP Configuration
1. Ensure Apache and MySQL are running
2. Copy project to `C:\xampp\htdocs\RentKaro`
3. Access via `http://localhost/RentKaro`

#### Production Server (Linux/Apache)
```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/RentKaro
sudo find /var/www/RentKaro -type d -exec chmod 755 {} \;
sudo find /var/www/RentKaro -type f -exec chmod 644 {} \;

# Enable required Apache modules
sudo a2enmod rewrite headers
sudo systemctl restart apache2
```

### 4. Security Enhancements

#### Production Settings
1. **Enable HTTPS** - Update `.htaccess`:
   ```apache
   # Uncomment these lines in .htaccess
   RewriteEngine On
   RewriteCond %{HTTPS} !=on
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

2. **Configure session security** in `database/database.php`:
   ```php
   "secure" => true,  // Set to true with HTTPS
   ```

3. **Set strong database password** - Update `database.php`

4. **Disable error display** in production:
   ```php
   // Add to database.php after connection
   error_reporting(E_ALL);
   ini_set('display_errors', 0);
   ini_set('log_errors', 1);
   ```

### 5. Payment Gateway Integration

Replace the simulated payment in `backend/process-payment.php` with a real gateway:

#### Razorpay Integration Example
```php
// Replace the simulated payment section
$apiKey = getenv('RAZORPAY_KEY_ID');
$apiSecret = getenv('RAZPAY_KEY_SECRET');

// Validate payment signature
$attributes = [
    'razorpay_payment_id' => $_POST['razorpay_payment_id'],
    'razorpay_order_id' => $_POST['razorpay_order_id'],
    'razorpay_signature' => $_POST['razorpay_signature']
];

$signature = hash_hmac('sha256', $attributes['razorpay_order_id'] . '|' . $attributes['razorpay_payment_id'], $apiSecret);

if ($signature !== $attributes['razorpay_signature']) {
    echo json_encode(["success" => false, "message" => "Invalid signature"]);
    exit;
}
```

### 6. Database Backup Schedule

Set up automated backups:
```bash
# Add to crontab for daily backup at 2 AM
0 2 * * * mysqldump -u root -p rentkaro > /backup/rentkaro_$(date +\%Y\%m\%d).sql
```

## Deployment Steps

### Local Testing
1. Access `http://localhost/RentKaro/admin-login.html`
2. Login with admin credentials:
   - Email: `admin@rentkaro.com`
   - Password: `Suyash@0527` (your database password)
3. Test all admin functions
4. Test user registration and booking flow

### Production Deployment
1. Backup existing database
2. Upload all files
3. Run database migration
4. Configure environment variables
5. Run final tests
6. Update DNS if needed

## Post-Deployment

### Monitor Logs
```bash
# Check Apache error log
tail -f /var/log/apache2/error.log

# Check PHP errors
tail -f /var/log/php/error.log
```

### Verify Security
- [ ] HTTPS is enforced
- [ ] Database password is strong
- [ ] Admin credentials are changed
- [ ] .htaccess rules are working
- [ ] File permissions are correct

## Troubleshooting

### Database Connection Errors
- Verify MySQL is running
- Check credentials in `database/database.php`
- Ensure user has proper permissions

### 404 Errors
- Verify `.htaccess` exists and is readable
- Enable mod_rewrite: `a2enmod rewrite`

### 500 Errors
- Check Apache error log
- Verify PHP version (7.4+ required)
- Check file permissions

## Support

For issues, check:
- Apache error logs
- PHP error logs
- Browser developer console
