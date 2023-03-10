<?php
/**
 * map.tap.disable() for Safari (bug)
 * extensions-leaflet-map
 */
// Direktzugriff auf diese Datei verhindern:
defined( 'ABSPATH' ) or die();

add_filter('pre_do_shortcode_tag', function ( $output, $shortcode ) {
	if ( 'leaflet-map' == $shortcode ) {
		wp_enqueue_script('safari_leaflet',
			plugins_url('js/safari.js',LEAFEXT_PLUGIN_FILE),
			array('wp_leaflet_map'), null);
	}
	return $output;
}, 10, 2);
