<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram;

/**
 * Class representing LineTradeAgreementType
 *
 * XSD Type: LineTradeAgreementType
 */
class LineTradeAgreementType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePriceType $grossPriceProductTradePrice
     */
    private $grossPriceProductTradePrice = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePriceType $netPriceProductTradePrice
     */
    private $netPriceProductTradePrice = null;

    /**
     * Gets as grossPriceProductTradePrice
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePriceType
     */
    public function getGrossPriceProductTradePrice()
    {
        return $this->grossPriceProductTradePrice;
    }

    /**
     * Sets a new grossPriceProductTradePrice
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePriceType $grossPriceProductTradePrice
     * @return self
     */
    public function setGrossPriceProductTradePrice(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePriceType $grossPriceProductTradePrice = null)
    {
        $this->grossPriceProductTradePrice = $grossPriceProductTradePrice;
        return $this;
    }

    /**
     * Gets as netPriceProductTradePrice
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePriceType
     */
    public function getNetPriceProductTradePrice()
    {
        return $this->netPriceProductTradePrice;
    }

    /**
     * Sets a new netPriceProductTradePrice
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePriceType $netPriceProductTradePrice
     * @return self
     */
    public function setNetPriceProductTradePrice(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePriceType $netPriceProductTradePrice)
    {
        $this->netPriceProductTradePrice = $netPriceProductTradePrice;
        return $this;
    }
}
