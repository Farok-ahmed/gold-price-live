<?php
/**
 * Price Table Shortcode
 *
 * @package Gold_Price_Lived
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class Gold_Price_Table
 */
class Gold_Price_Table {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_shortcode( 'gold_price_table', array( $this, 'render_table_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    /**
     * Enqueue table styles
     */
    public function enqueue_styles() {
        wp_enqueue_style( 
            'gold-price-lived-table', 
            GOLD_PRICE_LIVED_PLUGIN_URL . 'assets/css/price-table.css', 
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
     * Render the price table shortcode
     * Usage: [gold_price_table]
     */
    public function render_table_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'title' => 'SPOT PRICE PER GRAM',
            'show_updated_time' => 'yes'
        ), $atts );

        $data = $this->get_price_data();

        if ( ! $data ) {
            return '<div class="gold-price-table-error">Unable to fetch prices</div>';
        }

        // Extract prices per gram
        $gold_price = isset( $data['items'][0]['xauPrice'] ) ? $data['items'][0]['xauPrice'] : 0;
        $silver_price = isset( $data['items'][0]['xagPrice'] ) ? $data['items'][0]['xagPrice'] : 0;
        $platinum_price = isset( $data['items'][0]['xptPrice'] ) ? $data['items'][0]['xptPrice'] : 0;

        // Convert to per gram (prices are usually per troy ounce, 1 troy oz = 31.1035 grams)
        $gold_per_gram = $gold_price / 31.1035;
        $silver_per_gram = $silver_price / 31.1035;
        $platinum_per_gram = $platinum_price / 31.1035;

        // Get currency symbol
        $currency_symbol = gold_price_lived_get_currency_symbol();

        // Get timestamp
        $timestamp = isset( $data['ts'] ) ? $data['ts'] : time() * 1000;
        $updated_time = date( 'n/j/Y g:i A', $timestamp / 1000 );
        $timezone = 'EDT'; // You can make this dynamic if needed

        ob_start();
        ?>
        <div class="gold-price-table-wrapper">
            <?php if ( $atts['show_updated_time'] === 'yes' ) : ?>
            <div class="price-updated-info">
                Prices updated as of: <?php echo esc_html( $updated_time . ' ' . $timezone ); ?>
            </div>
            <?php endif; ?>

            <div class="spot-price-title">
                <?php echo esc_html( $atts['title'] ); ?>
            </div>

            <table class="gold-price-table">
                <thead>
                    <tr>
                        <th>Metal</th>
                        <th>Price Per Gram</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="metal-name">Gold price per gram</td>
                        <td class="metal-price"><?php echo esc_html( $currency_symbol . ' ' . number_format( $gold_per_gram, 2 ) . '/g' ); ?></td>
                    </tr>
                    <tr>
                        <td class="metal-name">Silver price per gram</td>
                        <td class="metal-price"><?php echo esc_html( $currency_symbol . ' ' . number_format( $silver_per_gram, 2 ) . '/g' ); ?></td>
                    </tr>
                    <tr>
                        <td class="metal-name">Platinum price per gram</td>
                        <td class="metal-price"><?php echo esc_html( $currency_symbol . ' ' . number_format( $platinum_per_gram, 2 ) . '/g' ); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="price-disclaimer">
                *Spot price, also known as market price, is the current live value of precious metals. Our pricing is based on a percentage of the spot price and updates throughout the day to reflect real-time market changes.
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the class
new Gold_Price_Table();
