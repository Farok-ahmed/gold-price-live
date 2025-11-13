<?php
/**
 * Plugin Name: Gold Price Live
 * Plugin URI: https://example.com/gold-price-lived
 * Description: A plugin to display live gold prices
 * Version: 1.0.0
 * Author: Md Farok Ahmed
 * Author URI: https://farok.me
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gold-price-lived
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Currently plugin version.
 */
define( 'GOLD_PRICE_LIVED_VERSION', '1.0.0' );
define( 'GOLD_PRICE_LIVED_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GOLD_PRICE_LIVED_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_gold_price_lived() {
    // Set activation notice flag
    set_transient( 'gold_price_lived_activation_notice', true, 60 );
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'activate_gold_price_lived' );

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_gold_price_lived() {
    // Clear cache on deactivation
    delete_transient( 'gold_price_lived_data' );
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'deactivate_gold_price_lived' );

/**
 * Admin notice for activation
 */
function gold_price_lived_activation_notice() {
    if ( get_transient( 'gold_price_lived_activation_notice' ) ) {
        $api_key = get_option( 'gold_price_lived_api_key', '' );
        
        if ( empty( $api_key ) ) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <strong>Gold Price Live</strong> has been activated! 
                    Please <a href="<?php echo admin_url( 'options-general.php?page=gold-price-lived-settings' ); ?>">configure your API URL</a> to start displaying live prices.
                </p>
            </div>
            <?php
        }
        
        delete_transient( 'gold_price_lived_activation_notice' );
    }
}
add_action( 'admin_notices', 'gold_price_lived_activation_notice' );

/**
 * Initialize the plugin.
 */
function gold_price_lived_init() {
    // Load plugin text domain for translations
    load_plugin_textdomain( 'gold-price-lived', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
    // Include admin settings
    if ( is_admin() ) {
        require_once GOLD_PRICE_LIVED_PLUGIN_DIR . 'includes/class-admin-settings.php';
    }
    
    // Include required files
    require_once GOLD_PRICE_LIVED_PLUGIN_DIR . 'includes/class-topbar.php';
    require_once GOLD_PRICE_LIVED_PLUGIN_DIR . 'includes/class-price-table.php';
    require_once GOLD_PRICE_LIVED_PLUGIN_DIR . 'includes/class-premium-jewellery.php';
    require_once GOLD_PRICE_LIVED_PLUGIN_DIR . 'includes/class-jewellery.php';
    require_once GOLD_PRICE_LIVED_PLUGIN_DIR . 'includes/class-calculator.php';
}
add_action( 'plugins_loaded', 'gold_price_lived_init' );

/**
 * Fetch gold prices from API
 */
function gold_price_lived_fetch_prices() {
    // Check if API key is configured
    $api_key = get_option( 'gold_price_lived_api_key', '' );
    
    if ( empty( $api_key ) ) {
        return false;
    }
    
    // Use the API key as the API URL directly
    $api_url = trim( $api_key );
    
    // Check for cached data (cache for 12 hours - fetches twice daily)
    $cache_key = 'gold_price_lived_data';
    $cached_data = get_transient( $cache_key );
    
    if ( false !== $cached_data ) {
        return $cached_data;
    }
    
    // Fetch data from API
    $response = wp_remote_get( $api_url, array(
        'timeout' => 15,
        'headers' => array(
            'Accept' => 'application/json'
        )
    ) );
    
    if ( is_wp_error( $response ) ) {
        return false;
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( ! $data ) {
        return false;
    }
    
    // Cache the data for 12 hours (twice daily updates)
    set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );
    
    // Store cache timestamp
    set_transient( 'gold_price_lived_cache_time', current_time( 'timestamp' ), 12 * HOUR_IN_SECONDS );
    
    return $data;
}

/**
 * Get currency symbol based on selected currency
 */
function gold_price_lived_get_currency_symbol() {
    $currency = gold_price_lived_get_currency();
    
    switch ( $currency ) {
        case 'CAD':
            return 'CAD $';
        case 'USD':
        default:
            return 'USD $';
    }
}

/**
 * Get currency code from API URL
 */
function gold_price_lived_get_currency() {
    $api_key = get_option( 'gold_price_lived_api_key', '' );
    
    if ( empty( $api_key ) ) {
        return 'USD';
    }
    
    // Extract currency from API URL
    // Expected format: https://data-asg.goldprice.org/dbXRates/USD or /CAD
    if ( preg_match( '/\/dbXRates\/([A-Z]{3})$/i', $api_key, $matches ) ) {
        return strtoupper( $matches[1] );
    }
    
    // Default to USD if pattern doesn't match
    return 'USD';
}
