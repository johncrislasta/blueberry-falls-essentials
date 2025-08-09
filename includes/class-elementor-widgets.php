<?php
namespace BlueberryFalls\Essentials;

/**
 * Register Elementor Widgets and Categories
 */
class Elementor_Widgets {

    private static $_instance = null;
    private $listing_amenities = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // Initialize Listing Amenities for shortcode support
        $this->listing_amenities = new Listing_Amenities();
        
        // Register category first
        add_action('elementor/elements/categories_registered', [$this, 'register_widget_categories']);
        // Then register widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    /**
     * Register custom widget categories
     */
    public function register_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'blueberry-elements',
            [
                'title' => __('Blueberry Elements', 'blueberry-falls-essentials'),
                'icon' => 'fa fa-plug',
            ]
        );
    }

    /**
     * Register all widgets
     */
    public function register_widgets($widgets_manager) {
        // Include Widget Files
        require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/widgets/class-listing-amenities.php';
        require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/widgets/class-listing-reviews-widget.php';
        require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/widgets/class-instagram-carousel.php';
        require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/widgets/class-featured-amenities-widget.php';

        // Register Widgets
        $widgets_to_register = [
            'Listing_Amenities_Widget',
            'Listing_Reviews_Widget',
            'Instagram_Carousel',
            'Featured_Amenities_Widget'
        ];

        foreach ($widgets_to_register as $widget_class) {
            if (class_exists($widget_class)) {
                $widgets_manager->register(new $widget_class());
            }
        }
    }
}

// Initialize the class
function blueberry_elementor_widgets_init() {
    // First include the Listing_Amenities class
    require_once BLUEBERRY_ESSENTIALS_PATH . 'includes/widgets/class-listing-amenities.php';
    
    // Then initialize the widgets
    if (class_exists('\\Elementor\\Plugin')) {
        Elementor_Widgets::instance();
    }
}
add_action('plugins_loaded', 'blueberry_elementor_widgets_init');
