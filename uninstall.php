<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Gold_Price_Lived
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Clean up plugin data on uninstall
 */

// Delete plugin options
delete_option( 'gold_price_lived_api_key' );
delete_option( 'gold_price_lived_currency' );

// Delete transients (cached data)
delete_transient( 'gold_price_lived_data' );
delete_transient( 'gold_price_lived_cache_time' );
delete_transient( 'gold_price_lived_activation_notice' );

// For multisite, clean up for all sites
if ( is_multisite() ) {
    global $wpdb;
    
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        
        // Delete options
        delete_option( 'gold_price_lived_api_key' );
        delete_option( 'gold_price_lived_currency' );
        
        // Delete transients
        delete_transient( 'gold_price_lived_data' );
        delete_transient( 'gold_price_lived_cache_time' );
        delete_transient( 'gold_price_lived_activation_notice' );
        
        restore_current_blog();
    }
}
