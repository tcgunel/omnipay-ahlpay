<?php

namespace Omnipay\Ahlpay\Message;

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;

class PurchaseResponse extends RemoteAbstractResponse implements RedirectResponseInterface
{
    /** @var bool */
    private $is3D = false;

    /**
     * @param RequestInterface $request
     * @param string|array<string, mixed> $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        /** @var PurchaseRequest $req */
        $req = $request;
        $this->is3D = (bool) $req->getSecure();
    }

    public function isSuccessful(): bool
    {
        if ($this->is3D) {
            return false;
        }

        return parent::isSuccessful();
    }

    public function isRedirect(): bool
    {
        if (!$this->is3D) {
            return false;
        }

        return ($this->parsedData['isSuccess'] ?? false) === true;
    }

    public function getRedirectUrl(): string
    {
        return '';
    }

    public function getRedirectMethod(): string
    {
        return 'GET';
    }

    /**
     * Get the redirect HTML content from Ahlpay.
     *
     * @return string|null
     */
    public function getRedirectHtml(): ?string
    {
        if ($this->is3D && ($this->parsedData['isSuccess'] ?? false)) {
            return $this->parsedData['data'] ?? null;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRedirectData(): array
    {
        return $this->parsedData;
    }
}
