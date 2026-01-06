<?php
/**
 * FAQ page settings view
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Smoobu Calendar - FAQ', 'smoobu-calendar' ); ?></h1>
	<div class="smoobu-accordion">
		<?php
		if ( ! empty( $content ) ) {
			foreach ( $content as $single ) {
				Smoobu_Main::load_template(
					'admin/loop/faq',
					array(
						'question' => $single['question'],
						'answer'   => $single['answer'],
					)
				);
			}
		}
		?>
	</div>
</div>
