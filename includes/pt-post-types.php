<?php
/**
 * POst Types
 *
 * @package     Pledge Tracker
 * @subpackage  Post Types
 * @copyright   Copyright (c) 2013, Arelthia Phillips
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
*/

/*
 *registration code for pledge post type
 * 
 */ 
function pt_register_pledge_posttype() {
    $labels = array(
        'name'              => _x( 'Pledges', 'post type general name' ),
        'singular_name'     => _x( 'Pledge', 'post type singular name' ),
        'add_new'           => __( 'Add New' ),
        'add_new_item'      => __( 'Add New Pledge' ),
        'edit_item'         => __( 'Edit Pledge' ),
        'new_item'          => __( 'New Pledge' ),
        'view_item'         => __( 'View Pledge' ),
        'search_items'      => __( 'Search Pledge' ),
        'not_found'         => __( 'No pledge found' ),
        'not_found_in_trash'=> __( 'No pledge found in trash' ),
        'parent_item_colon' => __( 'Pledge Parent' ),
        'menu_name'         => __( 'Pledges' )
        );
    
    $taxonomies = array();

    $supports = array('title');
    
    $post_type_args = array(
        'labels'            => $labels,
        'singular_label'    => __('Pledge'),
        'public'            => true,
        'show_ui'           => true,
        'publicly_queryable'=> true,
        'query_var'         => true,
        'capability_type'   => 'post',
        'map_meta_cap'    => true,
        'has_archive'       => true,
        'hierarchical'      => false,
            'exclude_from_search'   => true, // cant be brought up via a search
            'show_in_nav_menus' => false, // not in nav menus (we'll do this manually)
            'rewrite'           => array('slug' => 'pledge', 'with_front' => false ),
            'supports'          => $supports,
            'menu_position'     => 5,
            'menu_icon'         => PT_PLUGIN . '/images/chart-up-color.png',
            'taxonomies'        => $taxonomies
            );
    register_post_type('pledge',$post_type_args);
}
add_action('init', 'pt_register_pledge_posttype');

/*
 * display payment fields
 *
 */
function pt_print_payment_fields($cnt, $pp_payments = null) {
if ($pp_payments === null){
    $a = $b = $c = '';
}else{
    $a = $pp_payments['date'];
    
    $c = $pp_payments['amount'];
}
return  <<<HTML
<li><label>Date:</label><input type="text" class="pt-datepicker" id="payment_data[$cnt][date]" name="payment_data[$cnt][date]" size="8" value="$a"/><label>Payment :</label><input type="number" name="payment_data[$cnt][amount]" size="8"  step="0.01" value="$c" title="Payment - no dollar sign and no comma(s) - cents (.##) are optional" /><span class="remove">Remove</span></li>
HTML;
} 

//add custom field - payment
add_action("add_meta_boxes", "pt_object_init");

function pt_object_init(){
  add_meta_box("payment_meta_id", "Pledge Payments :","pt_payment_meta", "pledge", "advanced", "high");

}

/*
 * show all payments in backend and allow to add new payments
 *
 */
function pt_payment_meta(){
   global $post;

   $data = get_post_meta($post->ID,"payment_data",true);
   echo '<div>';
   echo '<ul id="payment_items">';
   $c = 0;
   if (count($data) > 0){
    foreach((array)$data as $p ){
        if (isset($p['amount']) || isset($p['date'])){
            echo pt_print_payment_fields($c,$p);
            $c = $c +1;
        }
    }

}
echo '</ul>';

?>
<span id="here"></span>
<span class="add"><?php echo __('Add Payment'); ?></span>
<script>
var $ =jQuery.noConflict();
$(document).ready(function() {
    var count = <?php echo $c; ?>;
    $(".add").click(function() {
        count = count + 1;
        $('#payment_items').append('<? echo implode('',explode("\n",pt_print_payment_fields('count'))); ?>'.replace(/count/g, count));
        $('.pt-datepicker').datepicker();
        return false;
    });
    $(".remove").live('click', function() {
        $(this).parent().remove();
    });
});
</script>
<style>#payment_items {list-style: none;}</style>
<?php
echo '</div>';
}


//Save payment
add_action('save_post', 'pt_save_details');

function pt_save_details($post_id){ 
    global $post;


    // to prevent metadata or custom fields from disappearing... 
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
        return $post_id; 
    // OK, we're authenticated: we need to find and save the data
    if (isset($_POST['payment_data'])){
        $data = $_POST['payment_data'];
        update_post_meta($post_id,'payment_data',$data);
    }else{
        delete_post_meta($post_id,'payment_data');
    }
} 

/*
 * User (Family Rep) metabox for pledge
 *
 */

add_action("admin_init", "pt_users_meta_init");

function pt_users_meta_init(){
  add_meta_box("users-meta", "Pledger", "pt_users", "pledge", "normal", "high");
}

function pt_users(){
  global $post;
  $custom = get_post_custom($post->ID);
  $users = $custom["users"][0];
  $pamt = $custom["pamt"][0];

    // prepare arguments
  $user_args  = array(
// search only for Authors role
    'role' => 'family_rep',
// order results by display_name
    'orderby' => 'display_name'
    );
// Create the WP_User_Query object
  $wp_user_query = new WP_User_Query($user_args);
// Get the results
  $authors = $wp_user_query->get_results();
// Check for results
  if (!empty($authors))
  {
    // Name is your custom field key
    echo "<select name='users'>";
    // loop trough each author
    foreach ($authors as $author)
    {
        // get all the user's data
        $author_info = get_userdata($author->ID);
        $author_id = get_post_meta($post->ID, 'users', true);
        if($author_id == $author_info->ID) { $author_selected = 'selected="selected"'; } else { $author_selected = ''; }
        echo '<option value='.$author_info->ID.' '.$author_selected.'>'.$author_info->first_name.' '.$author_info->last_name.'</option>';
    }
    echo "</select>";
    
    echo "<label>Pledged Amount :</label>";
    echo "<input type='number' name='pamt' size='10'  step='0.01' value=" . $pamt . " title='Payment with no dollar sign no comma ' />";
    

} else {
    echo 'No authors found';
}

}

/*
 * Save Meta Details
 *
 */
add_action('save_post', 'pt_save_userlist');

function pt_save_userlist(){
  global $post;

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return $post->ID;
    }
   //update the user meta in the database

update_post_meta($post->ID, "users", $_POST["users"]);
update_post_meta($post->ID, "pamt", $_POST["pamt"]);
update_user_meta( $_POST["users"], 'pt_pledge_id', $post->ID);

}    