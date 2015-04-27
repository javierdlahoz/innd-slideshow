<?php

namespace Slideshow\Models;

use Slideshow\AbstractSingleton;

/**
* Slideshow Model class handles all the data operations
*
* @package    views
* @version    1.0
*/
class SlideshowModel extends AbstractSingleton{

	/**
	* Save the slideshow data
	*
	* @param  int  $postId The post to save
	* @return void
	*/
	public function saveSlideshow( $postId ){
		//verify the post type is for Halloween Products and metadata has been posted
		if ( get_post_type( $postId ) == SLIDESHOW_POST_TYPE && isset( $_POST['slide_subtitle'] ) ) {
			
			//if autosave skip saving data
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
				return;

			//check nonce for security
			check_admin_referer( 'meta-box-save', 'in-slideshow-plugin' );

			// save the meta box data as post metadata
			update_post_meta( $postId, '_slide_subtitle', sanitize_text_field( $_POST['slide_subtitle'] ) );
			update_post_meta( $postId, '_slide_link'    , sanitize_text_field( $_POST['slide_link'] ) );
	        update_post_meta( $postId, '_slide_video'   , esc_url( $_POST['slide_video'] ) );
		}
	}


	/**
	* Filter the slideshow list using the custom taxonomy
	*
	* @param  glogal string $typenow The actual post type
	* @param  glogal object $wp_query The actual query
	* @return void
	*/
	public function filterSlideshows(){
		global $typenow;
	    global $wp_query;

	    //Show the filter just in the custom post type in-slideshow 
	    if ($typenow == SLIDESHOW_POST_TYPE ) {
	        //$taxonomy = SLIDESHOW_TAXONOMY_TYPE;
	        $group_taxonomy = get_taxonomy(SLIDESHOW_TAXONOMY_TYPE);
	        wp_dropdown_categories(array(
	            'show_option_all' =>  __("Show All Groups"),
	            'taxonomy'        =>  SLIDESHOW_TAXONOMY_TYPE,
	            'name'            =>  SLIDESHOW_TAXONOMY_TYPE,
	            'orderby'         =>  'name',
	            'selected'        =>  isset($wp_query->query[SLIDESHOW_TAXONOMY_TYPE]) ? $wp_query->query[SLIDESHOW_TAXONOMY_TYPE] : "" ,
	            'hierarchical'    =>  true,
	            'depth'           =>  3,
	            'show_count'      =>  true, // Show # listings in the category
	            'hide_empty'      =>  false, // Hide the empty categories
	        ));
	    }
	}

	/**
	* Modify the actual query
	*
	* @param  glogal string $pagenow The actual page 
	* @param  glogal object $query The actual query
	* @return void
	*/
	public function modifyQuery($query){
		global $pagenow;
	    $qv = &$query->query_vars;
	    if ($pagenow=='edit.php' && isset( $qv[SLIDESHOW_TAXONOMY_TYPE] ) && $qv[SLIDESHOW_TAXONOMY_TYPE] > 0  ) {
	        $term = get_term_by('id', $qv[SLIDESHOW_TAXONOMY_TYPE], SLIDESHOW_TAXONOMY_TYPE);
	        $qv['term'] = $term->slug;
	        $query->set( SLIDESHOW_TAXONOMY_TYPE,  $term->slug );
	    }
	}


	/**
	* Get the slides defined in a group
	*
	* @param array $atts attributes for the slideshow
	* @param object $slides The reference to the slide list
	* @return string the slideshow datasource type
	*/
	public function getShortcodeSlides( $atts, &$slides  ){
		// Wordpress native types
	    $nativeTypes = array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks');

	    // Read the configuration file 
	    // simplexml_load_file( plugins_url( 'config.xml', __FILE__ ) ) 
	    if( simplexml_load_file( SLIDESHOW_PLUGIN_DIR .  'config.xml' ) !== FALSE  ){
	        $xml = simplexml_load_file( SLIDESHOW_PLUGIN_DIR .  'config.xml' ) ;  
	        $maxSlides   = (isset($xml->maxSlides)  && $xml->maxSlides  != "" ) ? (string) $xml->maxSlides : 10;
	        $dataSource  = (isset($xml->dataSource) && $xml->dataSource != "" ) ? (string) $xml->dataSource : "default";

	        //Custom parameters
	        $custom =    $xml->custom;
	        $postType    = (isset($custom->postType)   && $custom->postType   != "" ) ? (string) $custom->postType   : "";
	        $titleField  = (isset($custom->title)      && $custom->title      != "" ) ? (string) $custom->title      : "";
	        $showFields  = (isset($custom->showFields) && $custom->showFields != "" ) ? (string) $custom->showFields : "";
	        $orderField  = (isset($custom->orderField) && $custom->orderField != "" ) ? (string) $custom->orderField : "";
	        $orderType   = (isset($custom->orderType ) && $custom->orderType  != "" ) ? (string) $custom->orderType  : "";
	        $videoField  = (isset($custom->videoField) && $custom->videoField != "" ) ? (string) $custom->videoField  : "";
	    }
	    else{
	        // Use the basic default configuration
	        $maxSlides  = 10;
	        $dataSource = "default";
	    }
	    
	    $slideshowType = "video"; // image - video 

	    // Slideshow types logic
	    if($dataSource == "default"){
	        // Get the  group of the slideshow
	        $group = $atts["group"];
	        // use slishow post
	        $postType = SLIDESHOW_POST_TYPE;
	        $args = array(
	            'posts_per_page' => $maxSlides,
	            'post_type'      => $postType
	        );

	        // If the slideshow belongs to a group make a taxonomy query looking for the term
	        if($group != ""){
	            $args['tax_query'] = array(
	                        array(
	                          'taxonomy'=>SLIDESHOW_TAXONOMY_TYPE,
	                          'field'=>'slug',
	                          'terms'=> $group 
	                           )
	                        );
	        }
	    }
	    else{
	        // use custom post type
	         $args = array(
	            'posts_per_page' => $maxSlides  ,
	            'post_type'      => $postType   ,
	            'order'          => $orderType  ,
	            'orderby'        => $orderField 
	        );

	        // Determine if we need to order by a custom field
	        if( ! in_array( $orderField , $nativeTypes) ) {
	            $args['meta_key'] = $orderField;
	        }
	    }

	    $slides = new \WP_Query( $args );
	    return $dataSource;
	}


	/**
	* Search for the shortcode in all the page and posts
	* 
	* @param object $wp_query The actual query
	* @return string $permalink the link of the content or a empty  string if not found
	*/
	function searchShortcode(  $wp_query )	{

	    //Get the actual term slug
	    $posts  = $wp_query->get_posts();
	    $post = $posts[0];
	    $terms = wp_get_post_terms( $post->ID, SLIDESHOW_TAXONOMY_TYPE );
	    $term = $terms[0]->slug;

	    // Search all the post and pages
	    $args = array( 'posts_per_page' => -1, 'post_type' => array('post', 'page' ) );
	    $posts = get_posts( $args );

	    //$pattern = get_shortcode_regex();
	    $pattern = '\[(\[?)(slideshow)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
	    
	    // Search each post/page looking for the slideshow plugin
	    foreach ($posts as $post){
	        if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
	            && array_key_exists( 2, $matches )
	            && in_array( 'slideshow', $matches[2] ) )
	        {
	            $content = $post->post_content;
	            $search  = 'group="' .$term . '"';
	            $search2 = "group='" .$term . "'";

	            // If there is a match return to the post/page url
	            if( strpos($content, $search)  !== FALSE || 
	                strpos($content, $search2) !== FALSE ){
	                $permalink = get_permalink( $post->ID );
	                return $permalink;
	            }   
	        }    
	    }
	    return "";
	}


	/**
	* Customize the forms for the slideshow taxonomy
	* 
	* @return script Script to modify the forms
	*/
	function slideGroupForm() {
		global $current_screen;
		//print_r($current_screen);
		//exit;
		if( $current_screen->taxonomy  === SLIDESHOW_TAXONOMY_TYPE){
			?>
				<script type="text/javascript">
				    jQuery(document).ready( function($) {
				    	// Hide on list
				    	$('#parent').parent().remove();
				        $('#tag-description').parent().remove();
				        $('#tag-slug').parent().remove();

				        // Hide on edit
				        $('.term-description-wrap').remove();
				        $('.term-parent-wrap').remove();

				        //Delete Rich Text Tags Plugin
				        $('.description').remove();
				        $('#wp-tag_description-wrap').remove();
				        $('#addtag label').last().remove();
				        if ( ! $(".wp-list-table").length > 0){
				        	$('.form-field').last().remove();
				        }
				    });
				</script>
			<?php
		}
	}


	/**
	* Customize the columns in the custom post type  
	* 
	* @return array $columns The columns to display
	*/
	function slideColumns($columns) {
		
	    $newColumns = array(
	        'cb' => '<input type="checkbox" />',
	        'title' => __('Title'),
	        'header_icon' => __('Preview'),
			//'description' => __('Description'),
	        'taxonomy-slide-group' => __('Slideshow Group '),
	        'date' => __('Date')
	        );
	       
	    return $newColumns;	
	}


	/**
	* Add a new custom column to the slideshow custom post type
	* 
	* @return string $out The html for the column
	*/
	function slideCustomColumns($column_name, $id) {
		$out = "";
		if( get_post_type( $id ) == SLIDESHOW_POST_TYPE){
		    switch ($column_name) {
		        case 'header_icon': 
		            // get header image url
		        	$post = get_post($id);
					$slideImage    = wp_get_attachment_url( get_post_thumbnail_id( $post->ID )); 
		        	if($slideImage != ""){
		        		echo "<img src=\"{$slideImage}\" width=\"250\" height=\"83\"/>"; 
		        	}
		            break;
		        default:
		            break;
		    }
		}
    	return $out;  
	}

	/**
	* Customize the columns in the taxonomy list 
	* 
	* @return array $columns The columns to display
	*/
	function slideGroupColumns($columns) {
	    $newColumns = array(
	        'cb' => '<input type="checkbox" />',
	        'name' => __('Name'),
	        'header_icon' => __('Preview'),
			//'description' => __('Description'),
	        'slug' => __('Slug'),
	        'posts' => __('Slides')
	        );
	    return $newColumns;	
	}


	/**
	* Add a new custom column to the slideshow taxonomy
	* 
	* @return string $out The html for the column
	*/
	function slideGroupCustomColumns($out, $column_name, $id) {
		$term = get_term($id, SLIDESHOW_TAXONOMY_TYPE);
	    switch ($column_name) {
	        case 'header_icon': 
	            // get header image url
	        	$slideImage = self::getSlideGroupImage( $term->slug  );

	        	if($slideImage != ""){
	        		$out .= "<img src=\"{$slideImage}\" width=\"250\" height=\"83\"/>"; 
	        	}
	            break;
	 
	        default:
	            break;
	    }
    	return $out;  
	}
	

	/**
	* Get the first image on the slideshow group 
	* 
	* @return string $slideImage The image to display in the taxonomy list
	*/
	function getSlideGroupImage($slug) {
		// Query the slides for slug
	    $args = array(
        	'posts_per_page' => $maxSlides,
        	'post_type'      => SLIDESHOW_POST_TYPE,
        	'tax_query'      => array(
			                        array(
			                          'taxonomy'=>SLIDESHOW_TAXONOMY_TYPE,
			                          'field'=>'slug',
			                          'terms'=> $slug 
			                           )
			                        )
    	);
    	$slides = new \WP_Query( $args );

    	// Search for the image in all the group post
    	$slides = $slides->get_posts();
    	$slideImage = "";
    	if(isset($slides)){
        	foreach ($slides as $slide) {
        		 $image = wp_get_attachment_url( get_post_thumbnail_id( $slide->ID ) );
        		 if($image != "" && $slideImage== ""){
        		 	$slideImage = $image;
        		 }    
        	}
        }
        return $slideImage;
	}

	
}


