<?php
namespace BlueberryFalls\Essentials;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Listing_Calendar_Metabox {
    private $post_type = 'listing';
    private $meta_key = 'listing_calendar';

    public function __construct() {
        add_action('init', [$this, 'register_meta']);
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('save_post_' . $this->post_type, [$this, 'save_meta']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }

    public function register_meta() {
        register_post_meta('listing', $this->meta_key, [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'integer',
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
    }

    public function add_meta_box() {
        add_meta_box(
            'listing_calendar',
            __('Listing Calendar', 'blueberry-falls-essentials'),
            [$this, 'render_meta_box'],
            $this->post_type,
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('listing_calendar_nonce', 'listing_calendar_nonce');
        
        $selected_calendar = get_post_meta($post->ID, $this->meta_key, true);
        $calendars = $this->get_active_calendars();

        ?>
        <div id="listing-calendar-container">
            <p>Select the calendar for this listing:</p>
            <select id="<?php echo esc_attr($this->meta_key); ?>" name="<?php echo esc_attr($this->meta_key); ?>">
                <option value="">Select a Calendar</option>
                <?php foreach ($calendars as $calendar_id => $calendar_name) : ?>
                    <option value="<?php echo esc_attr($calendar_id); ?>" 
                            <?php selected($selected_calendar, $calendar_id); ?>>
                        <?php echo esc_html($calendar_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    private function get_active_calendars() {
        global $wpdb;
        
        $calendars = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, name FROM {$wpdb->prefix}wpbs_calendars WHERE status = %s",
                'active'
            ),
            ARRAY_A
        );

        return array_column($calendars, 'name', 'id');
    }

    public function enqueue_scripts($hook) {
        global $post;
        
        if (!($hook == 'post.php' || $hook == 'post-new.php') || 
            !is_object($post) || $post->post_type !== $this->post_type) {
            return;
        }

        wp_enqueue_style('listing-calendar-style', plugins_url('assets/css/listing-calendar.css', BLUEBERRY_ESSENTIALS_FILE), array(), BLUEBERRY_ESSENTIALS_VERSION);
        wp_enqueue_script('listing-calendar-script', plugins_url('assets/js/listing-calendar.js', BLUEBERRY_ESSENTIALS_FILE), array('jquery', 'wp-data', 'wp-element', 'wp-api-fetch'), BLUEBERRY_ESSENTIALS_VERSION, true);

        wp_localize_script('listing-calendar-script', 'listingCalendar', [
            'nonce' => wp_create_nonce('wp_rest'),
            'meta_key' => $this->meta_key,
            'post_id' => $post->ID
        ]);
    }

    public function enqueue_frontend_scripts() {
        if (is_singular('listing')) {
            
            wp_enqueue_script(
                'listing-booking-calendar-frontend',
                BLUEBERRY_ESSENTIALS_URL . 'assets/js/listing-booking-calendar-frontend.js',
                ['jquery'],
                BLUEBERRY_ESSENTIALS_VERSION,
                true
            );
        }
    }

    public function save_meta($post_id) {
        if (!isset($_POST['listing_calendar_nonce']) || !wp_verify_nonce($_POST['listing_calendar_nonce'], 'listing_calendar_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST[$this->meta_key])) {
            $calendar_id = absint($_POST[$this->meta_key]);
            update_post_meta($post_id, $this->meta_key, $calendar_id);
        }
    }
}

// Initialize the metabox
new Listing_Calendar_Metabox();
