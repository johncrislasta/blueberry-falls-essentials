<?php
namespace BlueberryFalls\Essentials;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class Instagram_Assets {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_init', [$this, 'add_settings']);
    }

    public function enqueue_scripts() {
        wp_enqueue_style('blueberry-instagram-carousel', BLUEBERRY_ESSENTIALS_URL . 'assets/css/instagram-carousel.css', [], BLUEBERRY_ESSENTIALS_VERSION);
        
        // Enqueue Swiper CSS and JS
//        wp_enqueue_style('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.css', [], '8.4.2');
//        wp_enqueue_script('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.js', [], '8.4.2', true);
        
        wp_enqueue_script('blueberry-instagram-carousel', BLUEBERRY_ESSENTIALS_URL . 'assets/js/instagram-carousel.js', ['jquery', 'swiper'], BLUEBERRY_ESSENTIALS_VERSION, true);
        
        // Pass Instagram access token to JavaScript
        wp_localize_script('blueberry-instagram-carousel', 'blueberry_instagram', [
            'access_token' => get_option('blueberry_instagram_access_token', '')
        ]);
    }

    public function add_settings() {
        add_option('blueberry_instagram_access_token', '');
        
        add_settings_section(
            'blueberry_instagram_section',
            'Instagram Settings',
            '__return_empty_string',
            'blueberry-falls-settings'
        );
        
        add_settings_field(
            'blueberry_instagram_access_token',
            'Instagram Access Token',
            [$this, 'instagram_token_field'],
            'blueberry-falls-settings',
            'blueberry_instagram_section'
        );
        
        register_setting('blueberry-falls-settings', 'blueberry_instagram_access_token');
    }

    public function instagram_token_field() {
        $value = get_option('blueberry_instagram_access_token', '');
        echo '<input type="text" name="blueberry_instagram_access_token" value="' . esc_attr($value) . '" class="regular-text" />
              <p class="description">Get your Instagram access token from the Instagram Graph API.</p>';
    }
}

// Initialize the class
new Instagram_Assets();
