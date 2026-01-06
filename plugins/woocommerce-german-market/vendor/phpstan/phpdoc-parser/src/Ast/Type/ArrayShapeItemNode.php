<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Node;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;

class ArrayShapeItemNode implements Node
{

	use NodeAttributes;

	/** @var ConstExprIntegerNode|ConstExprStringNode|IdentifierTypeNode|null */
	public $keyName;

	public bool $optional;

	public TypeNode $valueType;

	/**
	 * @param ConstExprIntegerNode|ConstExprStringNode|IdentifierTypeNode|null $keyName
	 */
	public function __construct($keyName, bool $optional, TypeNode $valueType)
	{
		$this->keyName = $keyName;
		$this->optional = $optional;
		$this->valueType = $valueType;
	}


	public function __toString(): string
	{
		if ($this->keyName !== null) {
			return sprintf(
				'%s%s: %s',
				(string) $this->keyName,
				$this->optional ? '?' : '',
				(string) $this->valueType,
			);
		}

		return (string) $this->valueType;
	}

}
