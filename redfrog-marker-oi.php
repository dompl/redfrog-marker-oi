<?php
/**
 * Plugin Name:       MarkerIO Integration
 * Plugin URI:        https://redfrogstudio.co.uk/markerio-plugin
 * Description:       This plugin integrates MarkerIO functionality into WordPress.
 * Version:           1.0.7
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Dom Kapelewski
 * Author URI:        https://yourwebsite.com
 * Text Domain:       markerio-integration
 * License:           GNU v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once plugin_dir_path( __FILE__ ) . 'mygithub-pluginup-dater.php';

$myUpdateChecker = new MyGitHubPluginUpdater( __FILE__, 'dompl', pathinfo( wp_basename( __FILE__ ), PATHINFO_FILENAME ) );

/**
 * The KsMarkerIoIntegration class is responsible for integrating the Marker.io service into WordPress.
 * It sets up the necessary actions and filters to output the Marker.io script and handle bug cookies.
 *
 * @class KsMarkerIoIntegration
 * @package RedFrog_Marker_OI
 */
class KsMarkerIoIntegration {

    public function __construct() {
        add_action( 'init', [$this, 'MarkerIosetBugCookie'] );
        add_action( 'init', [$this, 'RemoveOldPluginHook'], 10 );
        add_action( 'wp_head', [$this, 'MarkerIooutputHtml'], 20 );
        add_action( 'admin_init', [$this, 'register_markerio_settings'] );
    }

    /**
     * Removes the old plugin hook if the function 'marker_io' exists.
     * This function removes the 'marker_io' action from the 'wp_head' hook.
     */
    public function RemoveOldPluginHook() {
        if ( function_exists( 'marker_io' ) ) {
            remove_action( 'wp_head', 'marker_io' );
        }
    }

    /**
     * Sets a cookie if the 'bug' parameter is present in the URL and the 'bug' cookie is not set.
     *
     * @return void
     */
    public function MarkerIosetBugCookie() {
        if ( get_option( 'ks_marker_io' ) && isset( $_GET['bug'] ) && !  isset( $_COOKIE['bug'] ) ) {
            setcookie( 'bug', 1, time() + ( 86400 * 30 ), '/' );
        }
    }

    /**
     * Outputs the Marker.io script if the 'bug' cookie or the 'bug' parameter is present in the URL.
     *
     * @return void
     */
    public function MarkerIooutputHtml() {
        $markerIOKey = get_option( 'ks_marker_io' );
        if ( $markerIOKey && isset( $_COOKIE['bug'] ) || isset( $_GET['bug'] ) ) {
            $html = "<script>window.markerConfig = {";
            $html .= "project:'{$markerIOKey}',
                      source: 'snippet'";

            if ( is_user_logged_in() ) {
                $current_user = wp_get_current_user();
                $email        = $current_user->user_email;
                $fullName     = $current_user->display_name;
                $html .= ", reporter: {
                            email: '$email',
                            fullName: '$fullName',
                        }";
            }

            $ipAddress = $_SERVER['REMOTE_ADDR'];
            $html .= ", customData: {
                        ipAddress: '$ipAddress',
                      }";

            $html .= "};</script>";
            $html .= '<script>!function(e,r,a){if(!e.__Marker){e.__Marker={};var t=[],n={__cs:t};["show","hide","isVisible","capture","cancelCapture","unload","reload","isExtensionInstalled","setReporter","setCustomData","on","off"].forEach(function(e){n[e]=function(){var r=Array.prototype.slice.call(arguments);r.unshift(e),t.push(r)}}),e.Marker=n;var s=r.createElement("script");s.async=1,s.src="https://edge.marker.io/latest/shim.js";var i=r.getElementsByTagName("script")[0];i.parentNode.insertBefore(s,i)}}(window,document);</script>';
            echo $html;
        }
    }

    /**
     * Registers the Marker.io settings in the General Settings page.
     *
     * @return void
     */
    public function register_markerio_settings() {
        register_setting( 'general', 'ks_marker_io', 'esc_attr' );

        add_settings_field(
            'ks_marker_io',
            '<label for="ks_marker_io">Marker IO Key</label>',
            [$this, 'marker_io_html_field'],
            'general'
        );
    }

    /**
     * Outputs the HTML for the Marker.io settings field.
     *
     * @return void
     */
    public function marker_io_html_field() {
        $value = get_option( 'ks_marker_io', '' );
        echo '<input type="text" id="ks_marker_io" name="ks_marker_io" value="' . esc_attr( $value ) . '" />';
    }
}

// Initialize your plugin
new KsMarkerIoIntegration();
