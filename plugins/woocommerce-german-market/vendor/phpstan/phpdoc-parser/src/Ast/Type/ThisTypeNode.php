<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ThisTypeNode implements TypeNode
{

	use NodeAttributes;

	public function __toString(): string
	{
		return '$this';
	}

}
