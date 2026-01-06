<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;

class PropertyTagValueNode implements PhpDocTagValueNode
{

	use NodeAttributes;

	public TypeNode $type;

	public string $propertyName;

	/** @var string (may be empty) */
	public string $description;

	public function __construct(TypeNode $type, string $propertyName, string $description)
	{
		$this->type = $type;
		$this->propertyName = $propertyName;
		$this->description = $description;
	}


	public function __toString(): string
	{
		return trim("{$this->type} {$this->propertyName} {$this->description}");
	}

}
