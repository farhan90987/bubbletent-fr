<?php
if (!current_user_can('view_site_health_checks')) {
    wp_die(__('Sorry, you are not allowed to access site health information.'), '', 403);
}

wp_enqueue_style('site-health');
wp_enqueue_script('site-health');

?>

<div class="health-check-header">
    <div class="health-check-title-section">
        <h1>
            <?php _e('Listeo Site Health'); ?>
        </h1>
    </div>
</div>
<div class="health-check-body health-check-debug-tab hide-if-no-js">
    <h2>Pages</h2>

    <table class="widefat striped health-check-table listeo-health-check-table-pages">
        <?php
        $pages = listeo_core_get_dashboard_pages_list();
        foreach ($pages as $key => $page) {
        ?>
            <tr>
                <td>
                    <?php echo $page['title']; ?>
                </td>


                <?php if (get_option($page['option']) && ( 'publish' === get_post_status(get_option($page['option']) ) ) ) { ?>
                    <td colspan="2">
                        <span style="color:#20c220;">&#10003;</span> Page exists: <a href="<?php echo get_permalink(get_option($page['option'])); ?>"><?php echo get_the_title(get_option($page['option'])); ?></a>
                    </td>
                <?php } else { ?>
                    <td>
                        <span style="color:red">&#x2717;</span> Page is missing.
                    </td>
                    <td>
                        <a class="button" data-page="<?php echo esc_attr($page['option']); ?>" href=" #">Create</a>
                    </td>
                <?php } ?>


            </tr>
        <?php } ?>
    </table>
    <h2>Database Tables</h2>
    <table class="widefat striped health-check-table">

        <?php
        global $wpdb;
        $listeo_tables_list = array(
            'listeo_core_activity_log' => 'Activity Log',
            'listeo_core_commissions' => 'Commissions',
            'listeo_core_commissions_payouts' => 'Payouts',
            'listeo_core_conversations' => 'Conversations',
            'listeo_core_messages' => 'Messages',
            'listeo_core_conversations' => 'Messages',
            'listeo_core_stats' => 'Statistics',
            'listeo_core_user_packages' => 'User Packages',
            'bookings_calendar' => 'Bookings',
            'bookings_meta' => 'Bookings Meta',
        );

        foreach ($listeo_tables_list as $table => $name) { ?>
            <tr>
                <td><?php echo $name; ?> Table:</td>
                <td>
                    <?php
                    
                    $table_name = $wpdb->prefix . $table;
                    //var_dump($wpdb->get_var("SHOW TABLES LIKE '$table_name'"));
                    // }
                    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) { ?>

                        <span style="color:#20c220;">&#10003;</span> Table exists
                    <?php } else { ?>
                        <span style="color:red">&#x2717;</span> Table does not exists, try to reactivate Listeo Core plugin or <a href="https://themeforest.net/item/listeo-directory-listings-wordpress-theme/23239259/support">contact Support</a>
                    <?php } ?>

                </td>
            </tr>
        <?php } ?>

    </table>
</div>