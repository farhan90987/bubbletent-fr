<?php
declare( strict_types = 1 );

namespace MarketPress\GermanMarket\Shipping;

use DVDoug\BoxPacker\Box;
use JsonSerializable;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Package_Box implements Box {

	/**
	 * @acces private
	 *
	 * @var string
	 */
	private string $reference;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $outerWidth;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $outerLength;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $outerDepth;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $emptyWeight;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $innerWidth;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $innerLength;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $innerDepth;

	/**
	 * @acces private
	 *
	 * @var int
	 */
	private int $maxWeight;

	/**
	 * Class constructor.
	 */
	public function __construct(
		string $reference,
		int $outerWidth,
		int $outerLength,
		int $outerDepth,
		int $emptyWeight,
		int $innerWidth,
		int $innerLength,
		int $innerDepth,
		int $maxWeight
	) {
		$this->reference   = $reference;
		$this->outerWidth  = $outerWidth;
		$this->outerLength = $outerLength;
		$this->outerDepth  = $outerDepth;
		$this->emptyWeight = $emptyWeight;
		$this->innerWidth  = $innerWidth;
		$this->innerLength = $innerLength;
		$this->innerDepth  = $innerDepth;
		$this->maxWeight   = $maxWeight;
	}

	/**
	 * @acces public
	 *
	 * @return string
	 */
	public function getReference() : string {

		return $this->reference;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getOuterWidth() : int {

		return $this->outerWidth;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getOuterLength() : int {

		return $this->outerLength;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getOuterDepth() : int {

		return $this->outerDepth;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getEmptyWeight() : int {

		return $this->emptyWeight;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getInnerWidth() : int {

		return $this->innerWidth;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getInnerLength() : int {

		return $this->innerLength;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getInnerDepth() : int {

		return $this->innerDepth;
	}

	/**
	 * @acces public
	 *
	 * @return int
	 */
	public function getMaxWeight() : int {

		return $this->maxWeight;
	}

	/**
	 * @acces public
	 *
	 * @return false|string
	 */
	public function jsonSerialize() {

		return json_encode( array(
			'reference'   => $this->reference,
			'innerWidth'  => $this->innerWidth,
			'innerLength' => $this->innerLength,
			'innerDepth'  => $this->innerDepth,
			'emptyWeight' => $this->emptyWeight,
			'maxWeight'   => $this->maxWeight,
		) );
	}

}
