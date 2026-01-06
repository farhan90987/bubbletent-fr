<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Node;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;

/**
 * @phpstan-type ValueType = DoctrineAnnotation|IdentifierTypeNode|DoctrineArray|ConstExprNode
 */
class DoctrineArgument implements Node
{

	use NodeAttributes;

	public ?IdentifierTypeNode $key = null;

	/** @var ValueType */
	public $value;

	/**
	 * @param ValueType $value
	 */
	public function __construct(?IdentifierTypeNode $key, $value)
	{
		$this->key = $key;
		$this->value = $value;
	}


	public function __toString(): string
	{
		if ($this->key === null) {
			return (string) $this->value;
		}

		return $this->key . '=' . $this->value;
	}

}
