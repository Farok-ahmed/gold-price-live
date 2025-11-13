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
        add_filter( 'plugin_action_links_' . plugin_basename( GOLD_PRICE_LIVED_PLUGIN_DIR . 'gold-price-lived.php' ), array( $this, 'add_settings_link' ) );
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

        add_settings_section(
            'gold_price_lived_main_section',
            __( 'API Configuration', 'gold-price-lived' ),
            array( $this, 'main_section_callback' ),
            'gold-price-lived-settings'
        );

        add_settings_field(
            'gold_price_lived_api_key_field',
            __( 'API URL', 'gold-price-lived' ),
            array( $this, 'api_key_field_callback' ),
            'gold-price-lived-settings',
            'gold_price_lived_main_section'
        );
    }

    /**
     * Main section callback
     */
    public function main_section_callback() {
        echo '<p>' . __( 'Enter your Gold Price API URL. The currency will be automatically detected from the URL.', 'gold-price-lived' ) . '</p>';
    }

    /**
     * API URL field callback
     */
    public function api_key_field_callback() {
        $api_key = get_option( 'gold_price_lived_api_key', '' );
        $has_key = ! empty( $api_key );
        
        // Detect currency from API URL
        $detected_currency = 'Not detected';
        if ( $has_key ) {
            $detected_currency = gold_price_lived_get_currency();
        }
        ?>
        <div style="position: relative; display: inline-block;">
            <input 
                type="password" 
                id="gold_price_api_key_input"
                name="gold_price_lived_api_key" 
                value="<?php echo esc_attr( $api_key ); ?>" 
                class="regular-text" 
                placeholder="<?php esc_attr_e( 'https://data-asg.goldprice.org/dbXRates/USD', 'gold-price-lived' ); ?>"
                autocomplete="off"
            />
            <button 
                type="button" 
                id="toggle_api_key_visibility" 
                class="button button-small"
                style="margin-left: 5px; vertical-align: middle;"
            >
                <span class="dashicons dashicons-visibility" style="line-height: 1.4;"></span>
            </button>
        </div>
        <?php if ( $has_key ) : ?>
        <p class="description" style="color: #46b450;">
            <span class="dashicons dashicons-yes-alt" style="vertical-align: middle;"></span>
            <?php printf( __( 'API URL is configured. Detected Currency: <strong>%s</strong>', 'gold-price-lived' ), $detected_currency ); ?>
        </p>
        <?php endif; ?>
        <p class="description">
            <?php _e( 'Enter the complete API URL from <a href="https://data-asg.goldprice.org" target="_blank">goldprice.org</a>', 'gold-price-lived' ); ?><br>
            <?php _e( 'Examples:', 'gold-price-lived' ); ?><br>
            • <?php _e( 'For USD: <code>https://data-asg.goldprice.org/dbXRates/USD</code>', 'gold-price-lived' ); ?><br>
            • <?php _e( 'For CAD: <code>https://data-asg.goldprice.org/dbXRates/CAD</code>', 'gold-price-lived' ); ?><br>
            <em><?php _e( 'Currency will be automatically detected from the URL', 'gold-price-lived' ); ?></em>
        </p>
        
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
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields( 'gold_price_lived_settings_group' );
                do_settings_sections( 'gold-price-lived-settings' );
                submit_button( __( 'Save Settings', 'gold-price-lived' ) );
                ?>
            </form>

            <div style="margin-top: 30px; padding: 20px; background: #fff; border-left: 4px solid #2271b1;">
                <h2><?php _e( 'Available Shortcodes', 'gold-price-lived' ); ?></h2>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><code>[gold_price_topbar]</code> - <?php _e( 'Horizontal price bar', 'gold-price-lived' ); ?></li>
                    <li><code>[gold_price_table]</code> - <?php _e( 'Spot price table', 'gold-price-lived' ); ?></li>
                    <li><code>[gold_price_premium_jewellery]</code> - <?php _e( 'Premium jewellery table', 'gold-price-lived' ); ?></li>
                    <li><code>[gold_price_jewellery]</code> - <?php _e( 'Complete jewellery table', 'gold-price-lived' ); ?></li>
                    <li><code>[gold_price_calculator]</code> - <?php _e( 'Scrap metal calculator', 'gold-price-lived' ); ?></li>
                </ul>
            </div>

            <div style="margin-top: 20px; padding: 20px; background: #fff; border-left: 4px solid #46b450;">
                <h2><?php _e( 'Current Status', 'gold-price-lived' ); ?></h2>
                <?php
                $api_key = get_option( 'gold_price_lived_api_key', '' );
                if ( empty( $api_key ) ) {
                    echo '<p style="color: #dc3232;"><strong>' . __( 'API Key not configured. Please add your API key above.', 'gold-price-lived' ) . '</strong></p>';
                } else {
                    echo '<p style="color: #46b450;"><strong>' . __( 'API Key configured successfully!', 'gold-price-lived' ) . '</strong></p>';
                    
                    // Check cache status
                    $cached_data = get_transient( 'gold_price_lived_data' );
                    $cache_timestamp = get_transient( 'gold_price_lived_cache_time' );
                    
                    if ( $cached_data && $cache_timestamp ) {
                        $last_updated = human_time_diff( $cache_timestamp, current_time( 'timestamp' ) );
                        echo '<p>' . __( 'Last Updated:', 'gold-price-lived' ) . ' <strong>' . $last_updated . ' ago</strong></p>';
                        echo '<p>' . __( 'Next Update:', 'gold-price-lived' ) . ' <strong>' . __( 'Automatic (twice daily)', 'gold-price-lived' ) . '</strong></p>';
                    }
                    
                    // Test API connection
                    $test_data = gold_price_lived_fetch_prices();
                    if ( $test_data ) {
                        echo '<p style="color: #46b450;">' . __( 'API Connection: Working', 'gold-price-lived' ) . '</p>';
                        if ( isset( $test_data['items'][0]['xauPrice'] ) ) {
                            $gold_price = number_format( $test_data['items'][0]['xauPrice'], 2 );
                            echo '<p>' . __( 'Current Gold Price:', 'gold-price-lived' ) . ' <strong>$' . $gold_price . ' USD/oz</strong></p>';
                        }
                    } else {
                        echo '<p style="color: #dc3232;">' . __( 'API Connection: Failed. Please check your API key.', 'gold-price-lived' ) . '</p>';
                    }
                    
                    // Fetch Now button
                    ?>
                    <form method="post" style="margin-top: 20px;">
                        <?php wp_nonce_field( 'gold_price_fetch_now', 'fetch_nonce' ); ?>
                        <button type="submit" name="fetch_now" class="button button-secondary">
                            <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                            <?php _e( 'Fetch Now', 'gold-price-lived' ); ?>
                        </button>
                        <p class="description">
                            <?php _e( 'Click to fetch fresh data immediately and clear the cache.', 'gold-price-lived' ); ?>
                        </p>
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
}

// Initialize the class
new Gold_Price_Admin_Settings();
