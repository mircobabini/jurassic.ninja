<?php

namespace jn;

add_action( 'jurassic_ninja_init', function() {
	$whitelist = [
		'jetpack' => 'Jetpack',
		'code-snippets' => 'Code Snippets',
		'config-constants' => 'Config Constants',
		'gutenberg' => 'Gutenberg',
		'woocommerce' => 'WooCommerce',
		'wordpress-beta-tester' => 'WordPress Beta Tester Plugin',
		'wp-downgrade' => 'WP Downgrade',
		'wp-log-viewer' => 'WP Log Viewer',
		'wp-rollback' => 'WP Rollback',
	];
	$defaults = [
		'branch' => false,
		'code-snippets' => false,
		'config-constants' => false,
		'gutenberg' => false,
		'jetpack' => false,
		'woocommerce' => false,
		'wordpress-beta-tester' => false,
		'wp-downgrade' => false,
		'wp-log-viewer' => false,
	];

	add_action( 'jurassic_ninja_add_features_before_auto_login', function( &$app = null, $features, $domain ) use ( $defaults ) {
		$features = array_merge( $defaults, $features );
		foreach( $whitelist as $key => $whitelisted ) {
			if ( isset( $features[ $key ] ) && $features[ $key ] ) {
				debug( '%s: Adding $whitelisted', $domain, $whitelisted );
				add_directory_plugin( $key );
			}
		}
	}, 10, 3 );

	add_filter( 'jurassic_ninja_rest_feature_defaults', function( $defaults ) {
		return array_merge( $defaults, [
			'gutenberg' => (bool) settings( 'add_gutenberg_by_default', false ),
			'jetpack' => (bool) settings( 'add_jetpack_by_default', true ),
			'woocommerce' => (bool) settings( 'add_woocommerce_by_default', false ),
		] );
	} );

	add_filter( 'jurassic_ninja_rest_create_request_features', function( $features, $json_params ) {
		foreach( $whitelist as $key => $whitelisted ) {
			if ( isset( $json_params[ $key ] ) && $json_params[ $key ] ) {
				$features[$key] = $json_params[$key];
			}
		}
		return $features;
	}, 10, 2 );
} );

add_action( 'jurassic_ninja_admin_init', function() {
	add_filter( 'jurassic_ninja_settings_options_page', function( $options_page ) {
		/**
		 * Filter settings about default plugins.
		 *
		 * @since 3.0
		 *
		 * @param array $settings_default_plugins Array of settings entries. See RationalOptionPages docs.
		 */
		$fields = apply_filters( 'jurassic_ninja_settings_options_page_default_plugins', [
			'add_jetpack_by_default' => [
				'id' => 'add_jetpack_by_default',
				'title' => __( 'Add Jetpack to every launched WordPress', 'jurassic-ninja' ),
				'text' => __( 'Install and activate Jetpack on launch', 'jurassic-ninja' ),
				'type' => 'checkbox',
				'checked' => true,
			],
			'add_gutenberg_by_default' => [
				'id' => 'add_gutenberg_by_default',
				'title' => __( 'Add Gutenberg to every launched WordPress', 'jurassic-ninja' ),
				'text' => __( 'Install and activate Gutenberg on launch', 'jurassic-ninja' ),
				'type' => 'checkbox',
				'checked' => false,
			],
			'add_woocommerce_by_default' => [
				'id' => 'add_woocommerce_by_default',
				'title' => __( 'Add WooCommerce to every launched WordPress', 'jurassic-ninja' ),
				'text' => __( 'Install and activate WooCommerce on launch', 'jurassic-ninja' ),
				'type' => 'checkbox',
				'checked' => false,
			],
		] );
		$settings = [
			'title' => __( 'Default plugins', 'jurassic-ninja' ),
			'text' => '<p>' . __( 'Choose plugins you want installed on launch by default.', 'jurassic-ninja' ) . '</p>',
			'fields' => $fields,
		];

		$options_page[ SETTINGS_KEY ]['sections']['plugins'] = $settings;
		return $options_page;
	}, 1 );
}, 1 );

/**
 * Installs and activates a given plugin from the Plugin Directory.
 */
function add_directory_plugin( $plugin_slug ) {
	$cmd = "wp plugin install $plugin_slug --activate";
	add_filter( 'jurassic_ninja_feature_command', function ( $s ) use ( $cmd ) {
		return "$s && $cmd";
	} );
}

