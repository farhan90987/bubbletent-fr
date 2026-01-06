<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;

class IdentifierTypeNode implements TypeNode
{

	use NodeAttributes;

	public string $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}


	public function __toString(): string
	{
		return $this->name;
	}

}
