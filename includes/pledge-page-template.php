<?php
/*
Template Name: Pledge
*/
?>

	<?php if ( have_posts() ) : ?>
		<div class="loop">This is my pledges page
			<div class="loop-content">
				<?php while ( have_posts() ) : // The Loop ?>
					<?php the_post(); ?>
					
					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						
						<div class="entry-content clearfix">
						
							<?php the_content(); ?>
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
	


