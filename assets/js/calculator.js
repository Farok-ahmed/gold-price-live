/**
 * Scrap Metal Calculator JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize Nice Select
        if ($.fn.niceSelect) {
            $('.calc-select').niceSelect();
        }

        // Metal change handler - update purity options
        $('#metal-select').on('change', function() {
            updatePurityOptions($(this).val());
        });

        // Calculate button handler
        $('#calculate-btn').on('click', function() {
            calculatePrice();
        });

        // Enter key handler
        $('#weight-input').on('keypress', function(e) {
            if (e.which === 13) {
                calculatePrice();
            }
        });

        /**
         * Update purity options based on selected metal
         */
        function updatePurityOptions(metal) {
            var $puritySelect = $('#purity-select');
            var options = '';

            if (metal === 'gold') {
                options = `
                    <option value="24">24 karat</option>
                    <option value="22">22 karat</option>
                    <option value="21">21 karat</option>
                    <option value="18">18 karat</option>
                    <option value="14">14 karat</option>
                    <option value="10">10 karat</option>
                    <option value="9">9 karat</option>
                `;
            } else if (metal === 'silver') {
                options = `
                    <option value="sterling">925 Jewellery</option>
                    <option value="fine">925 Flatware</option>
                    <option value="coin">925 Mexican</option>
                `;
            } else if (metal === 'platinum') {
                options = `
                    <option value="999">999 Platinum</option>
                    <option value="950">950 Platinum</option>
                    <option value="900">900 Platinum</option>
                `;
            }

            $puritySelect.html(options);
            
            // Re-initialize nice select
            if ($.fn.niceSelect) {
                $puritySelect.niceSelect('update');
            }
        }

        /**
         * Calculate scrap metal price
         */
        function calculatePrice() {
            var metal = $('#metal-select').val();
            var purity = $('#purity-select').val();
            var weight = $('#weight-input').val();

            // Hide previous results/errors
            $('#calculator-result').hide();
            $('#calculator-error').hide();

            // Validate weight
            if (!weight || parseFloat(weight) <= 0) {
                showError('Please enter a valid weight');
                return;
            }

            // Show loading state
            $('#calculate-btn').text('Calculating...').prop('disabled', true);

            // AJAX request
            $.ajax({
                url: goldPriceCalc.ajax_url,
                type: 'POST',
                data: {
                    action: 'calculate_scrap_metal_price',
                    nonce: goldPriceCalc.nonce,
                    metal: metal,
                    purity: purity,
                    weight: weight
                },
                success: function(response) {
                    if (response.success) {
                        showResult(response.data.total);
                    } else {
                        showError(response.data.message || 'Calculation failed');
                    }
                },
                error: function() {
                    showError('An error occurred. Please try again.');
                },
                complete: function() {
                    $('#calculate-btn').text('Calculate').prop('disabled', false);
                }
            });
        }

        /**
         * Show calculation result
         */
        function showResult(total) {
            $('#result-value').text(total);
            $('#calculator-result').fadeIn();
        }

        /**
         * Show error message
         */
        function showError(message) {
            $('#calculator-error').text(message).fadeIn();
        }
    });

})(jQuery);
