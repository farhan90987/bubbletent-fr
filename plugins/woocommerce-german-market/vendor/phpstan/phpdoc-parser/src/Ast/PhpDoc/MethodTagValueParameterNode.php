<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Node;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type\TypeNode;

class MethodTagValueParameterNode implements Node
{

	use NodeAttributes;

	public ?TypeNode $type = null;

	public bool $isReference;

	public bool $isVariadic;

	public string $parameterName;

	public ?ConstExprNode $defaultValue = null;

	public function __construct(?TypeNode $type, bool $isReference, bool $isVariadic, string $parameterName, ?ConstExprNode $defaultValue)
	{
		$this->type = $type;
		$this->isReference = $isReference;
		$this->isVariadic = $isVariadic;
		$this->parameterName = $parameterName;
		$this->defaultValue = $defaultValue;
	}


	public function __toString(): string
	{
		$type = $this->type !== null ? "{$this->type} " : '';
		$isReference = $this->isReference ? '&' : '';
		$isVariadic = $this->isVariadic ? '...' : '';
		$default = $this->defaultValue !== null ? " = {$this->defaultValue}" : '';
		return "{$type}{$isReference}{$isVariadic}{$this->parameterName}{$default}";
	}

}
