<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ConstTypeNode implements TypeNode
{

	use NodeAttributes;

	public ConstExprNode $constExpr;

	public function __construct(ConstExprNode $constExpr)
	{
		$this->constExpr = $constExpr;
	}

	public function __toString(): string
	{
		return $this->constExpr->__toString();
	}

}
