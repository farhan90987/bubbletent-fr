<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt;

/**
 * Class representing DateTimeType
 *
 * XSD Type: DateTimeType
 */
class DateTimeType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\DateTimeType\DateTimeStringAType $dateTimeString
     */
    private $dateTimeString = null;

    /**
     * Gets as dateTimeString
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\DateTimeType\DateTimeStringAType
     */
    public function getDateTimeString()
    {
        return $this->dateTimeString;
    }

    /**
     * Sets a new dateTimeString
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\DateTimeType\DateTimeStringAType $dateTimeString
     * @return self
     */
    public function setDateTimeString(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\DateTimeType\DateTimeStringAType $dateTimeString = null)
    {
        $this->dateTimeString = $dateTimeString;
        return $this;
    }
}
