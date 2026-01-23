# Custos e Configuração dos Serviços Externos

Este documento serve como **índice centralizado** dos custos de cada package. A documentação detalhada (como obter API keys, configuração completa) está no README.md de cada package.

---

## Resumo Rápido

### Packages 100% Gratuitos

| Package | Documentação |
|---------|--------------|
| `base-api` | [README](../../packages/core/base-api/README.md) |
| `helpers` | [README](../../packages/core/helpers/README.md) |
| `security` | [README](../../packages/core/security/README.md) |
| `rate-limiter` | [README](../../packages/core/rate-limiter/README.md) |
| `auth` | [README](../../packages/core/auth/README.md) |
| `performance` | [README](../../packages/core/performance/README.md) |
| `reverb` | [README](../../packages/core/reverb/README.md) |
| `api-docs` | [README](../../packages/core/api-docs/README.md) |
| `health` | [README](../../packages/core/health/README.md) |
| `email-validator` | [README](../../packages/brazilian/email-validator/README.md) |
| `banking` | [README](../../packages/brazilian/banking/README.md) |
| `media-library` | [README](../../packages/storage/media-library/README.md) |
| `geolocation` (Nominatim) | [README](../../packages/brazilian/geolocation/README.md) |

### Packages com Serviços Pagos

| Package | Serviço | Custo | Documentação |
|---------|---------|-------|--------------|
| `google-auth` | Google OAuth | Grátis | [README](../../packages/auth/google/README.md) |
| `facebook-auth` | Facebook OAuth | Grátis | [README](../../packages/auth/facebook/README.md) |
| `microsoft-auth` | Microsoft OAuth | Grátis | [README](../../packages/auth/microsoft/README.md) |
| `recaptcha` | Google reCAPTCHA | Grátis (1M/mês) | [README](../../packages/core/recaptcha/README.md) |
| `payment-stripe` | Stripe | 2.9% + $0.30/tx | [README](../../packages/payment/stripe/README.md) |
| `payment-mercadopago` | MercadoPago | 0.99% - 4.98% | [README](../../packages/payment/mercadopago/README.md) |
| `payment-abacatepay` | AbacatePay | R$ 0,80/PIX | [README](../../packages/payment/abacatepay/README.md) |
| `sms-twilio` | Twilio | ~$0.05/SMS (BR) | [README](../../packages/sms/twilio/README.md) |
| `sms-comtele` | Comtele | R$ 0,069+/SMS | [README](../../packages/sms/comtele/README.md) |
| `whatsapp-official` | Meta Cloud API | 1k grátis/mês | [README](../../packages/whatsapp/official/README.md) |
| `whatsapp-converx` | Converx | Sob consulta | [README](../../packages/whatsapp/converx/README.md) |
| `storage-s3` | AWS S3 | Free tier 12 meses | [README](../../packages/storage/s3/README.md) |
| `geolocation` (Google/HERE) | Google Maps / HERE | Free tier limitado | [README](../../packages/brazilian/geolocation/README.md) |

---

## Detalhes por Categoria

### OAuth (Gratuito)

### Google Auth

**Custo**: Gratuito (ilimitado)

**Como obter as credenciais**:

1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um novo projeto ou selecione existente
3. Vá em **APIs & Services** → **Credentials**
4. Clique em **Create Credentials** → **OAuth client ID**
5. Selecione **Web application**
6. Adicione as URLs de redirect:
   - `https://seusite.com/auth/google/callback`
   - `http://localhost:8000/auth/google/callback` (dev)
7. Copie o **Client ID** e **Client Secret**

**Configuração** (`.env`):
```env
GOOGLE_CLIENT_ID=123456789.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxx
GOOGLE_REDIRECT_URI=https://seusite.com/auth/google/callback
```

---

### Facebook Auth

**Custo**: Gratuito (ilimitado)

**Como obter as credenciais**:

1. Acesse [Facebook Developers](https://developers.facebook.com/)
2. Clique em **My Apps** → **Create App**
3. Selecione **Consumer** ou **Business**
4. Dê um nome ao app
5. No dashboard, vá em **Settings** → **Basic**
6. Copie **App ID** e **App Secret**
7. Em **Facebook Login** → **Settings**, adicione:
   - Valid OAuth Redirect URIs: `https://seusite.com/auth/facebook/callback`

**Configuração** (`.env`):
```env
FACEBOOK_CLIENT_ID=1234567890
FACEBOOK_CLIENT_SECRET=abcdef123456
FACEBOOK_REDIRECT_URI=https://seusite.com/auth/facebook/callback
```

---

### Microsoft Auth

**Custo**: Gratuito (ilimitado)

**Como obter as credenciais**:

1. Acesse [Azure Portal](https://portal.azure.com/)
2. Vá em **Azure Active Directory** → **App registrations**
3. Clique em **New registration**
4. Configure:
   - Name: Nome do seu app
   - Supported account types: Escolha conforme necessidade
   - Redirect URI: `https://seusite.com/auth/microsoft/callback`
5. Após criar, copie:
   - **Application (client) ID**
   - **Directory (tenant) ID**
6. Vá em **Certificates & secrets** → **New client secret**
7. Copie o **Value** do secret (só aparece uma vez!)

**Configuração** (`.env`):
```env
MICROSOFT_CLIENT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
MICROSOFT_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MICROSOFT_TENANT_ID=common
MICROSOFT_REDIRECT_URI=https://seusite.com/auth/microsoft/callback
```

---

## Pagamentos (Taxa por transação)

### Stripe

**Custo**: 2.9% + $0.30 por transação (varia por país)

**Free tier**: Não tem mensalidade, só paga quando processa

**Como obter as credenciais**:

1. Acesse [Stripe Dashboard](https://dashboard.stripe.com/)
2. Crie uma conta (validação de identidade necessária)
3. No dashboard, vá em **Developers** → **API keys**
4. Copie:
   - **Publishable key** (pk_test_xxx ou pk_live_xxx)
   - **Secret key** (sk_test_xxx ou sk_live_xxx)
5. Para webhooks, vá em **Developers** → **Webhooks**
6. Adicione endpoint: `https://seusite.com/webhooks/stripe`
7. Copie o **Signing secret** (whsec_xxx)

**Configuração** (`.env`):
```env
STRIPE_KEY=pk_test_xxxxxxxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
STRIPE_CURRENCY=brl
```

**Links úteis**:
- [Preços Stripe Brasil](https://stripe.com/br/pricing)
- [Documentação](https://stripe.com/docs)

---

### MercadoPago

**Custo**:
- PIX: 0.99% (mín R$ 0,01)
- Cartão crédito: 4.98%
- Boleto: R$ 3,49

**Free tier**: Não tem mensalidade, só paga quando processa

**Como obter as credenciais**:

1. Acesse [MercadoPago Developers](https://www.mercadopago.com.br/developers/)
2. Faça login com sua conta MercadoPago
3. Vá em **Suas integrações** → **Criar aplicação**
4. Preencha os dados do app
5. Após criar, vá em **Credenciais de produção**
6. Copie:
   - **Public Key**
   - **Access Token**
7. Para webhooks, vá em **Webhooks** e configure a URL

**Configuração** (`.env`):
```env
MERCADOPAGO_PUBLIC_KEY=APP_USR-xxxxxxxx
MERCADOPAGO_ACCESS_TOKEN=APP_USR-xxxxxxxxxxxxx
MERCADOPAGO_WEBHOOK_SECRET=sua_senha_webhook
```

**Links úteis**:
- [Preços MercadoPago](https://www.mercadopago.com.br/ajuda/custo-receber-pagamentos_220)
- [Documentação](https://www.mercadopago.com.br/developers/pt/docs)

---

### AbacatePay

**Custo**: R$ 0,80 por PIX (taxa fixa)

**Free tier**: Não tem mensalidade

**Como obter as credenciais**:

1. Acesse [AbacatePay](https://abacatepay.com/)
2. Crie uma conta
3. Complete a verificação de identidade
4. No dashboard, vá em **Configurações** → **API**
5. Gere uma **API Key**

**Configuração** (`.env`):
```env
ABACATEPAY_API_KEY=abct_xxxxxxxxxxxxxxxx
ABACATEPAY_WEBHOOK_SECRET=sua_senha_webhook
```

**Links úteis**:
- [Preços AbacatePay](https://abacatepay.com/pricing)
- [Documentação](https://docs.abacatepay.com/)

---

## SMS (Pago)

### Twilio

**Custo**: ~$0.0075/SMS enviado (EUA), varia por país

**Free tier**: ~$15 de crédito ao criar conta

**Como obter as credenciais**:

1. Acesse [Twilio Console](https://www.twilio.com/console)
2. Crie uma conta (cartão de crédito necessário para verificação)
3. No dashboard, você verá:
   - **Account SID**
   - **Auth Token**
4. Vá em **Phone Numbers** → **Buy a number**
5. Compre um número (a partir de $1/mês)

**Configuração** (`.env`):
```env
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_FROM=+15551234567
TWILIO_WEBHOOK_URL=https://seusite.com/webhooks/twilio
```

**Preços aproximados**:
| País | Envio | Recebimento |
|------|-------|-------------|
| Brasil | $0.0495 | $0.0075 |
| EUA | $0.0079 | $0.0075 |

**Links úteis**:
- [Preços Twilio](https://www.twilio.com/sms/pricing/br)
- [Documentação](https://www.twilio.com/docs/sms)

---

### Comtele

**Custo**: A partir de R$ 0,069/SMS (pacotes)

**Free tier**: Não tem

**Como obter as credenciais**:

1. Acesse [Comtele](https://www.comtele.com.br/)
2. Clique em **Criar conta**
3. Complete o cadastro
4. Entre em contato com comercial para ativar API
5. No painel, vá em **Configurações** → **API**
6. Copie sua **API Key**

**Configuração** (`.env`):
```env
COMTELE_API_KEY=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
COMTELE_SENDER=SeuNome
```

**Pacotes de crédito**:
| Quantidade | Preço | Por SMS |
|------------|-------|---------|
| 500 | R$ 49,50 | R$ 0,099 |
| 1.000 | R$ 89,00 | R$ 0,089 |
| 5.000 | R$ 395,00 | R$ 0,079 |
| 10.000 | R$ 690,00 | R$ 0,069 |

**Links úteis**:
- [Preços Comtele](https://www.comtele.com.br/preco/)
- [Documentação API](https://www.comtele.com.br/api-sms/)

---

## WhatsApp (Pago)

### WhatsApp Business API (Meta)

**Custo**: Varia por tipo de conversa e país

**Free tier**: 1.000 conversas/mês gratuitas

**Como obter as credenciais**:

1. Acesse [Meta for Developers](https://developers.facebook.com/)
2. Crie um app do tipo **Business**
3. Adicione o produto **WhatsApp**
4. Siga o setup wizard:
   - Vincule uma conta WhatsApp Business
   - Verifique seu número de telefone
5. No dashboard do WhatsApp, copie:
   - **Phone Number ID**
   - **WhatsApp Business Account ID**
6. Em **Configuration** → **Access Tokens**, gere um token permanente
7. Configure o webhook URL

**Configuração** (`.env`):
```env
WHATSAPP_TOKEN=EAAxxxxxxxxxxxxxxx
WHATSAPP_PHONE_NUMBER_ID=1234567890
WHATSAPP_BUSINESS_ACCOUNT_ID=1234567890
WHATSAPP_WEBHOOK_VERIFY_TOKEN=seu_token_verificacao
WHATSAPP_WEBHOOK_URL=https://seusite.com/webhooks/whatsapp
```

**Preços (Brasil)**:
| Tipo | Custo |
|------|-------|
| Marketing | ~R$ 0,40/conversa |
| Utility | ~R$ 0,20/conversa |
| Authentication | ~R$ 0,18/conversa |
| Service | ~R$ 0,15/conversa |

**Links úteis**:
- [Preços WhatsApp API](https://developers.facebook.com/docs/whatsapp/pricing)
- [Documentação](https://developers.facebook.com/docs/whatsapp/cloud-api)

---

### Converx

**Custo**: Sob consulta (planos mensais)

**Como obter as credenciais**:

1. Acesse [Converx](https://converx.com.br/) ou entre em contato comercial
2. Contrate um plano
3. Após ativação, acesse o painel
4. Vá em **Configurações** → **API**
5. Copie:
   - **API URL** (base URL da sua instância)
   - **API Token**
   - **Inbox ID** (ID da caixa de entrada)

**Configuração** (`.env`):
```env
CONVERX_API_URL=https://sua-instancia.converx.com.br/api/v1
CONVERX_API_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxx
CONVERX_INBOX_ID=1
```

**Links úteis**:
- [Site Converx](https://converx.com.br/)

---

## Storage (Free tier + Pago)

### AWS S3

**Custo**: Pay-as-you-go

**Free tier**: 5GB storage + 20.000 GET + 2.000 PUT por 12 meses

**Como obter as credenciais**:

1. Acesse [AWS Console](https://console.aws.amazon.com/)
2. Crie uma conta AWS (cartão necessário)
3. Vá em **IAM** → **Users** → **Add user**
4. Nome: `s3-api-user`
5. Selecione **Programmatic access**
6. Em permissões, anexe: `AmazonS3FullAccess`
7. Copie:
   - **Access Key ID**
   - **Secret Access Key**
8. Vá em **S3** → **Create bucket**
9. Configure:
   - Nome único globalmente
   - Região (ex: `sa-east-1` para São Paulo)
   - Block public access: conforme necessidade

**Configuração** (`.env`):
```env
AWS_ACCESS_KEY_ID=AKIAxxxxxxxxxxxxxxxx
AWS_SECRET_ACCESS_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=meu-bucket-unico

# CloudFront (opcional)
AWS_CLOUDFRONT_ENABLED=false
AWS_CLOUDFRONT_URL=https://dxxxxxxx.cloudfront.net
```

**Preços aproximados (São Paulo)**:
| Recurso | Custo |
|---------|-------|
| Storage | $0.0245/GB/mês |
| PUT/COPY/POST | $0.0054/1.000 requests |
| GET/SELECT | $0.00043/1.000 requests |
| Data transfer out | $0.15/GB (primeiros 10TB) |

**Links úteis**:
- [Preços S3](https://aws.amazon.com/s3/pricing/)
- [Free tier](https://aws.amazon.com/free/)
- [Documentação](https://docs.aws.amazon.com/s3/)

---

## Geocoding (Free tier + Pago)

### Nominatim (OpenStreetMap)

**Custo**: Gratuito

**Limitações**: 1 request/segundo, não para uso comercial pesado

**Configuração** (`.env`):
```env
GEOCODING_PROVIDER=nominatim
NOMINATIM_USER_AGENT=MeuApp/1.0 (contato@email.com)
```

**Importante**: Defina um User-Agent identificável conforme [policy](https://operations.osmfoundation.org/policies/nominatim/)

---

### Google Maps Geocoding

**Custo**: $5/1.000 requests

**Free tier**: $200/mês de crédito (40.000 requests)

**Como obter as credenciais**:

1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Crie ou selecione um projeto
3. Vá em **APIs & Services** → **Enable APIs**
4. Habilite **Geocoding API**
5. Vá em **Credentials** → **Create Credentials** → **API key**
6. Restrinja a key:
   - Application restrictions: HTTP referrers ou IP
   - API restrictions: Geocoding API

**Configuração** (`.env`):
```env
GEOCODING_PROVIDER=google
GOOGLE_MAPS_API_KEY=AIzaxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Links úteis**:
- [Preços Google Maps](https://developers.google.com/maps/documentation/geocoding/usage-and-billing)

---

### HERE Geocoding

**Custo**: $1/1.000 requests

**Free tier**: 250.000 requests/mês

**Como obter as credenciais**:

1. Acesse [HERE Developer](https://developer.here.com/)
2. Crie uma conta
3. Vá em **Projects** → **Create project**
4. Em **REST**, gere uma **API Key**

**Configuração** (`.env`):
```env
GEOCODING_PROVIDER=here
HERE_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Links úteis**:
- [Preços HERE](https://developer.here.com/pricing)

---

## reCAPTCHA

### Google reCAPTCHA v3

**Custo**: Gratuito (até 1 milhão de requests/mês)

**Como obter as credenciais**:

1. Acesse [reCAPTCHA Admin](https://www.google.com/recaptcha/admin)
2. Clique em **+** para criar novo site
3. Configure:
   - Label: Nome do seu site
   - reCAPTCHA type: **v3**
   - Domains: seusite.com, localhost
4. Aceite os termos
5. Copie:
   - **Site Key** (para frontend)
   - **Secret Key** (para backend)

**Configuração** (`.env`):
```env
RECAPTCHA_V3_SITE_KEY=6Lcxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
RECAPTCHA_V3_SECRET=6Lcxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
RECAPTCHA_THRESHOLD=0.5
```

**Links úteis**:
- [Documentação reCAPTCHA](https://developers.google.com/recaptcha/docs/v3)

---

## Resumo de Custos

### Para começar (custo zero):

Use apenas packages gratuitos:
- `base-api`, `helpers`, `security`, `rate-limiter`, `auth`
- `performance`, `reverb`, `api-docs`, `health`
- `email-validator`, `banking`, `media-library`
- OAuth: Google, Facebook, Microsoft
- Geocoding: Nominatim (gratuito)

### Custo mensal estimado (startup pequena):

| Serviço | Uso | Custo/mês |
|---------|-----|-----------|
| AWS S3 | 10GB + 100k requests | ~$5 |
| Twilio | 1.000 SMS | ~$50 |
| WhatsApp API | 1.000 conversas | Grátis (free tier) |
| Stripe | $10.000 processados | ~$320 (2.9%) |
| **Total** | | **~$375/mês** |

### Custo mensal estimado (empresa média):

| Serviço | Uso | Custo/mês |
|---------|-----|-----------|
| AWS S3 | 100GB + 1M requests | ~$50 |
| Twilio | 10.000 SMS | ~$500 |
| WhatsApp API | 10.000 conversas | ~$2.000 |
| Stripe | $100.000 processados | ~$3.200 |
| Google Maps | 100.000 requests | ~$300 |
| **Total** | | **~$6.050/mês** |

---

## Checklist de Configuração

### Ambiente de Desenvolvimento

```env
# Sempre usar keys de teste/sandbox!
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
MERCADOPAGO_ACCESS_TOKEN=TEST-xxx

# Usar providers gratuitos
GEOCODING_PROVIDER=nominatim

# Desabilitar serviços pagos
SMS_ENABLED=false
WHATSAPP_ENABLED=false
```

### Ambiente de Produção

```env
# Keys de produção
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx
MERCADOPAGO_ACCESS_TOKEN=APP_USR-xxx

# HTTPS obrigatório para webhooks
APP_URL=https://seusite.com

# Configurar todos os webhooks nas plataformas
```

---

**Última atualização**: 24 de Janeiro de 2026
