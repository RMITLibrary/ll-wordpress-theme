<?php
//-----------------------------
//  indigenous_knowledges
//
//  Outputs a wrapper with a subtitle and heading, plus optional icon via CSS.
//  Shortcode:
//  [indigenous-knowledges topic="life sciences" heading-tag="h2"]
//  Antibiotic microbes in bush medicine
//  [/indigenous-knowledges]

// Expected output
// <div class="indigenous-knowledges">
//	<div clsass="indigenous-knowledges__text"><p class="subtitle">Indigenous knowledges in life sciences</p>
//		<h2>Antibiotic microbes in bush medicine</h2></div>
// </div>

function indigenous_knowledges_shortcode( $atts, $content = null ) {
    $default = array(
        'topic'    => '',
        'heading-tag' => 'h2',
        'id'          => '',
        'classes'     => ''
    );

    $a = shortcode_atts( $default, $atts );

    $subtitle   = "Indigenous knowledges";
	if ( ! empty( $a['topic'] ) ) {
		$subtitle   = "Indigenous knowledges in " . wp_kses_post( $a['topic'] );
	}
	
    $content    = wp_kses_post( do_shortcode( $content ) );

    // Sanitize heading tag
    $allowed_headings = array( 'h1','h2','h3','h4','h5','h6' );
    $heading_tag = in_array( $a['heading-tag'], $allowed_headings, true )
        ? $a['heading-tag']
        : 'h3';

    $id_attr = '';
    if ( ! empty( $a['id'] ) ) {
        $id_attr = ' id="' . esc_attr( $a['id'] ) . '"';
    }

    $class_list = array( 'indigenous-knowledges' );
    if ( ! empty( $a['classes'] ) ) {
        $extra_classes = preg_split( '/\s+/', $a['classes'], -1, PREG_SPLIT_NO_EMPTY );
        foreach ( $extra_classes as $class_token ) {
            $class_list[] = sanitize_html_class( $class_token );
        }
    }

    $wrapper_class = esc_attr( implode( ' ', $class_list ) );

    ob_start();
    ?>
<div class="<?php echo $wrapper_class; ?>"<?php echo $id_attr; ?>><div class="indigenous-knowledges__text">
<?php if ( $subtitle ) : ?>
    <p class="subtitle"><?php echo $subtitle; ?></p>
<?php endif; ?>
    <<?php echo tag_escape( $heading_tag ); ?>>
        <?php echo $content; ?>
    </<?php echo tag_escape( $heading_tag ); ?>>
</div></div>
<?php
    return ob_get_clean();
}

// add code to list (used in the_content_filter)
add_shortcode_to_list( 'indigenous-knowledges' );
// optional shorter alias
add_shortcode_to_list( 'ik-title' );

// add code to wordpress itself
add_shortcode( 'indigenous-knowledges', 'indigenous_knowledges_shortcode' );
add_shortcode( 'ik-title', 'indigenous_knowledges_shortcode' );

?>
