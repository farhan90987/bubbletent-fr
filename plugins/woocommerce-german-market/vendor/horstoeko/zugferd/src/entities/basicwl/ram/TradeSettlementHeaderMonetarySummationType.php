<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram;

/**
 * Class representing TradeSettlementHeaderMonetarySummationType
 *
 * XSD Type: TradeSettlementHeaderMonetarySummationType
 */
class TradeSettlementHeaderMonetarySummationType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $lineTotalAmount
     */
    private $lineTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $chargeTotalAmount
     */
    private $chargeTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $allowanceTotalAmount
     */
    private $allowanceTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $taxBasisTotalAmount
     */
    private $taxBasisTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType[] $taxTotalAmount
     */
    private $taxTotalAmount = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $grandTotalAmount
     */
    private $grandTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $totalPrepaidAmount
     */
    private $totalPrepaidAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $duePayableAmount
     */
    private $duePayableAmount = null;

    /**
     * Gets as lineTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType
     */
    public function getLineTotalAmount()
    {
        return $this->lineTotalAmount;
    }

    /**
     * Sets a new lineTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $lineTotalAmount
     * @return self
     */
    public function setLineTotalAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $lineTotalAmount)
    {
        $this->lineTotalAmount = $lineTotalAmount;
        return $this;
    }

    /**
     * Gets as chargeTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType
     */
    public function getChargeTotalAmount()
    {
        return $this->chargeTotalAmount;
    }

    /**
     * Sets a new chargeTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $chargeTotalAmount
     * @return self
     */
    public function setChargeTotalAmount(?\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $chargeTotalAmount = null)
    {
        $this->chargeTotalAmount = $chargeTotalAmount;
        return $this;
    }

    /**
     * Gets as allowanceTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType
     */
    public function getAllowanceTotalAmount()
    {
        return $this->allowanceTotalAmount;
    }

    /**
     * Sets a new allowanceTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $allowanceTotalAmount
     * @return self
     */
    public function setAllowanceTotalAmount(?\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $allowanceTotalAmount = null)
    {
        $this->allowanceTotalAmount = $allowanceTotalAmount;
        return $this;
    }

    /**
     * Gets as taxBasisTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType
     */
    public function getTaxBasisTotalAmount()
    {
        return $this->taxBasisTotalAmount;
    }

    /**
     * Sets a new taxBasisTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $taxBasisTotalAmount
     * @return self
     */
    public function setTaxBasisTotalAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $taxBasisTotalAmount)
    {
        $this->taxBasisTotalAmount = $taxBasisTotalAmount;
        return $this;
    }

    /**
     * Adds as taxTotalAmount
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $taxTotalAmount
     */
    public function addToTaxTotalAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $taxTotalAmount)
    {
        $this->taxTotalAmount[] = $taxTotalAmount;
        return $this;
    }

    /**
     * isset taxTotalAmount
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetTaxTotalAmount($index)
    {
        return isset($this->taxTotalAmount[$index]);
    }

    /**
     * unset taxTotalAmount
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetTaxTotalAmount($index)
    {
        unset($this->taxTotalAmount[$index]);
    }

    /**
     * Gets as taxTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType[]
     */
    public function getTaxTotalAmount()
    {
        return $this->taxTotalAmount;
    }

    /**
     * Sets a new taxTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType[] $taxTotalAmount
     * @return self
     */
    public function setTaxTotalAmount(?array $taxTotalAmount = null)
    {
        $this->taxTotalAmount = $taxTotalAmount;
        return $this;
    }

    /**
     * Gets as grandTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType
     */
    public function getGrandTotalAmount()
    {
        return $this->grandTotalAmount;
    }

    /**
     * Sets a new grandTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $grandTotalAmount
     * @return self
     */
    public function setGrandTotalAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $grandTotalAmount)
    {
        $this->grandTotalAmount = $grandTotalAmount;
        return $this;
    }

    /**
     * Gets as totalPrepaidAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType
     */
    public function getTotalPrepaidAmount()
    {
        return $this->totalPrepaidAmount;
    }

    /**
     * Sets a new totalPrepaidAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $totalPrepaidAmount
     * @return self
     */
    public function setTotalPrepaidAmount(?\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $totalPrepaidAmount = null)
    {
        $this->totalPrepaidAmount = $totalPrepaidAmount;
        return $this;
    }

    /**
     * Gets as duePayableAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType
     */
    public function getDuePayableAmount()
    {
        return $this->duePayableAmount;
    }

    /**
     * Sets a new duePayableAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $duePayableAmount
     * @return self
     */
    public function setDuePayableAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\AmountType $duePayableAmount)
    {
        $this->duePayableAmount = $duePayableAmount;
        return $this;
    }
}
