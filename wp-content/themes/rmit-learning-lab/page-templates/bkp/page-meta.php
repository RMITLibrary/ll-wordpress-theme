

<?php //set variables for easy application later on - field keys are difficult to remember what they apply to when you're writing code
//essential
$llkeywords = get_field( "field_62df591d2cd87" );
$llcategory = get_field("field_62f4ae4077552");
?>



<!-- start another accordion item, don't display if empty -->
<?php if (empty( $terms)&&empty( $terms)) {
  echo "<!-- Above fields are empty -->";
} else { ?>
        <!-- keywords echo - https://www.advancedcustomfields.com/resources/taxonomy/ --> 
        <p><strong>Keywords: &nbsp;&nbsp;</strong>
        <?php 
        $terms = get_field('field_62df591d2cd87');
        if( $terms ): ?>
            <?php foreach( $terms as $term ): ?>
                <a href="<?php echo esc_url( get_term_link( $term ) ); ?>&post_type=post_type_dlor"><?php echo esc_html( $term->name ); ?></a> &nbsp;&nbsp;&nbsp;
            <?php endforeach; ?>
        <?php endif; ?>
        </p>
        <!-- keywords echo end -->
        <!-- category echo - https://www.advancedcustomfields.com/resources/taxonomy/ --> 
        <p><strong>Category: </strong>
        <?php 
        $terms = get_field('field_62f4ae4077552');
        if( $terms ): ?>
            <?php foreach( $terms as $term ): ?>
                <a href="<?php echo esc_url( get_term_link( $term ) ); ?>&post_type=post_type_dlor""><?php echo esc_html( $term->name ); ?></a> &nbsp; &nbsp; &nbsp; 
            <?php endforeach; ?>
        <?php endif; ?>
        </p>
        <!-- category echo end -->

<?php }// close if/else statement ?>




 














