<?php
/**
 * Topbar Shortcode
 *
 * @package Gold_Price_Lived
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class Gold_Price_Topbar
 */
class Gold_Price_Topbar {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_shortcode( 'gold_price_topbar', array( $this, 'render_topbar_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    /**
     * Enqueue topbar styles
     */
    public function enqueue_styles() {
        wp_enqueue_style( 
            'gold-price-lived-topbar', 
            GOLD_PRICE_LIVED_PLUGIN_URL . 'assets/css/topbar.css', 
            array(), 
            GOLD_PRICE_LIVED_VERSION 
        );
    }

    /**
     * Get price data from API
     */
    private function get_price_data() {
        return gold_price_lived_fetch_prices();
    }

    /**
     * Topbar shortcode to display gold, silver, and platinum prices
     * Usage: [gold_price_topbar]
     */
    public function render_topbar_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'show_date' => 'yes',
            'show_time' => 'yes'
        ), $atts );
        
        $data = $this->get_price_data();
        
        if ( ! $data ) {
            return '<div class="gold-price-topbar error">Unable to fetch prices</div>';
        }
        
        // Extract prices
        $gold_price = isset( $data['items'][0]['xauPrice'] ) ? number_format( $data['items'][0]['xauPrice'], 2 ) : 'N/A';
        $silver_price = isset( $data['items'][0]['xagPrice'] ) ? number_format( $data['items'][0]['xagPrice'], 2 ) : 'N/A';
        $platinum_price = isset( $data['items'][0]['xptPrice'] ) ? number_format( $data['items'][0]['xptPrice'], 2 ) : 'N/A';
        
        // Get timestamp
        $timestamp = isset( $data['ts'] ) ? $data['ts'] : time() * 1000;
        $date = date( 'F j, Y', $timestamp / 1000 );
        $time = date( 'h:i A', $timestamp / 1000 );
        
        ob_start();
        ?>
        <div class="gold-price-topbar">
            <div class="gold-price-container">
                <div class="price-item gold">
                    <span class="metal-name">Gold</span>
                    <span class="price">$<?php echo esc_html( $gold_price ); ?></span>
                </div>
                <div class="price-item silver">
                    <span class="metal-name">Silver</span>
                    <span class="price">$<?php echo esc_html( $silver_price ); ?></span>
                </div>
                <div class="price-item platinum">
                    <span class="metal-name">Platinum</span>
                    <span class="price">$<?php echo esc_html( $platinum_price ); ?></span>
                </div>
                <?php if ( $atts['show_date'] === 'yes' || $atts['show_time'] === 'yes' ) : ?>
                <div class="price-date-time">
                    <?php if ( $atts['show_date'] === 'yes' ) : ?>
                        <span class="date"><?php echo esc_html( $date ); ?></span>
                    <?php endif; ?>
                    <?php if ( $atts['show_time'] === 'yes' ) : ?>
                        <span class="time"><?php echo esc_html( $time ); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the class
new Gold_Price_Topbar();
