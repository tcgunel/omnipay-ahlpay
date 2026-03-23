<?php

namespace Omnipay\Ahlpay\Tests\Feature;

use Omnipay\Ahlpay\Constants\TxnType;
use Omnipay\Ahlpay\Helpers\Helper;
use Omnipay\Ahlpay\Message\PurchaseRequest;
use Omnipay\Ahlpay\Message\PurchaseResponse;
use Omnipay\Ahlpay\Tests\TestCase;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;

class PurchaseTest extends TestCase
{
	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_non3d_purchase_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$this->assertIsArray($data);

		// Verify card info
		$this->assertEquals('4355084355084358', $data['cardNumber']);
		$this->assertEquals('12', $data['expiryDateMonth']);
		$this->assertEquals('2030', $data['expiryDateYear']);
		$this->assertEquals('000', $data['cvv']);
		$this->assertEquals('Test User', $data['cardHolderName']);

		// Verify merchant info
		$this->assertEquals(5001, $data['merchantId']);
		$this->assertEquals(100, $data['memberId']);
		$this->assertEquals('test@ahlpay.com', $data['userCode']);

		// Verify transaction info
		$this->assertEquals('10000', $data['totalAmount']);
		$this->assertEquals(TxnType::AUTH, $data['txnType']);
		$this->assertEquals('1', $data['installmentCount']);
		$this->assertEquals('949', $data['currency']);
		$this->assertEquals('ORDER-12345', $data['orderId']);
		$this->assertEquals('RNDORDER-12345', $data['rnd']);

		// Verify hash is present
		$this->assertNotEmpty($data['hash']);

		// Verify hash calculation
		$expectedHash = Helper::generateHash('AhlStoreKey456' . 'RNDORDER-12345' . 'ORDER-12345' . '10000' . '5001');
		$this->assertEquals($expectedHash, $data['hash']);

		// Non-3D should not have OkUrl/FailUrl
		$this->assertArrayNotHasKey('okUrl', $data);
		$this->assertArrayNotHasKey('failUrl', $data);
	}

	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_3d_purchase_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest3D.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$this->assertIsArray($data);

		// 3D should have OkUrl/FailUrl
		$this->assertEquals('https://example.com/payment/success', $data['okUrl']);
		$this->assertEquals('https://example.com/payment/fail', $data['failUrl']);
	}

	public function test_purchase_request_validation_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest-ValidationError.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidRequestException::class);

		$request->getData();
	}

	public function test_non3d_purchase_sends_http_request_success()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$this->setMockHttpResponse('PurchaseResponseSuccess.txt');

		$response = $this->gateway->purchase($options)->send();

		$this->assertTrue($response->isSuccessful());

		$this->assertFalse($response->isRedirect());

		$this->assertEquals('AHL001', $response->getTransactionReference());

		// Verify the HTTP request was sent
		$requests = $this->getMockedRequests();
		$this->assertCount(1, $requests);

		$httpRequest = $requests[0];
		$this->assertEquals('POST', $httpRequest->getMethod());
		$this->assertStringContainsString(
			'testahlsanalpos.ahlpay.com.tr/api/Payment/PaymentNon3D',
			(string) $httpRequest->getUri()
		);

		// Verify Authorization header
		$this->assertStringContainsString(
			'Bearer',
			$httpRequest->getHeaderLine('Authorization')
		);

		// Verify the body is JSON
		$body = (string) $httpRequest->getBody();
		$decoded = json_decode($body, true);
		$this->assertIsArray($decoded);
		$this->assertEquals('ORDER-12345', $decoded['orderId']);
	}

	public function test_non3d_purchase_sends_http_request_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$this->setMockHttpResponse('PurchaseResponseError.txt');

		$response = $this->gateway->purchase($options)->send();

		$this->assertFalse($response->isSuccessful());

		$this->assertEquals('Kart numarasi hatali', $response->getMessage());
	}

	public function test_3d_purchase_sends_http_request_redirect()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest3D.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$this->setMockHttpResponse('PurchaseResponse3DSuccess.txt');

		$response = $this->gateway->purchase($options)->send();

		$this->assertFalse($response->isSuccessful());
		$this->assertTrue($response->isRedirect());

		// Verify the HTTP request was sent to 3D endpoint
		$requests = $this->getMockedRequests();
		$httpRequest = $requests[0];
		$this->assertStringContainsString(
			'testahlsanalpos.ahlpay.com.tr/api/Payment/Payment3DWithEventRedirect',
			(string) $httpRequest->getUri()
		);
	}

	public function test_non3d_purchase_prod_endpoint()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$options['testMode'] = false;

		$this->setMockHttpResponse('PurchaseResponseSuccess.txt');

		$this->gateway->purchase($options)->send();

		$requests = $this->getMockedRequests();
		$httpRequest = $requests[0];

		$this->assertStringContainsString(
			'ahlsanalpos.ahlpay.com.tr/api/Payment/PaymentNon3D',
			(string) $httpRequest->getUri()
		);
	}

	public function test_purchase_gateway_method()
	{
		$request = $this->gateway->purchase([
			'merchantId' => '100',
			'merchantUser' => 'test@ahlpay.com',
			'merchantPassword' => 'AhlPass123',
			'merchantStorekey' => 'AhlStoreKey456',
		]);

		$this->assertInstanceOf(PurchaseRequest::class, $request);
	}
}
