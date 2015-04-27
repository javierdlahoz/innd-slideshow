<?php
namespace Slideshow\Views;

use Slideshow\AbstractSingleton;
use Slideshow\Models\SlideshowModel;

/**
* Slideshow View class handles all the templates/views for the slideshow
*
* @package    views
* @version    1.0
*/
class SlideshowView extends AbstractSingleton{

	/**
	* Register the slideshow Metabox
	*
	*/
	public function registerMetabox(){
		add_meta_box( 'in-slideshow-meta', 
                      __( 'Slide Information','in-slideshow-plugin' ), 
                      array(&$this, 'showMetaBox' ) , 
                      'in-slideshow', 'normal', 'default' );
	}


	/**
	* Display the slideshow Metabox
	*
	* @param object $post the post to display
	*/
	public function showMetaBox($post){
		// retrieve our custom meta box values
	    $subtitle = get_post_meta( $post->ID, '_slide_subtitle', true );
	    $link     = get_post_meta( $post->ID, '_slide_link', true );
	    $video    = get_post_meta( $post->ID, '_slide_video', true );

	    //Display the view 
	    $name = "slideshow-metabox";
	    $file = SLIDESHOW_PLUGIN_DIR . 'views/'. $name . '.php';
	    include( $file );
	} 


	/**
	* The shortcode to display the slideshow 
	*
	* @param array $atts attributes for the slideshow
	* @param object $content
	*/
	public function slideshowShortcode( $atts, $content = null  ){
		//Get the actual slide
		$actualSlide = get_option( "in_slide_actual", "");
		delete_option("in_slide_actual");

		$hs_show = "";
		$slideshowType = "video";
		$slides = array();
		$dataSource = SlideshowModel::getSingleton()->getShortcodeSlides( $atts, $slides  );

		// Just display if there  is more than one image on the actual slideshow group
		$numSlides = $slides->post_count;
		if( $numSlides > 0 ){
	    
		    // Display slideshow header
		    self::displayHeader();

		    if($dataSource == "default"){
		        // Display default slideshow
		        self::displaySlideshow($slides, $slideshowType, $actualSlide);
		    }
		    else{
		        // Display  custom slideshow
		        self::displayCustomSlideshow($slides, $slideshowType, $titleField, $showFields, $videoField, $actualSlide);
		    }

		    // Display slideshow footer
		    self::displayFooter( $slides->post_count );
		}

		//return the shortcode value to display
	    return $hs_show;
	}

	/**
	* Display the default slideshow
	*
	* @param array $slides The slides to  show
	* @param string $slideshowType the type of the slideshow 
	*/
	function displaySlideshow($slides, $slideshowType, $actualSlide){
	    $active    = "active";
	    $numSlides = $slides->post_count;
	    //Display the actual slide
	    if($actualSlide > 0 ){
	    	$active = "";
	    }
	    //loop the slides
	    while ( $slides->have_posts() ) : 
	        $slides->the_post();
	        $id       = get_the_ID();
	        $title    = get_the_title();
	        $subtitle = get_post_meta( $id, '_slide_subtitle', true); 
	        $link     = get_post_meta( $id, '_slide_link', true); 
	        $video    = get_post_meta( $id, '_slide_video', true); 
	        $video    = self::getEmbedVideoUrl($video);
	        $image    = wp_get_attachment_url( get_post_thumbnail_id( $id )); 

	        //
	        if($actualSlide > 0 && $actualSlide == $id){
	        	$active = "active";
	        }
	        //Display the view 
	        $name = "slideshow-default";
	        $file = SLIDESHOW_PLUGIN_DIR . 'views/'. $name . '.php';
	        include( $file );   

	        $active = "";
	    endwhile;

	   //Display the bullets
	   self::displayBullets($numSlides);

	    //Reset query
	    //wp_reset_query();
	    // Reset Post Data
	    wp_reset_postdata();
	}


	/**
	* Display the custom slideshow (asociated to a custom post type)\
	*
	* @param object $slides the slides object
	* @param string $slideshowType the type of slideshow image|video
	* @param string $titleField custom field to use as the title title|customField
	* @param string $showFields custom fields to show separated by commas
	* @param string $videoField the custom video field
	*/
	function displayCustomSlideshow($slides, $slideshowType, $titleField, $showFields, $videoField, $actualSlide){
	    $active     = "active";
	    $numSlides = $slides->post_count;
	    $showFields = explode(",", $showFields);
	    // loop the posts
	    $posts      = $slides->get_posts();
	    foreach($posts as $post):
	       
	        // Get id, link,  image
	        $id    = $post->ID;
	        $link  = get_permalink($id);
	        $image = wp_get_attachment_url( get_post_thumbnail_id( $id ) );    

	        // Get the title
	        if($titleField == "title"){
	            $title = get_the_title($id);
	        }
	        else{
	            $title = get_post_meta( $id, $titleField, true); 
	        }

	        // Get the video
	        $video = "";
	        if($slideshowType == "video"){
	            $video = get_post_meta( $id, $videoField, true); 
	            $video = getEmbedVideoUrl($video);
	        }

	        // Get the custom fields to show
	        $data = array();
	        foreach($showFields as $item){
	            $item = trim($item);
	            if($item != ""){
	                $data[$item] = get_post_meta( $id, $item, true); 
	            }
	        }
	        
	        //Display the view 
	        $name = "slideshow-custom";
	        $file = SLIDESHOW_PLUGIN_DIR . 'views/'. $name . '.php';
	        include( $file );   

	        $active = "";

	    endforeach;

	    //Display the bullets
	    displayBullets($numSlides);
	   
	    //Reset query
	    //wp_reset_query();
	    // Reset Post Data
	    wp_reset_postdata();
	}


	/**
	* Display iframe for the video
	*
	* @param string $video the url of the video to show
	*/
	function showIframe( $video ){
	    //Display the view 
	    $name = "slideshow-iframe";
	    $file = SLIDESHOW_PLUGIN_DIR . 'views/'. $name . '.php';
	    include( $file ); 
	}


	/**
	* Display the slideshow bullets
	*
	* @param int $numSlides the num of slides of the actual slideshow
	*/
	function displayBullets( $numSlides ){
	    //Display the bullets if there is more than one slide
	     //Display the view 
	    if( $numSlides > 1){
	        $name = "slideshow-bullets";
	        $file = SLIDESHOW_PLUGIN_DIR . 'views/'. $name . '.php';
	        include( $file ); 
	    }
	}

	/**
	* Return the iframe url from a video url
	*
	* @param  string  $videoUrl the url of the video
	* @return string The url to be used in a iframe element src property
	*/
	function getEmbedVideoUrl($videoUrl){
	    // Remove info from videos on youtube
	    $youtubeHide = "?hd=1&rel=0&autohide=1&showinfo=0";
	    if(strpos($videoUrl , "vimeo") !== FALSE){
	        $tmp = explode("/", $videoUrl );
	        $values = array_values($tmp);
	        $src = "//player.vimeo.com/video/" . end( $values );
	        return $src;
	    }
	    elseif(strpos($videoUrl , "youtube") !== FALSE){
	        //https://www.youtube.com/watch?v=rLy-3pqY2YM&feature=youtu.be
	        $tmp = explode("=", $videoUrl );
	        $values = array_values($tmp);
	        $video = $values[1];
	        $tmp = explode("&", $video );
	        $src = "//www.youtube.com/embed/" . $tmp[0] . $youtubeHide;
	        return $src;
	    }
	    elseif(strpos($videoUrl , "youtu.be") !== FALSE){
	        $tmp = explode("youtu.be/", $videoUrl);
	        $videoValue = $tmp[1];
	        $src = "//www.youtube.com/embed/" . $tmp[1] . $youtubeHide;
	        return $src;
	    }
	    return $videoUrl;
	}

	/**
	* Display slideshow header
	*
	*/
	function displayHeader( ){
	    $name = "slideshow-header";
	    $file = SLIDESHOW_PLUGIN_DIR . 'views/'. $name . '.php';
	    include( $file ); 
	}


	/**
	* Display slideshow footer
	*
	* @param int $numSlides the num of slides of the actual slideshow
	*/
	function displayFooter( $numSlides ){
	    if( $numSlides > 1 ) :
	        $name = "slideshow-prev-next";
	        $file = SLIDESHOW_PLUGIN_DIR . 'views/'. $name . '.php';
	        include( $file ); 
	    endif;

	    //Display the slideshow footer
	    $name = "slideshow-footer";
	    $file = SLIDESHOW_PLUGIN_DIR . 'views/'. $name . '.php';
	    include( $file ); 
	}

	
	/**
	* Search for the shortcode in all  the post and pages and return the 
	* url of the content that includes the shortcode
	* 
	* @param string $template the actual template url
	*/
	public function slideshowTemplate($template) {
	    global $wp_query;
	    
	    // If the post type is in-slideshow post type search for the shortcode in all the  pages and post
	    if( get_post_type() === "in-slideshow" ){
	        // Get the link to the shortcode
	        $link = SlideshowModel::getSingleton()->searchShortcode( $wp_query );
	        // Redirect the user to the link
	        if ($link != "") {
	        	//Get the post
	        	$post = $wp_query->posts[0];
				update_option( "in_slide_actual", $post->ID);
	            wp_redirect( $link );
	            exit;
	        }
	    }
	    return $template;
	}

	/**
	* Include the plugin required scripts
	* 
	* @return string $permalink the link of the content or a empty  string if not found
	*/
	public function includeScripts(){
	    // Register the script like this for a plugin:
	    wp_register_script( 'iframe-tracker', SLIDESHOW_PLUGIN_URL . 'js/jquery.iframetracker.js' , array( 'jquery' ) );
	    // For either a plugin or a theme, you can then enqueue the script:
	    wp_enqueue_script( 'iframe-tracker' );
	}

	/**
	* Display the slideshow single template.
	*
	* It is used in case the  shortcode is not used in any post/page 
	*/
	function slideshowDefaultTemplate($single) {
	    global $post;
	    if ($post->post_type == 'in-slideshow') {
	        if( file_exists( SLIDESHOW_PLUGIN_DIR . 'views/single-slide.php'))
	            return SLIDESHOW_PLUGIN_DIR . 'views/single-slide.php';
	    }
	    return $single;
	}



}


