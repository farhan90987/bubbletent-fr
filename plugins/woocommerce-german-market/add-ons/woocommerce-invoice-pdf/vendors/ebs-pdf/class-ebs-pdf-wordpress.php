<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use MarketPress\German_Market\Dompdf\Dompdf;
use MarketPress\German_Market\Dompdf\Options;

if ( ! class_exists( 'Ebs_Pdf_Wordpress' ) ) {

	require_once( 'abstract-class-ebs-pdf.php' );
	
	/**
	* this class extends Ebs_Pdf to be used in wordpress
	*
	* @class 	Ebs_Pdf_Wordpress
	* @version	1.0
	* @category	Class
	* @requires class DOMPDF (abstract class Ebs_Pdf already included)
	*/ 

	class Ebs_Pdf_Wordpress extends Ebs_Pdf {
		
		public $prefix;
		public $args = array();
		
		/**
		* constructor, creates DOMPDF object, set prefix and user_unit
		*
		* @since 0.0.1
		* @access public
		* @arguments string $prefix
		* @overload
		* @return void
		*/		
		public function __construct( $prefix = NULL, $args = array() ) {

			$this->args = $args;
			$options = new Options();
			$options->set( 'isPhpEnabled', TRUE );

			if ( get_option( 'wp_wc_invoice_pdf_image_remote_scources', 'off' ) == 'on' ) {
				$options->set( 'isRemoteEnabled', true );
			} else {

				if ( ! empty( trim( get_option( 'wp_wc_invoice_pdf_custom_fonts', '' ) ) ) ) {
					$options->set( 'isRemoteEnabled', true );
				}
			}

			$options->set( 'chroot', WP_CONTENT_DIR );

			do_action( 'gm_invoice_pdf_ebs_pdf_set_options', $options );

			$this->pdf 						= new Dompdf( $options );
			$this->prefix = ( $prefix === NULL ) ? 'ebs_pdf_wordpress_' : $prefix;
			$this->paper_size				= get_option( $this->prefix . 'paper_size', 'A4' );
			$this->pdf->set_paper( $this->paper_size, 'portrait' );
			$this->user_unit				= get_option( $this->prefix . 'user_unit', 'cm' );
			do_action( $this->prefix . 'init_pdf' );
		}
		
		/**
		* get option
		*
		* @since 0.0.1
		* @access public
		* @arguments string $option_name, $default
		* @abstract
		* @static
		* @return string
		*/	
		public function get_option( $option_name, $default = '' ) {
			$option_value = get_option( $this->prefix . $option_name, $default );

			$option_value = apply_filters( $this->prefix . 'get_option', $option_value, $option_name, $default );

			if ( 'document_title' === $option_name && has_filter( 'wp_wc_invoice_pdf_template_invoice_content' ) ) {
				$option_value = isset( $this->args[ 'filename' ] ) ? $this->args[ 'filename' ] : $option_value;
			}

			return ( $this->needs_user_unit( $option_name, $option_value ) ) ? $option_value . $this->user_unit : $option_value;
		}
		
		/**
		* get template parts for header
		*
		* @since 0.0.1
		* @access public
		* @static
		* @arguments array $args
		* @return string
		*/
		public function get_template_part_header( $args = array() ) {
			return apply_filters( $this->prefix . 'get_part_header', '', $args );
		}
		
		/**
		* get template parts for footer
		*
		* @since 0.0.1
		* @access public
		* @static
		* @arguments array $args
		* @return string
		*/
		public function get_template_part_footer( $args = array() ) {
			return apply_filters( $this->prefix . 'get_part_footer', '', $args );
		}
		
		/**
		* get template parts for main
		*
		* @since 0.0.1
		* @access public
		* @static
		* @arguments array $args
		* @return string
		*/
		public function get_template_part_main( $args = array() ) {
			return apply_filters( $this->prefix . 'get_part_main', '', $args );
		}
		
		/**
		* get template parts for fonts
		*
		* @since 0.0.1
		* @access public
		* @static
		* @arguments array $args
		* @return string
		*/
		public function get_template_part_fonts( $args = array() ) {
			return apply_filters( $this->prefix . 'get_part_fonts', '', $args );
		}
		
		/**
		* get template parts for background
		*
		* @since 0.0.1
		* @access public
		* @static
		* @arguments array $args
		* @return string
		*/
		public function get_template_part_background( $args = array() ) {
			return apply_filters( $this->prefix . 'get_part_background', '', $args );
		}
		
		/**
		* get tamplate dir (path)
		*
		* @since 0.0.1
		* @access public
		* @arguments string $template_name
		* @static
		* @abstract
		* @arguments array $args
		* @return string
		*/
		public function get_template_dir() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'templates';	
		}

		/**
		* Modify File Template
		*
		* @since 3.37
		* @access public
		* @param String $file_contents
		* @param String $template_name
		* @param Array $args
		* @return string
		*/
		public function modify_template( $file_contents, $template_name, $args ) {
			return apply_filters( $this->prefix . 'modify_template', $file_contents, $template_name, $args );
		}
	}
}
