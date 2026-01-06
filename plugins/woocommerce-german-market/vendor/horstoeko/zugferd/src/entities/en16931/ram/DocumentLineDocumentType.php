<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram;

/**
 * Class representing DocumentLineDocumentType
 *
 * XSD Type: DocumentLineDocumentType
 */
class DocumentLineDocumentType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $lineID
     */
    private $lineID = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\NoteType $includedNote
     */
    private $includedNote = null;

    /**
     * Gets as lineID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType
     */
    public function getLineID()
    {
        return $this->lineID;
    }

    /**
     * Sets a new lineID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $lineID
     * @return self
     */
    public function setLineID(\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $lineID)
    {
        $this->lineID = $lineID;
        return $this;
    }

    /**
     * Gets as includedNote
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\NoteType
     */
    public function getIncludedNote()
    {
        return $this->includedNote;
    }

    /**
     * Sets a new includedNote
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\NoteType $includedNote
     * @return self
     */
    public function setIncludedNote(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\NoteType $includedNote = null)
    {
        $this->includedNote = $includedNote;
        return $this;
    }
}
