<?php

/*
Plugin Name: Pledge Tracker
Plugin URI: http://pintopproductions.com/pledge-tracker
Description: This plugin allows you to track pledges and pledge payments. Pledgers are able to login and view their progress
Author: Arelthia Phillips
Author URI:http://pintopproductions.com
Version:2.2


ToDo: 
upload users and initial pledge amount

*/
/*****************
* global variables
*****************/
//setlocale(LC_MONETARY, "en_US");
global $pt_prefix;
//get the plugin settings from options table
$pt_options = get_option('pt_settings');
$pt_prefix = 'pt_';
$pt_plugin_name = 'Pledge Tracker';


/*****************
* constants
*****************/


define('PT_PLUGIN', plugin_dir_url( __FILE__ ) );
/*****************
* includes
*****************/

include('includes/pt-options.php');  //settings page
include('includes/pt-importer.php');  //import page
include('includes/pt-post-types.php');  //custom post types for the plugin
include('includes/pt-user-functions.php');  //custom post types for the plugin
include('includes/pt-display-functions.php');  //custom post types for the plugin

/*
 *enqueue front end scripts
 * 
 */ 
function pt_styles()
{

    wp_register_style( 'ptstyles', plugins_url('css/style.css', __FILE__) );
    wp_enqueue_style( 'ptstyles' );
}

add_action( 'wp_enqueue_scripts', 'pt_styles' );

/*
 * enqueue Jquery Datepicker script
 * 
 */
function pt_datepicker_ui_scripts() {
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-ui-slider');
}

/*
 * enqueue Jquery Datepicker css
 * 
 */
function pt_datepicker_ui_styles() {
	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css', false, '1.8', 'all');
}


// these are for newest versions of WP
add_action('admin_print_scripts-post.php', 'pt_datepicker_ui_scripts');
add_action('admin_print_scripts-edit.php', 'pt_datepicker_ui_scripts');
add_action('admin_print_scripts-post-new.php', 'pt_datepicker_ui_scripts');
add_action('admin_print_styles-post.php', 'pt_datepicker_ui_styles');
add_action('admin_print_styles-edit.php', 'pt_datepicker_ui_styles');
add_action('admin_print_styles-post-new.php', 'pt_datepicker_ui_styles');

/*
 * Add script to trigger the datepicker on the text field
 * 
 */
function pt_ui_scripts() {

	global $post;
	?>
	<script type="text/javascript">
			jQuery(document).ready(function($)
			{
				var dateFormat = 'mm/dd/yy';
				$('.pt-datepicker').datepicker();
			});
	  </script>
	<?php
}

if ((isset($_GET['post']) && (isset($_GET['action']) && $_GET['action'] == 'edit') ) || (strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php')))
{
	add_action('admin_head', 'pt_ui_scripts');
}

/*
 * Prevent Bulk Edit
 * 
 */ 
add_filter( 'bulk_actions-' . 'edit-pledge', '__return_empty_array' );