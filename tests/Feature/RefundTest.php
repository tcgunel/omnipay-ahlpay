<?php

namespace Omnipay\Ahlpay\Tests\Feature;

use Omnipay\Ahlpay\Constants\TxnType;
use Omnipay\Ahlpay\Helpers\Helper;
use Omnipay\Ahlpay\Message\RefundRequest;
use Omnipay\Ahlpay\Tests\TestCase;
use Omnipay\Common\Exception\InvalidRequestException;

class RefundTest extends TestCase
{
	/**
	 * @throws InvalidRequestException
	 * @throws \JsonException
	 */
	public function test_refund_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/RefundRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$this->assertIsArray($data);
		$this->assertEquals(100, $data['memberId']);
		$this->assertEquals(5001, $data['merchantId']);
		$this->assertEquals('test@ahlpay.com', $data['userCode']);
		$this->assertEquals(TxnType::REFUND, $data['txnType']);
		$this->assertEquals('ORDER-12345', $data['orderId']);
		$this->assertEquals('5000', $data['totalAmount']);
		$this->assertEquals('949', $data['currency']);
		$this->assertEquals('RNDORDER-12345', $data['rnd']);

		// Verify hash
		$expectedHash = Helper::generateHash('AhlStoreKey456' . 'RNDORDER-12345' . 'ORDER-12345' . '5000' . '5001');
		$this->assertEquals($expectedHash, $data['hash']);
	}

	public function test_refund_request_validation_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/RefundRequest-ValidationError.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidRequestException::class);

		$request->getData();
	}

	public function test_refund_success()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/RefundRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$this->setMockHttpResponse('RefundResponseSuccess.txt');

		$response = $this->gateway->refund($options)->send();

		$this->assertTrue($response->isSuccessful());
		$this->assertEquals('Islem basarili', $response->getMessage());

		// Verify the HTTP request was sent to refund endpoint
		$requests = $this->getMockedRequests();
		$httpRequest = $requests[0];
		$this->assertStringContainsString(
			'testahlsanalpos.ahlpay.com.tr/api/Payment/Refund',
			(string) $httpRequest->getUri()
		);
	}

	public function test_refund_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/RefundRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$this->setMockHttpResponse('RefundResponseError.txt');

		$response = $this->gateway->refund($options)->send();

		$this->assertFalse($response->isSuccessful());
		$this->assertEquals('Iade edilecek islem bulunamadi', $response->getMessage());
	}

	public function test_refund_gateway_method()
	{
		$request = $this->gateway->refund([
			'merchantId' => '100',
			'merchantUser' => 'test@ahlpay.com',
			'merchantPassword' => 'AhlPass123',
			'merchantStorekey' => 'AhlStoreKey456',
		]);

		$this->assertInstanceOf(RefundRequest::class, $request);
	}
}
