<?php
declare( strict_types = 1 );

namespace MarketPress\GermanMarket\Shipping;

use DVDoug\BoxPacker\Item;
use JsonSerializable;
use stdClass;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Package_Item implements Item {

	/**
	 * @acces private
	 *
	 * @var string
	 */
	private string $description;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $width;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $length;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $depth;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $weight;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private $keepFlat;

	/**
	 * Objects that recurse.
	 *
	 * @var stdClass
	 */
	private $a;

	/**
	 * Test objects that recurse.
	 *
	 * @var stdClass
	 */
	private $b;

	/**
	 * TestItem constructor.
	 */
	public function __construct(
		string $description,
		int $width,
		int $length,
		int $depth,
		int $weight,
		bool $keepFlat
	) {
		$this->description = $description;
		$this->width       = $width;
		$this->length      = $length;
		$this->depth       = $depth;
		$this->weight      = $weight;
		$this->keepFlat    = $keepFlat;

		$this->a           = new stdClass();
		$this->b           = new stdClass();

		$this->a->b        = $this->b;
		$this->b->a        = $this->a;
	}

	/**
	 * @acces public
	 *
	 * @return string
	 */
	public function getDescription() : string {

		return $this->description;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getWidth() : int {

		return $this->width;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getLength() : int {

		return $this->length;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getDepth() : int {

		return $this->depth;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getWeight() : int {

		return $this->weight;
	}

	/**
	 * @acces public
	 *
	 * @return bool
	 */
	public function getKeepFlat() : bool {

		return $this->keepFlat;
	}

	/**
	 * @acces public
	 *
	 * @return false|string
	 */
	public function jsonSerialize() {

		return json_encode( array(
			'description' => $this->description,
			'width'       => $this->width,
			'length'      => $this->length,
			'depth'       => $this->depth,
			'weight'      => $this->weight,
			'keepFlat'    => $this->keepFlat,
		) );
	}
}
