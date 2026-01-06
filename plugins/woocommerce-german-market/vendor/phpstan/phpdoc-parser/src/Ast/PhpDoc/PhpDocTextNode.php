<?php declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;

class PhpDocTextNode implements PhpDocChildNode
{

	use NodeAttributes;

	public string $text;

	public function __construct(string $text)
	{
		$this->text = $text;
	}


	public function __toString(): string
	{
		return $this->text;
	}

}
