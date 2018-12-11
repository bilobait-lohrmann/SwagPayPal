<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPayPal\PayPal\Api\Common;

use SwagPayPal\PayPal\Api\PayPalStruct;

abstract class Value extends PayPalStruct
{
    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $value;

    protected function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    protected function setValue(string $value): void
    {
        $this->value = $value;
    }
}
