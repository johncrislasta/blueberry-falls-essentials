<?php
namespace BlueberryFalls\Essentials;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Listing_Reviews_Metabox {
    private $post_type = 'listing';
    private $meta_key = '_listing_reviews';

    public function __construct() {
        add_action('init', [$this, 'register_meta']);
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post', [$this, 'save_reviews']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts($hook) {
        if ($hook != 'post.php' && $hook != 'post-new.php') {
            return;
        }
    
        wp_enqueue_script(
            'listing-reviews',
            plugins_url('assets/js/listing-reviews.js', dirname(__FILE__)),
            ['jquery'],
            BLUEBERRY_ESSENTIALS_VERSION,
            true
        );
    
        wp_enqueue_style(
            'listing-reviews',
            plugins_url('assets/css/listing-reviews.css', dirname(__FILE__)),
            [],
            BLUEBERRY_ESSENTIALS_VERSION
        );
    }
    
    public function register_meta() {
        register_post_meta('listing', $this->meta_key, [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'array',
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
    }

    public function add_meta_box() {
        add_meta_box(
            'listing_reviews',
            __('Guest Reviews', 'blueberry-falls-essentials'),
            [$this, 'render_meta_box'],
            $this->post_type,
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('listing_reviews_nonce', 'listing_reviews_nonce');

        $reviews = get_post_meta($post->ID, $this->meta_key, true);
        if (!is_array($reviews)) {
            $reviews = [];
        }

        echo '<div id="listing-reviews-container">';
        echo '<div id="reviews-list">';

        foreach ($reviews as $index => $review) {
            $this->render_review_fields($index, $review);
        }

        echo '</div>';
        echo '<button type="button" id="add-review" class="button">Add Review</button>';
        echo '</div>';

        // Hidden template for new review fields
        echo '<div id="review-template" style="display: none;">';
        $this->render_review_fields('new', []);
        echo '</div>';
    }

    private function render_review_fields($index, $review) {
        $review_name = isset($review['name']) ? esc_attr($review['name']) : '';
        $review_text = isset($review['text']) ? esc_textarea($review['text']) : '';
        $review_rating = isset($review['rating']) ? (int)$review['rating'] : 5;

        echo '<div class="review-item" data-index="' . esc_attr($index) . '">';
        echo '<div class="review-fields">';
        echo '<div class="review-field">';
        echo '<label for="review-name-' . esc_attr($index) . '">Name:</label>';
        echo '<input type="text" id="review-name-' . esc_attr($index) . '" name="reviews[' . esc_attr($index) . '][name]" value="' . $review_name . '">';
        echo '</div>';

        echo '<div class="review-field">';
        echo '<label for="review-text-' . esc_attr($index) . '">Review:</label>';
        echo '<textarea id="review-text-' . esc_attr($index) . '" name="reviews[' . esc_attr($index) . '][text]">' . $review_text . '</textarea>';
        echo '</div>';

        echo '<div class="review-field">';
        echo '<label for="review-rating-' . esc_attr($index) . '">Rating:</label>';
        echo '<select id="review-rating-' . esc_attr($index) . '" name="reviews[' . esc_attr($index) . '][rating]">';
        for ($i = 1; $i <= 5; $i++) {
            echo '<option value="' . $i . '" ' . selected($review_rating, $i, false) . '>' . $i . ' stars</option>';
        }
        echo '</select>';
        echo '</div>';

        if ($index !== 'new') {
            echo '<button type="button" class="remove-review button button-small">Remove</button>';
        }
        echo '</div>';
        echo '</div>';
    }

    public function save_reviews($post_id) {
        if (!isset($_POST['listing_reviews_nonce']) || !wp_verify_nonce($_POST['listing_reviews_nonce'], 'listing_reviews_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['reviews'])) {
            $reviews = array_filter($_POST['reviews'], function($review) {
                return !empty($review['name']) && !empty($review['text']);
            });

            update_post_meta($post_id, $this->meta_key, $reviews);
        }
    }
}

// Initialize the metabox
new Listing_Reviews_Metabox();