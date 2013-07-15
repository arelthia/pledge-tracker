<?php
/*
Template Name: Pledge Archive

ToDo: 

*/
ob_start();
?>

<?php require_once( get_stylesheet_directory() . '/header.php'); ?>

		<?php

		if (!is_user_logged_in()) {
			$redirect_url = get_permalink();
			echo wp_login_form();
			
		} else{

			// get our current user
			$user = wp_get_current_user();

//print_r($user);
			//is the current user a family rep
			if(in_array( 'family_rep', $user->roles )){
				$pledge_id = '';
	   		
	   		
		    // get the users pledge id
			$pledge_id = get_user_meta( $user->ID, 'pt_pledge_id', true );
	   			//$pledge_id = get_user_pledge_id($user->ID);
	   		//	print_r($pledge_id);
			//get the post based on the pledge id
				$pledge = get_post( absint( $pledge_id ) );
				
			//get the permalink for the post	
				$sendto = esc_url( get_permalink( $pledge ) );
				echo $sendto;
			//send user to their pledge page	
				wp_redirect( $sendto, 302 );
				
			}else{
				echo '<p class="error">No Pledge found for ' . $user->user_login . '</p>' ;

			}

		}
			
		?>

<?php
include( get_stylesheet_directory() . '/footer.php');

ob_flush();	
