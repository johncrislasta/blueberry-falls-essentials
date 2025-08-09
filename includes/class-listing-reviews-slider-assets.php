<?php
namespace BlueberryFalls\Essentials;

if (!defined('ABSPATH')) {
    exit;
}

class Listing_Reviews_Slider_Assets {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        
        // Enqueue our custom CSS
        wp_enqueue_style(
            'listing-reviews-slider',
            BLUEBERRY_ESSENTIALS_URL . 'assets/css/listing-reviews-slider.css',
            [],
            BLUEBERRY_ESSENTIALS_VERSION
        );

        // Enqueue our custom JS
        wp_enqueue_script(
            'listing-reviews-slider',
            BLUEBERRY_ESSENTIALS_URL . 'assets/js/listing-reviews-slider.js',
            ['jquery'],
            BLUEBERRY_ESSENTIALS_VERSION,
            true
        );
    }
}

new Listing_Reviews_Slider_Assets();
