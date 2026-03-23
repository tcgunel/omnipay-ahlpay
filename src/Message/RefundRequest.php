<?php

namespace Omnipay\Ahlpay\Message;

use Omnipay\Ahlpay\Constants\TxnType;
use Omnipay\Ahlpay\Helpers\Helper;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

class RefundRequest extends RemoteAbstractRequest
{
	/**
	 * @throws InvalidRequestException
	 * @return array<string, mixed>
	 */
	public function getData(): array
	{
		$this->validateAll();

		$orderId = $this->getOrderNumber() ?? $this->getTransactionId();
		$rnd = 'RND' . $orderId;
		$totalAmount = Helper::formatAmount($this->getAmountInteger());

		$hashString = $this->getMerchantStorekey() . $rnd . $orderId . $totalAmount . $this->getAhlpayMerchantId();
		$hash = Helper::generateHash($hashString);

		return [
			'memberId' => (int) $this->getMerchantId(),
			'merchantId' => (int) $this->getAhlpayMerchantId(),
			'userCode' => $this->getMerchantUser(),
			'txnType' => TxnType::REFUND,
			'orderId' => $orderId,
			'totalAmount' => $totalAmount,
			'currency' => $this->getCurrencyNumeric(),
			'rnd' => $rnd,
			'hash' => $hash,
			'description' => $orderId . ' nolu siparis iadesi',
			'requestIp' => $this->getClientIp() ?? '1.1.1.1',
		];
	}

	/**
	 * @throws InvalidRequestException
	 */
	protected function validateAll(): void
	{
		$this->validateSettings();
		$this->validate('amount', 'currency', 'token', 'ahlpayMerchantId');

		if (!$this->getOrderNumber() && !$this->getTransactionId()) {
			throw new InvalidRequestException('The orderNumber or transactionId parameter is required');
		}
	}

	/**
	 * @param array<string, mixed> $data
	 * @return ResponseInterface|RefundResponse
	 */
	public function sendData($data)
	{
		$responseBody = $this->postJson($data, '/api/Payment/Refund');

		return $this->createResponse($responseBody);
	}

	/**
	 * @param string|array<string, mixed> $data
	 * @return RefundResponse
	 */
	protected function createResponse($data): RefundResponse
	{
		return $this->response = new RefundResponse($this, $data);
	}
}
