<?php
/**
 * Uninstall PLedge Tracker
 *
 * @package     Pledge Tracker
 * @subpackage  Uninstall 
 * @copyright   Copyright (c) 2013, Arelthia Phillips
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
*/

//if not called from WordPress
if( !defined( 'WP_UNINSTALL_PLUGIN') )
	exit ();

/*
 * pt_remove_family_reps deletes all users with teh role of Family Rep
 *
 */
function pt_remove_family_reps() {
	global $wpdb;
	$args = array( 'role' => 'family_rep' );
	$familyreps = get_users( $args );
	if( !empty($familyreps) ) {
		require_once( ABSPATH.'wp-admin/includes/user.php' );
		$i = 0;
		foreach( $familyreps as $rep ) {
			if( wp_delete_user( $rep->ID ) ) {
				$i++;
			}
		}
		
	}
}


/*
 * pt_delete_pledges deletes all pledges
 *
 */
function pt_delete_pledges(){
 	global $wpdb;
	//if option set to delete all pledge records (posts) delete
	//select all DELETE * FROM wp_posts WHERE post_type = 'pledges'
	$pt_delete_flag = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_type='%s'", 'pledge' ) ); 
		
}

//delete the pledges
pt_delete_pledges();

//delete all users with family rep role
pt_remove_family_reps();

//remove family rep role
remove_role( 'family_rep' );	

//delete options
delete_option( 'pt_settings');










