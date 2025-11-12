<?php
/**
 * Scrap Metal Calculator Shortcode
 *
 * @package Gold_Price_Lived
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class Gold_Price_Calculator
 */
class Gold_Price_Calculator {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_shortcode( 'gold_price_calculator', array( $this, 'render_calculator_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_calculate_scrap_metal_price', array( $this, 'ajax_calculate_price' ) );
        add_action( 'wp_ajax_nopriv_calculate_scrap_metal_price', array( $this, 'ajax_calculate_price' ) );
    }

    /**
     * Enqueue calculator assets
     */
    public function enqueue_assets() {
        // Enqueue Nice Select CSS
        wp_enqueue_style( 
            'nice-select', 
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css', 
            array(), 
            '1.1.0' 
        );

        // Enqueue calculator CSS
        wp_enqueue_style( 
            'gold-price-calculator', 
            GOLD_PRICE_LIVED_PLUGIN_URL . 'assets/css/calculator.css', 
            array( 'nice-select' ), 
            GOLD_PRICE_LIVED_VERSION 
        );

        // Enqueue jQuery (WordPress default)
        wp_enqueue_script( 'jquery' );

        // Enqueue Nice Select JS
        wp_enqueue_script( 
            'nice-select', 
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js', 
            array( 'jquery' ), 
            '1.1.0', 
            true 
        );

        // Enqueue calculator JS
        wp_enqueue_script( 
            'gold-price-calculator', 
            GOLD_PRICE_LIVED_PLUGIN_URL . 'assets/js/calculator.js', 
            array( 'jquery', 'nice-select' ), 
            GOLD_PRICE_LIVED_VERSION, 
            true 
        );

        // Localize script
        wp_localize_script( 'gold-price-calculator', 'goldPriceCalc', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'gold-price-calc-nonce' )
        ) );
    }

    /**
     * Get current gold spot price
     */
    private function get_spot_price() {
        $data = gold_price_lived_fetch_prices();
        
        if ( ! $data || ! isset( $data['items'][0]['xauPrice'] ) ) {
            return 0;
        }

        // Convert from troy ounce to per gram
        return $data['items'][0]['xauPrice'] / 31.1035;
    }

    /**
     * AJAX handler for price calculation
     */
    public function ajax_calculate_price() {
        check_ajax_referer( 'gold-price-calc-nonce', 'nonce' );

        $metal = isset( $_POST['metal'] ) ? sanitize_text_field( $_POST['metal'] ) : 'gold';
        $purity = isset( $_POST['purity'] ) ? sanitize_text_field( $_POST['purity'] ) : '24';
        $weight = isset( $_POST['weight'] ) ? floatval( $_POST['weight'] ) : 0;

        if ( $weight <= 0 ) {
            wp_send_json_error( array( 'message' => 'Please enter a valid weight' ) );
        }

        $data = gold_price_lived_fetch_prices();
        
        if ( ! $data ) {
            wp_send_json_error( array( 'message' => 'Unable to fetch current prices' ) );
        }

        // Get prices per gram
        $gold_per_gram = isset( $data['items'][0]['xauPrice'] ) ? $data['items'][0]['xauPrice'] / 31.1035 : 0;
        $silver_per_gram = isset( $data['items'][0]['xagPrice'] ) ? $data['items'][0]['xagPrice'] / 31.1035 : 0;
        $platinum_per_gram = isset( $data['items'][0]['xptPrice'] ) ? $data['items'][0]['xptPrice'] / 31.1035 : 0;

        $price_per_gram = 0;
        $purity_percentage = 1;

        // Calculate based on metal type and purity
        switch ( $metal ) {
            case 'gold':
                $price_per_gram = $gold_per_gram;
                $purity_percentage = $this->get_gold_purity( $purity );
                break;
            case 'silver':
                $price_per_gram = $silver_per_gram;
                $purity_percentage = $this->get_silver_purity( $purity );
                break;
            case 'platinum':
                $price_per_gram = $platinum_per_gram;
                $purity_percentage = $this->get_platinum_purity( $purity );
                break;
        }

        $total_value = $price_per_gram * $purity_percentage * $weight;

        wp_send_json_success( array(
            'total' => number_format( $total_value, 2 ),
            'price_per_gram' => number_format( $price_per_gram * $purity_percentage, 2 ),
            'weight' => $weight,
            'metal' => ucfirst( $metal ),
            'purity' => $purity
        ) );
    }

    /**
     * Get gold purity percentage
     */
    private function get_gold_purity( $karat ) {
        $purities = array(
            '24' => 0.999,
            '22' => 0.916,
            '21' => 0.875,
            '18' => 0.750,
            '14' => 0.585,
            '10' => 0.417,
            '9' => 0.375
        );
        return isset( $purities[ $karat ] ) ? $purities[ $karat ] : 1;
    }

    /**
     * Get silver purity percentage
     */
    private function get_silver_purity( $type ) {
        $purities = array(
            'sterling' => 0.925,  // 925 Jewellery
            'fine' => 0.925,      // 925 Flatware
            'coin' => 0.925       // 925 Mexican
        );
        return isset( $purities[ $type ] ) ? $purities[ $type ] : 1;
    }

    /**
     * Get platinum purity percentage
     */
    private function get_platinum_purity( $type ) {
        $purities = array(
            '999' => 0.999,
            '950' => 0.950,
            '900' => 0.900
        );
        return isset( $purities[ $type ] ) ? $purities[ $type ] : 1;
    }

    /**
     * Render the calculator shortcode
     * Usage: [gold_price_calculator]
     */
    public function render_calculator_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'title' => 'Scrap Metal Calculator',
            'show_spot_price' => 'yes'
        ), $atts );

        $spot_price = $this->get_spot_price();

        ob_start();
        ?>
        <div class="scrap-metal-calculator-wrapper">
            <?php if ( $atts['show_spot_price'] === 'yes' && $spot_price > 0 ) : ?>
            <div class="spot-price-display">
                Gold Spot Price: <span class="spot-amount">$<?php echo number_format( $spot_price * 31.1035, 2 ); ?> CAD</span>
                <span class="spot-change">156.17</span>
            </div>
            <?php endif; ?>

            <div class="calculator-container">
                <div class="calculator-header">
                    <?php echo esc_html( $atts['title'] ); ?>
                </div>

                <div class="calculator-body">
                    <div class="calculator-row">
                        <div class="calculator-field">
                            <label for="metal-select">Select metal:</label>
                            <select id="metal-select" name="metal" class="calc-select">
                                <option value="gold">Gold</option>
                                <option value="silver">Silver</option>
                                <option value="platinum">Platinum</option>
                            </select>
                        </div>

                        <div class="calculator-field">
                            <label for="purity-select">Purity:</label>
                            <select id="purity-select" name="purity" class="calc-select">
                                <option value="24">24 karat</option>
                                <option value="22">22 karat</option>
                                <option value="21">21 karat</option>
                                <option value="18">18 karat</option>
                                <option value="14">14 karat</option>
                                <option value="10">10 karat</option>
                                <option value="9">9 karat</option>
                            </select>
                        </div>

                        <div class="calculator-field">
                            <label for="weight-input">Weight(g):</label>
                            <input type="number" id="weight-input" name="weight" class="calc-input" placeholder="Enter weight" step="0.01" min="0">
                        </div>

                        <div class="calculator-field">
                            <button type="button" id="calculate-btn" class="calc-button">Calculate</button>
                        </div>
                    </div>
                </div>

                <div id="calculator-result" class="calculator-result" style="display: none;">
                    <div class="result-content">
                        <span class="result-label">Estimated Value:</span>
                        <span class="result-amount">$<span id="result-value">0.00</span></span>
                    </div>
                </div>

                <div id="calculator-error" class="calculator-error" style="display: none;"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the class
new Gold_Price_Calculator();
