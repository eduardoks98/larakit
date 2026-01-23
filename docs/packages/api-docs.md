# 📚 Package: api-docs

**Package Name**: `eduardoks98/api-docs`
**Propósito**: Documentação automática com Scramble + OpenAPI 3.1

---

## 📋 Visão Geral

Documentação automática da API:

- **Scramble** - Auto-geração a partir do código
- **OpenAPI 3.1** - Formato padrão de mercado
- **Stoplight UI** - Interface moderna e interativa
- **Zero Annotations** - Analisa Form Requests automaticamente
- **Always Up-to-date** - Sincronizado com código

---

## 📦 Instalação

```bash
composer require eduardoks98/api-docs
php artisan vendor:publish --provider="Eduardoks98\ApiDocs\ApiDocsServiceProvider"
```

---

## ⚙️ Configuração

```php
return [
    'api_path' => 'api/v1',
    'api_domain' => null,

    'info' => [
        'title' => env('APP_NAME', 'API'),
        'version' => '1.0.0',
        'description' => 'RESTful API built with eduardoks98/api-base',
    ],

    'servers' => [
        ['url' => env('APP_URL') . '/api/v1'],
    ],
];
```

---

## 🚀 Uso

### 1. Acessar Documentação

```
http://localhost:8000/docs/api       - UI interativa
http://localhost:8000/docs/api.json  - OpenAPI JSON
http://localhost:8000/docs/api.yaml  - OpenAPI YAML
```

### 2. Auto-Discovery

Scramble analisa automaticamente:

- **Controllers** - Endpoints e métodos HTTP
- **Form Requests** - Regras de validação
- **API Resources** - Estrutura de resposta
- **Route Middleware** - Autenticação

### 3. Anotações Opcionais

```php
/**
 * Get user by ID
 *
 * @param int $id User ID
 * @return UserResource
 *
 * @response 200 {
 *   "data": {
 *     "id": 1,
 *     "name": "John Doe",
 *     "email": "john@example.com"
 *   }
 * }
 * @response 404 {
 *   "type": "https://api.example.com/errors/not-found",
 *   "title": "User Not Found"
 * }
 */
public function show(int $id)
{
    // ...
}
```

### 4. Customizar OpenAPI Info

```php
// No Service Provider
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;

Scramble::extendOpenApi(function (OpenApi $openApi) {
    $openApi->info->title = 'My API';
    $openApi->info->version = '1.0.0';
    $openApi->info->description = 'Production-ready REST API';
});
```

---

## 📝 Exemplos

### Exemplo 1: Documentar CRUD Completo

```php
/**
 * List all users
 *
 * Returns a paginated list of users.
 *
 * @unauthenticated
 */
public function index()
{
    return UserResource::collection(User::paginate(15));
}

/**
 * Create a new user
 *
 * @response 201 {
 *   "data": {
 *     "id": 1,
 *     "name": "John Doe",
 *     "email": "john@example.com",
 *     "created_at": "2024-01-23T10:00:00Z"
 *   }
 * }
 */
public function store(StoreUserRequest $request)
{
    $user = User::create($request->validated());
    return new UserResource($user);
}
```

Scramble gera automaticamente:
- Endpoint path e método
- Request body schema (a partir do Form Request)
- Response schema (a partir do Resource)
- Authentication requirements

---

## 🔗 Dependências

```json
{
  "dedoc/scramble": "^0.10",
  "eduardoks98/base-api": "^1.0"
}
```

---

**Anterior**: [← Reverb](./reverb.md) | **Próximo**: [Health →](./health.md)
