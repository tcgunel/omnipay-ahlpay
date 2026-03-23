<?php

namespace Omnipay\Ahlpay;

use Omnipay\Ahlpay\Message\CompletePurchaseRequest;
use Omnipay\Ahlpay\Message\PurchaseRequest;
use Omnipay\Ahlpay\Message\RefundRequest;
use Omnipay\Ahlpay\Message\VoidRequest;
use Omnipay\Ahlpay\Traits\PurchaseGettersSetters;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Ahlpay Gateway
 *
 * (c) Tolga Can Gunel
 * 2015, mobius.studio
 * http://www.github.com/tcgunel/omnipay-ahlpay
 *
 * Ahlpay uses a JSON API with token-based authentication.
 * Before each transaction, an authentication token must be obtained.
 *
 * @method \Omnipay\Common\Message\NotificationInterface acceptNotification(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface authorize(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface completeAuthorize(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface createCard(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface updateCard(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface deleteCard(array $options = [])
 */
class Gateway extends AbstractGateway
{
	use PurchaseGettersSetters;

	public function getName(): string
	{
		return 'Ahlpay';
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getDefaultParameters(): array
	{
		return [
			'clientIp'          => '127.0.0.1',
			'merchantId'        => '',
			'merchantUser'      => '',
			'merchantPassword'  => '',
			'merchantStorekey'  => '',
			'installment'       => 1,
			'secure'            => false,
			'token'             => '',
			'tokenType'         => 'Bearer',
			'ahlpayMerchantId'  => 0,
		];
	}

	/**
	 * @param array<string, mixed> $options
	 * @return AbstractRequest|PurchaseRequest
	 */
	public function purchase(array $options = [])
	{
		return $this->createRequest(PurchaseRequest::class, $options);
	}

	/**
	 * @param array<string, mixed> $options
	 * @return AbstractRequest|CompletePurchaseRequest
	 */
	public function completePurchase(array $options = [])
	{
		return $this->createRequest(CompletePurchaseRequest::class, $options);
	}

	/**
	 * @param array<string, mixed> $options
	 * @return AbstractRequest|VoidRequest
	 */
	public function void(array $options = [])
	{
		return $this->createRequest(VoidRequest::class, $options);
	}

	/**
	 * @param array<string, mixed> $options
	 * @return AbstractRequest|RefundRequest
	 */
	public function refund(array $options = [])
	{
		return $this->createRequest(RefundRequest::class, $options);
	}
}
