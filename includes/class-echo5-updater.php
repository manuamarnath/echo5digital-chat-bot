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

    public function __construct($file) {
        $this->file = $file;
        add_action('admin_init', array($this, 'set_plugin_properties'));
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
    }

    public function set_plugin_properties() {
        $this->plugin = get_plugin_data($this->file);
        $this->basename = plugin_basename($this->file);
        $this->active = is_plugin_active($this->basename);
    }

    private function get_repository_info() {
        if (is_null($this->github_response)) {
            $request_uri = sprintf('https://api.github.com/repos/%s/releases/latest', $this->github_repo);
            $response = wp_remote_get($request_uri);

            if (is_wp_error($response)) {
                return false;
            }

            $response = json_decode(wp_remote_retrieve_body($response));
            if ($response) {
                $this->github_response = $response;
            }
        }
    }

    public function check_update($transient) {
        if (property_exists($transient, 'checked')) {
            if ($checked = $transient->checked) {
                $this->get_repository_info();

                if ($this->github_response === false) {
                    return $transient;
                }

                $out_of_date = version_compare(
                    str_replace('v', '', $this->github_response->tag_name),
                    $this->plugin['Version'],
                    'gt'
                );

                if ($out_of_date) {
                    $plugin = array(
                        'url' => $this->plugin["PluginURI"],
                        'slug' => current(explode('/', $this->basename)),
                        'package' => $this->github_response->assets[0]->browser_download_url,
                        'new_version' => str_replace('v', '', $this->github_response->tag_name),
                        'icons' => array(
                            '1x' => plugin_dir_url($this->file) . 'images/bot-avatar.svg',
                            '2x' => plugin_dir_url($this->file) . 'images/bot-avatar.svg'
                        )
                    );

                    $transient->response[$this->basename] = (object) $plugin;
                }
            }
        }

        return $transient;
    }

    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!empty($args->slug)) {
            if ($args->slug == current(explode('/', $this->basename))) {
                $this->get_repository_info();

                return (object) array(
                    'name'              => $this->plugin["Name"],
                    'slug'              => $this->basename,
                    'version'           => str_replace('v', '', $this->github_response->tag_name),
                    'author'            => $this->plugin["AuthorName"],
                    'author_profile'    => $this->plugin["AuthorURI"],
                    'last_updated'      => $this->github_response->published_at,
                    'homepage'          => $this->plugin["PluginURI"],
                    'short_description' => $this->plugin["Description"],
                    'sections'          => array(
                        'Description'   => $this->plugin["Description"],
                        'Updates'       => $this->github_response->body,
                    ),
                    'download_link'     => $this->github_response->assets[0]->browser_download_url,
                    'banners'           => array(
                        'low'  => plugin_dir_url($this->file) . 'images/bot-avatar.svg',
                        'high' => plugin_dir_url($this->file) . 'images/bot-avatar.svg'
                    )
                );
            }
        }
        return $result;
    }

    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        $install_directory = plugin_dir_path($this->file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;

        if ($this->active) {
            activate_plugin($this->basename);
        }

        return $result;
    }
}
