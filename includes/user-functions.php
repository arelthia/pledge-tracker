<?php
/**
 * User Functions
 *
 * @package     Pledge Tracker
 * @subpackage  User Functions
 * @copyright   Copyright (c) 2013, Arelthi Phillips
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
*/

/*
 * Create Family Rep role
 *
 */

function role_set (){

    global $wp_roles;


//add family member role
    add_role( 'family_rep', 'Family Rep', array(
        'read' => true,
        'level_0' => true,
        ) );
}

add_action('init', 'role_set');


/*
 * Create a pledge when a Family Rep is registered
 *
 */


function pt_pledge_onregister( $user_id )
{
    $userobj = get_user_by( 'id', $user_id );
    $username = $userobj->user_firstname . "&nbsp;" . $userobj->user_lastname;
    /*print_r($userobj);*/
    if ( in_array( 'family_rep', $userobj->roles)){
        $pledge_id = wp_insert_post(
            array(
                'post_type'     => 'pledge',
                'post_title'    => sprintf( '%s&rsquo;s Pledge', $username )
                ), 
            true
            );

        if( is_wp_error( $pledge_id ) ) $pledge_id = 0;

        update_user_meta( $user_id, 'pt_pledge_id', absint( $pledge_id ) );
        update_post_meta($pledge_id, "users", $user_id);
        update_post_meta($pledge_id, "pamt", 0);
    }
}

add_action( 'user_register', 'pt_pledge_onregister' );

/*
 * Custom Registration email message
 *
 */
if ( !function_exists('wp_new_user_notification') ) {
    function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
        global $pt_options; 
        $user = new WP_User($user_id);

        $user_login = stripslashes($user->user_login);
        $user_email = stripslashes($user->user_email);

        $message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

        @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);

        if ( empty($plaintext_pass) )
            return;

        $message  = sprintf(__( '%s'), $pt_options['pt_registration_email'] ). "\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n";
        $message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n\r\n";
        $message .= sprintf(__('If you have any problems, please contact me at %s.'), get_option('admin_email')) . "\r\n\r\n";
        
        wp_mail($user_email, sprintf(__('[%s] Your username and password'), get_option('blogname')), $message);

    }
}

/*
 * Get User Pledge ID
 * returns the $pt_pledge_id
 */
function get_user_pledge_id($user_id){
    $meta_key = 'users';
    $meta_value = $user_id;
    $pt_pledge_obj = $wpdb->get_results( $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '%s' AND meta_value = '%s' ", 
     $meta_key,
     $meta_value
     ) ); 
    //get the post id
    $pt_pledge_id = $pt_pledge_obj[0];
    $pt_pledge_id = $pt_pledge_id->post_id;
    return $pt_pledge_id;
}




/*
 * Remove fields from user profile
 *
 */

function remove_from_profile( $contactmethods) {
    unset($contactmethods['aim']);
    unset($contactmethods['yim']);
    unset($contactmethods['jabber']);
    
    return $contactmethods;
}
add_filter('user_contactmethods', 'remove_from_profile', 10, 2); 


/*
 * Remove profile color options
 *
 */
function admin_del_options() {
 global $_wp_admin_css_colors;
 $_wp_admin_css_colors = 0;
}

add_action('admin_head', 'admin_del_options');


/*
 * Remove personal options
 *
 */
function hide_personal_options(){
    echo "\n" . '<script type="text/javascript">jQuery(document).ready(function($) {
        $(\'form#your-profile > h3\').hide();
        $(\'form#your-profile\').show();
        $(\'form#your-profile label[for=url], form#your-profile input#url\').hide();
        $(\'form#your-profile label[for=description], form#your-profile textarea#description, form#your-profile span.description\').hide();
        $(\'form#createuser label[for=url], form#createuser input#url\').hide();
    });

</script>' . "\n";
}
add_action('admin_head','hide_personal_options');
