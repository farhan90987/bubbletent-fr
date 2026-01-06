<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeVisitor;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\AbstractNodeVisitor;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Attribute;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Node;

final class CloningVisitor extends AbstractNodeVisitor
{

	public function enterNode(Node $originalNode): Node
	{
		$node = clone $originalNode;
		$node->setAttribute(Attribute::ORIGINAL_NODE, $originalNode);

		return $node;
	}

}
