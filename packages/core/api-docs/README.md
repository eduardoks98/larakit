# 📚 API Docs - Scramble OpenAPI Documentation

Auto-generated API documentation with Scramble and OpenAPI 3.1 support.

## Installation
```bash
composer require eduardoks98/api-docs
```

## Features
- ✅ **Zero-Config** - Auto-discovers routes and controllers
- ✅ **OpenAPI 3.1** - Standard format for API documentation
- ✅ **Stoplight UI** - Beautiful interactive documentation
- ✅ **Form Request Detection** - Automatic validation rules
- ✅ **API Resource Support** - Response examples from resources

## Quick Start
```bash
# Access documentation
http://localhost:8000/docs/api

# OpenAPI JSON
http://localhost:8000/docs/api.json
```

## Customize
```php
// Service Provider
Scramble::extendOpenApi(function (OpenApi $openApi) {
    $openApi->info->title = 'My API';
    $openApi->info->version = '1.0.0';
});
```

## License
MIT - Eduardo Steffens (@eduardoks98)
