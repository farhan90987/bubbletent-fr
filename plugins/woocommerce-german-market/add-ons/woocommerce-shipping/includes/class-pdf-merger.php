<?php
/**
 *  Class Wgm_Shipping_Pdf_Merger
 */

namespace MarketPress\GermanMarket\Shipping;

use Woocommerce_German_Market;
use MarketPress\German_Market\setasign\Fpdi\Fpdi;
use Exception;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Pdf_Merger {

	private $_files;
	private $_fpdi;

	/**
	 * Singleton.
	 *
	 * @acces public
	 * @static
	 *
	 * @var self
	 */
	public static $instance;

	/**
	 * Singleton getInstance.
	 *
	 * @static
	 *
	 * @return self
	 */
	public static function get_instance() : self {

		return ( null !== self::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * Class Constructor.
	 *
	 * @return void
	 */
	public function __construct() {}

	/**
	 * Add a PDF for inclusion in the merge with a valid file path. Pages should be formatted: 1,3,6, 12-16.
	 *
	 * @param string $filepath
	 * @param string $pages
	 * @param string|null $orientation
	 *
	 * @return self
	 * @throws Exception
	 */
	public function addPDF( string $filepath, string $pages = 'all', ?string $orientation = null ) : self {

		if ( file_exists( $filepath ) ) {
			if ( strtolower( $pages ) != 'all' ) {
				$pages = $this->_rewritepages( $pages );
			}

			$this->_files[] = array( $filepath, $pages, $orientation );
		} else {
			throw new Exception( "Could not locate PDF on '$filepath'" );
		}

		return $this;
	}

	/**
	 * Merges your provided PDFs and outputs to specified location.
	 *
	 * @param string $outputmode
	 * @param string $outputpath
	 * @param string $orientation
	 *
	 * @return string|bool
	 * @throws Exception
	 */
	public function merge( string $outputmode = 'browser', string $outputpath = 'dpd-shipping-label.pdf', string $orientation = 'A' ) {

		if ( ! isset( $this->_files ) || ! is_array( $this->_files ) ) {
			throw new Exception( "No PDFs to merge." );
		}

		$fpdi = new Fpdi();

		// merger operations
		foreach ( $this->_files as $file ) {
			$filename        = $file[ 0 ];
			$filepages       = $file[ 1 ];
			$fileorientation = ( ! is_null( $file[ 2 ] ) ) ? $file[ 2 ] : $orientation;

			$count = $fpdi->setSourceFile( $filename );

			//add the pages
			if ( $filepages == 'all' ) {
				for ( $i = 1; $i <= $count; $i ++ ) {
					$template = $fpdi->importPage( $i );
					$size     = $fpdi->getTemplateSize( $template );
					if ( $fileorientation === 'A' ) {
						$fileorientation = ( $size[ 'width' ] > $size[ 'height' ] ) ? 'L' : 'P';
					}
					$fpdi->AddPage( $fileorientation, array( $size[ 'width' ], $size[ 'height' ] ) );
					$fpdi->useTemplate( $template );
				}
			} else {
				foreach ( $filepages as $page ) {
					if ( ! $template = $fpdi->importPage( $page ) ) {
						throw new Exception( "Could not load page '$page' in PDF '$filename'. Check that the page exists." );
					}
					$size = $fpdi->getTemplateSize( $template );

					$fpdi->AddPage( $fileorientation, array( $size[ 'width' ], $size[ 'height' ] ) );
					$fpdi->useTemplate( $template );
				}
			}
		}

		//output operations
		$mode = $this->_switchmode( $outputmode );

		if ( $mode == 'S' ) {
			return $fpdi->Output( $outputpath, 'S' );
		} else {
			if ( $fpdi->Output( $outputpath, $mode ) == '' ) {
				return true;
			} else {
				throw new Exception( "Error outputting PDF to '$outputmode'." );

				return false;
			}
		}

	}

	/**
	 * FPDI uses single characters for specifying the output location. Change our more descriptive string into proper format.
	 *
	 * @param string $mode
	 *
	 * @return string
	 */
	private function _switchmode( string $mode ) : string {

		switch ( strtolower( $mode ) ) {
			case 'download':
				return 'D';
				break;
			case 'file':
				return 'F';
				break;
			case 'string':
				return 'S';
				break;
			case 'browser':
			default:
				return 'I';
				break;
		}
	}

	/**
	 * Takes our provided pages in the form of 1,3,4,16-50 and creates an array of all pages
	 *
	 * @param string $pages
	 *
	 * @return array
	 * @throws Exception
	 */
	private function _rewritepages( $pages ) {

		$pages = str_replace( ' ', '', $pages );
		$part  = explode( ',', $pages );

		//parse hyphens
		foreach ( $part as $i ) {
			$ind = explode( '-', $i );

			if ( count( $ind ) == 2 ) {
				$x = $ind[ 0 ]; //start page
				$y = $ind[ 1 ]; //end page

				if ( $x > $y ) {
					throw new Exception( "Starting page, '$x' is greater than ending page '$y'." );

					return false;
				}

				//add middle pages
				while ( $x <= $y ) {
					$newpages[] = (int) $x;
					$x ++;
				}
			} else {
				$newpages[] = (int) $ind[ 0 ];
			}
		}

		return $newpages;
	}

}
