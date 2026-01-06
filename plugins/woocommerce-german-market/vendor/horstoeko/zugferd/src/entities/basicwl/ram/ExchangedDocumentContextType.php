<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram;

/**
 * Class representing ExchangedDocumentContextType
 *
 * XSD Type: ExchangedDocumentContextType
 */
class ExchangedDocumentContextType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\DocumentContextParameterType $businessProcessSpecifiedDocumentContextParameter
     */
    private $businessProcessSpecifiedDocumentContextParameter = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\DocumentContextParameterType $guidelineSpecifiedDocumentContextParameter
     */
    private $guidelineSpecifiedDocumentContextParameter = null;

    /**
     * Gets as businessProcessSpecifiedDocumentContextParameter
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\DocumentContextParameterType
     */
    public function getBusinessProcessSpecifiedDocumentContextParameter()
    {
        return $this->businessProcessSpecifiedDocumentContextParameter;
    }

    /**
     * Sets a new businessProcessSpecifiedDocumentContextParameter
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\DocumentContextParameterType $businessProcessSpecifiedDocumentContextParameter
     * @return self
     */
    public function setBusinessProcessSpecifiedDocumentContextParameter(?\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\DocumentContextParameterType $businessProcessSpecifiedDocumentContextParameter = null)
    {
        $this->businessProcessSpecifiedDocumentContextParameter = $businessProcessSpecifiedDocumentContextParameter;
        return $this;
    }

    /**
     * Gets as guidelineSpecifiedDocumentContextParameter
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\DocumentContextParameterType
     */
    public function getGuidelineSpecifiedDocumentContextParameter()
    {
        return $this->guidelineSpecifiedDocumentContextParameter;
    }

    /**
     * Sets a new guidelineSpecifiedDocumentContextParameter
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\DocumentContextParameterType $guidelineSpecifiedDocumentContextParameter
     * @return self
     */
    public function setGuidelineSpecifiedDocumentContextParameter(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\DocumentContextParameterType $guidelineSpecifiedDocumentContextParameter)
    {
        $this->guidelineSpecifiedDocumentContextParameter = $guidelineSpecifiedDocumentContextParameter;
        return $this;
    }
}
