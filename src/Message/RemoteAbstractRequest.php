<?php

namespace Omnipay\Ahlpay\Message;

use Omnipay\Ahlpay\Traits\PurchaseGettersSetters;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

abstract class RemoteAbstractRequest extends AbstractRequest
{
    use PurchaseGettersSetters;

    /**
     * @throws InvalidRequestException
     */
    protected function validateSettings(): void
    {
        $this->validate('merchantId', 'merchantUser', 'merchantPassword', 'merchantStorekey');
    }

    /**
     * Get the base API URL based on test mode.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        if ($this->getTestMode()) {
            return 'https://testahlsanalpos.ahlpay.com.tr';
        }

        return 'https://ahlsanalpos.ahlpay.com.tr';
    }

    /**
     * Post JSON data to the Ahlpay API.
     *
     * @param array<string, mixed> $data
     * @param string $endpoint
     * @return string
     */
    protected function postJson(array $data, string $endpoint): string
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $token = $this->getToken();
        $tokenType = $this->getTokenType() ?? 'Bearer';

        if ($token) {
            $headers['Authorization'] = $tokenType . ' ' . $token;
        }

        $httpResponse = $this->httpClient->request(
            'POST',
            $this->getBaseUrl() . $endpoint,
            $headers,
            json_encode($data)
        );

        return (string) $httpResponse->getBody();
    }

    abstract protected function createResponse($data);
}
