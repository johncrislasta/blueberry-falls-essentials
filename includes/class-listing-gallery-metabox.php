<?php
namespace BlueberryFalls\Essentials;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Listing_Gallery_Metabox {
    private $post_type = 'listing';
    private $meta_key = '_listing_gallery';

    public function __construct() {
        add_action('init', [$this, 'register_meta']);
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('save_post_' . $this->post_type, [$this, 'save_meta']);
    }

    public function register_meta() {
        register_post_meta('listing', $this->meta_key, [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'sanitize_callback' => function($value) {
                // Ensure we get an array of integers
                if (empty($value)) {
                    return '';
                }
                $ids = array_map('intval', explode(',', $value));
                return implode(',', array_filter($ids));
            },
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
    }

    public function add_meta_box() {
        add_meta_box(
            'listing_gallery',
            __('Listing Gallery', 'blueberry-falls-essentials'),
            [$this, 'render_meta_box'],
            $this->post_type,
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('listing_gallery_nonce', 'listing_gallery_nonce');

        $gallery_data = get_post_meta($post->ID, $this->meta_key, true);

        // Handle both string and array formats
        if (is_array($gallery_data)) {
            $gallery = $gallery_data;
        } else {
            $gallery = !empty($gallery_data) ? explode(',', $gallery_data) : array();
        }

        ?>
        <div id="listing-gallery-container">
            <div id="gallery-images">
                <?php foreach ($gallery as $attachment_id) :
                    $image = wp_get_attachment_image_src($attachment_id, 'thumbnail');
                    if ( ! $image ) continue;
                    ?>
                    <div class="gallery-item">
                        <?php
                        echo '<img src="' . esc_url($image[0]) . '" alt="Gallery Image">';
                        ?>
                        <button type="button" class="remove-image" data-id="<?php echo esc_attr($attachment_id); ?>">—</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" id="<?php echo esc_attr($this->meta_key); ?>" name="<?php echo esc_attr($this->meta_key); ?>" value="<?php echo esc_attr(is_array($gallery) ? implode(',', $gallery) : $gallery); ?>">
            <button type="button" id="add-gallery-image" class="button">Add Image</button>
        </div>
        <?php
    }

    public function save_meta($post_id) {
        if (!isset($_POST['listing_gallery_nonce']) || !wp_verify_nonce($_POST['listing_gallery_nonce'], 'listing_gallery_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST[$this->meta_key])) {
            $gallery_data = sanitize_text_field($_POST[$this->meta_key]);
            // Ensure we only save valid attachment IDs
            $ids = array_map('intval', explode(',', $gallery_data));
            $sanitized_data = implode(',', array_filter($ids));
            update_post_meta($post_id, $this->meta_key, $sanitized_data);
        }
    }

    public function enqueue_scripts($hook) {
        global $post;

        // Only enqueue on post edit screens for our post type
        if (!($hook == 'post.php' || $hook == 'post-new.php') ||
            !is_object($post) || $post->post_type !== $this->post_type) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style('listing-gallery-style', plugins_url('assets/css/listing-gallery.css', BLUEBERRY_ESSENTIALS_FILE), array(), BLUEBERRY_ESSENTIALS_VERSION);
        wp_enqueue_script('listing-gallery-script', plugins_url('assets/js/listing-gallery.js', BLUEBERRY_ESSENTIALS_FILE), array('jquery', 'wp-data', 'wp-element', 'wp-api-fetch', 'jquery-ui-core', 'jquery-ui-sortable'), BLUEBERRY_ESSENTIALS_VERSION, true);

        wp_localize_script('listing-gallery-script', 'listingGallery', [
            'nonce' => wp_create_nonce('wp_rest'),
            'meta_key' => $this->meta_key,
            'post_id' => $post->ID
        ]);
    }
}

// Initialize the metabox
new Listing_Gallery_Metabox();
