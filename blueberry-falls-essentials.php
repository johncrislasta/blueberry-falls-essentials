<?php
/**
 * Plugin Name: Blueberry Falls Essentials
 * Description: Registers Retreats Custom Post Type and core functionalities for Blueberry Falls LLC.
 * Version: 1.0.1
 * Author: JCYL.work
 */

namespace BlueberryFalls;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Define constants
define('BLUEBERRY_ESSENTIALS_FILE', __FILE__);
define('BLUEBERRY_ESSENTIALS_PATH', plugin_dir_path(__FILE__));
define('BLUEBERRY_ESSENTIALS_URL', plugin_dir_url(__FILE__));
define('BLUEBERRY_ESSENTIALS_VERSION', '1.0.1');

final class Essentials {
	
	public function __construct() {
		add_action('init', [$this, 'init_plugin']);
	}
	
	public function init_plugin() {
		// Check if Elementor is installed before loading Instagram carousel
		if (defined('ELEMENTOR_PATH')) {
			require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-instagram-assets.php';
			require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/widgets/class-listing-amenities.php';
			require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/widgets/class-listing-reviews.php';
			require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/widgets/class-instagram-carousel.php';
			require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/widgets/class-featured-amenities.php';
		}
		
		// Autoload Classes
		require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-listing-gallery-metabox.php';
		require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-listing-calendar-metabox.php';
		require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-wpbs-search-fields.php';
		require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-svg-uploader.php';
		require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-listing-reviews-metabox.php';
		require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-listing-reviews-slider-assets.php';
		require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-custom-carousel-assets.php';
		require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-blueberry-settings.php';
		require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-instagram-webhook.php';
		require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/class-instagram-oauth.php';
	}

}

// Initialize classes
new Essentials();
