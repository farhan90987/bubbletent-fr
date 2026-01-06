<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\ConstExpr;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ConstExprFalseNode implements ConstExprNode
{

	use NodeAttributes;

	public function __toString(): string
	{
		return 'false';
	}

}
