<?php 
//Get the slide group
$post = $wp_query->post;
$terms = wp_get_post_terms( $post->ID, 'slide-group' );
$slug = $terms[0]->slug;

//Save the actual slideshow 
update_option( "in_slide_actual", $post->ID);
//Show the slideshow
get_header(); 
echo do_shortcode("[slideshow group='$slug']"); 
get_footer(); 
?>
