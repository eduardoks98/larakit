# 🔄 Reverb - Laravel Reverb WebSocket Wrapper

Ready-to-use Laravel Reverb configuration for real-time WebSocket communication.

## Installation
```bash
composer require eduardoks98/reverb
php artisan reverb:install
```

## Features
- ✅ Pre-configured Laravel Reverb setup
- ✅ Channel authorization examples
- ✅ Broadcasting event templates
- ✅ Laravel Echo integration guide

## Quick Start
```bash
# Start Reverb server
php artisan reverb:start

# Frontend (Laravel Echo)
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY
});
```

## License
MIT - Eduardo Steffens (@eduardoks98)
