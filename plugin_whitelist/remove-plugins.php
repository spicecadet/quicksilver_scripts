<?php
/** QuickSilver script to remove plugins which are listed on an allow list.
 *
 * @package Plugin_allow_list
 */

new PluginAllowList();

class PluginAllowList {

	public $url = 'https://gist.githubusercontent.com/spicecadet/3a2e1156a86a686aed34d2865703eb59/raw/eec77cdb28913fdb156b0400c88ea6b67358569a/plugin-whitelist.txt';
	public $site_name;
	public $site_env;
	public $data;
	public $allowed_plugins;
	public $active_plugins;
	public array $all_plugins;
	public $isPantheon;

	public function __construct() {
		$this->data = $this->qs_load_file( $this->url );
		$this->allowed_plugins = explode( PHP_EOL, $this->data );
		echo( "Allowed Plugins: \n" );
		var_dump( $this->allowed_plugins );

		$this->active_plugins = $this->qs_get_active_plugins();
		echo( "Listing active plugins\n" );
		var_dump( $this->active_plugins );

		$this->all_plugins = $this->qs_get_all_plugins();
		echo( "Listing all plugins\n" );
		var_dump( $this->all_plugins );

		$this->qs_delete_plugins( $this->allowed_plugins, $this->all_plugins );
	}

	/**
	 * Get the Pantheon site name.
	 * @return string|null
	 */
	public function getPantheonSiteName(): ?string
	{
		return !empty($_ENV['PANTHEON_SITE_NAME']) ? $_ENV['PANTHEON_SITE_NAME'] : NULL;
	}

	/**
	 * Get the Pantheon environment.
	 *
	 * @return string|null
	 */
	public function getPantheonEnvironment(): ?string
	{
		return !empty($_ENV['PANTHEON_ENVIRONMENT']) ? $_ENV['PANTHEON_ENVIRONMENT'] : NULL;
	}

	/**
	 * Check if in the Quicksilver context.
	 *
	 * @return bool|void
	 */
	public function isQuicksilver() {
		if ( $this->isPantheon() && !empty($_POST['wf_type']) ) {
			return true;
		}
		die( 'No Pantheon Quicksilver environment detected.' );
	}

	/**
	 * Set Quicksilver variables from POST data.
	 * @return void
	 */
	public function setQuicksilverVariables() {
		$this->site_name = $this->getPantheonSiteName();
		$this->site_env = $this->getPantheonEnvironment();
	}

	/**
	 * Load a file from url.
	 *
	 * @param string $url The URL where the list is located.
	 * @return string $data The list of plugins from the url
	 */
	public function qs_load_file( string $url ) {

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_URL, $url );

		$data = curl_exec( $ch );
		curl_close( $ch );

		echo( "Plugin data loaded from external list \n" );
		return $data;
	}

	/**
	 * Get Active Plugins
	 *
	 * @return string $active_plugins The list of active plugins.
	 */
	public function qs_get_active_plugins() {

		// Get Active plugin List.
		ob_start();
		passthru( 'wp option get active_plugins --format=json' );
		$active_plugins = json_decode( ob_get_contents() );
		ob_end_clean();
		return( $active_plugins );
	}
	/**
	 * Get All Plugins
	 *
	 * @return string $all_plugins The list of all installed plugin.
	 *
	 * @todo this needs the full plugin slug, not just the plugin name.
	 */
	public function qs_get_all_plugins() {

		ob_start();
		passthru( "wp plugin list --field=name --format=json" );
		$all_plugins = json_decode( ob_get_contents() );
		ob_end_clean();

		// remove loader plugin.
		$find_key = array_search( 'loader', $all_plugins );
		unset( $all_plugins[ $find_key ] );

		return $all_plugins;
	}

	/**
	 * Delete blocked plugins
	 *
	 * @param array $allowed_plugins List of allowed plugins.
	 * @param array $all_plugins List of all installed plugins.
	 */
	public function qs_delete_plugins( array $allowed_plugins, array $all_plugins ) {

		foreach ( $all_plugins as $plugin ) {

			if ( $this->allowed_plugin_search( $plugin, $allowed_plugins ) === true ) {
				echo "Plugin allowed - $plugin \n";
			} else {
				echo "Plugin not allowed - deleting: $plugin \n";
				passthru( "wp plugin uninstall $plugin" );
			}
		}
	}

	/**
	 * Search allowed plugin list to see if there's a match
	 *
	 * @param string $substring The substring to search for.
	 * @param array  $allowed_plugins_array The array to search through.
	 */
	public function allowed_plugin_search( string $substring, array $allowed_plugins_array ) {
		foreach ( $allowed_plugins_array as $allowed ) {
			if ( strpos( $allowed, $substring ) !== false ) {
				return true;
			}
		}
		return false;
	}
}
