<?php
namespace BlueberryFalls\Essentials;

/**
 * Class to handle WPBookingSystem search fields
 */
final class WPBS_Search_Fields {
    public function __construct() {
         add_filter('wpbs_search_widget_additional_fields', [$this, 'add_people_field']);
    }

    public function add_people_field($fields) {
        $fields[] = array(
            'name' => 'Guests',
            'type' => 'dropdown',
            'required' => true,
            'placeholder' => '',
            'values' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
            'validation' => function($value, $data) {
                if (!isset($data['calendar_id'])) {
                    return false;
                }

                // Get the listing ID associated with this calendar
                $listing_post = get_posts([
                    'meta_key' => 'listing_calendar',
                    'meta_value' => $data['calendar_id'],
                    'post_type' => 'listing',
                    'posts_per_page' => 1
                ]);

                if (empty($listing_post)) {
                    return false;
                }

                $listing_id = $listing_post[0]->ID;

                // Get the guest capacity from ACF fields
                $guests = get_field('listing_guests', $listing_id);
                $guests_plus = get_field('listing_guests_plus', $listing_id);

                if (!$guests) {
                    return false;
                }

                // If guests_plus is true, allow one extra guest
                $max_guests = $guests_plus ? $guests + 1 : $guests;

                return $value <= $max_guests;
            }
        );

        return $fields;
    }
}

// Initialize the class
new WPBS_Search_Fields();
