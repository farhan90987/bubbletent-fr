<?php

namespace YayMail\Integrations\Translations;

defined( 'ABSPATH' ) || exit;

abstract class BaseIntegration {
	public static function initialize() {
		static::before_initialize();
	}

	abstract public static function before_initialize();

	abstract public static function get_integration_plugin();

	abstract public static function get_available_languages();

	abstract public static function get_site_language( $order );

	public static function get_dashboard_active_language() {
		$languages       = static::get_available_languages();
		$cookie_language = ! empty( $_COOKIE['yaymail_dashboard_language'] ) ? sanitize_text_field( $_COOKIE['yaymail_dashboard_language'] ) : 'en';

		if ( empty( $languages ) ) {
			self::change_dashboard_language( 'en' );
			return 'en';
		}

		$language_codes = array_column( $languages, null, 'code' );

		if ( empty( $language_codes[ $cookie_language ] ) ) {
			self::change_dashboard_language( $languages[0]['code'] );
			return $languages[0]['code'];
		}

		return $language_codes[ $cookie_language ]['code'];
	}

	public static function change_dashboard_language( $language_code ) {
		setcookie( 'yaymail_dashboard_language', $language_code, time() + 86400, defined( 'COOKIEPATH' ) ? COOKIEPATH : '/' );
	}

	public static function get_language_meta_query( $order = null ) {
		global $pagenow;
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
		if ( is_admin() && ( ( isset( $_GET['page'] ) && 'yaymail-settings' === $_GET['page'] ) || wp_verify_nonce( $nonce, 'email-nonce' ) ) ) {
			$language = self::get_dashboard_active_language();
		} else {
			$language = static::get_site_language( $order );
		}
		self::update_direction_option( $language );
		if ( 'en' != $language ) {
			$language_query = array(
				'key'     => '_yaymail_template_language',
				'value'   => $language,
				'compare' => '=',
			);
		} else {
			$language_query = array(
				'key'     => '_yaymail_template_language',
				'compare' => 'NOT EXISTS',
				'value'   => '',
			);
		}
		return $language_query;
	}

	public static function get_template_id( $args, $order = null ) {
		$query_args = array(
			'post_type'        => 'yaymail_template',
			'post_status'      => array( 'publish', 'pending', 'future' ),
			'meta_query'       => array(
				'relation' => 'AND',
				array(
					'key'     => '_yaymail_template',
					'value'   => $args['email_template'],
					'compare' => '=',
				),
				self::get_language_meta_query( $order ),
			),
			'suppress_filters' => true,
		);
		$posts      = new \WP_Query( $query_args );
		if ( $posts->have_posts() ) {
			return $posts->post->ID;
		}
		return false;
	}

	public static function update_language_meta( $post_id ) {
		$language = self::get_dashboard_active_language();
		if ( 'en' != $language ) {
			update_post_meta( $post_id, '_yaymail_template_language', $language );
		}
	}

	public static function update_direction_option( $language ) {
		$direction = in_array( $language, array( 'ar', 'iw', 'fa', 'he' ) ) ? 'rtl' : 'ltr';
		update_option( 'yaymail_direction', $direction );
	}

	public static function get_list_template( $posts, $get_post_id ) {
		$template_export = array();
		if ( count( $posts ) > 0 ) {
			$list_use_temp      = array();
			$dashboard_language = self::get_dashboard_active_language();
			foreach ( $posts as $key => $post ) {
				$template          = get_post_meta( $post->ID, '_yaymail_template', true );
				$template_language = get_post_meta( $post->ID, '_yaymail_template_language', true );
				if ( isset( $list_use_temp[ $template ][ $template_language ] )
					&& isset( $list_use_temp[ $template ][ $template_language ]['prev_id'] ) ) {
					wp_delete_post( $post->ID );
				} else {
					$list_use_temp[ $template ][ $template_language ]['prev_id'] = $post->ID;
					$language_meta = get_post_meta( $post->ID, '_yaymail_template_language', true );
					if ( $dashboard_language === $language_meta || ( 'en' === $dashboard_language && '' === $language_meta ) ) {
						$template        = get_post_meta( $post->ID, '_yaymail_template', true );
						$template_status = get_post_meta( $post->ID, '_yaymail_status', true );
						if ( $get_post_id ) {
							$template_export[ $template ]['post_id']         = $post->ID;
							$template_export[ $template ]['_yaymail_status'] = $template_status;
						} else {
							$template_export[ $template ] = $template_status;
						}
					}
				}
			}
		}
		return $template_export;
	}

}
