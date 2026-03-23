<?php

namespace Omnipay\Ahlpay\Tests\Feature;

use Omnipay\Ahlpay\Gateway;
use Omnipay\Ahlpay\Message\CompletePurchaseRequest;
use Omnipay\Ahlpay\Message\PurchaseRequest;
use Omnipay\Ahlpay\Message\RefundRequest;
use Omnipay\Ahlpay\Message\VoidRequest;
use Omnipay\Ahlpay\Tests\TestCase;

class GatewayTest extends TestCase
{
	public function test_gateway_name()
	{
		$this->assertEquals('Ahlpay', $this->gateway->getName());
	}

	public function test_gateway_default_parameters()
	{
		$defaults = $this->gateway->getDefaultParameters();

		$this->assertArrayHasKey('clientIp', $defaults);
		$this->assertArrayHasKey('merchantId', $defaults);
		$this->assertArrayHasKey('merchantUser', $defaults);
		$this->assertArrayHasKey('merchantPassword', $defaults);
		$this->assertArrayHasKey('merchantStorekey', $defaults);
		$this->assertArrayHasKey('installment', $defaults);
		$this->assertArrayHasKey('secure', $defaults);
		$this->assertArrayHasKey('token', $defaults);
		$this->assertArrayHasKey('tokenType', $defaults);
		$this->assertArrayHasKey('ahlpayMerchantId', $defaults);

		$this->assertEquals('127.0.0.1', $defaults['clientIp']);
		$this->assertFalse($defaults['secure']);
		$this->assertEquals('Bearer', $defaults['tokenType']);
	}

	public function test_gateway_purchase_returns_correct_request()
	{
		$request = $this->gateway->purchase([]);

		$this->assertInstanceOf(PurchaseRequest::class, $request);
	}

	public function test_gateway_complete_purchase_returns_correct_request()
	{
		$request = $this->gateway->completePurchase([]);

		$this->assertInstanceOf(CompletePurchaseRequest::class, $request);
	}

	public function test_gateway_void_returns_correct_request()
	{
		$request = $this->gateway->void([]);

		$this->assertInstanceOf(VoidRequest::class, $request);
	}

	public function test_gateway_refund_returns_correct_request()
	{
		$request = $this->gateway->refund([]);

		$this->assertInstanceOf(RefundRequest::class, $request);
	}

	public function test_gateway_getters_setters()
	{
		$this->gateway->setMerchantId('100');
		$this->assertEquals('100', $this->gateway->getMerchantId());

		$this->gateway->setMerchantUser('test@ahlpay.com');
		$this->assertEquals('test@ahlpay.com', $this->gateway->getMerchantUser());

		$this->gateway->setMerchantPassword('AhlPass123');
		$this->assertEquals('AhlPass123', $this->gateway->getMerchantPassword());

		$this->gateway->setMerchantStorekey('AhlStoreKey456');
		$this->assertEquals('AhlStoreKey456', $this->gateway->getMerchantStorekey());

		$this->gateway->setInstallment(3);
		$this->assertEquals(3, $this->gateway->getInstallment());

		$this->gateway->setSecure(true);
		$this->assertTrue($this->gateway->getSecure());

		$this->gateway->setClientIp('192.168.1.1');
		$this->assertEquals('192.168.1.1', $this->gateway->getClientIp());

		$this->gateway->setToken('test-token');
		$this->assertEquals('test-token', $this->gateway->getToken());

		$this->gateway->setTokenType('Bearer');
		$this->assertEquals('Bearer', $this->gateway->getTokenType());

		$this->gateway->setAhlpayMerchantId(5001);
		$this->assertEquals(5001, $this->gateway->getAhlpayMerchantId());
	}
}
