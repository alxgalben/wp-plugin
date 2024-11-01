<?php

/*
Plugin Name: Transfer Feedback & Rating with Thank You Details
Description: Plugin pentru colectarea feedback-ului și rating-urilor pentru transferuri și afișarea detaliilor în pagina de mulțumire.
Version: 1.1
Author: Alex Galben
*/

function tf_enqueue_scripts() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'tf_enqueue_scripts');

// Shortcode for Feedback Form
function tf_feedback_form_shortcode($atts) {
    $atts = shortcode_atts(['transfer_code' => ''], $atts, 'tf_feedback_form');
    $transfer_code = esc_attr($atts['transfer_code']);

    ob_start();
    $criteria = tf_get_criteria();
    ?>
    <form id="feedbackForm" class="feedback-form" data-transfer-code="<?php echo $transfer_code; ?>">
        <?php if ($criteria): ?>
            <?php foreach ($criteria as $criterion): ?>
                <div class="form-group">
                    <label><?php echo esc_html($criterion['name']); ?></label>
                    <div class="rating" data-criterion-id="<?php echo esc_attr($criterion['id']); ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star" data-rating="<?php echo $i; ?>">&#9733;</span>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="form-group">
            <label for="feedback-text">Feedback opțional:</label>
            <textarea class="form-control" id="feedback-text" rows="3"></textarea>
        </div>
        <button type="button" class="btn btn-primary" onclick="submitFeedback()">Trimite Feedback</button>
    </form>

    <script>
        jQuery(document).ready(function($) {
            $('.rating .star').on('click', function() {
                $(this).siblings().removeClass('selected');
                $(this).addClass('selected').prevAll().addClass('selected');
            });
        });

        // AJAX
        function submitFeedback() {
            const feedbackData = {
                transfer_code: jQuery('#feedbackForm').data('transfer-code'),
                ratings: [],
                feedback: jQuery('#feedback-text').val()
            };

            jQuery('.rating').each(function() {
                const criterionId = jQuery(this).data('criterion-id');
                const rating = jQuery(this).find('.star.selected').length;
                feedbackData.ratings.push({
                    criterion_id: criterionId,
                    rating: rating
                });
            });

            jQuery.ajax({
                url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                type: 'POST',
                data: {
                    action: 'submit_feedback',
                    feedback_data: feedbackData
                },
                success: function(response) {
                    alert(response.message || 'Feedback trimis!');
                }
            });
        }
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('tf_feedback_form', 'tf_feedback_form_shortcode');

function tf_submit_feedback() {
    $feedback_data = $_POST['feedback_data'];

    $api_url = 'https://api.example.com/feedback';
    $response = wp_remote_post($api_url, [
        'method'    => 'POST',
        'body'      => json_encode($feedback_data),
        'headers'   => ['Content-Type' => 'application/json'],
    ]);

    if (is_wp_error($response)) {
        wp_send_json(['error' => 'Feedback-ul nu a putut fi trimis.']);
    } else {
        wp_send_json(['message' => 'Feedback trimis cu succes.']);
    }
}
add_action('wp_ajax_submit_feedback', 'tf_submit_feedback');
add_action('wp_ajax_nopriv_submit_feedback', 'tf_submit_feedback');

function tf_get_criteria() {
    $api_url = 'https://api.example.com/criteria';
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return [];
    } else {
        $criteria = json_decode(wp_remote_retrieve_body($response), true);
        return $criteria ?: [];
    }
}

function tf_thank_you_details_shortcode($atts) {
    $atts = shortcode_atts(['transfer_code' => ''], $atts, 'tf_thank_you_details');
    $transfer_code = esc_attr($atts['transfer_code']);

    // Dummy data
    $transfer_details = [
        'nume' => 'Ion',
        'prenume' => 'Popescu',
        'nr_zbor' => 'AB1234',
        'data_ora' => '2023-12-15 10:30',
        'preluare' => 'Aeroportul Otopeni',
        'destinatie' => 'Hotel Intercontinental'
    ];

    ob_start();
    if ($transfer_details): ?>
        <div class="container">
            <h3>Detalii Transfer</h3>
            <div class="row">
                <div class="col-md-6"><strong>Nume:</strong> <?php echo esc_html($transfer_details['nume']); ?></div>
                <div class="col-md-6"><strong>Prenume:</strong> <?php echo esc_html($transfer_details['prenume']); ?></div>
                <div class="col-md-6"><strong>Zbor:</strong> <?php echo esc_html($transfer_details['nr_zbor']); ?></div>
                <div class="col-md-6"><strong>Data și Ora:</strong> <?php echo esc_html($transfer_details['data_ora']); ?></div>
                <div class="col-md-6"><strong>Preluare:</strong> <?php echo esc_html($transfer_details['preluare']); ?></div>
                <div class="col-md-6"><strong>Destinație:</strong> <?php echo esc_html($transfer_details['destinatie']); ?></div>
            </div>
        </div>
    <?php else: ?>
        <p>Detaliile transferului nu au fost găsite.</p>
    <?php
    endif;

    return ob_get_clean();
}
add_shortcode('tf_thank_you_details', 'tf_thank_you_details_shortcode');

?>
