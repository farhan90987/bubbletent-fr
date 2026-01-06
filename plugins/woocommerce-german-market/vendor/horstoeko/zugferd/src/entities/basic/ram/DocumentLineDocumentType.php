<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram;

/**
 * Class representing DocumentLineDocumentType
 *
 * XSD Type: DocumentLineDocumentType
 */
class DocumentLineDocumentType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $lineID
     */
    private $lineID = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\NoteType $includedNote
     */
    private $includedNote = null;

    /**
     * Gets as lineID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType
     */
    public function getLineID()
    {
        return $this->lineID;
    }

    /**
     * Sets a new lineID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $lineID
     * @return self
     */
    public function setLineID(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $lineID)
    {
        $this->lineID = $lineID;
        return $this;
    }

    /**
     * Gets as includedNote
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\NoteType
     */
    public function getIncludedNote()
    {
        return $this->includedNote;
    }

    /**
     * Sets a new includedNote
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\NoteType $includedNote
     * @return self
     */
    public function setIncludedNote(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\NoteType $includedNote = null)
    {
        $this->includedNote = $includedNote;
        return $this;
    }
}
