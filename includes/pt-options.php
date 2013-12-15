<?php
/**
 * Option
 *
 * @package     Pledge Tracker
 * @subpackage  Options
 * @copyright   Copyright (c) 2013, Arelthia Phillips
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 *
 * TODO: Add the option to set the from email address
*/



/*
 * Add submenu page, settings, to the pledge menu
 *
 */
function pt_register_submenu_page() {
	add_submenu_page( 'edit.php?post_type=pledge', 'Pledge Tracker settings', 'Settings', 'manage_options', basename(__FILE__), 'pt_submenu_page' ); 

}
add_action('admin_menu', 'pt_register_submenu_page');

/*
 * Create the settings page
 *
 */
function pt_submenu_page() {
		global $pt_options; 
	//psc_settings enter the info
	//psc_options get the info 


	//start output buffer
	ob_start(); ?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div><h2>Pledge Tracker Options</h2>
		<form method="post" action="options.php">
			<?php settings_fields('pt_settings_group'); /*group name*/?>
			
			<?php /* Archive Page Title*/?>
			
			<p>
				<h3>Log In Page</h3>
				<!--description is a wordpress class  / psc_settings is the name of my settings-->
				<label class="description" for="pt_settings[login_title]">
				Login Page Title
				</label>
				
				<!--Text Box: id and name must match the for-->
				<input id="pt_settings[login_title]" name="pt_settings[login_title]" type="text" value="<?php echo $pt_options['login_title']; ?>" />
			</p>
					
			<p>
			<h3>Progress Page</h3>	
			<label class="description" for="pt_options[pt_custom_html]">
				Content to add to the top of Pledge page 
			</label>	
			<?php /* Ad rich text editor*/ pt_displayeditor(); ?>
			</p>
 			<hr /><h2>Email Settings</h2> 
			<p>
				
				<!--description is a wordpress class  / psc_settings is the name of my settings-->
				<label class="description" for="pt_settings[pt_mail_from_name]">
				<h3>Email From</h3>
				</label>
				
				<!--Text Box: id and name must match the for-->
				<input id="pt_settings[pt_mail_from_name]" name="pt_settings[pt_mail_from_name]" type="text" value="<?php echo $pt_options['pt_mail_from_name']; ?>" />
			</p>
			<p>
				
				<!--description is a wordpress class  / psc_settings is the name of my settings-->
				<label class="description" for="pt_settings[pt_mail_from_address]">
				<h3>Email From Address</h3>
				</label>
				
				<!--Text Box: id and name must match the for-->
				<input id="pt_settings[pt_mail_from_address]" name="pt_settings[pt_mail_from_address]" type="text" value="<?php echo $pt_options['pt_mail_from_address']; ?>" />
			</p>
			<p>
			
			<label class="description" for="pt_settings[pt_registration_email]">
				<h3>Custom Registration Email (Add text to go in the new user registration email)</h3>
			</label>
			<textarea id="pt_settings[pt_registration_email]" name="pt_settings[pt_registration_email]" cols="75" rows="10" placeholdrt="Add your custom email text here" value="<?php echo $pt_options['pt_registration_email']; ?>"><?php echo $pt_options['pt_registration_email']; ?></textarea>
			</p>
			<p class="submit">
			<!--Calss button-primary gives you a blue button -->
				<input type="submit" class="button-primary" value="Save Options" />
			</p>
			
		</form>
	</div>	
	<?php
	//echo everything after the output buffer
	echo ob_get_clean();



}

/*
 * setup rich text editor for custom content above the title
 * TODO: Option for custom content to go in oter locations 
 */
function pt_displayeditor() {
	global $pt_options; 
    $pt_custom_html = get_option( $pt_options['pt_custom_html'] );

    echo wp_editor( $pt_options[pt_custom_html], 'pt_settings[pt_custom_html]', array('textarea_name' => 'pt_settings[pt_custom_html]')  );
}

/*
 * Register options page settings
 * 
 */
function pt_register_settings() {
	//register_setting('settings group',  'name of settings');
	register_setting('pt_settings_group', 'pt_settings');
}
add_action('admin_init', 'pt_register_settings');


/*
 * pt_login_title action to display a custom title on the Login Screen
 */
function pt_display_login_title(){
	global $pt_options; 
	
	echo $pt_options['login_title'];
	
}
add_action('pt_login_title', 'pt_display_login_title');

/*
 * pt_pledge_top action action hook to display content at teh top of the single pledge page 
 */
function pt_display_pledge_top(){
	global $pt_options; 
	
	echo $pt_options['pt_custom_html'];
	
}
add_action('pt_pledge_top', 'pt_display_pledge_top');


/*
 * pt_mail_from changes the email address the registration email is sent from
 */
function pt_mail_from($old) {
	global $pt_options; 
 	if (!empty($pt_options['pt_mail_from_address']))
 		return $pt_options['pt_mail_from_address'];

 	return $old;
}
add_filter('wp_mail_from', 'pt_mail_from');

/*
 * pt_mail_from_name changes the name the registration email is sent from
 * if setting is not set the from name will be the blog name
 */
function pt_mail_from_name($old) {
	global $pt_options; 
	if (isset($pt_options['pt_mail_from_name']))
		return $pt_options['pt_mail_from_name'];

	return get_bloginfo('name');
}
add_filter('wp_mail_from_name', 'pt_mail_from_name');