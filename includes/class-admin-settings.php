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
            __( 'API Key', 'gold-price-lived' ),
            array( $this, 'api_key_field_callback' ),
            'gold-price-lived-settings',
            'gold_price_lived_main_section'
        );
    }

    /**
     * Main section callback
     */
    public function main_section_callback() {
        echo '<p>' . __( 'Enter your Gold Price API key to enable live price data.', 'gold-price-lived' ) . '</p>';
    }

    /**
     * API Key field callback
     */
    public function api_key_field_callback() {
        $api_key = get_option( 'gold_price_lived_api_key', '' );
        ?>
        <input 
            type="text" 
            name="gold_price_lived_api_key" 
            value="<?php echo esc_attr( $api_key ); ?>" 
            class="regular-text" 
            placeholder="<?php esc_attr_e( 'Enter your API key', 'gold-price-lived' ); ?>"
        />
        <p class="description">
            <?php _e( 'Get your API key from <a href="https://data-asg.goldprice.org" target="_blank">goldprice.org</a>', 'gold-price-lived' ); ?>
        </p>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
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
                }
                ?>
            </div>
        </div>
        <?php
    }
}

// Initialize the class
new Gold_Price_Admin_Settings();
