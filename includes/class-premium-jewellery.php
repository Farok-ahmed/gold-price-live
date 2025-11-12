<?php
/**
 * Premium Jewellery Table Shortcode
 *
 * @package Gold_Price_Lived
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class Gold_Price_Premium_Jewellery
 */
class Gold_Price_Premium_Jewellery {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_shortcode( 'gold_price_premium_jewellery', array( $this, 'render_jewellery_table_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    /**
     * Enqueue jewellery table styles
     */
    public function enqueue_styles() {
        wp_enqueue_style( 
            'gold-price-lived-jewellery', 
            GOLD_PRICE_LIVED_PLUGIN_URL . 'assets/css/premium-jewellery.css', 
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
     * Calculate premium jewellery prices based on karat
     */
    private function calculate_jewellery_prices( $gold_price_per_gram ) {
        // Premium jewellery prices based on gold purity percentages
        $jewellery_prices = array(
            '24kt' => array(
                'purity' => 99.9,
                'label' => '24kt Premium (99.9% pure gold)',
                'price' => $gold_price_per_gram * 0.999
            ),
            '22kt' => array(
                'purity' => 91.6,
                'label' => '22kt Premium (91.6% pure gold)',
                'price' => $gold_price_per_gram * 0.916
            ),
            '21kt' => array(
                'purity' => 87.5,
                'label' => '21kt Premium (87.5% pure gold)',
                'price' => $gold_price_per_gram * 0.875
            ),
            '18kt' => array(
                'purity' => 75.0,
                'label' => '18kt Premium (75.0% pure gold)',
                'price' => $gold_price_per_gram * 0.750
            ),
            '14kt' => array(
                'purity' => 58.5,
                'label' => '14kt Premium (58.5% pure gold)',
                'price' => $gold_price_per_gram * 0.585
            ),
            '10kt' => array(
                'purity' => 41.7,
                'label' => '10kt Premium (41.7% pure gold)',
                'price' => $gold_price_per_gram * 0.417
            ),
            '9kt' => array(
                'purity' => 37.5,
                'label' => '9kt Premium (37.5% pure gold)',
                'price' => $gold_price_per_gram * 0.375
            )
        );

        return $jewellery_prices;
    }

    /**
     * Render the premium jewellery table shortcode
     * Usage: [gold_price_premium_jewellery]
     */
    public function render_jewellery_table_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'title' => 'PREMIUM JEWELLERY',
            'show_disclaimer' => 'yes'
        ), $atts );

        $data = $this->get_price_data();

        if ( ! $data ) {
            return '<div class="gold-price-jewellery-error">Unable to fetch prices</div>';
        }

        // Get gold price per troy ounce and convert to per gram
        $gold_price_oz = isset( $data['items'][0]['xauPrice'] ) ? $data['items'][0]['xauPrice'] : 0;
        $gold_price_per_gram = $gold_price_oz / 31.1035;

        // Calculate jewellery prices
        $jewellery_prices = $this->calculate_jewellery_prices( $gold_price_per_gram );

        ob_start();
        ?>
        <div class="gold-price-jewellery-wrapper">
            <div class="jewellery-title">
                <?php echo esc_html( $atts['title'] ); ?>
            </div>

            <table class="gold-price-jewellery-table">
                <thead>
                    <tr>
                        <th>Premium Jewellery</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $jewellery_prices as $karat => $info ) : ?>
                    <tr>
                        <td class="jewellery-type"><?php echo esc_html( $info['label'] ); ?></td>
                        <td class="jewellery-price">$<?php echo number_format( $info['price'], 2 ); ?>/g</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ( $atts['show_disclaimer'] === 'yes' ) : ?>
            <div class="jewellery-disclaimer">
                *Listed above are prices which apply to authentic premium brand name pieces (e.g. Tiffany, Cartier, Birks, etc.), select vintage and antique items, and select items with gemstones.
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the class
new Gold_Price_Premium_Jewellery();
