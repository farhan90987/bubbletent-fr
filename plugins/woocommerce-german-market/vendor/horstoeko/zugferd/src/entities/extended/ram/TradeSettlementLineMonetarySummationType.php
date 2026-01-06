<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing TradeSettlementLineMonetarySummationType
 *
 * XSD Type: TradeSettlementLineMonetarySummationType
 */
class TradeSettlementLineMonetarySummationType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $lineTotalAmount
     */
    private $lineTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $chargeTotalAmount
     */
    private $chargeTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $allowanceTotalAmount
     */
    private $allowanceTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $taxTotalAmount
     */
    private $taxTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $grandTotalAmount
     */
    private $grandTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $totalAllowanceChargeAmount
     */
    private $totalAllowanceChargeAmount = null;

    /**
     * Gets as lineTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType
     */
    public function getLineTotalAmount()
    {
        return $this->lineTotalAmount;
    }

    /**
     * Sets a new lineTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $lineTotalAmount
     * @return self
     */
    public function setLineTotalAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $lineTotalAmount)
    {
        $this->lineTotalAmount = $lineTotalAmount;
        return $this;
    }

    /**
     * Gets as chargeTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType
     */
    public function getChargeTotalAmount()
    {
        return $this->chargeTotalAmount;
    }

    /**
     * Sets a new chargeTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $chargeTotalAmount
     * @return self
     */
    public function setChargeTotalAmount(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $chargeTotalAmount = null)
    {
        $this->chargeTotalAmount = $chargeTotalAmount;
        return $this;
    }

    /**
     * Gets as allowanceTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType
     */
    public function getAllowanceTotalAmount()
    {
        return $this->allowanceTotalAmount;
    }

    /**
     * Sets a new allowanceTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $allowanceTotalAmount
     * @return self
     */
    public function setAllowanceTotalAmount(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $allowanceTotalAmount = null)
    {
        $this->allowanceTotalAmount = $allowanceTotalAmount;
        return $this;
    }

    /**
     * Gets as taxTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType
     */
    public function getTaxTotalAmount()
    {
        return $this->taxTotalAmount;
    }

    /**
     * Sets a new taxTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $taxTotalAmount
     * @return self
     */
    public function setTaxTotalAmount(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $taxTotalAmount = null)
    {
        $this->taxTotalAmount = $taxTotalAmount;
        return $this;
    }

    /**
     * Gets as grandTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType
     */
    public function getGrandTotalAmount()
    {
        return $this->grandTotalAmount;
    }

    /**
     * Sets a new grandTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $grandTotalAmount
     * @return self
     */
    public function setGrandTotalAmount(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $grandTotalAmount = null)
    {
        $this->grandTotalAmount = $grandTotalAmount;
        return $this;
    }

    /**
     * Gets as totalAllowanceChargeAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType
     */
    public function getTotalAllowanceChargeAmount()
    {
        return $this->totalAllowanceChargeAmount;
    }

    /**
     * Sets a new totalAllowanceChargeAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $totalAllowanceChargeAmount
     * @return self
     */
    public function setTotalAllowanceChargeAmount(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\AmountType $totalAllowanceChargeAmount = null)
    {
        $this->totalAllowanceChargeAmount = $totalAllowanceChargeAmount;
        return $this;
    }
}
