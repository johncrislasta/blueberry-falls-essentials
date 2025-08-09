<?php
namespace BlueberryFalls\Essentials\Widgets;

/**
 * Listing Reviews Widget
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Listing_Reviews extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'listing_reviews';
    }

    public function get_title() {
        return __('Listing Reviews', 'blueberry-falls-essentials');
    }

    public function get_icon() {
        return 'eicon-testimonial';
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
            'layout',
            [
                'label' => __('Layout', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid' => __('Grid', 'blueberry-falls-essentials'),
                    'rows' => __('Rows', 'blueberry-falls-essentials'),
                ],
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Number of Reviews', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
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
                ],
                'selectors' => [
                    '{{WRAPPER}} .reviews-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
                'condition' => [
                    'layout' => 'grid',
                ],
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label' => __('Gap', 'blueberry-falls-essentials'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .reviews-grid' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .review-row + .review-row' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Get the current post's title to match with review_listing term
        $current_listing_title = get_the_title();
        
        $args = [
            'post_type' => 'review',
            'posts_per_page' => $settings['posts_per_page'],
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'review_listing',
                    'field'    => 'name',
                    'terms'    => $current_listing_title,
                ],
            ],
        ];

        if ($settings['layout'] === 'grid') {
            $args['post_parent'] = 0; // Only show parent reviews in grid
            $this->render_grid_layout($args);
        } else {
            $this->render_row_layout($args);
        }
    }

    protected function render_grid_layout($args) {
        $reviews = new \WP_Query($args);
        
        if (!$reviews->have_posts()) {
            echo '<p>' . __('No reviews found.', 'blueberry-falls-essentials') . '</p>';
            return;
        }

        echo '<div class="reviews-grid">';
        
        while ($reviews->have_posts()) {
            $reviews->the_post();
            $this->render_review_card(get_the_ID());
        }
        
        echo '</div>';
        wp_reset_postdata();
    }

    protected function render_row_layout($args) {
        $reviews = new \WP_Query($args);
        
        if (!$reviews->have_posts()) {
            echo '<p>' . __('No reviews found.', 'blueberry-falls-essentials') . '</p>';
            return;
        }

        echo '<div class="reviews-rows">';
        
        while ($reviews->have_posts()) {
            $reviews->the_post();
            echo '<div class="review-row">';
            $this->render_review_card(get_the_ID(), false);
            $this->render_responses(get_the_ID());
            echo '</div>';
        }
        
        echo '</div>';
        wp_reset_postdata();
    }

    protected function render_review_card($post_id, $show_excerpt = true) {
        $name = get_field('name', $post_id) ?: get_the_title($post_id);
        $address = get_field('address', $post_id) ?: '';
        $stars = get_field('stars', $post_id) ?: 0;
        $categories = get_the_terms($post_id, 'review_category');
        $category = !empty($categories) ? $categories[0]->name : '';
        $date = get_the_date('F Y', $post_id);
        $content = $show_excerpt 
            ? wp_trim_words(get_the_content(), 33, '...') 
            : get_the_content();
        ?>
        <article class="review-card">
            <header class="review-header">
                <div class="review-avatar">
                    <?php if (has_post_thumbnail($post_id)) : ?>
                        <?php echo get_the_post_thumbnail($post_id, 'thumbnail', ['class' => 'circle-image']); ?>
                    <?php endif; ?>
                </div>
                <div class="review-meta">
                    <h3 class="review-name"><?php echo esc_html($name); ?></h3>
                    <?php if ($address) : ?>
                        <div class="review-address"><?php echo esc_html($address); ?></div>
                    <?php endif; ?>
                </div>
            </header>
            
            <div class="review-details">
                <?php if ($stars) : ?>
                <div class="review-rating">
                    <strong><?php echo esc_html($stars); ?> <span class="star active"></span></strong>
                    <span class="review-meta-separator">·</span>
                    <span class="review-date"><?php echo esc_html($date); ?></span>
                    <?php if ($category) : ?>
                        <span class="review-meta-separator">·</span>
                        <span class="review-category"><?php echo esc_html($category); ?></span>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="review-meta-row">
                    <span class="review-date"><?php echo esc_html($date); ?></span>
                    <?php if ($category) : ?>
                        <span class="review-meta-separator">·</span>
                        <span class="review-category"><?php echo esc_html($category); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="review-content">
                    <?php echo wp_kses_post($content); ?>
                </div>
                
                <?php if ($show_excerpt) : ?>
                    <a href="#" class="show-all-reviews"><?php _e('Show more', 'blueberry-falls-essentials'); ?></a>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }

    protected function render_responses($parent_id) {
        // Get the current listing's title to match with review_listing term
        $current_listing_title = get_the_title();
        
        $responses = new \WP_Query([
            'post_type' => 'review',
            'post_parent' => $parent_id,
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'ASC',
            'tax_query' => [
                [
                    'taxonomy' => 'review_listing',
                    'field'    => 'name',
                    'terms'    => $current_listing_title,
                ],
            ],
        ]);

        if (!$responses->have_posts()) {
            return;
        }

        echo '<div class="review-responses">';
        
        while ($responses->have_posts()) {
            $responses->the_post();
            $this->render_response_card(get_the_ID());
        }
        
        echo '</div>';
        wp_reset_postdata();
    }

    protected function render_response_card($post_id) {
        $name = get_field('name', $post_id) ?: get_the_title($post_id);
        $date = get_the_date('F Y', $post_id);
        ?>
        <article class="review-response">
            <header class="response-header">
                <div class="response-avatar">
                    <?php if (has_post_thumbnail($post_id)) : ?>
                        <?php echo get_the_post_thumbnail($post_id, 'thumbnail', ['class' => 'circle-image small']); ?>
                    <?php endif; ?>
                </div>
                <div class="response-meta">
                    <h4 class="response-name"><?php echo esc_html($name); ?></h4>
                    <span class="response-date"><?php echo esc_html($date); ?></span>
                </div>
            </header>
            <div class="response-content">
                <?php the_content(); ?>
            </div>
        </article>
        <?php
    }
}

// Register the widget
function register_listing_reviews_widget($widgets_manager) {
    $widgets_manager->register(new Listing_Reviews());
}

add_action('elementor/widgets/register', 'BlueberryFalls\Essentials\Widgets\register_listing_reviews_widget');