<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ArrayTypeNode implements TypeNode
{

	use NodeAttributes;

	public TypeNode $type;

	public function __construct(TypeNode $type)
	{
		$this->type = $type;
	}


	public function __toString(): string
	{
		if (
			$this->type instanceof CallableTypeNode
			|| $this->type instanceof ConstTypeNode
			|| $this->type instanceof NullableTypeNode
		) {
			return '(' . $this->type . ')[]';
		}

		return $this->type . '[]';
	}

}
