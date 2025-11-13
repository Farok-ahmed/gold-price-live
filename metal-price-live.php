<?php
/**
 * Plugin Name: Metal Price Live
 * Plugin URI: https://wordpress.org/plugins/metal-price-live/
 * Description: A plugin to display live gold, silver, and platinum prices from multiple API providers (GoldPrice.org, MetalPriceAPI.com, Metals-API.com)
 * Version: 1.0.0
 * Author: Md Farok Ahmed
 * Author URI: https://farok.me
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: metal-price-live
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
                    <strong>Metal Price Live</strong> has been activated! 
                    Please <a href="<?php echo admin_url( 'admin.php?page=metal-price-live-settings' ); ?>">configure your API URL</a> to start displaying live prices.
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
    load_plugin_textdomain( 'metal-price-live', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
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
 * Detect API provider from URL
 * 
 * Supported providers:
 * - GoldPrice.org: https://data-asg.goldprice.org/dbXRates/USD
 * - MetalPriceAPI.com: https://api.metalpriceapi.com/v1/latest?api_key=KEY&base=USD&currencies=XAG,XAU,XPT
 * - Metals-API.com: https://metals-api.com/api/latest?access_key=KEY&base=USD&symbols=XAU,XAG,XPT
 * 
 * @param string $api_url The API URL to check
 * @return string Provider identifier (goldprice|metalpriceapi|metals-api|unknown)
 */
function gold_price_lived_detect_api_provider( $api_url ) {
    if ( strpos( $api_url, 'goldprice.org' ) !== false ) {
        return 'goldprice';
    } elseif ( strpos( $api_url, 'metalpriceapi.com' ) !== false ) {
        return 'metalpriceapi';
    } elseif ( strpos( $api_url, 'metals-api.com' ) !== false ) {
        return 'metals-api';
    }
    return 'unknown';
}

/**
 * Normalize API response to standard format
 * 
 * Different API providers return data in different formats.
 * This function converts all formats to a standard structure.
 * 
 * Standard format:
 * {
 *   "items": [{"xauPrice": 2685.45, "xagPrice": 31.89, "xptPrice": 967.50}],
 *   "ts": 1234567890000,
 *   "provider": "goldprice"
 * }
 * 
 * @param array $raw_data Raw API response data
 * @param string $provider Provider identifier
 * @return array Normalized data structure
 */
function gold_price_lived_normalize_response( $raw_data, $provider ) {
    $normalized = array(
        'items' => array(
            array(
                'xauPrice' => 0,  // Gold price per troy ounce
                'xagPrice' => 0,  // Silver price per troy ounce
                'xptPrice' => 0,  // Platinum price per troy ounce
            )
        ),
        'ts' => time() * 1000,
        'provider' => $provider
    );

    switch ( $provider ) {
        case 'goldprice':
            // Format: {"items":[{"xauPrice":2685.45,"xagPrice":31.89,"xptPrice":967.50}],"ts":1234567890}
            if ( isset( $raw_data['items'][0] ) ) {
                $normalized['items'][0]['xauPrice'] = isset( $raw_data['items'][0]['xauPrice'] ) ? floatval( $raw_data['items'][0]['xauPrice'] ) : 0;
                $normalized['items'][0]['xagPrice'] = isset( $raw_data['items'][0]['xagPrice'] ) ? floatval( $raw_data['items'][0]['xagPrice'] ) : 0;
                $normalized['items'][0]['xptPrice'] = isset( $raw_data['items'][0]['xptPrice'] ) ? floatval( $raw_data['items'][0]['xptPrice'] ) : 0;
                $normalized['ts'] = isset( $raw_data['ts'] ) ? $raw_data['ts'] : time() * 1000;
            }
            break;

        case 'metalpriceapi':
            // Format: {"rates":{"XAU":0.000372,"XAG":0.03134,"XPT":0.001034},"base":"CAD"}
            if ( isset( $raw_data['rates'] ) ) {
                // Rates are inverse (currency per unit of metal), need to convert
                if ( isset( $raw_data['rates']['XAU'] ) && $raw_data['rates']['XAU'] > 0 ) {
                    $normalized['items'][0]['xauPrice'] = 1 / floatval( $raw_data['rates']['XAU'] );
                }
                if ( isset( $raw_data['rates']['XAG'] ) && $raw_data['rates']['XAG'] > 0 ) {
                    $normalized['items'][0]['xagPrice'] = 1 / floatval( $raw_data['rates']['XAG'] );
                }
                if ( isset( $raw_data['rates']['XPT'] ) && $raw_data['rates']['XPT'] > 0 ) {
                    $normalized['items'][0]['xptPrice'] = 1 / floatval( $raw_data['rates']['XPT'] );
                }
            }
            break;

        case 'metals-api':
            // Format similar to metalpriceapi: {"rates":{"XAU":0.000372},"base":"USD"}
            if ( isset( $raw_data['rates'] ) ) {
                if ( isset( $raw_data['rates']['XAU'] ) && $raw_data['rates']['XAU'] > 0 ) {
                    $normalized['items'][0]['xauPrice'] = 1 / floatval( $raw_data['rates']['XAU'] );
                }
                if ( isset( $raw_data['rates']['XAG'] ) && $raw_data['rates']['XAG'] > 0 ) {
                    $normalized['items'][0]['xagPrice'] = 1 / floatval( $raw_data['rates']['XAG'] );
                }
                if ( isset( $raw_data['rates']['XPT'] ) && $raw_data['rates']['XPT'] > 0 ) {
                    $normalized['items'][0]['xptPrice'] = 1 / floatval( $raw_data['rates']['XPT'] );
                }
            }
            break;
    }

    return $normalized;
}

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
    $raw_data = json_decode( $body, true );
    
    if ( ! $raw_data ) {
        return false;
    }
    
    // Detect API provider and normalize response
    $provider = gold_price_lived_detect_api_provider( $api_url );
    $data = gold_price_lived_normalize_response( $raw_data, $provider );
    
    // Cache the normalized data for 12 hours (twice daily updates)
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
        case 'USD':
            return 'USD $';
        case 'CAD':
            return 'CAD $';
        case 'EUR':
            return 'EUR €';
        case 'GBP':
            return 'GBP £';
        case 'AUD':
            return 'AUD $';
        case 'JPY':
            return 'JPY ¥';
        case 'CHF':
            return 'CHF Fr';
        case 'INR':
            return 'INR ₹';
        case 'CNY':
            return 'CNY ¥';
        default:
            return $currency . ' $';
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
    
    // Try different API URL patterns
    
    // Pattern 1: GoldPrice.org format - https://data-asg.goldprice.org/dbXRates/USD
    if ( preg_match( '/\/dbXRates\/([A-Z]{3})$/i', $api_key, $matches ) ) {
        return strtoupper( $matches[1] );
    }
    
    // Pattern 2: MetalPriceAPI format - ?base=CAD or &base=USD
    if ( preg_match( '/[?&]base=([A-Z]{3})/i', $api_key, $matches ) ) {
        return strtoupper( $matches[1] );
    }
    
    // Pattern 3: Metals-API format - similar to MetalPriceAPI
    if ( preg_match( '/[?&]from=([A-Z]{3})/i', $api_key, $matches ) ) {
        return strtoupper( $matches[1] );
    }
    
    // Default to USD if pattern doesn't match
    return 'USD';
}
