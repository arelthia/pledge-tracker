<?php

/**
* Pt Importer
*
* @package     Pledge Tracker
* @subpackage  Pt Importer
* @copyright   Copyright (c) 2013, Arelthia Phillips
* @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
* @since       2.0.1
*
*
* TODO:Consider an option to import users and their pledge
* Notes:
*   1. Csv requires the first row to be headings: username, date, amount
*   2. Will not import a paymnet if the user does not exists 
*   3. Will not import a payment if the user does not have a pledge created
*   4. Will not import a payment if the user has multiple pledges
*   5. Will import payment if pledge is in draft mode     
*   6. To delete all pledge payments the user must type delete in the confirm field
*/ 

ini_set('max_execution_time', 90);
define('cnt',  '0');
/*
* Add Import page to Pledge menu
*/
function pt_menu_setup() {
    add_submenu_page( 'edit.php?post_type=pledge', 'Importer', 'Import', 'manage_options', 'importer', 'pt_import_page' ); 
}
add_action('admin_menu', 'pt_menu_setup');




/*
* Importer form
*/

function pt_import_page(){

    global $pt_g_log;

?>

    <div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div><h2>Import Options</h2>    
    <h3>Import Payments</h3>

    <p>Csv should have a field for the following: unique user name, date, amount</p>

    <form class="add:the-list: validate" method="post" action="" enctype="multipart/form-data">
        <!-- File input -->
        <p><label for="payfile">Upload file:</label><br/>
        <input name="payfile" id="payfile" type="file" value="" aria-required="true" /></p>
        <input type="hidden" name="pt_action" value="process_csv_import"/>
        <?php wp_nonce_field('pt_csvui_nonce' , 'pt_csvui_nonce'); ?>
        <p class="submit"><input type="submit" class="button" name="submit" value="Import" /></p>
    </form>
  
  <hr />
        <h3>Delete All Payments</h3>
        <p>This action can not be reversed. Type delete and press the Delete All Payments Button </p>
        <form class="" method="post" action="" enctype="">
            <!-- Button to Delete All Payments -->
            <p><label for="deleteall">Confirm:</label><br/>
            <input type="text" name="deleteall" id="deleteall" value="" />
            <input type="hidden" name="pt_del" value="delete_payments"/>
            <?php wp_nonce_field('pt_del_nonce' , 'pt_del_nonce'); ?>
            <p class="submit"><input type="submit" class="button" name="submit" value="Delete All Payments" /></p>
        </form>
    </div>
<?php
    

}


/*
* Process the csv upon file upload
*/
function pt_process_csv() {
    global $wpdb, $gpt_log;
    $pt_cur_payments = ''; //payments already made on a pledge
    //$cnt = 0; 

    $pt_uploaded = 0;

    if( isset( $_POST['pt_action'] ) && $_POST['pt_action'] == 'process_csv_import' ) {

        if( ! wp_verify_nonce( $_POST['pt_csvui_nonce'], 'pt_csvui_nonce' ) )
            return;

        $csv = isset( $_FILES['payfile'] ) ? $_FILES['payfile']['tmp_name'] : false;

        if( !$csv ){
            $gpt_log['error'][] = 'Please upload a CSV file.';
            pt_import_notices();
            return;
        }    
        $delimiter = ',';

        $csv_array = pt_csv_to_array( $csv, $delimiter );

        
        foreach( $csv_array as $payment ) {
            //if user name valid
            $user_data = get_user_by( 'login', $payment['username'] );
            if( ! $user_data ) {
                $gpt_log['error'][] = $payment['username'] . ' not found';       

            } else{
                //find pledge for user
                $pt_post_type = 'pledge';
                $pt_post_status = 'publish';
                $meta_key = 'users';
                $meta_value = $user_data->ID;
                $pt_pledge_obj = $wpdb->get_results( $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '%s' AND meta_value = '%s' ", 
                 $meta_key,
                 $meta_value
                 ) ); 


                if ( ! $pt_pledge_obj ) {
                    $gpt_log['error'][] = 'Pledge not found for ' . $payment['username']; 
                //print_r($pt_pledge_obj);
                } elseif (count($pt_pledge_obj) > 1 ){
                    //error if user has multiple pledges
                    $gpt_log['error'][] = 'Multiple pledges found for ' . $payment['username'];
                } else{
                    //get the post id
                    $pt_pledge_id = $pt_pledge_obj[cnt];
                    $pt_pledge_id = $pt_pledge_id->post_id;

                    //get pledge payment data
                    $pt_cur_payments = get_post_meta($pt_pledge_id,"payment_data");

                    //set the next pledge number 
                    $next_payment = count($pt_cur_payments[cnt]) + 1;

                    //add new payment to the array 
                    $pt_cur_payments[cnt][$next_payment] = array(
                            'date' => $payment['date'],
                            'amount' => $payment['amount']

                            );

                    //update the pledge
                    update_post_meta($pt_pledge_id,'payment_data',$pt_cur_payments[cnt]);
                    //echo $payment['username'] . '<br />';
                    //print_r($pt_cur_payments[$cnt]);

                    $pt_uploaded++;
                }//end else 
            }//end else

        }//end for each
        $gpt_log['notice'][] =  $pt_uploaded . ' payment(s) imported.';
        //display notices for results
        pt_import_notices();
    }//end if process csv    
       
}

add_action('admin_init', 'pt_process_csv');


/*
* Parse the csv into an array
* Returns an array
*/
function pt_csv_to_array( $filename = '', $delimiter = ',') {

    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            if(!$header)
            $header = $row;
            else
            $data[] = array_combine($header, $row);
        }
        fclose($handle);
    }
    return $data;
}

/*
* Display the admin notices
*/
function pt_import_notices() {
    global $gpt_log;

    if (!empty($gpt_log)) {

        
        ?>

        <div class="wrap">
            <?php if (!empty($gpt_log['error'])): ?>

                <div class="error">
                <?php //display all error messages ?>
                <?php foreach ($gpt_log['error'] as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>

                </div>

            <?php endif; ?>
          
            <?php if (!empty($gpt_log['notice'])): ?>

                <div class="updated fade">
                <?php //display all notices ?>
                <?php foreach ($gpt_log['notice'] as $notice): ?>
                    <p><?php echo $notice; ?></p>
                <?php endforeach; ?>

                </div>

            <?php endif; ?>
        </div><!-- end wrap -->

        <?php
        

        $gpt_log = array();
    }
}


/*
* Delete All Payments
*/
function pt_delete_payments() {
    global $wpdb, $gpt_log;
    $pt_num_deleted = 0;
    //if the delete button pressed
     if( isset( $_POST['pt_del'] ) && $_POST['pt_del'] == 'delete_payments' ) {

        if( ! wp_verify_nonce( $_POST['pt_del_nonce'], 'pt_del_nonce' ) )
            return;

        if ( isset( $_POST['deleteall'] ) && $_POST['deleteall'] == 'delete'){

            //delete all payment_data
            $meta_key="payment_data";
            $pt_delete_flag = $wpdb->query( $wpdb->prepare(
                "DELETE FROM $wpdb->postmeta WHERE meta_key='%s'", 
                $meta_key
                ) ); 

            if ( $pt_delete_flag == '0' ){
                $gpt_log['error'][] =  'No payments deleted.';
            }else{
                $gpt_log['notice'][] =    'All payment(s) deleted from '. $pt_delete_flag . ' pledges .';
            }

        }elseif( isset( $_POST['deleteall'] ) && $_POST['deleteall'] == ''){

            $gpt_log['error'][] =  'To delete all posts type delete in the confirm field below';
        }

         //display notices for results
        pt_import_notices();

    }

}

add_action('admin_init', 'pt_delete_payments');

