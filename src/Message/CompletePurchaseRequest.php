<?php

namespace Omnipay\Ahlpay\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

class CompletePurchaseRequest extends RemoteAbstractRequest
{
	public function getRnd()
	{
		return $this->getParameter('rnd');
	}

	public function setRnd($value)
	{
		return $this->setParameter('rnd', $value);
	}

	/**
	 * Build the payment inquiry request data.
	 *
	 * @throws InvalidRequestException
	 * @return array<string, mixed>
	 */
	public function getData(): array
	{
		$this->validateAll();

		$orderId = $this->getOrderId() ?? $this->getTransactionId();
		$rnd = $this->getRnd() ?? 'RND' . $orderId;

		return [
			'memberId' => $this->getMerchantId(),
			'merchantId' => (int) $this->getAhlpayMerchantId(),
			'hash' => '',
			'rnd' => $rnd,
			'orderId' => $orderId,
		];
	}

	/**
	 * @throws InvalidRequestException
	 */
	protected function validateAll(): void
	{
		$this->validateSettings();
		$this->validate('token', 'ahlpayMerchantId');
	}

	/**
	 * @param array<string, mixed> $data
	 * @return ResponseInterface|CompletePurchaseResponse
	 */
	public function sendData($data)
	{
		$responseBody = $this->postJson($data, '/api/Payment/PaymentInquiry');

		return $this->createResponse($responseBody);
	}

	/**
	 * @param string|array<string, mixed> $data
	 * @return CompletePurchaseResponse
	 */
	protected function createResponse($data): CompletePurchaseResponse
	{
		return $this->response = new CompletePurchaseResponse($this, $data);
	}
}
