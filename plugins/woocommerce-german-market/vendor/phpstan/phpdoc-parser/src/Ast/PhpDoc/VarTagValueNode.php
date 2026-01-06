<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;

class VarTagValueNode implements PhpDocTagValueNode
{

	use NodeAttributes;

	public TypeNode $type;

	/** @var string (may be empty) */
	public string $variableName;

	/** @var string (may be empty) */
	public string $description;

	public function __construct(TypeNode $type, string $variableName, string $description)
	{
		$this->type = $type;
		$this->variableName = $variableName;
		$this->description = $description;
	}


	public function __toString(): string
	{
		return trim("$this->type " . trim("{$this->variableName} {$this->description}"));
	}

}
