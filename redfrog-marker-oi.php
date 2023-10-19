<?php
/**
 * Plugin Name:       MarkerIO Integration
 * Plugin URI:        https://redfrogstudio.co.uk/markerio-plugin
 * Description:       This plugin integrates MarkerIO functionality into WordPress.
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Dom Kapelewski
 * Author URI:        https://yourwebsite.com
 * Text Domain:       markerio-integration
 * License:           GNU v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once plugin_dir_path( __FILE__ ) . 'updates-checker.php';

$myUpdateChecker = new MyGitHubPluginUpdater( __FILE__, 'dompl', pathinfo( wp_basename( __FILE__ ), PATHINFO_FILENAME ) );

class KsMarkerIoIntegration {

    public function __construct() {
        add_action( 'init', [$this, 'MarkerIosetBugCookie'] );
        add_action( 'wp_head', [$this, 'MarkerIooutputHtml'] );
        add_action( 'admin_init', [$this, 'register_markerio_settings'] );
    }

    public function MarkerIosetBugCookie() {
        if ( get_option( 'ks_marker_io' ) && isset( $_GET['bug'] ) && !  isset( $_COOKIE['bug'] ) ) {
            setcookie( 'bug', 1, time() + ( 86400 * 30 ), '/' );
        }
    }

    public function MarkerIooutputHtml() {
        $markerIOKey = get_option( 'ks_marker_io' );
        if ( $markerIOKey && isset( $_COOKIE['bug'] ) || isset( $_GET['bug'] ) ) {
            $html = '<script>window.markerConfig = {project: \'' . $markerIOKey . '\',source: \'snippet\'};</script>';
            $html .= '<script>!function(e,r,a){if(!e.__Marker){e.__Marker={};var t=[],n={__cs:t};["show","hide","isVisible","capture","cancelCapture","unload","reload","isExtensionInstalled","setReporter","setCustomData","on","off"].forEach(function(e){n[e]=function(){var r=Array.prototype.slice.call(arguments);r.unshift(e),t.push(r)}}),e.Marker=n;var s=r.createElement("script");s.async=1,s.src="https://edge.marker.io/latest/shim.js";var i=r.getElementsByTagName("script")[0];i.parentNode.insertBefore(s,i)}}(window,document);</script>';
            echo $html;
        }
    }

    public function register_markerio_settings() {
        register_setting( 'general', 'ks_marker_io', 'esc_attr' );

        add_settings_field(
            'ks_marker_io',
            '<label for="ks_marker_io">Marker IO Key</label>',
            [$this, 'marker_io_html_field'],
            'general'
        );
    }

    public function marker_io_html_field() {
        $value = get_option( 'ks_marker_io', '' );
        echo '<input type="text" id="ks_marker_io" name="ks_marker_io" value="' . esc_attr( $value ) . '" />';
    }
}

// Initialize your plugin
new KsMarkerIoIntegration();