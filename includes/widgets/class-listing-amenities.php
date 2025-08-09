<?php
namespace BlueberryFalls\Essentials\Widgets;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Listing_Amenities {
    private $taxonomy = 'listing_amenity';

    public function __construct() {
        add_shortcode('listing_amenities', array($this, 'render_amenities'));
        add_action('elementor/widgets/register', array($this, 'register_elementor_widget'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'listing-amenities',
            BLUEBERRY_ESSENTIALS_URL . 'assets/css/listing-amenities.css',
            array(),
            BLUEBERRY_ESSENTIALS_VERSION
        );
    }

    public function render_amenities($atts) {
        $atts = shortcode_atts(array(
            'category' => '', // If set, only show amenities from this category
            'columns' => '3', // Number of columns for grid layout
            'show_icons' => 'true', // Show icons
        ), $atts);

        global $post;
        
        // Get all terms for the current post
        $terms = get_the_terms($post->ID, $this->taxonomy);
        
        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        // Group amenities by category
        $amenities_by_category = array();
        foreach ($terms as $term) {
            $category = get_field('category', $term);
            if (!$category) {
                $category = 'Uncategorized';
            }
            
            if (!isset($amenities_by_category[$category])) {
                $amenities_by_category[$category] = array();
            }
            
            $amenities_by_category[$category][] = array(
                'term' => $term,
                'icon' => get_field('icon', $term)
            );
        }

        // Sort categories alphabetically
        ksort($amenities_by_category);

        // Filter by category if specified
        if (!empty($atts['category']) && $atts['category'] !== 'all') {
            if (isset($amenities_by_category[$atts['category']])) {
                $amenities_by_category = array(
                    $atts['category'] => $amenities_by_category[$atts['category']]
                );
            } else {
                return ''; // Return empty if the specified category has no amenities
            }
        }

        ob_start();
        ?>
        <div class="listing-amenities-grid">
            <?php foreach ($amenities_by_category as $category => $amenities) : ?>
                <div class="amenity-category-section">
                    <h3 class="amenity-category-title"><?php echo esc_html($category); ?></h3>
                    <div class="amenity-items-grid" style="display: grid; grid-template-columns: repeat(<?php echo esc_attr($atts['columns']); ?>, 1fr); gap: 1rem;">
                        <?php foreach ($amenities as $amenity) : ?>
                            <div class="listing-amenity-item">
                                <?php if ($atts['show_icons'] === 'true' && $amenity['icon']) : ?>
                                    <img class="amenity-icon" src="<?php echo esc_attr($amenity['icon']); ?>"/>
                                <?php endif; ?>
                                <span class="amenity-name"><?php echo esc_html($amenity['term']->name); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function register_elementor_widget($widgets_manager) {
        if (!class_exists('Elementor\Widget_Base')) {
            return;
        }

        $widgets_manager->register(new BFE_Listing_Amenities_Widget());
    }
}

class BFE_Listing_Amenities_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'bfe-listing-amenities';
    }

    public function get_title() {
        return __('Listing Amenities', 'blueberry-falls-essentials');
    }

    public function get_icon() {
        return 'eicon-post-list';
    }

    public function get_categories() {
        return ['blueberry-elements'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'blueberry-falls-essentials'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'category',
            [
                'label' => __('Filter by Category', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_amenity_categories(),
                'default' => '',
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '1' => '1 Column',
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                ],
                'default' => '3',
            ]
        );

        $this->add_control(
            'show_icons',
            [
                'label' => __('Show Icons', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'blueberry-falls-essentials'),
                'label_off' => __('Hide', 'blueberry-falls-essentials'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'blueberry-falls-essentials'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 24,
                ],
                'selectors' => [
                    '{{WRAPPER}} .amenity-icon' => 'font-size: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .amenity-name, {{WRAPPER}} .amenity-category' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'category_title_heading',
            [
                'label' => __('Category Title', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'category_title_typography',
                'label' => __('Typography', 'blueberry-falls-essentials'),
                'selector' => '{{WRAPPER}} .amenity-category-title',
            ]
        );

        $this->add_control(
            'category_title_color',
            [
                'label' => __('Color', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .amenity-category-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'category_title_spacing',
            [
                'label' => __('Spacing', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .amenity-category-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $show_icons = $settings['show_icons'] === 'yes' ? 'true' : 'false';
        echo do_shortcode('[listing_amenities category="' . esc_attr($settings['category']) . '" columns="' . esc_attr($settings['columns']) . '" show_icons="' . $show_icons . '"]');
    }

    private function get_amenity_categories() {
        $terms = get_terms(array(
            'taxonomy' => 'listing_amenity',
            'fields' => 'ids',
            'hide_empty' => false,
        ));

        $categories = ['' => __('All Categories', 'blueberry-falls-essentials')];
        foreach ($terms as $term_id) {
            $category = get_field('category', $term_id);
            if ($category && !isset($categories[$category])) {
                $categories[$category] = $category;
            }
        }
        return $categories;
    }
}

// Initialize the class
new Listing_Amenities();
