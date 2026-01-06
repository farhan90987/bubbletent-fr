<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;

class NullableTypeNode implements TypeNode
{

	use NodeAttributes;

	public TypeNode $type;

	public function __construct(TypeNode $type)
	{
		$this->type = $type;
	}


	public function __toString(): string
	{
		return '?' . $this->type;
	}

}
