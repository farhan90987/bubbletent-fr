<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\qdt;

/**
 * Class representing FormattedDateTimeType
 *
 * XSD Type: FormattedDateTimeType
 */
class FormattedDateTimeType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\qdt\FormattedDateTimeType\DateTimeStringAType $dateTimeString
     */
    private $dateTimeString = null;

    /**
     * Gets as dateTimeString
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\qdt\FormattedDateTimeType\DateTimeStringAType
     */
    public function getDateTimeString()
    {
        return $this->dateTimeString;
    }

    /**
     * Sets a new dateTimeString
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\qdt\FormattedDateTimeType\DateTimeStringAType $dateTimeString
     * @return self
     */
    public function setDateTimeString(\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\qdt\FormattedDateTimeType\DateTimeStringAType $dateTimeString)
    {
        $this->dateTimeString = $dateTimeString;
        return $this;
    }
}
