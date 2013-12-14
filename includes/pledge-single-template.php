<?php
/*
Template Name: Pledge

*/
?>
         	
<?php 
get_header();
/*include( get_stylesheet_directory() . '/header.php');*/ ?>

 <div class="custom-cnt">
 <?php do_action('pt_pledge_top'); ?>
</div>

	<?php if ( have_posts() ) : ?>
		<div class="loop">
			<div class="loop-content">
				<?php while ( have_posts() ) : // The Loop ?>
					<?php the_post(); ?>
					
					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						
						<div class="entry-content clearfix">
						
							<h1 class="entry-title">
								<?php the_title(); ?>
							</h1>

							<?php the_content(); ?>
						

							<?php 
							
							$payments = get_post_meta($post->ID,"payment_data",true);
							echo pt_print_progress_chart($post->ID, $payments);
							echo '<div>';
    						
							echo pt_print_user_payments($payments);
							echo '</div>';

						
?>


						</div>
						
						<div class="entry-footer clearfix">
							
						</div>
					</div>
					<!-- end .post -->
					
					<?php comments_template(); // include comments template ?>
				<?php endwhile; // end of one post ?>
			</div>
		</div>
	<?php else : // do not delete ?>
		
	<?php endif; // do not delete ?>


<?php 
get_footer();	
/*include( get_stylesheet_directory() . '/footer.php'); */

