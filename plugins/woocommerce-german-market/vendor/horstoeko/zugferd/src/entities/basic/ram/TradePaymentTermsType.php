<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram;

/**
 * Class representing TradePaymentTermsType
 *
 * XSD Type: TradePaymentTermsType
 */
class TradePaymentTermsType
{

    /**
     * @var string $description
     */
    private $description = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\DateTimeType $dueDateDateTime
     */
    private $dueDateDateTime = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $directDebitMandateID
     */
    private $directDebitMandateID = null;

    /**
     * Gets as description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets a new description
     *
     * @param  string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Gets as dueDateDateTime
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\DateTimeType
     */
    public function getDueDateDateTime()
    {
        return $this->dueDateDateTime;
    }

    /**
     * Sets a new dueDateDateTime
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\DateTimeType $dueDateDateTime
     * @return self
     */
    public function setDueDateDateTime(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\DateTimeType $dueDateDateTime = null)
    {
        $this->dueDateDateTime = $dueDateDateTime;
        return $this;
    }

    /**
     * Gets as directDebitMandateID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType
     */
    public function getDirectDebitMandateID()
    {
        return $this->directDebitMandateID;
    }

    /**
     * Sets a new directDebitMandateID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $directDebitMandateID
     * @return self
     */
    public function setDirectDebitMandateID(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $directDebitMandateID = null)
    {
        $this->directDebitMandateID = $directDebitMandateID;
        return $this;
    }
}
