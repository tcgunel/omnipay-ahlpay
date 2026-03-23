<?php

namespace Omnipay\Ahlpay\Tests\Feature;

use Omnipay\Ahlpay\Constants\TxnType;
use Omnipay\Ahlpay\Helpers\Helper;
use Omnipay\Ahlpay\Message\VoidRequest;
use Omnipay\Ahlpay\Tests\TestCase;
use Omnipay\Common\Exception\InvalidRequestException;

class VoidTest extends TestCase
{
    /**
     * @throws InvalidRequestException
     * @throws \JsonException
     */
    public function test_void_request()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/VoidRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new VoidRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        $this->assertIsArray($data);
        $this->assertEquals(100, $data['memberId']);
        $this->assertEquals(5001, $data['merchantId']);
        $this->assertEquals('test@ahlpay.com', $data['userCode']);
        $this->assertEquals(TxnType::VOID, $data['txnType']);
        $this->assertEquals('ORDER-12345', $data['orderId']);
        $this->assertEquals('999999900', $data['totalAmount']);
        $this->assertEquals('949', $data['currency']);
        $this->assertEquals('RNDORDER-12345', $data['rnd']);

        // Verify hash
        $expectedHash = Helper::generateHash('AhlStoreKey456' . 'RNDORDER-12345' . 'ORDER-12345' . '999999900' . '5001');
        $this->assertEquals($expectedHash, $data['hash']);
    }

    public function test_void_request_validation_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/VoidRequest-ValidationError.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new VoidRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->expectException(InvalidRequestException::class);

        $request->getData();
    }

    public function test_void_success()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/VoidRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $this->setMockHttpResponse('VoidResponseSuccess.txt');

        $response = $this->gateway->void($options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('Islem basarili', $response->getMessage());

        // Verify the HTTP request was sent to void endpoint
        $requests = $this->getMockedRequests();
        $httpRequest = $requests[0];
        $this->assertStringContainsString(
            'testahlsanalpos.ahlpay.com.tr/api/Payment/Void',
            (string) $httpRequest->getUri()
        );
    }

    public function test_void_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/VoidRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $this->setMockHttpResponse('VoidResponseError.txt');

        $response = $this->gateway->void($options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('Iptal edilecek islem bulunamadi', $response->getMessage());
    }

    public function test_void_gateway_method()
    {
        $request = $this->gateway->void([
            'merchantId' => '100',
            'merchantUser' => 'test@ahlpay.com',
            'merchantPassword' => 'AhlPass123',
            'merchantStorekey' => 'AhlStoreKey456',
        ]);

        $this->assertInstanceOf(VoidRequest::class, $request);
    }
}
