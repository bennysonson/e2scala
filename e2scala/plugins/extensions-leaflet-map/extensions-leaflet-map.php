<?php
/**
 * Plugin Name: Extensions for Leaflet Map
 * Plugin URI:  https://wordpress.org/plugins/extensions-leaflet-map
 * Description: Extensions for the WordPress plugin Leaflet Map
 * Version:     2.2.7
 * Author:      hupe13
 * License:     GPL v2 or later
 * Text Domain: extensions-leaflet-map
**/

// Direktzugriff auf diese Datei verhindern:
defined( 'ABSPATH' ) or die();

define('LEAFEXT_PLUGIN_FILE', __FILE__); // /pfad/wp-content/plugins/extensions-leaflet-map/extensions-leaflet-map.php
define('LEAFEXT_PLUGIN_DIR', plugin_dir_path(__FILE__)); // /pfad/wp-content/plugins/extensions-leaflet-map-github/
define('LEAFEXT_PLUGIN_URL', WP_PLUGIN_URL . '/' . basename (LEAFEXT_PLUGIN_DIR)); // https://url/wp-content/plugins/extensions-leaflet-map-github/
define('LEAFEXT_PLUGIN_PICTS', LEAFEXT_PLUGIN_URL . '/pict/'); // https://url/wp-content/plugins/extensions-leaflet-map-github/pict/
define('LEAFEXT_PLUGIN_SETTINGS', dirname( plugin_basename( __FILE__ ) ) ); // extensions-leaflet-map

if ( ! function_exists( 'is_plugin_active' ) )
  require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

if ( ! is_plugin_active( 'leaflet-map/leaflet-map.php' ) ) {
  function leafext_require_leaflet_map_plugin(){?>
    <div class="notice notice-error" >
      <p> Please install and activate <a href="https://wordpress.org/plugins/leaflet-map/">Leaflet Map Plugin</a> before using Extensions for Leaflet Map.</p>
    </div><?php
  }
  add_action('admin_notices','leafext_require_leaflet_map_plugin');
  //register_activation_hook(__FILE__, 'leafext_require_leaflet_map_plugin');
}

if (is_admin()) {
  include_once LEAFEXT_PLUGIN_DIR . 'admin.php';
} //else {

  include_once LEAFEXT_PLUGIN_DIR . '/php/enqueue-leafletplugins.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/functions.php';
  include_once LEAFEXT_PLUGIN_DIR . '/pkg/JShrink/Minifier.php';

  include_once LEAFEXT_PLUGIN_DIR . '/php/elevation.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/sgpx.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/multielevation.php';

  include_once LEAFEXT_PLUGIN_DIR . '/php/fullscreen.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/gesture.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/hidemarkers.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/hover.php';

  include_once LEAFEXT_PLUGIN_DIR . '/php/markercluster.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/placementstrategies.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/clustergroup.php';

  include_once LEAFEXT_PLUGIN_DIR . '/php/zoomhome.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/layerswitch.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/providers.php';
  include_once LEAFEXT_PLUGIN_DIR . '/php/safari.php';


// Add settings to plugin page
function leafext_add_action_links ( $actions ) {
  $actions[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page='.LEAFEXT_PLUGIN_SETTINGS) ) .'">'. esc_html__( "Settings").'</a>';
  return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'leafext_add_action_links' );

// for translating a plugin
function leafext_extra_textdomain() {
  if (get_locale() == 'sv_SE') {
    load_plugin_textdomain('extensions-leaflet-map', false, LEAFEXT_PLUGIN_SETTINGS . '/lang/');
  }
}
add_action( 'plugins_loaded', 'leafext_extra_textdomain' );
