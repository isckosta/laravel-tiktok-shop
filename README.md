# Laravel TikTok Shop

Integração robusta e extensível com a **API da TikTok Shop**, construída para aplicações Laravel.
Suporta autenticação OAuth, múltiplas lojas (multi-tenant), gerenciamento de produtos, pedidos, preços, estoque e webhooks.

---

## 🚀 Requisitos

* PHP **^8.3**
* Laravel **^11 ou ^12**
* Extensão cURL habilitada

---

## 📦 Instalação

```bash
composer require isckosta/laravel-tiktok-shop
```

Publique as configurações:

```bash
php artisan vendor:publish --tag="tiktokshop-config"
```

Publique os migrations:

```bash
php artisan vendor:publish --tag="tiktokshop-migrations"
```

Opcionalmente, publique os controllers stubs:

```bash
php artisan vendor:publish --tag="tiktokshop-controllers"
```

Opcionalmente, publique as rotas:

```bash
php artisan vendor:publish --tag="tiktokshop-routes"
```

---

## ⚙️ Configuração

### 1. Variáveis de ambiente

```env
TTSHOP_SERVICE_ID=seu_service_id
TTSHOP_APP_KEY=seu_app_key
TTSHOP_APP_SECRET=seu_app_secret
TTSHOP_REDIRECT_URI=https://seusistema.com/tiktok/callback
TTSHOP_BASE_URI=https://open-api.tiktokglobalshop.com
TTSHOP_AUTH_BASE_URI=https://auth.tiktok-shops.com
```

> As credenciais (App Key e App Secret) são obtidas no painel da TikTok Shop Developer:
> [https://partner.tiktokglobalshop.com](https://partner.tiktokglobalshop.com)

---

## 🔑 Rotas & Fluxo de Autenticação (OAuth)

O package já registra automaticamente as rotas de autorização e callback se `enable_default_routes = true` (padrão):

```php
/tiktok/authorize     -> TikTokShopAuthController@redirect
/tiktok/callback      -> TikTokShopAuthController@callback
/webhooks/tiktok-shop -> TikTokWebhookController@handle
```

Se você **publicar as rotas**, o package vai dar preferência ao arquivo publicado (`routes/tiktokshop.php`) em vez do do package.
Se você **desabilitar a flag**, nenhuma rota será registrada e você deve criar manualmente.

### Como personalizar:

1. Desabilitar as rotas no `config/tiktokshop.php`:

   ```php
   'enable_default_routes' => false,
   ```
2. Publicar os controllers stubs:

   ```bash
   php artisan vendor:publish --tag="tiktokshop-controllers"
   ```
3. Definir suas próprias rotas em `routes/web.php` ou usar o arquivo publicado:

   ```php
   use App\Http\Controllers\TikTokOAuthController;
   use App\Http\Controllers\TikTokWebhookController;

   Route::get('/tiktok/authorize', [TikTokOAuthController::class, 'redirect']);
   Route::get('/tiktok/callback', [TikTokOAuthController::class, 'callback']);
   Route::post('/webhooks/tiktok', [TikTokWebhookController::class, 'handle']);
   ```

---

### Exemplo de Controller OAuth (stub publicado)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TikTokShop\Facades\TikTokShop;

class TikTokOAuthController extends Controller
{
    public function redirect()
    {
        $url = TikTokShop::oauth()->getAuthorizationUrl();
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');

        $tokens = TikTokShop::oauth()->getAccessToken($code);

        // Salvar tokens no banco
        // TikTokCredential::create($tokens);

        return response()->json($tokens);
    }
}
```

---

## 🛒 Uso Básico

### Buscar produtos

```php
$client = TikTokShop::connection('default');

$response = $client->products()->search([
    'page_size' => 20,
]);
```

### Criar produto

```php
$response = TikTokShop::connection('default')->products()->create($payload);
```

### Buscar pedidos

```php
$response = TikTokShop::connection('default')->orders()->list([
    'page_size' => 10,
]);
```

---

## ⚡ Comandos Artisan

### Autorizar uma loja

Inicia o fluxo de autorização OAuth:

```bash
php artisan tiktokshop:authorize
```

### Sincronizar produtos

Dispara a sincronização de produtos da loja com a TikTok Shop:

```bash
php artisan tiktokshop:sync-products
```

---

## 📂 Publicações

### Configuração

```bash
php artisan vendor:publish --tag="tiktokshop-config"
```

### Migrations

```bash
php artisan vendor:publish --tag="tiktokshop-migrations"
```

### Controllers stubs

```bash
php artisan vendor:publish --tag="tiktokshop-controllers"
```

### Rotas

```bash
php artisan vendor:publish --tag="tiktokshop-routes"
```

---

## ✅ Roadmap

* [x] Autenticação OAuth
* [x] Gerenciamento de produtos
* [x] Pedidos
* [ ] Webhooks
* [ ] Estoque e preços
* [ ] Relatórios e estatísticas
* [ ] SDK para Flutter/React Native

---

## 🧪 Testes

```bash
php artisan test
```

---

## 📄 Licença

MIT © [isckosta](https://github.com/isckosta)
