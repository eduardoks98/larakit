# 🏗️ Package: base-api

**Package Name**: `eduardoks98/base-api`
**Propósito**: Foundation package para REST APIs com RFC 7807, API Resources, e prevention N+1

---

## 📋 Visão Geral

O package `base-api` é a fundação de todas as APIs construídas com o Larakit. Ele fornece:

- **RFC 7807 Problem Details** - Respostas de erro padronizadas
- **API Resources** - Transformação de dados estruturada
- **HTTP Client** - Cliente Guzzle com retry logic
- **Base Controllers** - Controllers com métodos helper
- **Middleware** - Middleware para JSON e headers padrão
- **Global Helpers** - Funções helper globais para uso fácil

---

## 🎯 Quando Usar

✅ **Use este package quando**:
- Você está construindo uma REST API
- Você precisa de respostas padronizadas (RFC 7807)
- Você quer transformar modelos Eloquent em JSON estruturado
- Você precisa fazer chamadas HTTP para APIs externas
- Você quer prevenção de N+1 queries

❌ **Não use este package se**:
- Você está construindo uma aplicação tradicional MVC
- Você precisa de GraphQL (use o package `graphql` futuro)

---

## 📦 Instalação

```bash
composer require eduardoks98/base-api
```

### Publicar Configuração

```bash
php artisan vendor:publish --provider="Eduardoks98\BaseApi\BaseApiServiceProvider"
```

Isso criará:
- `config/base-api.php` - Configurações do package

---

## ⚙️ Configuração

### Arquivo de Configuração (`config/base-api.php`)

```php
return [
    // Formato de resposta: 'rfc7807' ou 'custom'
    'response_format' => 'rfc7807',

    // Versão padrão da API
    'api_version' => 'v1',

    // Configurações de paginação
    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    // Configurações do HTTP client
    'http_client' => [
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 1000, // ms
    ],
];
```

---

## 🚀 Uso

### 1. RFC 7807 Problem Details

#### Usando a Função Helper

```php
use function Eduardoks98\BaseApi\problemDetails;

// Em um controller
public function show($id)
{
    $user = User::find($id);

    if (!$user) {
        return problemDetails(
            type: 'https://api.example.com/errors/not-found',
            title: 'User Not Found',
            status: 404,
            detail: "User with ID {$id} was not found",
            instance: request()->path()
        );
    }

    return new UserResource($user);
}
```

#### Resposta Gerada

```json
{
  "type": "https://api.example.com/errors/not-found",
  "title": "User Not Found",
  "status": 404,
  "detail": "User with ID 123 was not found",
  "instance": "/api/v1/users/123"
}
```

### 2. API Resources

#### Criar um Resource

```php
namespace App\Http\Resources;

use Eduardoks98\BaseApi\Http\Resources\ApiResource;

class UserResource extends ApiResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

#### Usar o Resource

```php
// Single resource
return new UserResource($user);

// Collection
return UserResource::collection($users);

// Collection com paginação
return UserResource::collection(User::paginate(15));
```

### 3. Base Controller

#### Extend ApiController

```php
namespace App\Http\Controllers\Api\V1;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use App\Models\User;
use App\Http\Resources\UserResource;

class UserController extends ApiController
{
    public function index()
    {
        $users = User::paginate(15);
        return UserResource::collection($users);
    }

    public function store(Request $request)
    {
        $user = User::create($request->validated());
        return $this->created(new UserResource($user));
    }

    public function destroy($id)
    {
        User::destroy($id);
        return $this->noContent();
    }
}
```

#### Métodos Disponíveis no ApiController

```php
// Success com dados
$this->success($data, $code = 200);

// Created (201)
$this->created($data);

// No Content (204)
$this->noContent();

// Error
$this->error($message, $code = 400);
```

### 4. Global Helpers

#### apiResponse()

```php
// Resposta simples
return apiResponse(['message' => 'Success'], 200);

// Com paginação
return apiResponse($users, 200, 'SUCCESS', page: 1, total: 100);
```

#### Transaction Helpers

```php
use function Eduardoks98\BaseApi\{beginTransaction, commit, rollback};

public function store(Request $request)
{
    beginTransaction();

    try {
        $user = User::create($request->validated());
        $user->profile()->create($request->profile);

        commit();

        return $this->created(new UserResource($user));
    } catch (\Throwable $e) {
        rollback();
        throw $e;
    }
}
```

#### preventN1Query()

```php
// No AppServiceProvider
use function Eduardoks98\BaseApi\preventN1Query;

public function boot()
{
    // Habilita prevenção de lazy loading em desenvolvimento
    if (!app()->isProduction()) {
        preventN1Query();
    }
}
```

### 5. HTTP Client (API Base Service)

```php
use Eduardoks98\BaseApi\Services\ApiBaseService;

class ExternalApiService extends ApiBaseService
{
    protected string $baseUrl = 'https://api.external.com';

    public function getUsers()
    {
        return $this->get('/users');
    }

    public function createUser(array $data)
    {
        return $this->post('/users', $data);
    }
}
```

#### Métodos Disponíveis

```php
$service->get($endpoint, $query = []);
$service->post($endpoint, $data = []);
$service->put($endpoint, $data = []);
$service->delete($endpoint);
```

### 6. Middleware

#### ForceJsonResponse

Garante que todas as respostas sejam JSON, mesmo sem o header `Accept: application/json`.

```php
// Em routes/api.php
Route::middleware(['force.json'])->group(function () {
    // Suas rotas
});
```

#### SetApiHeaders

Adiciona headers padrão em todas as respostas:

```php
Content-Type: application/json
X-API-Version: v1
X-Request-ID: uuid
```

**Registrar no Kernel**:

```php
protected $middlewareGroups = [
    'api' => [
        'force.json',
        'api.headers',
        // ...
    ],
];
```

---

## 📝 Exemplos Completos

### Exemplo 1: CRUD Completo

```php
namespace App\Http\Controllers\Api\V1;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use App\Models\Post;
use App\Http\Resources\PostResource;
use App\Http\Requests\{StorePostRequest, UpdatePostRequest};

class PostController extends ApiController
{
    public function index()
    {
        $posts = Post::with('author')->paginate(15);
        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request)
    {
        beginTransaction();

        try {
            $post = Post::create($request->validated());
            commit();

            return $this->created(new PostResource($post));
        } catch (\Throwable $e) {
            rollback();

            return problemDetails(
                type: 'https://api.example.com/errors/post-creation-failed',
                title: 'Post Creation Failed',
                status: 500,
                detail: $e->getMessage(),
                instance: request()->path()
            );
        }
    }

    public function show($id)
    {
        $post = Post::with('author', 'comments')->find($id);

        if (!$post) {
            return problemDetails(
                type: 'https://api.example.com/errors/not-found',
                title: 'Post Not Found',
                status: 404,
                detail: "Post with ID {$id} was not found"
            );
        }

        return new PostResource($post);
    }

    public function update(UpdatePostRequest $request, $id)
    {
        $post = Post::findOrFail($id);
        $post->update($request->validated());

        return new PostResource($post);
    }

    public function destroy($id)
    {
        Post::destroy($id);
        return $this->noContent();
    }
}
```

### Exemplo 2: Integração com API Externa

```php
namespace App\Services;

use Eduardoks98\BaseApi\Services\ApiBaseService;

class GitHubService extends ApiBaseService
{
    protected string $baseUrl = 'https://api.github.com';

    protected array $headers = [
        'Accept' => 'application/vnd.github.v3+json',
    ];

    public function getUserRepos(string $username)
    {
        try {
            $response = $this->get("/users/{$username}/repos");
            return $response['data'];
        } catch (\Throwable $e) {
            \Log::error('GitHub API Error: ' . $e->getMessage());
            return [];
        }
    }

    public function createRepo(array $data)
    {
        return $this->post('/user/repos', $data);
    }
}
```

---

## 🔧 Traits Disponíveis

### HasApiResponses

```php
use Eduardoks98\BaseApi\Traits\HasApiResponses;

class MyController extends Controller
{
    use HasApiResponses;

    public function index()
    {
        return $this->success(['data' => 'value']);
    }
}
```

### HasTransactions

```php
use Eduardoks98\BaseApi\Traits\HasTransactions;

class MyService
{
    use HasTransactions;

    public function createUser($data)
    {
        $this->beginTransaction();

        try {
            // ... operations
            $this->commit();
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}
```

### PreventLazyLoading

```php
use Eduardoks98\BaseApi\Traits\PreventLazyLoading;

class AppServiceProvider extends ServiceProvider
{
    use PreventLazyLoading;

    public function boot()
    {
        $this->enableLazyLoadingPrevention();
    }
}
```

---

## 📚 API Reference

### Global Functions

| Função | Descrição |
|--------|-----------|
| `problemDetails($type, $title, $status, $detail, $instance)` | Cria resposta RFC 7807 |
| `apiResponse($data, $code, $status, $page, $total)` | Resposta JSON padronizada |
| `beginTransaction($connection)` | Inicia transação |
| `commit($connection)` | Confirma transação |
| `rollback($connection)` | Desfaz transação |
| `preventN1Query()` | Habilita prevenção N+1 |

---

## ⚠️ Troubleshooting

### Erro: "Class 'problemDetails' not found"

**Solução**: Certifique-se de importar a função:

```php
use function Eduardoks98\BaseApi\problemDetails;
```

### Erro: "Lazy loading is disabled"

**Solução**: Use eager loading:

```php
// ❌ Errado
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // N+1 query!
}

// ✅ Correto
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name;
}
```

---

## 🔗 Dependências

```json
{
  "guzzlehttp/guzzle": "^7.8",
  "illuminate/support": "^10.0|^11.0|^12.0",
  "illuminate/http": "^10.0|^11.0|^12.0"
}
```

---

## 🔗 Links Relacionados

- [RFC 7807 Specification](https://www.rfc-editor.org/rfc/rfc7807)
- [Laravel API Resources](https://laravel.com/docs/12.x/eloquent-resources)
- [Guzzle Documentation](https://docs.guzzlephp.org/)

---

**Próximo**: [Helpers Package →](./helpers.md)
