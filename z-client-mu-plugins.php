<?php

/**
 * Plugin Name: VIP Client mu-plugins
 * Description: Helper plugin to load custom mu-plugins in the `client-mu-plugins` dir.
 * Author: Automattic
 */

// Note: This file is prefixed with `z-` for load order

/**
 * Gets PHP files in the client-mu-plugins folder.
 *
 * The code for this function is adapted from `wp_get_mu_plugins()`
 */
function wpcom_vip_get_client_mu_plugins() {
	$client_mu_plugins = [];

	if ( ! is_dir( WPCOM_VIP_CLIENT_MU_PLUGIN_DIR ) ) {
		return;
	}

	$dir_handle = opendir( WPCOM_VIP_CLIENT_MU_PLUGIN_DIR );
	if ( ! $dir_handle ) {
		return;
	}

	do {
		$plugin = readdir( $dir_handle );
		if ( false === $plugin ) {
			break;
		}

		$is_php_file = substr( $plugin, -4 ) === '.php';
		if ( $is_php_file ) {
			$client_mu_plugins[] = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin;
		}
	} while ( false !== $plugin );

	closedir( $dir_handle );

	// Make sure plugins load in a consistent, predictable order
	sort( $client_mu_plugins );

	return $client_mu_plugins;
}

function wpcom_vip_get_client_mu_plugins_data() {
	$plugin_files = wpcom_vip_get_client_mu_plugins();

	if ( empty( $plugin_files ) ) {
		return $plugin_files;
	}

	foreach ( $plugin_files as $plugin_file ) {
		if ( ! is_readable( WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . "/$plugin_file" ) ) {
			continue;
		}

		$plugin_data = get_plugin_data( WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . "/$plugin_file", false, false ); //Do not apply markup/translate as it'll be cached.

		if ( empty( $plugin_data['Name'] ) ) {
			$plugin_data['Name'] = $plugin_file;
		}

		$wp_plugins[ $plugin_file ] = $plugin_data;
	}

	if ( isset( $wp_plugins['index.php'] ) && filesize( WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/index.php' ) <= 30 ) { // silence is golden
		unset( $wp_plugins['index.php'] );
	}

	uasort( $wp_plugins, '_sort_uname_callback' );

	return $wp_plugins;
}

// Let's load the plugins
foreach ( wpcom_vip_get_client_mu_plugins() as $client_mu_plugin ) {
	include_once( $client_mu_plugin );
}
unset( $client_mu_plugin );
