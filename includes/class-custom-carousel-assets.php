<?php
namespace BlueberryFalls\Essentials;

class Custom_Carousel_Assets {
    public function __construct() {
//	    add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts'], 99999999999999);

	    add_action('wp_footer', [$this, 'add_footer_script'], 99999999999999);
    }

    public function add_footer_script() {
        ?>
        <script>
            <?php echo file_get_contents(BLUEBERRY_ESSENTIALS_PATH . 'assets/js/custom-carousel-ui.js'); ?>
        </script>
        <?php
    }

    public function enqueue_scripts() {
		wp_enqueue_script(
			'custom-carousel-ui',
			BLUEBERRY_ESSENTIALS_URL . 'assets/js/custom-carousel-ui.js',
			array('jquery'),
			BLUEBERRY_ESSENTIALS_VERSION,
			true,
		);
	}
}

// Initialize the class
new Custom_Carousel_Assets();
