<?php

namespace Omnipay\Ahlpay\Tests\Feature;

use Omnipay\Ahlpay\Helpers\Helper;
use Omnipay\Ahlpay\Tests\TestCase;

class HelperTest extends TestCase
{
    public function test_generate_hash()
    {
        $hash = Helper::generateHash('teststringtohash');

        $this->assertNotEmpty($hash);
        $this->assertIsString($hash);

        // Should be uppercase hex
        $this->assertMatchesRegularExpression('/^[A-F0-9]+$/', $hash);

        // SHA512 produces 64 bytes = 128 hex chars
        $this->assertEquals(128, strlen($hash));
    }

    public function test_generate_hash_consistent()
    {
        $hash1 = Helper::generateHash('same-input');
        $hash2 = Helper::generateHash('same-input');

        $this->assertEquals($hash1, $hash2);
    }

    public function test_generate_hash_different_inputs()
    {
        $hash1 = Helper::generateHash('input-one');
        $hash2 = Helper::generateHash('input-two');

        $this->assertNotEquals($hash1, $hash2);
    }

    public function test_generate_hash_matches_manual_calculation()
    {
        $input = 'StoreKey456RNDORDER-00110005001';

        $hash = Helper::generateHash($input);

        // Manual calculation
        $bytes = mb_convert_encoding($input, 'UTF-16LE');
        $expected = strtoupper(bin2hex(hash('sha512', $bytes, true)));

        $this->assertEquals($expected, $hash);
    }

    public function test_format_amount()
    {
        $this->assertEquals('100', Helper::formatAmount(100));
        $this->assertEquals('10000', Helper::formatAmount(10000));
        $this->assertEquals('1234', Helper::formatAmount(1234));
        $this->assertEquals('0', Helper::formatAmount(0));
    }
}
