# Admin System Setup - Quick Guide

## Step 1: Run Database Update

First, you need to update your database schema to add admin support:

```bash
# Option 1: Via phpMyAdmin
# - Open phpMyAdmin (http://localhost/phpmyadmin)
# - Select the 'lost_and_found' database
# - Go to SQL tab
# - Copy and paste the contents of update_admin_schema.sql
# - Click 'Go' to execute

# Option 2: Via Command Line
mysql -u root -p lost_and_found < update_admin_schema.sql
```

The update will:
- Add `status` column to `items` table (Pending/Resolved/Closed)
- Add `verified` column to `users` table
- Create optional `admin_users` table
- Add indexes for better performance

## Step 2 Test Admin Login

1. Start XAMPP (Apache + MySQL)
2. Open: `http://localhost/kalri/admin.html`
3. Login with:
   - **Email**: `admin@me.com`
   - **Password**: `admin1234`

## Step 3: Verify Admin Features

### ✅ Submissions Tab
- Should show all items from the database
- Test filtering by type, category, status
- Test search functionality
- Update item status - should persist in database
- Delete an item - should remove from database

### ✅ Losters & Find ers Tab
- Should show users who posted lost/found items
- User counts should be accurate from database
- Toggle verification status - should update database

### ✅ Analytics Tab
- Statistics should match database counts
- Category breakdown should be accurate
- Activity log shows admin actions

### ✅ User Management
- Shows all registered users
- Displays item counts per user (from database join)
- Verify/unverify users updates database

## Troubleshooting

### Issue: "Admin access required" error
**Solution**: Clear browser cookies and sessionStorage, then log in again

### Issue: Items not displaying
**Solution**: 
1. Check if items exist in database: `SELECT * FROM items;`
2. Verify Apache/MySQL are running in XAMPP
3. Check browser console for PHP errors

### Issue: Status updates not saving
**Solution**: 
1. Verify `status` column was added to items table
2. Check `admin_update_item_status.php` for errors
3. Look in browser console for AJAX errors

## What Changed from localStorage

| Feature | Before (localStorage) | After (Database) |
|---------|----------------------|-------------------|
| Items | Stored in browser | Stored in MySQL `items` table |
| Users | Derived from items | Actual users in `users` table |
| Status | Separate localStorage keys | `status` column in `items` |
| Stats | Calculated from localStorage | Real-time COUNT queries |
| Verification | localStorage per user | `verified` column in `users` |

## Admin Credentials

Default admin login:
- Email: `admin@me.com`
- Password: `admin1234`

To change, edit: `admin_login.php` (lines 6-7)
