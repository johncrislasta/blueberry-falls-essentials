<?php
namespace BlueberryFalls\Essentials\Widgets;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Featured_Amenities extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'featured-amenities';
    }

    public function get_title() {
        return __('Featured Amenities', 'blueberry-falls-essentials');
    }

    public function get_icon() {
        return 'eicon-bullet-list';
    }

    public function get_categories() {
        return ['blueberry-elements'];
    }

    public function get_keywords() {
        return ['featured', 'amenities', 'listing', 'highlights'];
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
            'title',
            [
                'label' => __('Title', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Featured Amenities', 'blueberry-falls-essentials'),
                'placeholder' => __('Enter title', 'blueberry-falls-essentials'),
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

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .featured-amenities-list' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'blueberry-falls-essentials'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_heading',
            [
                'label' => __('Title', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', 'blueberry-falls-essentials'),
                'selector' => '{{WRAPPER}} .featured-amenities-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .featured-amenities-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'items_heading',
            [
                'label' => __('Items', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'item_typography',
                'label' => __('Typography', 'blueberry-falls-essentials'),
                'selector' => '{{WRAPPER}} .featured-amenity-item',
            ]
        );

        $this->add_control(
            'item_color',
            [
                'label' => __('Color', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .featured-amenity-item' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __('Icon Color', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .featured-amenity-item i' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_icons' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'column_gap',
            [
                'label' => __('Column Gap', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .featured-amenities-list' => 'grid-column-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'row_gap',
            [
                'label' => __('Row Gap', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .featured-amenities-list' => 'grid-row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if (!is_singular('listing')) {
            return;
        }

        $settings = $this->get_settings_for_display();
        $terms = get_field('featured_amenities');

        if (!$terms || !is_array($terms)) {
			echo '<p>' . esc_html__('No featured amenities found.', 'blueberry-falls-essentials') . '</p>';
            return;
        }
        
        // Add CSS for the grid
        $this->add_render_attribute('wrapper', 'class', 'featured-amenities-widget');
        $this->add_render_attribute('list', 'class', 'featured-amenities-list');
        ?>
        <div <?php echo $this->get_render_attribute_string('wrapper'); ?>>
            <?php if (!empty($settings['title'])) : ?>
                <h3 class="featured-amenities-title"><?php echo esc_html($settings['title']); ?></h3>
            <?php endif; ?>
            
            <ul <?php echo $this->get_render_attribute_string('list'); ?>>
                <?php foreach ($terms as $term) : 
                    $icon = get_field('icon', $term);
                    ?>
                    <li class="featured-amenity-item">
                        <?php if ($settings['show_icons'] === 'yes' && !empty($icon)) : ?>
                            <img class="featured-amenity-icon" src="<?php echo wp_kses_post($icon); ?>">
                        <?php endif; ?>
                        <span class="featured-amenity-name"><?php echo esc_html($term->name); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        view.addRenderAttribute('wrapper', 'class', 'featured-amenities-widget');
        view.addRenderAttribute('list', 'class', 'featured-amenities-list');
        
        // Default values for Elementor editor
        if (!settings.amenities) {
            settings.amenities = [
                { name: 'Amenity 1' },
                { name: 'Amenity 2' },
                { name: 'Amenity 3' },
            ];
        }
        #>
        <div {{{ view.getRenderAttributeString('wrapper') }}}>
            <# if (settings.title) { #>
                <h3 class="featured-amenities-title">{{{ settings.title }}}</h3>
            <# } #>
            
            <ul {{{ view.getRenderAttributeString('list') }}}>
                <# _.each(settings.amenities, function(amenity) { #>
                    <li class="featured-amenity-item">
                        <# if (settings.show_icons === 'yes' && amenity.icon) { #>
                            <span class="featured-amenity-icon">{{{ amenity.icon }}}</span>
                        <# } #>
                        <span class="featured-amenity-name">{{{ amenity.name }}}</span>
                    </li>
                <# }); #>
            </ul>
        </div>
        <?php
    }
}

// Register the widget
function register_featured_amenities_widget($widgets_manager) {
    $widgets_manager->register(new \BlueberryFalls\Essentials\Widgets\Featured_Amenities());
}

// Support both newer and older versions of Elementor
if (did_action('elementor/widgets/register')) {
    // Elementor >= 3.5.0
    add_action('elementor/widgets/register', 'BlueberryFalls\Essentials\Widgets\register_featured_amenities_widget');
} else {
    // Elementor < 3.5.0
    add_action('elementor/widgets/widgets_registered', 'BlueberryFalls\Essentials\Widgets\register_featured_amenities_widget');
}
