<?php
namespace BlueberryFalls\Essentials;

/**
 * Class to handle Instagram webhook functionality
 */
final class Instagram_Webhook {
    private $verify_token = 'blueberry_falls_2025';
    private $webhook_url;
    private $app_secret;

    public function __construct() {
        // Add webhook endpoint
        add_action('rest_api_init', [$this, 'register_routes']);
        
        // Add settings for webhook URL
        add_action('admin_init', [$this, 'add_webhook_settings']);
        
        // Get app secret from settings
        $this->app_secret = get_option('blueberry_instagram_app_secret', '');
    }

    public function register_routes() {
        register_rest_route('blueberry/v1', '/instagram/webhook', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'verify_webhook'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'handle_webhook'],
                'permission_callback' => '__return_true',
            ],
        ]);
    }

    public function verify_webhook($request) {
        $hub_mode = $request->get_param('hub_mode');
        $hub_challenge = $request->get_param('hub_challenge');
        $hub_verify_token = $request->get_param('hub_verify_token');

        if ($hub_mode === 'subscribe' && $hub_verify_token === $this->verify_token) {
            return new WP_REST_Response((int)$hub_challenge, 200);
        }

        return new WP_REST_Response('Invalid verification token', 403);
    }

    public function handle_webhook($request) {
        // Get the raw body
        $body = file_get_contents('php://input');
        
        // Verify the signature
        $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
        if (!$this->verify_signature($body, $signature)) {
            return new WP_REST_Response('Invalid signature', 403);
        }

        // Process the webhook data
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_REST_Response('Invalid JSON', 400);
        }

        // Log the webhook data for debugging
        error_log('Instagram Webhook Received: ' . print_r($data, true));

        // Process the webhook data as needed
        foreach ($data['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $this->process_change($change);
            }
        }

        return new WP_REST_Response('Webhook received', 200);
    }

    private function verify_signature($body, $signature) {
        if (empty($this->app_secret) || empty($signature)) {
            return false;
        }

        $expected_signature = 'sha256=' . hash_hmac('sha256', $body, $this->app_secret);
        return hash_equals($signature, $expected_signature);
    }

    private function process_change($change) {
        // Implement your webhook processing logic here
        // For now, just log the change
        error_log('Webhook Change: ' . print_r($change, true));
    }

    public function add_webhook_settings() {
        add_settings_section(
            'blueberry_instagram_webhook_section',
            'Instagram Webhook Settings',
            '__return_empty_string',
            'blueberry-falls-settings'
        );

        add_settings_field(
            'blueberry_instagram_webhook_url',
            'Webhook URL',
            [$this, 'webhook_url_field'],
            'blueberry-falls-settings',
            'blueberry_instagram_webhook_section'
        );

        add_settings_field(
            'blueberry_instagram_app_secret',
            'App Secret',
            [$this, 'app_secret_field'],
            'blueberry-falls-settings',
            'blueberry_instagram_webhook_section'
        );

        register_setting('blueberry-falls-settings', 'blueberry_instagram_webhook_url');
        register_setting('blueberry-falls-settings', 'blueberry_instagram_app_secret');
    }

    public function webhook_url_field() {
        $this->webhook_url = get_option('blueberry_instagram_webhook_url', '');
        
        // If not set, generate the webhook URL
        if (empty($this->webhook_url)) {
            $this->webhook_url = get_rest_url(null, 'blueberry/v1/instagram/webhook');
            update_option('blueberry_instagram_webhook_url', $this->webhook_url);
        }

        echo '<input type="text" name="blueberry_instagram_webhook_url" value="' . esc_attr($this->webhook_url) . '" class="regular-text" readonly />
              <p class="description">Copy this URL to configure your Instagram webhook in the Facebook Developer Console.</p>';
    }

    public function app_secret_field() {
        $app_secret = get_option('blueberry_instagram_app_secret', '');
        echo '<input type="password" name="blueberry_instagram_app_secret" value="' . esc_attr($app_secret) . '" class="regular-text" />
              <p class="description">Get this from your Facebook App Dashboard under Settings > Basic > App Secret</p>';
    }
}

// Initialize the webhook class
new Instagram_Webhook();
