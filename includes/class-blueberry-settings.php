<?php

namespace BlueberryFalls\Essentials;

/**
 * Class to handle Blueberry Falls settings page
 */
final class Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_page() {
        add_menu_page(
            'Blueberry Falls Settings',
            'Blueberry Falls',
            'manage_options',
            'blueberry-falls-settings',
            [$this, 'render_settings_page'],
            'dashicons-admin-generic',
            100
        );
    }

    public function register_settings() {
        // Instagram settings are already registered in InstagramAssets class
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Blueberry Falls Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('blueberry-falls-settings');
                do_settings_sections('blueberry-falls-settings');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }
}

// Initialize the settings class
new Settings();
