# Useful Commands

Quick reference for common commands when working with the Facebook Auth package.

## Installation & Setup

```bash
# Install package
composer require eduardoks98/facebook-auth

# Publish configuration
php artisan vendor:publish --tag=facebook-auth-config

# Publish migrations
php artisan vendor:publish --tag=facebook-auth-migrations

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration
php artisan migrate:fresh
```

## Development

```bash
# Start Laravel development server
php artisan serve

# Start on specific port
php artisan serve --port=8080

# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache configuration
php artisan config:cache
```

## Database

```bash
# Check migration status
php artisan migrate:status

# Create migration
php artisan make:migration create_custom_table

# Rollback last migration
php artisan migrate:rollback --step=1

# Reset database
php artisan migrate:reset

# Refresh database (rollback + migrate)
php artisan migrate:refresh

# Fresh database with seeding
php artisan migrate:fresh --seed

# Check database connection
php artisan db:show

# Open tinker (REPL)
php artisan tinker
```

## Testing

```bash
# Run all tests
composer test

# Or use Pest directly
vendor/bin/pest

# Run specific test file
vendor/bin/pest tests/Feature/FacebookAuthControllerTest.php

# Run specific test
vendor/bin/pest --filter="it_can_generate_facebook_authorization_url"

# Run tests with coverage
vendor/bin/pest --coverage

# Run tests in parallel
vendor/bin/pest --parallel

# Run tests with detailed output
vendor/bin/pest --verbose
```

## Tinker Commands

```bash
php artisan tinker
```

```php
// Check configuration
config('facebook-auth.app_id')
config('facebook-auth.app_secret')
config('facebook-auth.graph_api_version')

// Test service
$service = app(\Eduardoks98\FacebookAuth\Services\FacebookAuthService::class);
$url = $service->getAuthorizationUrl();
echo $url;

// Find Facebook user
$facebookUser = \Eduardoks98\FacebookAuth\Models\FacebookUser::findByFacebookId('123456789');
dd($facebookUser);

// Get all Facebook users
\Eduardoks98\FacebookAuth\Models\FacebookUser::all();

// Count Facebook users
\Eduardoks98\FacebookAuth\Models\FacebookUser::count();

// Get users with Facebook accounts
$users = \App\Models\User::has('facebookUser')->get();

// Create test Facebook user
$user = \App\Models\User::first();
\Eduardoks98\FacebookAuth\Models\FacebookUser::create([
    'user_id' => $user->id,
    'facebook_id' => '123456789',
    'email' => 'test@facebook.com',
    'name' => 'Test User',
]);

// Delete Facebook user
$facebookUser = \Eduardoks98\FacebookAuth\Models\FacebookUser::find(1);
$facebookUser->delete();
```

## Route Management

```bash
# List all routes
php artisan route:list

# Filter Facebook auth routes
php artisan route:list | grep facebook

# Show route details
php artisan route:list --path=facebook-auth

# Clear route cache
php artisan route:clear

# Cache routes
php artisan route:cache
```

## Logs

```bash
# Tail Laravel logs
tail -f storage/logs/laravel.log

# Clear logs
> storage/logs/laravel.log

# On Windows (PowerShell)
Clear-Content storage/logs/laravel.log

# Watch logs in real-time
Get-Content storage/logs/laravel.log -Wait -Tail 50
```

## Git Commands

```bash
# Initialize git (if not already)
git init

# Add files
git add .

# Commit changes
git commit -m "Add Facebook Auth package"

# Create .gitignore
echo "vendor/" > .gitignore
echo ".env" >> .gitignore
echo "composer.lock" >> .gitignore

# Check status
git status

# View changes
git diff
```

## Composer Commands

```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Update specific package
composer update eduardoks98/facebook-auth

# Dump autoload
composer dump-autoload

# Show package info
composer show eduardoks98/facebook-auth

# Validate composer.json
composer validate

# Check for security vulnerabilities
composer audit

# Remove package
composer remove eduardoks98/facebook-auth
```

## API Testing (using cURL)

```bash
# Get authorization URL
curl -X GET http://localhost:8000/api/facebook-auth/redirect

# Get user profile (authenticated)
curl -X GET http://localhost:8000/api/facebook-auth/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"

# Disconnect Facebook account
curl -X DELETE http://localhost:8000/api/facebook-auth/disconnect \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"

# Get current user
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## API Testing (using HTTPie)

```bash
# Install HTTPie
pip install httpie

# Get authorization URL
http GET http://localhost:8000/api/facebook-auth/redirect

# Get user profile (authenticated)
http GET http://localhost:8000/api/facebook-auth/profile \
  "Authorization:Bearer YOUR_TOKEN_HERE"

# Disconnect Facebook account
http DELETE http://localhost:8000/api/facebook-auth/disconnect \
  "Authorization:Bearer YOUR_TOKEN_HERE"
```

## Database Queries

```sql
-- View all Facebook users
SELECT * FROM facebook_users;

-- View users with Facebook accounts
SELECT u.*, fu.facebook_id, fu.email as facebook_email
FROM users u
INNER JOIN facebook_users fu ON u.id = fu.user_id;

-- Count users with Facebook accounts
SELECT COUNT(*) FROM facebook_users;

-- Find Facebook user by ID
SELECT * FROM facebook_users WHERE facebook_id = '123456789';

-- Delete Facebook user
DELETE FROM facebook_users WHERE facebook_id = '123456789';

-- Cleanup orphaned records (if any)
DELETE FROM facebook_users
WHERE user_id NOT IN (SELECT id FROM users);
```

## Environment Management

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Show current environment
php artisan env

# Check configuration value
php artisan tinker
>>> config('app.env')
>>> config('facebook-auth.app_id')
```

## Package Development

```bash
# Create symlink for local development
composer config repositories.facebook-auth path ./packages/facebook-auth
composer require eduardoks98/facebook-auth @dev

# Update autoload for development
composer dump-autoload

# Create new controller
php artisan make:controller FacebookCustomController

# Create new model
php artisan make:model CustomFacebookModel -m

# Create new middleware
php artisan make:middleware CustomMiddleware

# Create new test
php artisan make:test CustomTest --unit
```

## Production Deployment

```bash
# Install production dependencies only
composer install --no-dev --optimize-autoloader

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Restart queue workers
php artisan queue:restart

# Clear all caches
php artisan optimize:clear

# Optimize application
php artisan optimize
```

## Troubleshooting Commands

```bash
# Check PHP version
php -v

# Check Laravel version
php artisan --version

# Check installed packages
composer show

# Verify package installation
composer show eduardoks98/facebook-auth

# Check permissions
ls -la storage/
ls -la bootstrap/cache/

# Fix permissions (Unix/Linux/Mac)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Check database connection
php artisan db:show

# Check migrations
php artisan migrate:status

# Diagnose issues
php artisan about

# List all Artisan commands
php artisan list
```

## Quick Debugging

```bash
# Enable debug mode
# In .env: APP_DEBUG=true

# View last 50 lines of log
tail -n 50 storage/logs/laravel.log

# Enable query logging
php artisan tinker
>>> DB::listen(function($query) { dump($query->sql); });

# Check route exists
php artisan route:list | grep facebook-auth

# Verify config loaded
php artisan config:show facebook-auth

# Check service provider loaded
php artisan about
```

## Useful One-Liners

```bash
# Clear everything and recache
php artisan optimize:clear && php artisan optimize

# Fresh start (WARNING: Deletes all data)
php artisan migrate:fresh --seed

# Quick test
vendor/bin/pest --filter=facebook

# Check syntax errors
php -l src/Services/FacebookAuthService.php

# Find files containing text
grep -r "FacebookAuth" src/

# Count lines of code
find src/ -name "*.php" -exec wc -l {} + | tail -1
```

## PhpStorm / VS Code

```bash
# Generate IDE helper files
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta
```

## Docker (if using)

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Execute command in container
docker-compose exec app php artisan migrate

# Access container shell
docker-compose exec app bash
```

---

**Pro Tip**: Create aliases in your `.bashrc` or `.zshrc` for frequently used commands:

```bash
alias pa="php artisan"
alias pam="php artisan migrate"
alias pat="php artisan tinker"
alias pest="vendor/bin/pest"
alias composer-update="composer update && composer dump-autoload"
```
