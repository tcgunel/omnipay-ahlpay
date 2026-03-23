<?php

namespace Omnipay\Ahlpay\Tests\Feature;

use Omnipay\Ahlpay\Message\CompletePurchaseRequest;
use Omnipay\Ahlpay\Message\CompletePurchaseResponse;
use Omnipay\Ahlpay\Tests\TestCase;
use Omnipay\Common\Exception\InvalidRequestException;

class CompletePurchaseTest extends TestCase
{
	/**
	 * @throws InvalidRequestException
	 * @throws \JsonException
	 */
	public function test_complete_purchase_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/CompletePurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new CompletePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$this->assertIsArray($data);
		$this->assertEquals('100', $data['memberId']);
		$this->assertEquals(5001, $data['merchantId']);
		$this->assertEquals('ORDER-12345', $data['orderId']);
		$this->assertEquals('RNDORDER-12345', $data['rnd']);
	}

	public function test_complete_purchase_response_success()
	{
		$json = '{"isSuccess":true,"message":"Islem basarili","data":{"authCode":"AHL002","orderId":"ORDER-12345"}}';

		$response = new CompletePurchaseResponse(
			$this->getMockRequest(),
			$json
		);

		$this->assertTrue($response->isSuccessful());
		$this->assertEquals('Islem basarili', $response->getMessage());
		$this->assertEquals('AHL002', $response->getTransactionReference());
	}

	public function test_complete_purchase_response_error()
	{
		$json = '{"isSuccess":false,"message":"Islem bulunamadi","data":null}';

		$response = new CompletePurchaseResponse(
			$this->getMockRequest(),
			$json
		);

		$this->assertFalse($response->isSuccessful());
		$this->assertEquals('Islem bulunamadi', $response->getMessage());
	}

	public function test_complete_purchase_sends_http_request_success()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/CompletePurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$this->setMockHttpResponse('CompletePurchaseResponseSuccess.txt');

		$response = $this->gateway->completePurchase($options)->send();

		$this->assertTrue($response->isSuccessful());

		// Verify the HTTP request was sent to inquiry endpoint
		$requests = $this->getMockedRequests();
		$this->assertCount(1, $requests);

		$httpRequest = $requests[0];
		$this->assertEquals('POST', $httpRequest->getMethod());
		$this->assertStringContainsString(
			'testahlsanalpos.ahlpay.com.tr/api/Payment/PaymentInquiry',
			(string) $httpRequest->getUri()
		);
	}

	public function test_complete_purchase_sends_http_request_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/CompletePurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$this->setMockHttpResponse('CompletePurchaseResponseError.txt');

		$response = $this->gateway->completePurchase($options)->send();

		$this->assertFalse($response->isSuccessful());
		$this->assertEquals('Islem bulunamadi', $response->getMessage());
	}

	public function test_complete_purchase_gateway_method()
	{
		$request = $this->gateway->completePurchase([
			'merchantId' => '100',
			'merchantUser' => 'test@ahlpay.com',
			'merchantPassword' => 'AhlPass123',
			'merchantStorekey' => 'AhlStoreKey456',
		]);

		$this->assertInstanceOf(CompletePurchaseRequest::class, $request);
	}

	public function test_complete_purchase_request_validation_error()
	{
		$request = new CompletePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize([]);

		$this->expectException(InvalidRequestException::class);

		$request->getData();
	}
}
