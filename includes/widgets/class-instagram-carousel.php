<?php
namespace BlueberryFalls\Essentials\Widgets;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if Elementor is installed
if (!defined('ELEMENTOR_PATH')) {
    die('elementor path is not defined');
    return;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Core\Schemes\Color;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Plugin;

// Register Blueberry Elements category if it doesn't exist
function register_blueberry_elementor_category($elements_manager) {
    $elements_manager->add_category(
        'blueberry-elements',
        [
            'title' => __('Blueberry Elements', 'blueberry-falls-essentials'),
            'icon' => 'fa fa-plug',
        ]
    );
}
add_action('elementor/elements/categories_registered', 'BlueberryFalls\Essentials\Widgets\register_blueberry_elementor_category');

final class Instagram_Carousel extends Widget_Base {
    
    public function get_name() {
        return 'instagram-carousel';
    }

    public function get_title() {
        return __('Instagram Carousel', 'blueberry-falls-essentials');
    }

    public function get_icon() {
        return 'eicon-instagram';
    }

    public function get_categories() {
        return ['blueberry-elements'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Content', 'blueberry-falls-essentials'),
            ]
        );

        $this->add_control(
            'number_of_posts',
            [
                'label' => __('Number of Posts', 'blueberry-falls-essentials'),
                'type' => Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 20,
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'blueberry-falls-essentials'),
                'type' => Controls_Manager::SELECT,
                'default' => '4',
                'options' => [
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                    '5' => '5 Columns',
                ],
            ]
        );

        $this->add_control(
            'show_caption',
            [
                'label' => __('Show Caption', 'blueberry-falls-essentials'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Style', 'blueberry-falls-essentials'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'thumbnail',
                'default' => 'medium',
                'separator' => 'none',
            ]
        );

        $this->add_responsive_control(
            'spacing',
            [
                'label' => __('Spacing', 'blueberry-falls-essentials'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .instagram-carousel' => 'grid-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute('wrapper', [
			'class' => 'instagram-carousel',
			'data-columns' => $settings['columns'],
			'data-number' => $settings['number_of_posts'],
			'data-show-caption' => $settings['show_caption'] ? 'yes' : 'no'
		]);

		$this->add_render_attribute('carousel', [
			'class' => ['instagram-carousel-container', 'swiper-container']
		]);

		?>
        <div <?php echo $this->get_render_attribute_string('wrapper'); ?>>
            <div <?php echo $this->get_render_attribute_string('carousel'); ?>>
                <div class="swiper-wrapper">
                    <div class="instagram-loading">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

    public function get_script_depends() {
        return [
            'swiper',
            'blueberry-instagram-carousel',
        ];
    }

    public function get_style_depends() {
        return [
            'blueberry-instagram-carousel',
        ];
    }
}

// Register the widget
function register_instagram_carousel($widgets_manager) {
    // Support both older and newer versions of Elementor
    if (method_exists($widgets_manager, 'register')) {
        $widgets_manager->register(new Instagram_Carousel());
    } else {
        // For Elementor < 3.5.0
        $widgets_manager->register_widget_type(new Instagram_Carousel());
    }
}

// Support both newer and older versions of Elementor
if (did_action('elementor/widgets/register')) {
    // Elementor >= 3.5.0
    add_action('elementor/widgets/register', 'BlueberryFalls\Essentials\Widgets\register_instagram_carousel');
} else {
    // Elementor < 3.5.0
    add_action('elementor/widgets/widgets_registered', 'BlueberryFalls\Essentials\Widgets\register_instagram_carousel');
}
