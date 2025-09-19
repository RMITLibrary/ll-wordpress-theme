
<?php 
//set variables for easy application later on - field keys are difficult to remember what they apply to when you're writing code
//essential
$prev_post = get_field( "field_6576a5c6918a0" );
$next_post = get_field( "field_6576a62750787" );
?>
<p>&nbsp;</p>
 <div class="clearfix">
    <div class="float-start">
		<!-- previous page echo - https://www.advancedcustomfields.com/resources/relationship/ --> 
		
		<?php
			$prev_post = get_field('field_6576a5c6918a0');
			if( $prev_post ): ?>
				<p><strong>Previous... 
    			<?php foreach( $prev_post as $post ): 
        			// Setup this post for WP functions (variable must be named $post).
        			setup_postdata($post); ?>
            		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    			<?php endforeach; ?>
    	<?php 
    	// Reset the global post object so that the rest of the page works correctly.
    	wp_reset_postdata(); ?>
		<?php endif; ?>
		</strong></p>
		<!-- previous page echo end -->
	</div>
	<div class="float-end">		
		<!-- next page echo - https://www.advancedcustomfields.com/resources/relationship/ --> 
		
		<?php
			$next_post = get_field('field_6576a62750787');
			if( $next_post ): ?>
				<p><strong>Next... 
    			<?php foreach( $next_post as $post ): 
        		// Setup this post for WP functions (variable must be named $post).
        		setup_postdata($post); ?>
            	<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    			<?php endforeach; ?>
    		<?php 
    	// Reset the global post object so that the rest of the page works correctly.
    	wp_reset_postdata(); ?>
		<?php endif; ?>
		</strong></p>
<!-- next page echo end -->
</div>
</div>




 














