<?php
namespace BlueberryFalls\Essentials;

/**
 * Class to handle Instagram OAuth flow
 */
final class Instagram_OAuth {
    private $redirect_url;
    private $client_id;
    private $client_secret;
    private $oauth_url = 'https://api.instagram.com/oauth/authorize';

    public function __construct() {
        // Add OAuth endpoint
        add_action('rest_api_init', [$this, 'register_routes']);

        // Add settings for OAuth
        add_action('admin_init', [$this, 'add_oauth_settings']);

        // Add admin notices
        add_action('admin_notices', [$this, 'admin_notices']);

        // Initialize redirect URL
        $this->redirect_url = get_rest_url(null, 'blueberry/v1/instagram/oauth/callback');

        // Get client credentials from settings
        $this->client_id = get_option('blueberry_instagram_client_id', '');
        $this->client_secret = get_option('blueberry_instagram_client_secret', '');
    }

    public function register_routes() {
        // OAuth callback endpoint
        register_rest_route('blueberry/v1', '/instagram/oauth/callback', [
            [
                'methods' => ['GET', 'POST'],  // Accept both GET and POST
                'callback' => [$this, 'handle_oauth_callback'],
                'permission_callback' => '__return_true',
            ],
        ]);
    }

    public function get_authorization_url() {
        if (empty($this->client_id)) {
            return false;
        }

        $base_url = 'https://www.instagram.com/oauth/authorize';
        $redirect_uri = urlencode($this->redirect_url);
        $scopes = urlencode(implode(',', [
            'instagram_business_basic',
            'instagram_business_manage_messages',
            'instagram_business_manage_comments',
            'instagram_business_content_publish',
            'instagram_business_manage_insights'
        ]));

        $args = [
            'force_reauth' => true,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_url,
            'response_type' => 'code',
            'scope' => $scopes,
            // 'state' => wp_create_nonce('instagram_oauth_nonce')
        ];

        return add_query_arg($args, $base_url);
    }
	public function handle_oauth_callback($request) {
		// Check for error response first
		if ($request->get_param('error')) {
			$error_msg = 'Authorization failed: ' . $request->get_param('error_description') ?? 'User denied the request';
			set_transient('instagram_oauth_error', $error_msg, 45);
			wp_redirect(admin_url('admin.php?page=blueberry-falls-settings'));
			exit;
		}

		// Get authorization code
		$code = $request->get_param('code');
		if (empty($code)) {
			set_transient('instagram_oauth_error', 'No authorization code provided', 45);
			wp_redirect(admin_url('admin.php?page=blueberry-falls-settings'));
			exit;
		}

		// Exchange code for short-lived token
		$token_response = wp_remote_post('https://api.instagram.com/oauth/access_token', [
			'body' => [
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'grant_type' => 'authorization_code',
				'redirect_uri' => $this->redirect_url,
				'code' => $code,
			],
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
		]);

		if (is_wp_error($token_response)) {
			set_transient('instagram_oauth_error', 'Error exchanging code: ' . $token_response->get_error_message(), 45);
			wp_redirect(admin_url('admin.php?page=blueberry-falls-settings'));
			exit;
		}

		$token_data = json_decode(wp_remote_retrieve_body($token_response), true);

		// Check for token in response
		if (empty($token_data['access_token'])) {
			$error_msg = 'Failed to get access token. ';
			if (!empty($token_data['error_message'])) {
				$error_msg .= $token_data['error_message'];
			}
			set_transient('instagram_oauth_error', $error_msg, 45);
			wp_redirect(admin_url('admin.php?page=blueberry-falls-settings'));
			exit;
		}

		// Save the short-lived token
		$short_lived_token = $token_data['access_token'];
		update_option('blueberry_instagram_short_token', $short_lived_token);

		// Now exchange for long-lived token
		$long_token_url = add_query_arg([
			'grant_type' => 'ig_exchange_token',
			'client_secret' => $this->client_secret,
			'access_token' => $short_lived_token,
		], 'https://graph.instagram.com/access_token');

		$long_token_response = wp_remote_get($long_token_url);

		if (!is_wp_error($long_token_response)) {
			$long_token_data = json_decode(wp_remote_retrieve_body($long_token_response), true);
			if (!empty($long_token_data['access_token'])) {
				// Save the long-lived token
				update_option('blueberry_instagram_access_token', $long_token_data['access_token']);
				set_transient('instagram_oauth_success', 'Successfully connected to Instagram!', 45);
				wp_redirect(admin_url('admin.php?page=blueberry-falls-settings'));
				exit;
			}
		}

		// If we got here, something went wrong with the long-lived token
		set_transient('instagram_oauth_error', 'Connected but failed to get long-lived token. You may need to reconnect later.', 45);
		wp_redirect(admin_url('admin.php?page=blueberry-falls-settings'));
		exit;
	}

    public function admin_notices() {
        if ($error = get_transient('instagram_oauth_error')) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html($error); ?></p>
            </div>
            <?php
            delete_transient('instagram_oauth_error');
        }

        if ($success = get_transient('instagram_oauth_success')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($success); ?></p>
            </div>
            <?php
            delete_transient('instagram_oauth_success');
        }
    }

    public function add_oauth_settings() {
        add_settings_section(
            'blueberry_instagram_oauth_section',
            'Instagram OAuth Settings',
            [$this, 'oauth_section_callback'],
            'blueberry-falls-settings'
        );

        add_settings_field(
            'blueberry_instagram_client_id',
            'Client ID',
            [$this, 'client_id_field'],
            'blueberry-falls-settings',
            'blueberry_instagram_oauth_section'
        );

        add_settings_field(
            'blueberry_instagram_client_secret',
            'Client Secret',
            [$this, 'client_secret_field'],
            'blueberry-falls-settings',
            'blueberry_instagram_oauth_section'
        );

        register_setting('blueberry-falls-settings', 'blueberry_instagram_client_id', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        register_setting('blueberry-falls-settings', 'blueberry_instagram_client_secret', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
    }

    public function oauth_section_callback() {
        $auth_url = $this->get_authorization_url();
        $access_token = get_option('blueberry_instagram_access_token', '');

        if (!empty($access_token)) {
            echo '<div class="notice notice-success inline"><p>✅ Connected to Instagram</p></div>';
        }

        if ($auth_url) {

            // https://www.instagram.com/accounts/login/?force_authentication&platform_app_id=1733445047546429&next=%2Foauth%2Fauthorize%2Fthird_party%2F%3Fredirect_uri%3Dhttps%253A%252F%252Fwww.blueberryfalls.com%252Fwp-json%252Fblueberry%252Fv1%252Finstagram%252Foauth%252Fcallback%26response_type%3Dcode%26scope%3Dinstagram_business_basic%252Cinstagram_business_manage_messages%252Cinstagram_business_manage_comments%252Cinstagram_business_content_publish%252Cinstagram_business_manage_insights%26client_id%3D1733445047546429%26logger_id%3D597601ee-1a81-411d-8b56-d017ba34a304&enable_fb_login&flo=true
            echo '<p><a href="' . esc_url($auth_url) . '" class="button button-primary">' .
                 (!empty($access_token) ? 'Reconnect with Instagram' : 'Connect with Instagram') .
                 '</a></p>';
        } else {
            echo '<p>Please enter your Client ID and Client Secret to connect with Instagram.</p>';
        }
    }

    public function client_id_field() {
        $client_id = get_option('blueberry_instagram_client_id', '');
        echo '<input type="text" name="blueberry_instagram_client_id" value="' . esc_attr($client_id) . '" class="regular-text" />
              <p class="description">Get this from your <a href="https://developers.facebook.com/apps/" target="_blank">Facebook Developer Console</a> under App Settings > Basic</p>';
    }

    public function client_secret_field() {
        $client_secret = get_option('blueberry_instagram_client_secret', '');
        echo '<input type="password" name="blueberry_instagram_client_secret" value="' . esc_attr($client_secret) . '" class="regular-text" />
              <p class="description">Get this from your <a href="https://developers.facebook.com/apps/" target="_blank">Facebook Developer Console</a> under App Settings > Basic</p>';
    }
}

// Initialize the OAuth class
new Instagram_OAuth();

// https://www.instagram.com/oauth/authorize?force_reauth=true&client_id=1733445047546429&redirect_uri=https://www.blueberryfalls.com/wp-json/blueberry/v1/instagram/oauth/callback&response_type=code&scope=instagram_business_basic%2Cinstagram_business_manage_messages%2Cinstagram_business_manage_comments%2Cinstagram_business_content_publish%2Cinstagram_business_manage_insights&state=5de079b495
// https://www.instagram.com/oauth/authorize?force_reauth=true&client_id=1733445047546429&redirect_uri=https://www.blueberryfalls.com/wp-json/blueberry/v1/instagram/oauth/callback&response_type=code&scope=instagram_business_basic%2Cinstagram_business_manage_messages%2Cinstagram_business_manage_comments%2Cinstagram_business_content_publish%2Cinstagram_business_manage_insights
// https://www.instagram.com/oauth/authorize?force_reauth=1&client_id=1733445047546429&redirect_uri=https://www.blueberryfalls.com/wp-json/blueberry/v1/instagram/oauth/callback&response_type=code&scope=instagram_business_basic%2Cinstagram_business_manage_messages%2Cinstagram_business_manage_comments%2Cinstagram_business_content_publish%2Cinstagram_business_manage_insights

// https://www.blueberryfalls.com/wp-json/blueberry/v1/instagram/oauth/callback?code=AQBhgqu-PEt72abXftLQidyy7ntZEkrjs2-wZJapMIdkXXPsL9ih0MwL3MI8Ykvttxrwv9MBh10tuC65Yzvo12tQZ65P7_Aw8hTKgZXmbyHK9cKYcYEqgg7oBhFEx5X56_9eR09q3wz7cS0NTr3wrsGFHj5WSkoHSeEyP1wwlLJ0yu1DtRDbuMkXeoQYA-z1BqG3_UWrLrCk2h1tGOt3nd2o6ImiXBK_kTPFO-W68p1O4g#_
// No route was found matching the URL and request method.