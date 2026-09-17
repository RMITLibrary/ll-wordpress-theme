<?php

//-----------------------------
//	alert_banner_att

//	Creates an alert banner in the bootstrap style

//	args:		$atts:

//  alert:      The message, html can be included
//  mess:       Legacy alias for alert (optional)
//  type:       danger, warning or info (optional, defaults to legacy danger)
//  close:      false to hide the close button (optional)

// called from:  video_att

//  shortcode:  [alert-banner]

//	usage:
//  [alert-banner alert='<strong>Warning!</strong> Message goes here' /]
//  [alert-banner type='info' close='false']<strong>Info.</strong> Message goes here[/alert-banner]

//  Expected output
//<div class="alert alert-danger alert-dismissible">
//    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
//    <strong>Warning!</strong> Message here.
//</div>

function alert_banner_att($atts, $content = null) {
    $a = shortcode_atts(
        array(
            'alert' => '',
            'mess' => '',
            'type' => 'danger',
            'close' => 'true',
            'dismissible' => 'true',
        ),
        $atts
    );

    $content = trim((string) $content) === '' ? ($a['alert'] ?: $a['mess']) : do_shortcode($content);

    return doAlertBanner($content, $a['type'], $a['close'] !== 'false' && $a['dismissible'] !== 'false');
}

//-----------------------------
//	alert_banner_att

//	Creates an alert banner in the bootstrap style

//	args:		$atts:

//  alert:      The message, html can be included
//  type:       danger, warning or info (optional, defaults to legacy danger)
//  close:      false to hide the close button (optional)

// called from: video_att
//              aler_banner_att

function doAlertBanner($content, $type = 'danger', $dismissible = true)
{
    $type = in_array($type, array('danger', 'warning', 'info'), true) ? $type : 'danger';
    $classes = 'alert alert-' . $type . ($dismissible ? ' alert-dismissible' : '');

    $output = '<div class="' . esc_attr($classes) . '">'  . "\n";

    if($dismissible) {
        $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' . "\n";
    }

    //strips out <script> tags etc.
    $output .= wp_kses_post($content);
    $output .= '</div>';

    return $output;
}
ll_add_shortcode('alert-banner', 'alert_banner_att');
