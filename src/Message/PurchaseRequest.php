<?php

namespace Omnipay\Ahlpay\Message;

use Omnipay\Ahlpay\Constants\TxnType;
use Omnipay\Ahlpay\Helpers\Helper;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

class PurchaseRequest extends RemoteAbstractRequest
{
    /**
     * @throws InvalidRequestException
     * @throws InvalidCreditCardException
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $this->validateAll();

        $totalAmount = Helper::formatAmount($this->getAmountInteger());
        $orderId = $this->getOrderId() ?? $this->getTransactionId();
        $installment = $this->getInstallment() ?: '1';
        $rnd = 'RND' . $orderId;

        $hashString = $this->getMerchantStorekey() . $rnd . $orderId . $totalAmount . $this->getAhlpayMerchantId();
        $hash = Helper::generateHash($hashString);

        $data = [
            'cardNumber' => $this->getCard()->getNumber(),
            'expiryDateMonth' => str_pad((string) $this->getCard()->getExpiryMonth(), 2, '0', STR_PAD_LEFT),
            'expiryDateYear' => (string) $this->getCard()->getExpiryYear(),
            'cvv' => $this->getCard()->getCvv(),
            'cardHolderName' => $this->getCard()->getName(),
            'merchantId' => (int) $this->getAhlpayMerchantId(),
            'totalAmount' => $totalAmount,
            'memberId' => (int) $this->getMerchantId(),
            'userCode' => $this->getMerchantUser(),
            'txnType' => TxnType::AUTH,
            'installmentCount' => (string) $installment,
            'currency' => $this->getCurrencyNumeric(),
            'orderId' => $orderId,
            'webUrl' => '',
            'description' => $orderId . ' nolu siparis odemesi',
            'requestIp' => $this->getClientIp() ?? '127.0.0.1',
            'rnd' => $rnd,
            'hash' => $hash,
        ];

        if ($this->getSecure()) {
            $data['okUrl'] = $this->getReturnUrl();
            $data['failUrl'] = $this->getCancelUrl();
        }

        return $data;
    }

    /**
     * @throws InvalidRequestException
     * @throws InvalidCreditCardException
     */
    protected function validateAll(): void
    {
        $this->validateSettings();
        $this->validate('card', 'amount', 'currency', 'token', 'ahlpayMerchantId');
        $this->getCard()->validate();

        if ($this->getSecure()) {
            $this->validate('returnUrl', 'cancelUrl');
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return ResponseInterface|PurchaseResponse
     */
    public function sendData($data)
    {
        $endpoint = $this->getSecure()
            ? '/api/Payment/Payment3DWithEventRedirect'
            : '/api/Payment/PaymentNon3D';

        $responseBody = $this->postJson($data, $endpoint);

        return $this->createResponse($responseBody);
    }

    /**
     * @param string|array<string, mixed> $data
     * @return PurchaseResponse
     */
    protected function createResponse($data): PurchaseResponse
    {
        return $this->response = new PurchaseResponse($this, $data);
    }
}
