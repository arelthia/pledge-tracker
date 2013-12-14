<?php
/**
 * Display Functions
 *
 * @package     Pledge Tracker
 * @subpackage  Display Functions
 * @copyright   Copyright (c) 2013, Arelthia Phillips
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.2
*/
error_reporting(0);
/*
 * display progress chart
 *
 */
function pt_print_progress_chart($pt_post, $paidlist){

$custom = get_post_custom($pt_post);
$pledgedamt= $custom["pamt"][0];

 $totalpaid = 0;
    if (count($paidlist) > 0){
       foreach((array)$paidlist as $pt_payments ){
           if (isset($pt_payments['amount']) || isset($pt_payments['date'])){
               $totalpaid += number_format( $pt_payments['amount'], 2 ,'.','');
               
            }
        }
    }
    if ( $pledgedamt > 0 ){   
        $percent = ($totalpaid / $pledgedamt)*100;
    }else{
        $percent = 0; 
    }

return '<style>#amt-paid{width:' . absint($percent) .'%!Important;}</style><div class="current-progress"><div class ="progress-chart">
                          <span id="amt-paid" class="bar"></span>
                            </div>' . money_format("%n paid on pledge amount of ", $totalpaid) . money_format("%n", $pledgedamt ) .
                              ' </div>            
                           ';
}

/*
 * display payments on front end
 *
 */
function pt_print_user_payments($paidlist){
    $html = '';
    $c = 0;
    $totalpaid = 0;
    if (count($paidlist) > 0){
        $html = '<h3>Payment History</h3><table id="payments-made"><thead><tr> <th scope="col">Date</th><th scope="col">Amount</th></tr><tbody>';
        foreach((array)$paidlist as $pt_payments ){
           if (isset($pt_payments['amount']) || isset($pt_payments['date'])){
                $paydate = $pt_payments['date'];
                $newdate = date("m/d/Y", strtotime($paydate));
                $html .= '<tr><td>'. $newdate .'</td><td>'. money_format("%n", $pt_payments['amount']).'</td></tr>';
                $totalpaid += absint( $pt_payments['amount'] );
            }
        }
        $html .= '</tbody></table> ';

       
    }
    return $html;

}

/*
 *
 *add a new archive template
 */

function pt_archive_template( $archive_template )
{
    if ( is_post_type_archive ( 'pledge' ) ) {
        $archive_template = dirname( __FILE__ ) . '/pledge-archive-template.php';
    }
    return $archive_template;
}
add_filter( 'archive_template', 'pt_archive_template' );

/*
 *
 *add a new page template 
 */

function pt_pintop_single_template( $single_template )
{
    if ( 'pledge' == get_post_type() ) {
        $single_template = dirname( __FILE__ ) . '/pledge-single-template.php';
    }
    return $single_template;
}
add_filter( 'single_template', 'pt_pintop_single_template' );


/*
 *
 * add a redirect for family rep 
 * Handles when a user tries to go to a specific/single pledge page
 */
function pt_check_user()
{
    // not on a pledge page do nothing
    if( ! is_singular( 'pledge' ) ) return;

    // let admin continue
    if( current_user_can( 'manage_options' ) )
      return;


    // Send non logged in users back to the pledge page to log in 
    if( !is_user_logged_in() )
    {
      $reurl = home_url( '/pledge/');
      wp_redirect( $reurl , 302 );
      exit();
    }


  $user = wp_get_current_user();
  $pledge_id = get_queried_object_id();


  //if the user is trying to view a pledge that is not assigned to them  
  if( $pledge_id != get_user_meta( $user->ID, 'pt_pledge_id', true ) )
  {


     
    /* is the user a family rep, if so find their 
     * pledge page and send them to their 
     * own pledge page
     */
     if ( in_array( 'family_rep', $user->roles)){
        $pledge_id = get_user_meta( $user->ID, 'pt_pledge_id', true );
        $pledge = get_post( absint( $pledge_id ) );  
        $sendto = esc_url( get_permalink( $pledge ) );
          
      }else{
        //if the user is not a family rep send them to the home page.
        $sendto =  esc_url(home_url());
      }
      
      //send the user to where they need to go
      wp_redirect( $sendto, 302 );
      exit();
      
      
  }
    
}
add_action( 'template_redirect', 'pt_check_user' );



/*
 *
 *On Failed login redirect to page where login failed
 */

function pt_login_fail( $username ) {
 $referrer = $_SERVER['HTTP_REFERER'];  
   // if there's a valid referrer, and it's not the default log-in screen
 if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
      wp_redirect( $referrer );  // let's append some information (login=failed) to the URL for the theme to use
      exit;
  }
}

add_action( 'wp_login_failed', 'pt_login_fail' );  // hook failed login

