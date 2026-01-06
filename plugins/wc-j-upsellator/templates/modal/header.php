<div class='wc-timeline-container-shop-header flex-row-between'>
        <div class='wc-timeline-container-close-icon'>
            &#10005;
        </div>
        <?php if( woo_j_conf('modal_theme') == 'logo' ): ?>

            <div class='wc-timeline-container-header-image'>
                <img src="<?php echo $logo ?>">
            </div>	

        <?php else: ?>

            <div class='wc-timeline-container-header-text'>
                <?php echo wjc__( woo_j_conf('text_header') ) ?>
            </div>	

        <?php endif; ?>
                
</div>