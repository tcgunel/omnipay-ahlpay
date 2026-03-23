<?php

namespace Omnipay\Ahlpay\Helpers;

class Helper
{
    /**
     * Generate SHA512 hash for Ahlpay.
     *
     * Hash = SHA512(StoreKey + Rnd + OrderId + TotalAmount + MerchantId)
     * Result is hex-encoded uppercase string.
     *
     * @param string $hashString
     * @return string
     */
    public static function generateHash(string $hashString): string
    {
        $bytes = mb_convert_encoding($hashString, 'UTF-16LE');
        $hash = hash('sha512', $bytes, true);

        return strtoupper(bin2hex($hash));
    }

    /**
     * Format amount for Ahlpay: multiply by 100, no dots/commas.
     * e.g. 1.00 TRY = "100", 12.34 TRY = "1234"
     *
     * @param int $amountInteger
     * @return string
     */
    public static function formatAmount(int $amountInteger): string
    {
        return (string) $amountInteger;
    }
}
