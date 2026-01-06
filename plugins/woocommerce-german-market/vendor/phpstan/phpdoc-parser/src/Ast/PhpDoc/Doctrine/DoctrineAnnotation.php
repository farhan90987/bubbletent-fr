<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Node;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;

class DoctrineAnnotation implements Node
{

	use NodeAttributes;

	public string $name;

	/** @var list<DoctrineArgument> */
	public array $arguments;

	/**
	 * @param list<DoctrineArgument> $arguments
	 */
	public function __construct(string $name, array $arguments)
	{
		$this->name = $name;
		$this->arguments = $arguments;
	}

	public function __toString(): string
	{
		$arguments = implode(', ', $this->arguments);
		return $this->name . '(' . $arguments . ')';
	}

}
