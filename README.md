# Omnipay: Ahlpay

**Ahlpay sanal pos gateway for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP. This package implements Ahlpay support for Omnipay.

Ahlpay is a payment institution that uses a JSON-based API with token authentication.

## Installation

```bash
composer require tcgunel/omnipay-ahlpay
```

## Available Methods

| Method | Description |
|--------|-------------|
| `purchase()` | Direct (non-3D) sale or 3D Secure redirect |
| `completePurchase()` | Query payment status after 3D callback |
| `void()` | Cancel/void a transaction |
| `refund()` | Refund a transaction (full or partial) |

## Authentication

Ahlpay requires a two-step authentication process:

1. **Obtain a token** by calling the Ahlpay authentication API with your email/password
2. **Use the token** in subsequent payment requests

The token must be obtained outside of this gateway (e.g., via a separate HTTP call to `/api/Security/AuthenticationMerchant`) and passed to the gateway as a parameter.

## Usage

### Gateway Initialization

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Ahlpay');

$gateway->setMerchantId('100');           // Your member ID
$gateway->setMerchantUser('user@email'); // Login email
$gateway->setMerchantPassword('pass');    // Login password
$gateway->setMerchantStorekey('key');     // Store key for hash
$gateway->setToken('jwt-token');          // Auth token (obtained separately)
$gateway->setTokenType('Bearer');         // Token type
$gateway->setAhlpayMerchantId(5001);      // Ahlpay merchant ID (from token response)
$gateway->setTestMode(true);
```

### Non-3D Purchase (Direct Sale)

```php
$response = $gateway->purchase([
    'amount'      => '100.00',
    'currency'    => 'TRY',
    'transactionId' => 'ORDER-12345',
    'secure'      => false,
    'card'        => [
        'number'      => '4508034508034509',
        'expiryMonth' => '12',
        'expiryYear'  => '2030',
        'cvv'         => '000',
    ],
])->send();

if ($response->isSuccessful()) {
    echo 'Transaction ID: ' . $response->getTransactionReference();
} else {
    echo 'Error: ' . $response->getMessage();
}
```

### 3D Secure Purchase

```php
$response = $gateway->purchase([
    'amount'      => '100.00',
    'currency'    => 'TRY',
    'transactionId' => 'ORDER-12345',
    'secure'      => true,
    'returnUrl'   => 'https://yoursite.com/payment/success',
    'cancelUrl'   => 'https://yoursite.com/payment/fail',
    'card'        => [
        'number'      => '4508034508034509',
        'expiryMonth' => '12',
        'expiryYear'  => '2030',
        'cvv'         => '000',
    ],
])->send();

if ($response->isRedirect()) {
    // Ahlpay returns HTML content for 3D redirect
    echo $response->getRedirectHtml();
}
```

### Complete 3D Secure Purchase (Callback Handler)

After the bank posts back to your `returnUrl`, query the payment status:

```php
$response = $gateway->completePurchase([
    'transactionId' => $_POST['orderId'],
    'rnd'           => $_POST['rnd'],  // optional
])->send();

if ($response->isSuccessful()) {
    echo 'Payment confirmed! Transaction: ' . $response->getTransactionReference();
} else {
    echo 'Payment failed: ' . $response->getMessage();
}
```

### Void (Cancel)

```php
$response = $gateway->void([
    'orderNumber' => 'ORDER-12345',
    'currency'    => 'TRY',
])->send();

if ($response->isSuccessful()) {
    echo 'Transaction voided.';
} else {
    echo 'Error: ' . $response->getMessage();
}
```

### Refund

```php
$response = $gateway->refund([
    'orderNumber' => 'ORDER-12345',
    'amount'      => '50.00',
    'currency'    => 'TRY',
])->send();

if ($response->isSuccessful()) {
    echo 'Refund processed.';
} else {
    echo 'Error: ' . $response->getMessage();
}
```

## Hash Algorithm

```
SHA512(StoreKey + Rnd + OrderId + TotalAmount + MerchantId)
```

The hash uses UTF-16LE encoding before SHA512, and the result is uppercase hex.

## Endpoints

| Environment | Base URL |
|-------------|----------|
| Test | `https://testahlsanalpos.ahlpay.com.tr` |
| Production | `https://ahlsanalpos.ahlpay.com.tr` |

### API Paths

| Operation | Path |
|-----------|------|
| Non-3D Payment | `/api/Payment/PaymentNon3D` |
| 3D Payment | `/api/Payment/Payment3DWithEventRedirect` |
| Payment Inquiry | `/api/Payment/PaymentInquiry` |
| Void | `/api/Payment/Void` |
| Refund | `/api/Payment/Refund` |
| Authentication | `/api/Security/AuthenticationMerchant` |

## Running Tests

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Code Style

```bash
composer lint
```

## License

MIT License. See [LICENSE](LICENSE) for details.
