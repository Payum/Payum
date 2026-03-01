<?php

namespace Payum\PayTheFly;

/**
 * Constants for PayTheFly gateway.
 */
class Constants
{
    /**
     * BSC (BNB Smart Chain) chain ID.
     */
    public const CHAIN_BSC = 56;

    /**
     * TRON chain ID.
     */
    public const CHAIN_TRON = 728126428;

    /**
     * Native token address for BSC.
     */
    public const NATIVE_TOKEN_BSC = '0x0000000000000000000000000000000000000000';

    /**
     * Native token address for TRON.
     */
    public const NATIVE_TOKEN_TRON = 'T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb';

    /**
     * BSC native token decimals.
     */
    public const DECIMALS_BSC = 18;

    /**
     * TRON native token decimals.
     */
    public const DECIMALS_TRON = 6;

    /**
     * Webhook transaction type: payment.
     */
    public const TX_TYPE_PAYMENT = 1;

    /**
     * Webhook transaction type: withdrawal.
     */
    public const TX_TYPE_WITHDRAWAL = 2;

    /**
     * Payment status: new (not yet submitted).
     */
    public const STATUS_NEW = 'new';

    /**
     * Payment status: pending (user redirected to PayTheFly).
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Payment status: confirmed (webhook confirmed=true).
     */
    public const STATUS_CONFIRMED = 'confirmed';

    /**
     * Payment status: failed.
     */
    public const STATUS_FAILED = 'failed';

    /**
     * EIP-712 domain name.
     */
    public const EIP712_DOMAIN_NAME = 'PayTheFlyPro';

    /**
     * EIP-712 domain version.
     */
    public const EIP712_DOMAIN_VERSION = '1';

    /**
     * Chain symbol mapping.
     */
    public const CHAIN_SYMBOLS = [
        self::CHAIN_BSC => 'BSC',
        self::CHAIN_TRON => 'TRON',
    ];

    private function __construct()
    {
    }
}
