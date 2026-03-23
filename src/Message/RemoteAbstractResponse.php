<?php

namespace Omnipay\Ahlpay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

abstract class RemoteAbstractResponse extends AbstractResponse
{
    /** @var array<string, mixed> */
    protected $parsedData = [];

    /**
     * @param RequestInterface $request
     * @param string|array<string, mixed> $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $this->parsedData = is_array($decoded) ? $decoded : [];
        } elseif (is_array($data)) {
            $this->parsedData = $data;
        }
    }

    public function isSuccessful(): bool
    {
        return ($this->parsedData['isSuccess'] ?? false) === true;
    }

    public function getMessage(): ?string
    {
        return $this->parsedData['message'] ?? null;
    }

    public function getCode(): ?string
    {
        return isset($this->parsedData['errorCode']) ? (string) $this->parsedData['errorCode'] : null;
    }

    public function getTransactionReference(): ?string
    {
        if (isset($this->parsedData['data']) && is_array($this->parsedData['data'])) {
            return $this->parsedData['data']['authCode'] ?? null;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->parsedData;
    }
}
