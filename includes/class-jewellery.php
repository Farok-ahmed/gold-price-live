<?php
/**
 * Jewellery Table Shortcode (Gold, Silver, Platinum)
 *
 * @package Gold_Price_Lived
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class Gold_Price_Jewellery
 */
class Gold_Price_Jewellery {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_shortcode( 'gold_price_jewellery', array( $this, 'render_jewellery_table_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    /**
     * Enqueue jewellery table styles
     */
    public function enqueue_styles() {
        wp_enqueue_style( 
            'metal-price-live-jewellery-full', 
            GOLD_PRICE_LIVED_PLUGIN_URL . 'assets/css/jewellery.css', 
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
     * Calculate gold jewellery prices based on karat
     */
    private function calculate_gold_prices( $gold_price_per_gram ) {
        return array(
            array(
                'label' => '24kt (99.9% pure gold)',
                'price' => $gold_price_per_gram * 0.999
            ),
            array(
                'label' => '22kt (91.6% pure gold)',
                'price' => $gold_price_per_gram * 0.916
            ),
            array(
                'label' => '21kt (87.5% pure gold)',
                'price' => $gold_price_per_gram * 0.875
            ),
            array(
                'label' => '18kt (75.0% pure gold)',
                'price' => $gold_price_per_gram * 0.750
            ),
            array(
                'label' => '14kt (58.5% pure)',
                'price' => $gold_price_per_gram * 0.585
            ),
            array(
                'label' => '10kt (41.7% pure gold)',
                'price' => $gold_price_per_gram * 0.417
            ),
            array(
                'label' => '9kt (37.5% pure gold)',
                'price' => $gold_price_per_gram * 0.375
            ),
            array(
                'label' => 'Gold Filled Items*',
                'price' => $gold_price_per_gram * 0.01, // Approximate value
                'is_special' => true
            )
        );
    }

    /**
     * Calculate silver jewellery prices
     */
    private function calculate_silver_prices( $silver_price_per_gram ) {
        return array(
            array(
                'label' => 'Sterling Silver Flatware (92.5% pure silver)',
                'price' => $silver_price_per_gram * 0.925
            ),
            array(
                'label' => 'Sterling Silver Jewellery (92.5% pure silver)',
                'price' => $silver_price_per_gram * 0.925
            ),
            array(
                'label' => 'Mexican Silver Jewellery (92.5% pure silver)',
                'price' => $silver_price_per_gram * 0.925
            )
        );
    }

    /**
     * Calculate platinum jewellery prices
     */
    private function calculate_platinum_prices( $platinum_price_per_gram ) {
        return array(
            array(
                'label' => '999 Platinum (99.9% pure platinum)',
                'price' => $platinum_price_per_gram * 0.999
            ),
            array(
                'label' => '950 Platinum (95% pure platinum)',
                'price' => $platinum_price_per_gram * 0.95
            )
        );
    }

    /**
     * Render the jewellery table shortcode
     * Usage: [gold_price_jewellery]
     */
    public function render_jewellery_table_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'title' => 'JEWELLERY',
            'show_disclaimers' => 'yes'
        ), $atts );

        $data = $this->get_price_data();

        if ( ! $data ) {
            return '<div class="gold-jewellery-error">Unable to fetch prices</div>';
        }

        // Get prices per troy ounce and convert to per gram
        $gold_price_oz = isset( $data['items'][0]['xauPrice'] ) ? $data['items'][0]['xauPrice'] : 0;
        $silver_price_oz = isset( $data['items'][0]['xagPrice'] ) ? $data['items'][0]['xagPrice'] : 0;
        $platinum_price_oz = isset( $data['items'][0]['xptPrice'] ) ? $data['items'][0]['xptPrice'] : 0;

        $gold_price_per_gram = $gold_price_oz / 31.1035;
        $silver_price_per_gram = $silver_price_oz / 31.1035;
        $platinum_price_per_gram = $platinum_price_oz / 31.1035;

        // Calculate jewellery prices
        $gold_items = $this->calculate_gold_prices( $gold_price_per_gram );
        $silver_items = $this->calculate_silver_prices( $silver_price_per_gram );
        $platinum_items = $this->calculate_platinum_prices( $platinum_price_per_gram );

        // Get currency symbol
        $currency_symbol = gold_price_lived_get_currency_symbol();

        ob_start();
        ?>
        <div class="gold-jewellery-wrapper">
            <div class="jewellery-main-title">
                <?php echo esc_html( $atts['title'] ); ?>
            </div>

            <div class="jewellery-tables-container">
                <!-- Gold Jewellery Table -->
                <div class="jewellery-table-section">
                    <table class="gold-jewellery-table gold-section">
                        <thead>
                            <tr>
                                <th class="section-header">Gold Jewellery</th>
                                <th class="section-header">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $gold_items as $item ) : ?>
                            <tr<?php echo isset( $item['is_special'] ) ? ' class="special-item"' : ''; ?>>
                                <td class="item-name"><?php echo esc_html( $item['label'] ); ?></td>
                                <td class="item-price"><?php echo esc_html( $currency_symbol . ' ' . number_format( $item['price'], 2 ) . '/g' ); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ( $atts['show_disclaimers'] === 'yes' ) : ?>
                    <div class="table-disclaimer">
                        *Gold Filled Items include any gold-filled items, unstamped lockets and pocket watches.
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Silver and Platinum Tables Side by Side -->
                <div class="side-by-side-tables">
                    <!-- Silver Jewellery Table -->
                    <div class="jewellery-table-section half-width">
                        <table class="gold-jewellery-table silver-section">
                            <thead>
                                <tr>
                                    <th class="section-header">Silver</th>
                                    <th class="section-header">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $silver_items as $item ) : ?>
                                <tr>
                                    <td class="item-name"><?php echo esc_html( $item['label'] ); ?></td>
                                    <td class="item-price"><?php echo esc_html( $currency_symbol . ' ' . number_format( $item['price'], 2 ) . '/g' ); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Platinum Jewellery Table -->
                    <div class="jewellery-table-section half-width">
                        <table class="gold-jewellery-table platinum-section">
                            <thead>
                                <tr>
                                    <th class="section-header">Platinum</th>
                                    <th class="section-header">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $platinum_items as $item ) : ?>
                                <tr>
                                    <td class="item-name"><?php echo esc_html( $item['label'] ); ?></td>
                                    <td class="item-price"><?php echo esc_html( $currency_symbol . ' ' . number_format( $item['price'], 2 ) . '/g' ); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ( $atts['show_disclaimers'] === 'yes' ) : ?>
            <div class="main-disclaimer">
                These are our standard (and minimum) purchase prices. For items that are resellable, antique, brand name or for high volumes of gold, please call or visit for rates.
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the class
new Gold_Price_Jewellery();
