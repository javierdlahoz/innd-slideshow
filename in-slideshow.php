<?php
/*
Plugin Name: INND Image Slideshow 2
Plugin URI: http://innuevodigital.com
Description: Manage image slideshow in the front page
Version: 1.0
Author: InNuevoDigital
Author URI: http://innuevodigital.com
License: GPLv2
*/

/*  Copyright 2013  InDigital  (email : contact@innuevodigital.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define( 'SLIDESHOW_PLUGIN_URL',    plugin_dir_url( __FILE__ )  );
define( 'SLIDESHOW_PLUGIN_DIR',    plugin_dir_path( __FILE__ )  );
define( 'SLIDESHOW_POST_TYPE',     "in-slideshow"  );
define( 'SLIDESHOW_TAXONOMY_TYPE', "slide-group"  );


use Slideshow\AbstractSingleton;
use Slideshow\Views\SlideshowView;
use Slideshow\Models\SlideshowModel;

require SLIDESHOW_PLUGIN_DIR . "/classes/AbstractSingleton.php";
require SLIDESHOW_PLUGIN_DIR . "/classes/views/SlideshowView.php";
require SLIDESHOW_PLUGIN_DIR . "/classes/models/SlideshowModel.php";


if (!class_exists("SlideshowController")) {

    /**
    * Slideshow Controller class handles all the slideshow workflow
    *
    * @package    inslideshow
    * @version    1.0
    */
    class SlideshowController extends AbstractSingleton{


        /**
        * Reference to the Slideshow View 
        *
        * @access private
        * @var object
        */
        private $view;

        /**
        * Reference to the Slideshow Model 
        *
        * @access private
        * @var object
        */
        private $model;

        /**
        * Slideshow Post type
        *
        */
        const SLIDESHOW_POST_TYPE = "in-slideshow";

        /**
        * Slideshow Taxonomy type
        *
        */
        const SLIDESHOW_TAXONOMY_TYPE = 'slide-group';


        /**
        * Constructor for the class it  register the view and  model class
        * used to implement then MVC pattern
        *
        */
        protected function __construct() {
            $this->view  = SlideshowView::getSingleton();
            $this->model = SlideshowModel::getSingleton();

            //Init the plugin
            add_action('init', array(&$this, 'initPlugin' ));

            // View actions
            add_action('add_meta_boxes', array(&$this->view, 'registerMetabox' ));
            add_shortcode( 'slideshow',  array(&$this->view, 'slideshowShortcode' ) );
            add_filter('template_include', array(&$this->view, 'slideshowTemplate') , 1, 1);
            add_action( 'wp_enqueue_scripts', array(&$this->view, 'includeScripts')  );
            add_filter('single_template', array(&$this->view, 'slideshowDefaultTemplate') );

            // Model actions
            add_action('save_post', array(&$this->model, 'saveSlideshow' ) );
            add_action('restrict_manage_posts', array(&$this->model, 'filterSlideshows' ));
            add_filter('parse_query', array(&$this->model, 'modifyQuery' ));
            // Customize taxonomy forms
            add_filter("admin_footer-edit-tags.php", array(&$this->model, 'slideGroupForm' )); 
            // Modify default columns
            add_filter("manage_edit-". SLIDESHOW_POST_TYPE  . "_columns", array(&$this->model, 'slideColumns' )); 
            // Add new column  to the custom post type
            add_action("manage_posts_custom_column", array(&$this->model, 'slideCustomColumns' ) , 10, 2);
            // Modify default columns
            add_filter("manage_edit-". SLIDESHOW_TAXONOMY_TYPE  . "_columns", array(&$this->model, 'slideGroupColumns' )); 
            // Add new column  to the taxonomy
            add_filter("manage_". SLIDESHOW_TAXONOMY_TYPE ."_custom_column", array(&$this->model, 'slideGroupCustomColumns' ), 10, 3);
        }

        /**
        * Initializa the  plugin registering the post types and  taxonomys
        *
        */
        public function initPlugin(){
            self::registerSlideshowPostType();
            self::registerSlideshowTaxonomy(); 
        }



        /**
        * Register the slideshow post type
        *
        */
        private function registerSlideshowPostType(){
           //register the slide post type
            $labels = array(
                'name' => __( 'Slides', 'in-slideshow-plugin' ),
                'singular_name' => __( 'Slide', 'in-slideshow-plugin' ),
                'add_new' => __( 'Add New', 'in-slideshow-plugin' ),
                'add_new_item' => __( 'Add New Slide', 'in-slideshow-plugin' ),
                'edit_item' => __( 'Edit Slide', 'in-slideshow-plugin' ),
                'new_item' => __( 'New Slide', 'in-slideshow-plugin' ),
                'all_items' => __( 'All Slides', 'in-slideshow-plugin' ),
                'view_item' => __( 'View Slideshow', 'in-slideshow-plugin' ),
                'search_items' => __( 'Search Slides', 'in-slideshow-plugin' ),
                'not_found' =>  __( 'No slides found', 'in-slideshow-plugin' ),
                'not_found_in_trash' => __( 'No slides found in Trash', 'in-slideshow-plugin' ),
                'menu_name' => __( 'Slideshow', 'innd-slideshow-plugin' )
              );
            
              $args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true, 
                'show_in_menu' => true, 
                'query_var' => true,
                'capability_type' => 'post',
                'has_archive' => true, 
                'hierarchical' => false,
                'menu_position' => null,
                'rewrite' => array( 'slug' => 'slide' ),
                'supports' => array( 'title', 'thumbnail' )
              ); 
              
              //Register the slide custom  post type
              register_post_type( self::SLIDESHOW_POST_TYPE, $args ); 
        }

        /**
        * Register the slideshow taxonomy
        *
        */
        private function registerSlideshowTaxonomy(){
            register_taxonomy( self::SLIDESHOW_TAXONOMY_TYPE , 
                               'in-slideshow', array(  'hierarchical'      => true, 
                                               'label'             => 'Slideshow Group', 
                                               'query_var'         => true, 
                                               'rewrite'           => true ,
                                               'show_ui'           => true, // Display the column in the gui
                                               'show_admin_column' => true, // Display the column in the admin in a new column
                                            ) 
            );

           
        }
    }
}

// Get the slideshow controller singleton
$slideshowController = SlideshowController::getSingleton();