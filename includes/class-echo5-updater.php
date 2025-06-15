<?php
/**
 * Auto Updater Class
 *
 * @package Echo5_AI_Chatbot
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class Echo5_Updater {
    private $file;
    private $plugin;
    private $basename;
    private $active;
    private $github_response;
    private $github_repo = 'manuamarnath/echo5digital-chat-bot';
    private $authorize_token = ''; // Optional: GitHub token for private repos

    public function __construct($file) {
        $this->file = $file;
        add_action('admin_init', array($this, 'set_plugin_properties'));
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
        add_filter('upgrader_pre_download', array($this, 'download_package'), 10, 4);
        add_filter('upgrader_source_selection', array($this, 'after_install'), 10, 4);
        
        // Debug action
        add_action('admin_init', array($this, 'debug_check'));
    }

    public function debug_check() {
        if (isset($_GET['echo5_debug_update']) && current_user_can('manage_options')) {
            $debug_info = array(
                'plugin_data' => $this->plugin,
                'basename' => $this->basename,
                'github_response' => $this->get_repository_info(true)
            );
            error_log('Echo5 Update Debug: ' . print_r($debug_info, true));
            wp_die('<pre>' . print_r($debug_info, true) . '</pre>');
        }
    }

    public function set_plugin_properties() {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $this->plugin = get_plugin_data($this->file);
        $this->basename = plugin_basename($this->file);
        $this->active = is_plugin_active($this->basename);

        // Log plugin data
        error_log('Echo5 Plugin Data: ' . print_r($this->plugin, true));
    }

    private function get_repository_info($debug = false) {
        $args = array(
            'headers' => array(
                'User-Agent' => 'WordPress/' . get_bloginfo('version'),
                'Accept' => 'application/vnd.github.v3+json'
            ),
            'timeout' => 15
        );

        $request_uri = sprintf('https://api.github.com/repos/%s/releases/latest', $this->github_repo);
        
        // Log API request
        error_log('Echo5 GitHub API Request: ' . $request_uri);
        
        $response = wp_remote_get($request_uri, $args);

        if (is_wp_error($response)) {
            error_log('Echo5 GitHub API Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
        
        // Log API response
        error_log('Echo5 GitHub API Response Code: ' . $code);
        error_log('Echo5 GitHub API Response: ' . $body);

        if ($code === 200) {
            $data = json_decode($body);
            if ($debug) {
                return $data;
            }
            $this->github_response = $data;
            return $data;
        }

        return false;
    }

    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Log current check
        error_log('Echo5 Checking for updates...');
        error_log('Echo5 Current version: ' . $this->plugin['Version']);

        $remote_version = $this->get_repository_info();
        
        if ($remote_version) {
            $version = str_replace('v', '', $remote_version->tag_name);
            error_log('Echo5 Remote version: ' . $version);
            
            if (version_compare($version, $this->plugin['Version'], '>')) {
                error_log('Echo5 Update available!');
                
                $assets = $remote_version->assets;
                if (!empty($assets) && isset($assets[0])) {
                    $plugin = array(
                        'url' => $this->plugin["PluginURI"],
                        'slug' => dirname($this->basename),
                        'package' => $assets[0]->browser_download_url,
                        'new_version' => $version,
                        'requires' => '5.6',
                        'requires_php' => '7.4'
                    );
                    error_log('Echo5 Update package: ' . print_r($plugin, true));
                    $transient->response[$this->basename] = (object)$plugin;
                } else {
                    error_log('Echo5 No assets found in release!');
                }
            } else {
                error_log('Echo5 No update needed');
            }
        }

        return $transient;
    }

    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== dirname($this->basename)) {
            return $result;
        }

        $this->get_repository_info();

        if (!$this->github_response) {
            return $result;
        }

        return (object)array(
            'name' => $this->plugin["Name"],
            'slug' => dirname($this->basename),
            'version' => str_replace('v', '', $this->github_response->tag_name),
            'author' => $this->plugin["Author"],
            'author_profile' => $this->plugin["AuthorURI"],
            'last_updated' => $this->github_response->published_at,
            'homepage' => $this->plugin["PluginURI"],
            'short_description' => $this->plugin["Description"],
            'sections' => array(
                'description' => $this->plugin["Description"],
                'changelog' => nl2br($this->github_response->body)
            ),
            'download_link' => $this->github_response->assets[0]->browser_download_url
        );
    }

    public function download_package($reply, $package, $upgrader, $hook_extra) {
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->basename) {
            return $reply;
        }

        $upgrader->strings['downloading_package'] = __('Downloading update from GitHub...', 'echo5-ai-chatbot');
        
        return $reply;
    }

    public function after_install($source, $remote_source, $upgrader, $hook_extra) {
        global $wp_filesystem;

        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->basename) {
            return $source;
        }

        // Ensure proper file permissions
        $wp_filesystem->chmod($source, octdec(755), true);
        
        return $source;
    }
}
