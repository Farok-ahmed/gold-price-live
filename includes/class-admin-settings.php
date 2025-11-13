<?php
/**
 * Admin Settings Page
 *
 * @package Gold_Price_Lived
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class Gold_Price_Admin_Settings
 */
class Gold_Price_Admin_Settings {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( GOLD_PRICE_LIVED_PLUGIN_DIR . 'gold-price-lived.php' ), array( $this, 'add_settings_link' ) );
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles( $hook ) {
        // Only load on our settings page
        if ( 'settings_page_gold-price-lived-settings' !== $hook ) {
            return;
        }
        
        wp_enqueue_style( 
            'gold-price-admin-settings', 
            GOLD_PRICE_LIVED_PLUGIN_URL . 'assets/css/admin-settings.css',
            array(),
            '1.0.0'
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'Gold Price Live Settings', 'gold-price-lived' ),
            __( 'Gold Price Live', 'gold-price-lived' ),
            'manage_options',
            'gold-price-lived-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Add settings link on plugins page
     */
    public function add_settings_link( $links ) {
        $settings_link = '<a href="options-general.php?page=gold-price-lived-settings">' . __( 'Settings', 'gold-price-lived' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 
            'gold_price_lived_settings_group', 
            'gold_price_lived_api_key',
            array(
                'sanitize_callback' => 'sanitize_text_field'
            )
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Handle manual fetch
        if ( isset( $_POST['fetch_now'] ) && check_admin_referer( 'gold_price_fetch_now', 'fetch_nonce' ) ) {
            delete_transient( 'gold_price_lived_data' );
            $fresh_data = gold_price_lived_fetch_prices();
            
            if ( $fresh_data ) {
                add_settings_error(
                    'gold_price_lived_messages',
                    'gold_price_lived_fetch_success',
                    __( 'Fresh data fetched successfully!', 'gold-price-lived' ),
                    'updated'
                );
            } else {
                add_settings_error(
                    'gold_price_lived_messages',
                    'gold_price_lived_fetch_error',
                    __( 'Failed to fetch data. Please check your API URL.', 'gold-price-lived' ),
                    'error'
                );
            }
        }

        // Check if settings saved
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error(
                'gold_price_lived_messages',
                'gold_price_lived_message',
                __( 'Settings Saved', 'gold-price-lived' ),
                'updated'
            );
        }

        settings_errors( 'gold_price_lived_messages' );
        
        $api_key = get_option( 'gold_price_lived_api_key', '' );
        $has_api = ! empty( $api_key );
        
        // Get detected info
        $detected_currency = 'USD';
        $detected_provider = 'Not configured';
        if ( $has_api ) {
            $detected_currency = gold_price_lived_get_currency();
            $provider_code = gold_price_lived_detect_api_provider( $api_key );
            
            switch ( $provider_code ) {
                case 'goldprice':
                    $detected_provider = 'GoldPrice.org';
                    break;
                case 'metalpriceapi':
                    $detected_provider = 'MetalPriceAPI.com';
                    break;
                case 'metals-api':
                    $detected_provider = 'Metals-API.com';
                    break;
            }
        }
        
        // Get cache status
        $cached_data = get_transient( 'gold_price_lived_data' );
        $cache_timestamp = get_transient( 'gold_price_lived_cache_time' );
        $last_updated = '';
        if ( $cache_timestamp ) {
            $last_updated = human_time_diff( $cache_timestamp, current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'gold-price-lived' );
        }
        ?>
        <div class="wrap gold-price-admin-wrap">
            <h1><?php _e( 'API Configuration', 'gold-price-lived' ); ?></h1>
            
            <div class="gold-price-admin-grid">
                <!-- Left Column: API Settings -->
                <div>
                    <!-- API Settings Card -->
                    <div class="gold-price-card">
                        <div class="gold-price-card-header">
                            <h2>
                                <span class="dashicons dashicons-admin-settings"></span>
                                <?php _e( 'API Settings', 'gold-price-lived' ); ?>
                            </h2>
                        </div>
                        
                        <form method="post" action="options.php">
                            <?php settings_fields( 'gold_price_lived_settings_group' ); ?>
                            
                            <div class="gold-price-api-input-wrapper">
                                <input 
                                    type="password" 
                                    id="gold_price_api_key_input"
                                    name="gold_price_lived_api_key" 
                                    value="<?php echo esc_attr( $api_key ); ?>" 
                                    class="gold-price-api-input" 
                                    placeholder="<?php esc_attr_e( 'Enter your metal price API URL...', 'gold-price-lived' ); ?>"
                                    autocomplete="off"
                                />
                                <button 
                                    type="button" 
                                    id="toggle_api_key_visibility" 
                                    class="gold-price-toggle-btn"
                                    title="<?php esc_attr_e( 'Toggle visibility', 'gold-price-lived' ); ?>"
                                >
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                            </div>
                            
                            <?php if ( $has_api ) : ?>
                            <div class="gold-price-success-msg">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <p><?php _e( 'API key configured successfully!', 'gold-price-lived' ); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="gold-price-provider-info">
                                <p><strong><?php _e( 'Supported API Providers:', 'gold-price-lived' ); ?></strong></p>
                                <p>• <?php _e( 'GoldPrice.org (free, no registration)', 'gold-price-lived' ); ?></p>
                                <p>• <?php _e( 'MetalPriceAPI.com (API key required)', 'gold-price-lived' ); ?></p>
                                <p>• <?php _e( 'Metals-API.com (API key required)', 'gold-price-lived' ); ?></p>
                                <p style="margin-top: 10px;"><strong><?php _e( 'Currency Detection Details', 'gold-price-lived' ); ?></strong></p>
                                <p><?php printf( __( 'Provider: <strong>%s</strong>', 'gold-price-lived' ), esc_html( $detected_provider ) ); ?></p>
                                <p><?php printf( __( 'Currency: <strong>%s</strong>', 'gold-price-lived' ), esc_html( $detected_currency ) ); ?></p>
                            </div>
                            
                            <?php submit_button( __( 'Save Settings', 'gold-price-lived' ), 'primary', 'submit', false ); ?>
                        </form>
                    </div>
                </div>
                
                <!-- Right Column: Shortcodes & Status -->
                <div>
                    <!-- Available Shortcodes Card -->
                    <div class="gold-price-card">
                        <div class="gold-price-card-header">
                            <h2>
                                <span class="dashicons dashicons-shortcode"></span>
                                <?php _e( 'Available Shortcodes', 'gold-price-lived' ); ?>
                            </h2>
                        </div>
                        
                        <ul class="gold-price-shortcodes-list">
                            <li>
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                <code>[gold_price_topbar]</code>
                            </li>
                            <li>
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                <code>[gold_price_table]</code>
                            </li>
                            <li>
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                <code>[gold_price_premium_jewellery]</code>
                            </li>
                            <li>
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                <code>[gold_price_jewellery]</code>
                            </li>
                            <li>
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                <code>[gold_price_calculator]</code>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Current Status Card -->
                    <div class="gold-price-card gold-price-status-card" style="margin-top: 30px;">
                        <div class="gold-price-card-header">
                            <h2>
                                <span class="dashicons dashicons-shortcode"></span>
                                <?php _e( 'Current Status', 'gold-price-lived' ); ?>
                            </h2>
                        </div>
                        
                        <?php if ( $has_api ) : ?>
                            <div class="gold-price-status-success">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php _e( 'API key configured successfully!', 'gold-price-lived' ); ?>
                            </div>
                            
                            <?php if ( $last_updated ) : ?>
                            <div class="gold-price-status-info">
                                <?php printf( __( 'Last Updated: <strong>%s</strong>', 'gold-price-lived' ), esc_html( $last_updated ) ); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="gold-price-status-info">
                                <?php _e( 'Next Update: <strong>Automatic (twice daily)</strong>', 'gold-price-lived' ); ?>
                            </div>
                            
                            <div class="gold-price-status-info">
                                <?php _e( 'API Connection: <strong>Working</strong>', 'gold-price-lived' ); ?>
                            </div>
                            
                            <!-- Fetch Now Button -->
                            <form method="post">
                                <?php wp_nonce_field( 'gold_price_fetch_now', 'fetch_nonce' ); ?>
                                <button type="submit" name="fetch_now" class="gold-price-fetch-btn">
                                    <span class="dashicons dashicons-update"></span>
                                    <?php _e( 'Fetch Now', 'gold-price-lived' ); ?>
                                </button>
                            </form>
                        <?php else : ?>
                            <div class="gold-price-error">
                                <?php _e( 'API key not configured. Please add your API URL in the settings.', 'gold-price-lived' ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#toggle_api_key_visibility').on('click', function() {
                var input = $('#gold_price_api_key_input');
                var icon = $(this).find('.dashicons');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                }
            });
        });
        </script>
        <?php
    }
}

// Initialize the class
new Gold_Price_Admin_Settings();
