<?php
namespace BlueberryFalls\Essentials;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SVG_Uploader {
    public function __construct() {
        add_filter('upload_mimes', [$this, 'enable_svg_uploads']);
    }

    public function enable_svg_uploads($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        return $mimes;
    }
}

new SVG_Uploader();