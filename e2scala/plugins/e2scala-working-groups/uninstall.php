<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Clear db stored data
// $working_groups = get_posts(array('post_type' => 'working_group', 'numberposts' => -1));
// foreach($working_groups as $working_group) {
//     wp_delete_post($working_group->ID, true);
// }

// Access the db via SQL
// global $wpdb;
// $wpdb->query("DELETE FROM wp_posts WHERE post_type  = 'working_group'");
// $wpdb->query("DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id from wp_posts)");
// $wpdb->query("DELETE FROM wp_term_relationships WHERE object_id NOT IN (SELECT id from wp_posts)");
