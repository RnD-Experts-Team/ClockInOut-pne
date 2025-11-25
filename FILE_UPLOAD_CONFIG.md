# File Upload Configuration

The application has been updated to allow **unlimited file size uploads** for store managers when creating maintenance requests.

## Changes Made:

### 1. Frontend (View)
- **File**: `resources/views/native-requests/create.blade.php`
- Removed "Max 5MB per file" message from help text

### 2. Backend (Validation)
- **File**: `app/Http/Requests/StoreNativeRequestRequest.php`
- Removed `max:5120` validation rule (5MB limit)
- Removed corresponding error message

## PHP Configuration Required:

To allow large file uploads, you need to update your PHP configuration:

### Option 1: Update php.ini
Edit your `php.ini` file (located in `C:\xampp\php\php.ini`) and increase these values:

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

### Option 2: Update .htaccess
Add these lines to your `.htaccess` file in the public directory:

```apache
php_value upload_max_filesize 100M
php_value post_max_size 100M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 256M
```

### After making changes:
1. Restart Apache from XAMPP Control Panel
2. Verify the changes by creating a `phpinfo.php` file:
   ```php
   <?php phpinfo(); ?>
   ```
3. Access `http://localhost/phpinfo.php` and search for the values above

## Current Limits:
- **Number of files**: Still limited to 5 files per request
- **File size**: No application limit (PHP server limits apply)
- **File types**: JPEG, PNG, JPG, PDF only
