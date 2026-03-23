<?php

namespace Omnipay\Ahlpay\Message;

use Omnipay\Ahlpay\Constants\TxnType;
use Omnipay\Ahlpay\Helpers\Helper;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

class VoidRequest extends RemoteAbstractRequest
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
		$totalAmount = '999999900';

		$hashString = $this->getMerchantStorekey() . $rnd . $orderId . $totalAmount . $this->getAhlpayMerchantId();
		$hash = Helper::generateHash($hashString);

		return [
			'memberId' => (int) $this->getMerchantId(),
			'merchantId' => (int) $this->getAhlpayMerchantId(),
			'userCode' => $this->getMerchantUser(),
			'txnType' => TxnType::VOID,
			'orderId' => $orderId,
			'totalAmount' => $totalAmount,
			'currency' => $this->getCurrencyNumeric(),
			'rnd' => $rnd,
			'hash' => $hash,
			'description' => $orderId . ' nolu siparis iptali',
			'requestIp' => $this->getClientIp() ?? '1.1.1.1',
		];
	}

	/**
	 * @throws InvalidRequestException
	 */
	protected function validateAll(): void
	{
		$this->validateSettings();
		$this->validate('token', 'ahlpayMerchantId', 'currency');

		if (!$this->getOrderNumber() && !$this->getTransactionId()) {
			throw new InvalidRequestException('The orderNumber or transactionId parameter is required');
		}
	}

	/**
	 * @param array<string, mixed> $data
	 * @return ResponseInterface|VoidResponse
	 */
	public function sendData($data)
	{
		$responseBody = $this->postJson($data, '/api/Payment/Void');

		return $this->createResponse($responseBody);
	}

	/**
	 * @param string|array<string, mixed> $data
	 * @return VoidResponse
	 */
	protected function createResponse($data): VoidResponse
	{
		return $this->response = new VoidResponse($this, $data);
	}
}
