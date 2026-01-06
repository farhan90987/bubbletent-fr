<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram;

/**
 * Class representing TradeSettlementHeaderMonetarySummationType
 *
 * XSD Type: TradeSettlementHeaderMonetarySummationType
 */
class TradeSettlementHeaderMonetarySummationType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $taxBasisTotalAmount
     */
    private $taxBasisTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType[] $taxTotalAmount
     */
    private $taxTotalAmount = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $grandTotalAmount
     */
    private $grandTotalAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $duePayableAmount
     */
    private $duePayableAmount = null;

    /**
     * Gets as taxBasisTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType
     */
    public function getTaxBasisTotalAmount()
    {
        return $this->taxBasisTotalAmount;
    }

    /**
     * Sets a new taxBasisTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $taxBasisTotalAmount
     * @return self
     */
    public function setTaxBasisTotalAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $taxBasisTotalAmount)
    {
        $this->taxBasisTotalAmount = $taxBasisTotalAmount;
        return $this;
    }

    /**
     * Adds as taxTotalAmount
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $taxTotalAmount
     */
    public function addToTaxTotalAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $taxTotalAmount)
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
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType[]
     */
    public function getTaxTotalAmount()
    {
        return $this->taxTotalAmount;
    }

    /**
     * Sets a new taxTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType[] $taxTotalAmount
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
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType
     */
    public function getGrandTotalAmount()
    {
        return $this->grandTotalAmount;
    }

    /**
     * Sets a new grandTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $grandTotalAmount
     * @return self
     */
    public function setGrandTotalAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $grandTotalAmount)
    {
        $this->grandTotalAmount = $grandTotalAmount;
        return $this;
    }

    /**
     * Gets as duePayableAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType
     */
    public function getDuePayableAmount()
    {
        return $this->duePayableAmount;
    }

    /**
     * Sets a new duePayableAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $duePayableAmount
     * @return self
     */
    public function setDuePayableAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\AmountType $duePayableAmount)
    {
        $this->duePayableAmount = $duePayableAmount;
        return $this;
    }
}
