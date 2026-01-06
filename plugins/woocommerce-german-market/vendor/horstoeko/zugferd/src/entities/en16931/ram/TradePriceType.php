<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram;

/**
 * Class representing TradePriceType
 *
 * XSD Type: TradePriceType
 */
class TradePriceType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\AmountType $chargeAmount
     */
    private $chargeAmount = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\QuantityType $basisQuantity
     */
    private $basisQuantity = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAllowanceChargeType $appliedTradeAllowanceCharge
     */
    private $appliedTradeAllowanceCharge = null;

    /**
     * Gets as chargeAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\AmountType
     */
    public function getChargeAmount()
    {
        return $this->chargeAmount;
    }

    /**
     * Sets a new chargeAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\AmountType $chargeAmount
     * @return self
     */
    public function setChargeAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\AmountType $chargeAmount)
    {
        $this->chargeAmount = $chargeAmount;
        return $this;
    }

    /**
     * Gets as basisQuantity
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\QuantityType
     */
    public function getBasisQuantity()
    {
        return $this->basisQuantity;
    }

    /**
     * Sets a new basisQuantity
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\QuantityType $basisQuantity
     * @return self
     */
    public function setBasisQuantity(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\QuantityType $basisQuantity = null)
    {
        $this->basisQuantity = $basisQuantity;
        return $this;
    }

    /**
     * Gets as appliedTradeAllowanceCharge
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAllowanceChargeType
     */
    public function getAppliedTradeAllowanceCharge()
    {
        return $this->appliedTradeAllowanceCharge;
    }

    /**
     * Sets a new appliedTradeAllowanceCharge
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAllowanceChargeType $appliedTradeAllowanceCharge
     * @return self
     */
    public function setAppliedTradeAllowanceCharge(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAllowanceChargeType $appliedTradeAllowanceCharge = null)
    {
        $this->appliedTradeAllowanceCharge = $appliedTradeAllowanceCharge;
        return $this;
    }
}
