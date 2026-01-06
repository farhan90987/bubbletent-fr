<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing ReferencedDocumentType
 *
 * XSD Type: ReferencedDocumentType
 */
class ReferencedDocumentType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $issuerAssignedID
     */
    private $issuerAssignedID = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $uRIID
     */
    private $uRIID = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $lineID
     */
    private $lineID = null;

    /**
     * @var string $typeCode
     */
    private $typeCode = null;

    /**
     * @var string $name
     */
    private $name = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\BinaryObjectType $attachmentBinaryObject
     */
    private $attachmentBinaryObject = null;

    /**
     * @var string $referenceTypeCode
     */
    private $referenceTypeCode = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\qdt\FormattedDateTimeType $formattedIssueDateTime
     */
    private $formattedIssueDateTime = null;

    /**
     * Gets as issuerAssignedID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getIssuerAssignedID()
    {
        return $this->issuerAssignedID;
    }

    /**
     * Sets a new issuerAssignedID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $issuerAssignedID
     * @return self
     */
    public function setIssuerAssignedID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $issuerAssignedID = null)
    {
        $this->issuerAssignedID = $issuerAssignedID;
        return $this;
    }

    /**
     * Gets as uRIID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getURIID()
    {
        return $this->uRIID;
    }

    /**
     * Sets a new uRIID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $uRIID
     * @return self
     */
    public function setURIID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $uRIID = null)
    {
        $this->uRIID = $uRIID;
        return $this;
    }

    /**
     * Gets as lineID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getLineID()
    {
        return $this->lineID;
    }

    /**
     * Sets a new lineID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $lineID
     * @return self
     */
    public function setLineID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $lineID = null)
    {
        $this->lineID = $lineID;
        return $this;
    }

    /**
     * Gets as typeCode
     *
     * @return string
     */
    public function getTypeCode()
    {
        return $this->typeCode;
    }

    /**
     * Sets a new typeCode
     *
     * @param  string $typeCode
     * @return self
     */
    public function setTypeCode($typeCode)
    {
        $this->typeCode = $typeCode;
        return $this;
    }

    /**
     * Gets as name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets a new name
     *
     * @param  string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets as attachmentBinaryObject
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\BinaryObjectType
     */
    public function getAttachmentBinaryObject()
    {
        return $this->attachmentBinaryObject;
    }

    /**
     * Sets a new attachmentBinaryObject
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\BinaryObjectType $attachmentBinaryObject
     * @return self
     */
    public function setAttachmentBinaryObject(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\BinaryObjectType $attachmentBinaryObject = null)
    {
        $this->attachmentBinaryObject = $attachmentBinaryObject;
        return $this;
    }

    /**
     * Gets as referenceTypeCode
     *
     * @return string
     */
    public function getReferenceTypeCode()
    {
        return $this->referenceTypeCode;
    }

    /**
     * Sets a new referenceTypeCode
     *
     * @param  string $referenceTypeCode
     * @return self
     */
    public function setReferenceTypeCode($referenceTypeCode)
    {
        $this->referenceTypeCode = $referenceTypeCode;
        return $this;
    }

    /**
     * Gets as formattedIssueDateTime
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\qdt\FormattedDateTimeType
     */
    public function getFormattedIssueDateTime()
    {
        return $this->formattedIssueDateTime;
    }

    /**
     * Sets a new formattedIssueDateTime
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\qdt\FormattedDateTimeType $formattedIssueDateTime
     * @return self
     */
    public function setFormattedIssueDateTime(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\qdt\FormattedDateTimeType $formattedIssueDateTime = null)
    {
        $this->formattedIssueDateTime = $formattedIssueDateTime;
        return $this;
    }
}
