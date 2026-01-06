<?php

namespace PixelYourSite\SuperPack;

use function PixelYourSite\isEddActive;
use function PixelYourSite\isWooCommerceActive;

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SpPostTypesCondition extends SpCondition {

	public function register_sub_conditions() {
		$post_types = get_public_post_types();
		// Product form WooCommerce and EDD are handled separately.
		if ( isWooCommerceActive() ) {
			unset( $post_types[ 'product' ] );
		}
		if ( isEddActive() ) {
			unset( $post_types[ 'download' ] );
		}

		foreach ( $post_types as $slug => $post_type ) {
			$option = new SpPostTypeSingleCondition( [
				'post_type' => $slug,
			] );

			$this->options[] = $option;
			SpPixelCondition()->registerOption( $option );
		}
	}

	public function get_label() {
		return 'Post types';
	}

	public function get_name() {
		return 'post_types';
	}

	public function get_all_label() {
		return 'All Post types';
	}

	public function get_controls() {
		$options = array();

		foreach ( $this->options as $option ) {
			$options[] = array(
				'title' => $option->get_label(),
				'item'  => $option->get_name()
			);
		}

		return [
			'type'    => 'select_titled_array',
			'name'    => 'options',
			'options' => $options,

		];
	}

	public function check( $args ) {

        if (is_home() || is_category() || is_archive()) {
            return false;
        }

        $current_post_type = get_post_type();
        // Checking if the 'options' key is in $args and if it is an array
        if (isset($args['options']) && is_array($args['options'])) {
            //We compare the current mail type with the types in 'options'
            foreach ($args['options'] as $option) {
                if ($option === 'post_type_post' && $current_post_type === 'post') {
                    return true;
                }
                if ($option === 'post_type_page' && $current_post_type === 'page') {
                    return true;
                }
            }
        }

        return false;
	}
}