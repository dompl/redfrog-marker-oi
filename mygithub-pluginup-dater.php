<?php
/**
 *
 * MyGitHubPluginUpdater class is responsible for checking updates for a plugin hosted on GitHub.
 *
 * @package MyGitHubPluginUpdater
 */

if (  !  class_exists( 'MyGitHubPluginUpdater' ) ) {

    class MyGitHubPluginUpdater {

        /**
         * @var mixed
         */
        private $slug; // Plugin slug
        /**
         * @var mixed
         */
        private $pluginData; // Plugin data
        /**
         * @var mixed
         */
        private $username; // GitHub username
        /**
         * @var mixed
         */
        private $repo; // GitHub repo name
        /**
         * @var mixed
         */
        private $pluginFile; // __FILE__ of the main plugin file

        /**
         * @param $pluginFile
         * @param $gitHubUsername
         * @param $gitHubProjectName
         */
        function __construct( $pluginFile, $gitHubUsername, $gitHubProjectName ) {

            add_filter( 'pre_set_site_transient_update_plugins', [$this, 'setTransient'] );

            $this->pluginFile = $pluginFile;
            $this->username   = $gitHubUsername;
            $this->repo       = $gitHubProjectName;

            $this->slug = plugin_basename( $pluginFile );
        }

        // Get information regarding the latest version of the plugin from GitHub.
        private function getRepoReleaseInfo() {
            // Fetch releases from the GitHub API
            $url         = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases";
            $apiResponse = wp_remote_retrieve_body( wp_remote_get( $url ) );

            if ( is_wp_error( $apiResponse ) ) {
                return false;
            }

            $apiResponse = json_decode( $apiResponse );

            // Use only the latest release
            if ( is_array( $apiResponse ) ) {
                $apiResponse = $apiResponse[0];
            }

            return $apiResponse;
        }

        // Push in plugin version information to get the update notification
        /**
         * @param $transient
         * @return mixed
         */
        public function setTransient( $transient ) {

            if ( property_exists( $transient, 'checked' ) ) {

                $checked          = $transient->checked;
                $this->pluginData = get_plugin_data( WP_PLUGIN_DIR . '/' . $this->slug );

                $apiResponse = $this->getRepoReleaseInfo();

                if ( is_object( $apiResponse ) ) {

                    $newVer = $apiResponse->tag_name;
                    $oldVer = $this->pluginData['Version'];

                    if ( version_compare( $newVer, $oldVer, '>' ) ) {
                        $transient->response[$this->slug] = (object) [
                            'slug'        => $this->slug,
                            'new_version' => $newVer,
                            'package'     => $apiResponse->zipball_url,
                            'url'         => $this->pluginData['PluginURI']
                        ];
                    }
                }
            }

            return $transient;
        }}

}