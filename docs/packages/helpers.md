# 🇧🇷 Package: helpers

**Package Name**: `eduardoks98/helpers`
**Propósito**: Utilitários para mercado brasileiro - 100% framework-agnostic

---

## 📋 Visão Geral

O package `helpers` fornece utilitários específicos para o mercado brasileiro, incluindo:

- **Validação de Documentos** - CPF, CNPJ
- **Formatação** - Telefone, moeda, CEP
- **Data/Hora** - Dias úteis, horário comercial
- **Transformação de Dados** - Array, JSON, objetos
- **Database Helpers** - Transações (Laravel specific)

**Característica Especial**: Este package é 100% framework-agnostic e pode ser usado fora do Laravel!

---

## 🎯 Quando Usar

✅ **Use este package quando**:
- Você precisa validar CPF/CNPJ
- Você precisa formatar telefones brasileiros
- Você trabalha com valores monetários em R$
- Você precisa calcular dias úteis
- Você quer formatar datas no padrão brasileiro

❌ **Não use este package se**:
- Você não está no Brasil e não precisa dessas funcionalidades específicas

---

## 📦 Instalação

```bash
composer require eduardoks98/helpers
```

### Publicar Configuração (Opcional)

```bash
php artisan vendor:publish --provider="Eduardoks98\Helpers\HelpersServiceProvider"
```

Isso criará:
- `config/helpers.php` - Configurações opcionais

---

## ⚙️ Configuração

### Arquivo de Configuração (`config/helpers.php`)

```php
return [
    // Configuração de moeda
    'currency' => [
        'symbol' => 'R$',
        'decimals' => 2,
        'decimal_separator' => ',',
        'thousand_separator' => '.',
    ],

    // Horário comercial
    'business_hours' => [
        'start' => 8,  // 08:00
        'end' => 18,   // 18:00
        'timezone' => 'America/Sao_Paulo',
    ],
];
```

---

## 🚀 Uso

### 1. Validação de Documentos

#### Validar CPF

```php
use function Eduardoks98\Helpers\checkCPF;

// Validar CPF
$isValid = checkCPF('123.456.789-09'); // true ou false
$isValid = checkCPF('12345678909');     // Aceita com ou sem formatação

// Exemplo em validação de formulário
$request->validate([
    'cpf' => ['required', 'string', function ($attribute, $value, $fail) {
        if (!checkCPF($value)) {
            $fail('CPF inválido.');
        }
    }],
]);
```

#### Validar CNPJ

```php
use Eduardoks98\Helpers\Validators\CnpjValidator;

$isValid = CnpjValidator::validate('12.345.678/0001-90');
```

#### Validar CPF ou CNPJ (Auto-detecta)

```php
use Eduardoks98\Helpers\Validators\DocumentValidator;

$doc = '123.456.789-09'; // CPF
$isValid = DocumentValidator::validate($doc); // true

$doc = '12.345.678/0001-90'; // CNPJ
$isValid = DocumentValidator::validate($doc); // true
```

#### Formatar Documento (Adicionar zeros à esquerda)

```php
use function Eduardoks98\Helpers\formatarCpfCnpj;

$cpf = formatarCpfCnpj(12345678909);
// Resultado: "00012345678909"
```

---

### 2. Formatação

#### Formatar Telefone

```php
use Eduardoks98\Helpers\Formatters\PhoneFormatter;

// Celular
$phone = PhoneFormatter::format('11987654321');
// Resultado: "(11) 98765-4321"

// Telefone fixo
$phone = PhoneFormatter::format('1134567890');
// Resultado: "(11) 3456-7890"

// Com DDD e código do país
$phone = PhoneFormatter::format('5511987654321');
// Resultado: "+55 (11) 98765-4321"
```

#### Formatar Moeda

```php
use Eduardoks98\Helpers\Formatters\MoneyFormatter;

$formatted = MoneyFormatter::format(1234.56);
// Resultado: "R$ 1.234,56"

$formatted = MoneyFormatter::format(1000000);
// Resultado: "R$ 1.000.000,00"
```

#### Remover Caracteres Especiais

```php
use function Eduardoks98\Helpers\removerCaracteres;

$cleaned = removerCaracteres('123.456.789-09');
// Resultado: "12345678909"

$cleaned = removerCaracteres('(11) 98765-4321');
// Resultado: "11987654321"
```

---

### 3. Data/Hora

#### Calcular Dias Úteis

```php
use Eduardoks98\Helpers\BusinessHours;

// Calcular dias úteis entre duas datas
$start = '2024-01-01';
$end = '2024-01-31';
$businessDays = BusinessHours::calculate($start, $end);
// Resultado: 22 (exemplo)
```

#### Verificar Horário Comercial

```php
use Eduardoks98\Helpers\BusinessHours;

$isBusinessHour = BusinessHours::isBusinessHour(now());
// Resultado: true se entre 08:00 e 18:00 em dia útil

$datetime = '2024-01-15 14:30:00'; // Segunda-feira, 14:30
$isBusinessHour = BusinessHours::isBusinessHour($datetime);
// Resultado: true

$datetime = '2024-01-14 14:30:00'; // Domingo, 14:30
$isBusinessHour = BusinessHours::isBusinessHour($datetime);
// Resultado: false
```

#### Formatar Data para Padrão Brasileiro

```php
use Eduardoks98\Helpers\Formatters\DateFormatter;

$date = '2024-12-31';
$formatted = DateFormatter::toBrazilian($date);
// Resultado: "31/12/2024"
```

#### Converter Formatos de Data

```php
use Eduardoks98\Helpers\Formatters\DateFormatter;

$date = '31/12/2024';
$converted = DateFormatter::formatDateTo($date, 'd/m/Y', 'Y-m-d');
// Resultado: "2024-12-31"
```

---

### 4. Transformação de Dados

#### Array para Objeto

```php
use Eduardoks98\Helpers\ArrayHelper;

$array = ['name' => 'John', 'age' => 30];
$object = ArrayHelper::toObject($array);

echo $object->name; // "John"
echo $object->age;  // 30
```

#### Filtrar Array por Chaves

```php
use Eduardoks98\Helpers\ArrayHelper;

$data = [
    'name' => 'John',
    'age' => 30,
    'email' => 'john@example.com',
    'password' => 'secret',
];

$filtered = ArrayHelper::filterByKeys(['name', 'email'], $data);
// Resultado: ['name' => 'John', 'email' => 'john@example.com']
```

#### JSON Helpers

```php
use Eduardoks98\Helpers\JsonHelper;

// JSON to Array
$json = '{"name":"John","age":30}';
$array = JsonHelper::toArray($json);

// JSON to Object
$object = JsonHelper::toObject($json);

// Validar JSON
$isValid = JsonHelper::isValid($json); // true
$isValid = JsonHelper::isValid('invalid json'); // false
```

---

### 5. Database Helpers (Laravel)

#### Transaction Helpers

```php
use Eduardoks98\Helpers\TransactionHelper;

public function createUser($data)
{
    TransactionHelper::begin();

    try {
        $user = User::create($data);
        $user->profile()->create($data['profile']);

        TransactionHelper::commit();

        return $user;
    } catch (\Throwable $e) {
        TransactionHelper::rollback();
        throw $e;
    }
}

// Com conexão específica
TransactionHelper::begin('mysql');
TransactionHelper::commit('mysql');
TransactionHelper::rollback('mysql');
```

---

### 6. Outros Helpers

#### Gerar Token Aleatório

```php
use function Eduardoks98\Helpers\generateRandomToken;

// Token padrão (32 caracteres)
$token = generateRandomToken();

// Token com tamanho específico
$token = generateRandomToken(64);

// Token com opções
$token = generateRandomToken(32, [
    'uppercase' => true,
    'lowercase' => true,
    'numbers' => true,
    'special' => false,
]);
```

#### Converter Bytes para Formato Legível

```php
use function Eduardoks98\Helpers\convertBytesTo;

$size = convertBytesTo(1024);
// Resultado: "1.00 KB"

$size = convertBytesTo(1048576);
// Resultado: "1.00 MB"

$size = convertBytesTo(1073741824);
// Resultado: "1.00 GB"

// Com precisão personalizada
$size = convertBytesTo(1500, precision: 2);
// Resultado: "1.46 KB"
```

#### Verificar se Valor está Vazio (Enhanced)

```php
use function Eduardoks98\Helpers\isEmpty;

isEmpty(null);          // true
isEmpty('');            // true
isEmpty([]);            // true
isEmpty('   ');         // true (trim automático)
isEmpty('0');           // false
isEmpty(0);             // false
isEmpty('content');     // false
```

---

## 📝 Exemplos Completos

### Exemplo 1: Validação de Formulário de Cadastro

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use function Eduardoks98\Helpers\checkCPF;

class RegisterUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'cpf' => [
                'required',
                'string',
                'unique:users',
                function ($attribute, $value, $fail) {
                    if (!checkCPF($value)) {
                        $fail('O CPF informado é inválido.');
                    }
                }
            ],
            'phone' => 'required|string',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        // Limpar CPF e telefone
        $data['cpf'] = removerCaracteres($data['cpf']);
        $data['phone'] = removerCaracteres($data['phone']);

        return $data;
    }
}
```

### Exemplo 2: Formatar Dados para Exibição

```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Eduardoks98\Helpers\Formatters\{PhoneFormatter, MoneyFormatter, DateFormatter};

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'cpf' => $this->cpf, // Já está limpo no banco
            'cpf_formatted' => formatarCpfCnpj($this->cpf),
            'phone' => PhoneFormatter::format($this->phone),
            'balance' => MoneyFormatter::format($this->balance),
            'birth_date' => DateFormatter::toBrazilian($this->birth_date),
            'created_at' => $this->created_at->format('d/m/Y H:i:s'),
        ];
    }
}
```

### Exemplo 3: Cálculo de Prazo de Entrega

```php
namespace App\Services;

use Eduardoks98\Helpers\BusinessHours;

class DeliveryService
{
    public function calculateDeliveryDate($orderDate, $businessDaysToDeliver = 5)
    {
        $currentDate = new \DateTime($orderDate);
        $daysAdded = 0;

        while ($daysAdded < $businessDaysToDeliver) {
            $currentDate->modify('+1 day');

            if (BusinessHours::isBusinessDay($currentDate->format('Y-m-d'))) {
                $daysAdded++;
            }
        }

        return $currentDate->format('Y-m-d');
    }
}
```

---

## 📚 API Reference

### Funções Globais

| Função | Descrição |
|--------|-----------|
| `checkCPF($cpf)` | Valida CPF (retorna bool) |
| `formatarCpfCnpj($number)` | Adiciona zeros à esquerda |
| `removerCaracteres($string)` | Remove caracteres especiais |
| `generateRandomToken($length, $options)` | Gera token aleatório |
| `convertBytesTo($bytes, $precision)` | Converte bytes para KB/MB/GB |
| `isEmpty($value)` | Verifica se valor está vazio |

### Classes

| Classe | Métodos |
|--------|---------|
| `CpfValidator` | `validate($cpf)` |
| `CnpjValidator` | `validate($cnpj)` |
| `DocumentValidator` | `validate($doc)` |
| `PhoneFormatter` | `format($phone)` |
| `MoneyFormatter` | `format($amount)` |
| `DateFormatter` | `toBrazilian($date)`, `formatDateTo($date, $from, $to)` |
| `BusinessHours` | `calculate($start, $end)`, `isBusinessHour($datetime)` |
| `ArrayHelper` | `toObject($array)`, `filterByKeys($keys, $array)` |
| `JsonHelper` | `toArray($json)`, `toObject($json)`, `isValid($data)` |
| `TransactionHelper` | `begin($conn)`, `commit($conn)`, `rollback($conn)` |

---

## ⚠️ Troubleshooting

### CPF/CNPJ sempre inválido

**Problema**: Validação sempre retorna false.

**Solução**: Certifique-se de limpar caracteres especiais:

```php
use function Eduardoks98\Helpers\{checkCPF, removerCaracteres};

$cpf = '123.456.789-09';
$cpfLimpo = removerCaracteres($cpf); // "12345678909"
$isValid = checkCPF($cpfLimpo); // true
```

### Telefone não formata corretamente

**Problema**: PhoneFormatter retorna formato inesperado.

**Solução**: Forneça apenas números:

```php
// ❌ Errado
$phone = PhoneFormatter::format('(11) 98765-4321');

// ✅ Correto
$phone = removerCaracteres('(11) 98765-4321'); // "11987654321"
$formatted = PhoneFormatter::format($phone); // "(11) 98765-4321"
```

---

## 🔗 Dependências

**Nenhuma!** Este package é 100% standalone e pode ser usado fora do Laravel.

---

## 🔗 Links Relacionados

- [Validação CPF - Receita Federal](http://www.receita.fazenda.gov.br/)
- [Formatação de Telefones - ANATEL](https://www.anatel.gov.br/)

---

**Anterior**: [← Base API](./base-api.md) | **Próximo**: [Security →](./security.md)
