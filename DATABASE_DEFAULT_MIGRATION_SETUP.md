# Default Database Migration Setup

**Date**: September 26, 2025  
**Status**: ✅ COMPLETED  
**Purpose**: Make current database the default migration state

## Overview

The current database state has been captured and set as the default migration baseline. This allows for:
- Fresh installations to start with the complete current database structure
- Easy reset to a known good state
- Consistent development environment setup
- Proper migration tracking for future changes

## Files Created

### Database Dumps
- `database_schema_YYYYMMDD_HHMMSS.sql` - Complete database dump (structure + data)
- `database/schema/default_database_structure.sql` - Database structure only
- `database/schema/default_database_data.sql` - Database data only

### Command
- `app/Console/Commands/ResetToDefaultDatabase.php` - Artisan command to reset database

### Seeder
- `database/seeders/DefaultDatabaseSeeder.php` - Seeder for default data

## Current Database State (Captured)

### Migration Status
All migrations through `2025_09_26_092415_backfill_referral_bonus_at_registration_for_existing_users` are included in the default state.

### Key Features Included
- ✅ Complete user management system
- ✅ Trading and share management
- ✅ Payment processing system
- ✅ Referral bonus tracking (latest implementation)
- ✅ Market management
- ✅ Notification system
- ✅ Support system
- ✅ Audit logging
- ✅ Permission system
- ✅ Chat system

### Database Tables (58 total)
Core tables included in the default state:
- `users` - User accounts and profiles
- `user_shares` - Share ownership and trading
- `user_share_pairs` - Share matching and pairing
- `user_share_payments` - Payment processing
- `trades` - Available trading instruments
- `trade_periods` - Trading period configurations
- `general_settings` - Application settings
- `markets` - Market schedules and configuration
- `notifications` - User notifications
- `permissions` and `roles` - Access control
- `invoices` - Financial records
- `logs` - System audit logs
- And 45+ more supporting tables

## Usage

### Fresh Installation
For a completely fresh installation:
```bash
# Reset to default database state
php artisan db:reset-to-default --force

# This will:
# 1. Drop all existing tables
# 2. Import default structure
# 3. Import default data
# 4. Mark all migrations as run
```

### Development Reset
To reset development environment to known state:
```bash
# Interactive confirmation (recommended)
php artisan db:reset-to-default

# With confirmation prompts for safety
```

### Partial Operations
```bash
# Structure only (no data import)
php artisan db:reset-to-default --structure-only

# Data only (assumes structure exists)
php artisan db:reset-to-default --data-only

# Force operation without prompts
php artisan db:reset-to-default --force
```

## Command Options

### `php artisan db:reset-to-default`

**Options:**
- `--force` - Skip confirmation prompts
- `--structure-only` - Only reset database structure, skip data import
- `--data-only` - Only reset data, skip structure recreation

**Safety Features:**
- Interactive confirmation by default
- Detailed logging of operations
- Database transaction safety
- Foreign key constraint management
- Error handling and rollback

## What's Included in Default Data

### Essential System Data
- **Admin User**: Default superadmin account
- **Permissions**: Complete RBAC setup
- **Trade Configurations**: Safaricom and other trading instruments
- **Trade Periods**: 1-day, 7-day, 30-day trading periods
- **General Settings**: Default application settings including referral bonus
- **Markets**: Default market schedule configuration

### User Data
- Sample users with different roles
- Trading history and share ownership
- Payment records and transaction history
- Referral relationships and bonus tracking

## Migration History Preserved

The following migrations are marked as "run" in the default state:
```
[1] 2014_10_12_000000_create_users_table
[1] 2014_10_12_100000_create_password_resets_table
[1] 2019_08_19_000000_create_failed_jobs_table
[1] 2019_12_14_000001_create_personal_access_tokens_table
... (54 more migrations)
[25] 2025_09_26_092415_backfill_referral_bonus_at_registration_for_existing_users
```

## Future Migrations

### After Default State
Any new migrations created after this date will:
- Run normally with `php artisan migrate`
- Be tracked properly in the migrations table
- Build upon this established baseline

### Creating New Migrations
```bash
# Create new migration (will run on top of default state)
php artisan make:migration add_new_feature_to_users_table

# Run new migrations
php artisan migrate
```

## Technical Details

### Database Configuration
- **Database**: `u773742080_autobidder`
- **Connection**: MySQL via XAMPP
- **Character Set**: utf8mb4_unicode_ci
- **Storage Engine**: InnoDB

### File Locations
```
database/
├── migrations/           # All migration files (preserved)
├── seeders/             # Seeders including DefaultDatabaseSeeder
└── schema/              # Default database dumps
    ├── default_database_structure.sql
    └── default_database_data.sql
```

### Command Implementation
The reset command (`ResetToDefaultDatabase.php`) performs these steps:

1. **Safety Check**: Confirms operation with user
2. **Drop Tables**: Removes all existing tables (with foreign key handling)
3. **Import Structure**: Recreates all tables from schema dump
4. **Import Data**: Loads default data from data dump
5. **Update Migrations**: Marks all migrations as executed
6. **Verification**: Confirms successful completion

## Backup Strategy

### Before Using Reset Command
Always backup your current database before resetting:
```bash
# Manual backup
/Applications/XAMPP/xamppfiles/bin/mysqldump -u root u773742080_autobidder > backup_before_reset.sql

# Then reset
php artisan db:reset-to-default
```

### Regular Backups
Consider regular backups of your development database:
```bash
# Daily backup script example
/Applications/XAMPP/xamppfiles/bin/mysqldump -u root u773742080_autobidder > daily_backup_$(date +%Y%m%d).sql
```

## Troubleshooting

### Command Fails
If the reset command fails:
1. Check database connection
2. Verify file permissions
3. Ensure schema files exist
4. Check database user privileges

### Missing Schema Files
If schema files are missing:
```bash
# Regenerate from current database
/Applications/XAMPP/xamppfiles/bin/mysqldump -u root --no-data u773742080_autobidder > database/schema/default_database_structure.sql
/Applications/XAMPP/xamppfiles/bin/mysqldump -u root --no-create-info --complete-insert u773742080_autobidder > database/schema/default_database_data.sql
```

### Migration Issues
If migrations appear as "not run":
```bash
# Check migration status
php artisan migrate:status

# Reset migration tracking
php artisan db:reset-to-default --structure-only
```

## Maintenance

### Updating Default State
To update the default state with new changes:
1. Make your changes and migrations
2. Run `php artisan migrate`
3. Regenerate schema files
4. Update documentation
5. Test reset command

### Version Control
- ✅ Include schema files in version control
- ✅ Include reset command in version control
- ✅ Include this documentation in version control
- ❌ Do NOT include actual database dumps with sensitive data

## Security Considerations

### Data Sensitivity
- Default data includes sample users and transactions
- Remove or anonymize sensitive information before sharing
- Use environment-specific configurations for production

### Access Control
- Reset command requires database admin privileges
- Use with caution in production environments
- Consider creating separate commands for different environments

## Conclusion

The default database migration setup provides a robust foundation for:
- ✅ Consistent development environments
- ✅ Easy onboarding of new developers
- ✅ Reliable testing baseline
- ✅ Quick recovery from database issues
- ✅ Proper migration tracking

This setup ensures that the current mature state of the database (as of September 26, 2025) becomes the baseline for all future development and deployments.

---

**Last Updated**: September 26, 2025  
**Next Review**: When significant database changes are made