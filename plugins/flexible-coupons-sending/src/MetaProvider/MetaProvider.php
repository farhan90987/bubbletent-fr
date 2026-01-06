<?php

namespace WPDesk\FCS\MetaProvider;

use WC_Order_Item;
use WPDesk\FCS\MetaProvider\Source\MetaSource;

/**
 * Loads meta from all sources.
 */
class MetaProvider {

	/**
	 * @var MetaSource[]
	 */
	private $meta_sources;


	public function __construct( MetaSource ...$meta_sources ) {
		$this->meta_sources = $meta_sources;
	}

	/**
	 * Gets all meta.
	 *
	 * @return array of meta_name => meta_value pairs.
	 */
	public function get_all_meta( WC_Order_Item $item ): array {
		$all_meta = [];

		foreach ( $this->meta_sources as $source ) {
			$meta     = $source->get_meta( $item );
			$all_meta = \array_merge( $all_meta, $meta );
		}

		return $all_meta;
	}
}
