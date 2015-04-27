----------------------
Installation
----------------------

To install the plugin you have to do the following: 

- Install the plugin on wordpress and activate it. (Copy the plugin files in the plugins folders).
- In the left menu should appear a new item named Slideshow and three child items:
  - All slides: Displays all the slides
  - Add new: Add new slide and link to a slideshow group
  - Slideshow Group: Allow to manage the slideshows groups.

- To display a Slideshow Group in a page/post you can use this shortcode:

[slideshow group="homepage"]

* The group value should match the slug of the slideshow group item you want to show.

The slideshow can display images and videos, if the video is present for a slide the slideshow will
use the video in other case the slideshow will display the featured image.

On each slide there is a button called "View Slideshow", when the slideshow is inserted in the WYSWYG of a page/post the plugin 
redirect to that page/post. In case the shortcode is hardcoded in the template then the application redirect to show only the slideshow in 
a blank page.

----------------------
Configuration
----------------------

In the same plugin folder there is a configuration file called config.xml, in that file you can 
Customize the slideshow according your needs. Anyway if the file is not present the slideshow
Should work in the default mode. 
 
The configuration options are:


- maxSlides:  The max number of slides the slideshow is going to show.
- dataSource: The slideshow data source 
               - default: Default configuration
               - custom : Custom type configuration asociated to a custom post type

All the remaining parameteres are just needed if the dataSource parameter is custom
- postType   : The  post type we want to show  in the slideshow
- title      : The field in the custom post type that we are going to use as the slideshow title
- showFields : The list of fields separated by comma of the fields to show. Ej: subtitle, area, location
- orderField : The field in the custom post type to order by
- orderType  : The  order type: ASC, DESC
- videoField : The field in the custom post type to be used as the video.


