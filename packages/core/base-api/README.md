# 🏗️ Base API - Foundation Package

Foundation package para Laravel APIs com respostas padronizadas (RFC 7807), API Resources, e HTTP Client.

## 📦 Instalação

```bash
composer require eduardoks98/base-api
```

## 🚀 Features

- ✅ **RFC 7807 Problem Details** - Respostas de erro padronizadas
- ✅ **API Resources** - Base classes para transformação de dados
- ✅ **HTTP Client** - Guzzle wrapper com retry logic
- ✅ **Base Controller** - Controller com helpers úteis
- ✅ **Middleware** - ForceJson e SetApiHeaders
- ✅ **Global Helpers** - Funções helper para uso fácil
- ✅ **N+1 Prevention** - Detecção automática de lazy loading

## 📖 Documentação Completa

Veja a [documentação completa](../../docs/packages/base-api.md) para exemplos detalhados e referência da API.

## 🔧 Quick Start

### 1. Publicar Configuração

```bash
php artisan vendor:publish --provider="Eduardoks98\BaseApi\BaseApiServiceProvider"
```

### 2. Usar o Base Controller

```php
use Eduardoks98\BaseApi\Http\Controllers\ApiController;

class UserController extends ApiController
{
    public function index()
    {
        $users = User::paginate(15);
        return UserResource::collection($users);
    }
}
```

### 3. RFC 7807 Responses

```php
use function Eduardoks98\BaseApi\problemDetails;

return problemDetails(
    type: 'https://api.example.com/errors/not-found',
    title: 'User Not Found',
    status: 404,
    detail: 'User with ID 123 was not found'
);
```

## 📄 License

MIT

## 👤 Author

Eduardo Steffens - [@eduardoks98](https://github.com/eduardoks98)
