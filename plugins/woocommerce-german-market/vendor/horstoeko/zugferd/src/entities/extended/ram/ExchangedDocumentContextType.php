<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing ExchangedDocumentContextType
 *
 * XSD Type: ExchangedDocumentContextType
 */
class ExchangedDocumentContextType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IndicatorType $testIndicator
     */
    private $testIndicator = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\DocumentContextParameterType $businessProcessSpecifiedDocumentContextParameter
     */
    private $businessProcessSpecifiedDocumentContextParameter = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\DocumentContextParameterType $guidelineSpecifiedDocumentContextParameter
     */
    private $guidelineSpecifiedDocumentContextParameter = null;

    /**
     * Gets as testIndicator
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IndicatorType
     */
    public function getTestIndicator()
    {
        return $this->testIndicator;
    }

    /**
     * Sets a new testIndicator
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IndicatorType $testIndicator
     * @return self
     */
    public function setTestIndicator(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IndicatorType $testIndicator = null)
    {
        $this->testIndicator = $testIndicator;
        return $this;
    }

    /**
     * Gets as businessProcessSpecifiedDocumentContextParameter
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\DocumentContextParameterType
     */
    public function getBusinessProcessSpecifiedDocumentContextParameter()
    {
        return $this->businessProcessSpecifiedDocumentContextParameter;
    }

    /**
     * Sets a new businessProcessSpecifiedDocumentContextParameter
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\DocumentContextParameterType $businessProcessSpecifiedDocumentContextParameter
     * @return self
     */
    public function setBusinessProcessSpecifiedDocumentContextParameter(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\DocumentContextParameterType $businessProcessSpecifiedDocumentContextParameter = null)
    {
        $this->businessProcessSpecifiedDocumentContextParameter = $businessProcessSpecifiedDocumentContextParameter;
        return $this;
    }

    /**
     * Gets as guidelineSpecifiedDocumentContextParameter
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\DocumentContextParameterType
     */
    public function getGuidelineSpecifiedDocumentContextParameter()
    {
        return $this->guidelineSpecifiedDocumentContextParameter;
    }

    /**
     * Sets a new guidelineSpecifiedDocumentContextParameter
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\DocumentContextParameterType $guidelineSpecifiedDocumentContextParameter
     * @return self
     */
    public function setGuidelineSpecifiedDocumentContextParameter(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\DocumentContextParameterType $guidelineSpecifiedDocumentContextParameter)
    {
        $this->guidelineSpecifiedDocumentContextParameter = $guidelineSpecifiedDocumentContextParameter;
        return $this;
    }
}
