


<?php 

// set variables for easy application later on - field keys are difficult to remember what they apply to when you're writing code
// essential
// Previous page variables
$prevpage = get_field( "field_656fa66ea601f" );
// isolate the ID number from the ACF string by removing everything before and including the "=" equals sign
$prevpostid = get_field( 'field_656fa66ea601f', $post_id );
$prepid = substr($prevpostid, strpos($prevpostid, '=') + 1);
// use ID number to get slug and title from previous post
$prepost = get_post($prepid); 
$preslug = $prepost->post_name;
$pretitle = $prepost->post_title;
// ---------
// Next page variables
$nextpage = get_field( "field_656ec0592a232" );
//isolate the ID number from the ACF string by removing everything before and including the "=" equals sign
$nextpostid = get_field( 'field_656ec0592a232', $post_id );
$nexpid = substr($nextpostid, strpos($nextpostid, '=') + 1);
//use ID number to get slug and title from next post
$nexpost = get_post($nexpid); 
$nexslug = $nexpost->post_name;
$nextitle = $nexpost->post_title;

?>

<p>&nbsp;</p>
 <div class="clearfix">
    <div class="float-start">
		<!-- previous page echo - https://www.advancedcustomfields.com/resources/page-link/ -->   
		<?php 
			$isprevlink = get_field( 'field_656fa66ea601f');
			if( $isprevlink ): ?>
				<p><strong>Previous... 	<a href="/<?php echo( $preslug ); ?>"><?php echo( $pretitle ); ?></a></strong>
		<?php endif; ?>	
		<!-- previous page echo end -->
	</div>
	<div class="float-end">
		<!-- next page echo - https://www.advancedcustomfields.com/resources/page-link/ --> 
		<?php 
			$isnextlink = get_field( 'field_656ec0592a232');
			if( $isnextlink ): ?>
				<p><strong>What's next... <a href="/<?php echo( $nexslug ); ?>"><?php echo( $nextitle ); ?></a></strong>
		<?php endif; ?>	
		<!-- next page echo end -->
	</div>
</div>


 














