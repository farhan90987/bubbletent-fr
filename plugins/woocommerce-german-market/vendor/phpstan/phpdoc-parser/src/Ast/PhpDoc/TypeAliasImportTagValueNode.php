<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use function trim;

class TypeAliasImportTagValueNode implements PhpDocTagValueNode
{

	use NodeAttributes;

	public string $importedAlias;

	public IdentifierTypeNode $importedFrom;

	public ?string $importedAs = null;

	public function __construct(string $importedAlias, IdentifierTypeNode $importedFrom, ?string $importedAs)
	{
		$this->importedAlias = $importedAlias;
		$this->importedFrom = $importedFrom;
		$this->importedAs = $importedAs;
	}

	public function __toString(): string
	{
		return trim(
			"{$this->importedAlias} from {$this->importedFrom}"
			. ($this->importedAs !== null ? " as {$this->importedAs}" : ''),
		);
	}

}
