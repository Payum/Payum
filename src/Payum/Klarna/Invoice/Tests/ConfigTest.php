<?php
namespace Payum\Klarna\Invoice\Tests;

use Payum\Klarna\Invoice\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @test
     */
    public function shouldAllowGetDefaultPublicProperties()
    {
        $config = new Config();

        $this->assertEquals(\KlarnaCountry::SE, $config->country);
        $this->assertEquals(\KlarnaLanguage::SV, $config->language);
        $this->assertEquals(\KlarnaCurrency::SEK, $config->currency);
        $this->assertEquals(\Klarna::BETA, $config->mode);
        $this->assertEquals(\Klarna::BETA, $config->mode);
        $this->assertEquals('json', $config->pClassStorage);
        $this->assertEquals('./pclasses.json', $config->pClassStoragePath);
    }

    /**
     * @test
     */
    public function shouldAllowSetExpectedPublicProperties()
    {
        $config = new Config();

        $config->country = 'country';
        $config->eid = 'eid';
        $config->secret = 'secret';
        $config->language = 'lang';
        $config->currency = 'currency';
        $config->mode = 'mode';
        $config->pClassStorage = 'storage';
        $config->pClassStoragePath = 'storagePath';

        $this->assertEquals('country', $config->country);
        $this->assertEquals('eid', $config->eid);
        $this->assertEquals('secret', $config->secret);
        $this->assertEquals('lang', $config->language);
        $this->assertEquals('currency', $config->currency);
        $this->assertEquals('mode', $config->mode);
        $this->assertEquals('storage', $config->pClassStorage);
        $this->assertEquals('storagePath', $config->pClassStoragePath);
    }
}
