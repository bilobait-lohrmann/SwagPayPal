<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPayPal\PayPal\Struct\Payment;

use SwagPayPal\PayPal\Struct\Payment\Transactions\Amount;

class SaleRefund
{
    /**
     * @var Amount|null
     */
    private $amount;

    /**
     * @var string
     */
    private $invoiceNumber;

    public function getAmount(): ?Amount
    {
        return $this->amount;
    }

    public function setAmount(?Amount $amount): void
    {
        $this->amount = $amount;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): void
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    public function toArray(): array
    {
        //If the amount object is null, we do not need to add it to the array.
        //Note: A sale/capture will be refunded completely in that case
        return $this->getAmount() === null
            ? ['invoice_number' => $this->getInvoiceNumber()]
            : ['invoice_number' => $this->getInvoiceNumber(), 'amount' => $this->getAmount()->toArray()];
    }
}