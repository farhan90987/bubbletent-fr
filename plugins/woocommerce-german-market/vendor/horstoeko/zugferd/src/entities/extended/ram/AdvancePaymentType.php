<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing AdvancePaymentType
 *
 * XSD Type: AdvancePaymentType
 */
class AdvancePaymentType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $paidAmount
     */
    private $paidAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\qdt\FormattedDateTimeType $formattedReceivedDateTime
     */
    private $formattedReceivedDateTime = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeTaxType[] $includedTradeTax
     */
    private $includedTradeTax = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $invoiceSpecifiedReferencedDocument
     */
    private $invoiceSpecifiedReferencedDocument = null;

    /**
     * Gets as paidAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType
     */
    public function getPaidAmount()
    {
        return $this->paidAmount;
    }

    /**
     * Sets a new paidAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $paidAmount
     * @return self
     */
    public function setPaidAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $paidAmount)
    {
        $this->paidAmount = $paidAmount;
        return $this;
    }

    /**
     * Gets as formattedReceivedDateTime
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\qdt\FormattedDateTimeType
     */
    public function getFormattedReceivedDateTime()
    {
        return $this->formattedReceivedDateTime;
    }

    /**
     * Sets a new formattedReceivedDateTime
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\qdt\FormattedDateTimeType $formattedReceivedDateTime
     * @return self
     */
    public function setFormattedReceivedDateTime(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\qdt\FormattedDateTimeType $formattedReceivedDateTime = null)
    {
        $this->formattedReceivedDateTime = $formattedReceivedDateTime;
        return $this;
    }

    /**
     * Adds as includedTradeTax
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeTaxType $includedTradeTax
     */
    public function addToIncludedTradeTax(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeTaxType $includedTradeTax)
    {
        $this->includedTradeTax[] = $includedTradeTax;
        return $this;
    }

    /**
     * isset includedTradeTax
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetIncludedTradeTax($index)
    {
        return isset($this->includedTradeTax[$index]);
    }

    /**
     * unset includedTradeTax
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetIncludedTradeTax($index)
    {
        unset($this->includedTradeTax[$index]);
    }

    /**
     * Gets as includedTradeTax
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeTaxType[]
     */
    public function getIncludedTradeTax()
    {
        return $this->includedTradeTax;
    }

    /**
     * Sets a new includedTradeTax
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeTaxType[] $includedTradeTax
     * @return self
     */
    public function setIncludedTradeTax(array $includedTradeTax)
    {
        $this->includedTradeTax = $includedTradeTax;
        return $this;
    }

    /**
     * Gets as invoiceSpecifiedReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType
     */
    public function getInvoiceSpecifiedReferencedDocument()
    {
        return $this->invoiceSpecifiedReferencedDocument;
    }

    /**
     * Sets a new invoiceSpecifiedReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $invoiceSpecifiedReferencedDocument
     * @return self
     */
    public function setInvoiceSpecifiedReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $invoiceSpecifiedReferencedDocument = null)
    {
        $this->invoiceSpecifiedReferencedDocument = $invoiceSpecifiedReferencedDocument;
        return $this;
    }
}
