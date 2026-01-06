<?php
//Get array of terms
$template_loader = new Listeo_Core_Template_Loader;
$taxonomy = get_option('listeo_single_related_taxonomy');
$terms = get_the_terms($post->ID, $taxonomy, 'string');
if ($terms) :
  //Pluck out the IDs to get an array of IDS
  $term_ids = wp_list_pluck($terms, 'term_id');
  
  //Query posts with tax_query. Choose in 'IN' if want to query posts with any of the terms
  //Chose 'AND' if you want to query for posts with all terms
  $args = array(
    'post_type' => 'listing',
    'tax_query' => array(
      array(
        'taxonomy' => $taxonomy,
        'field' => 'id',
        'terms' => $term_ids,
        'operator' => 'IN' //Or 'AND' or 'NOT IN'
      )
    ),
    'posts_per_page' => 5,
    'ignore_sticky_posts' => 1,
    'orderby' => 'rand',
    'post__not_in' => array($post->ID)
  );
  
if(get_option('listeo_single_related_current_author')){
  global $post;
  $args['author'] = $post->post_author;
}
  
  $second_query = new WP_Query($args);
  //Loop through posts and display...
  if ($second_query->have_posts()) { ?>
    <h3 class="desc-headline no-border margin-bottom-35 margin-top-60 print-no"><?php esc_html_e('Similar Listings', 'listeo_core'); ?></h3>
    <div class="simple-slick-carousel "  data-slick='{"autoplay": true,"slidesToShow": 2}'>

      <?php
      while ($second_query->have_posts()) : $second_query->the_post();
      ?> <div class="fw-carousel-item">
          <?php
          $style= get_option('listeo_similar_grid_style','compact');
          $template_loader->get_template_part('content-listing-'.$style); ?>
        </div>
      <?php
      endwhile;
      wp_reset_postdata();
      wp_reset_query();
      ?>
    </div>
<?php }
endif;
?>