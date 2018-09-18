<?php

namespace jn;

add_action( 'jurassic_ninja_init', function() {
	$defaults = [
		'gutenpack' => false,
		'calypsobranch' => false,
	];

	add_action( 'jurassic_ninja_add_features_after_auto_login', function( &$app = null, $features, $domain ) use ( $defaults ) {
		$features = array_merge( $defaults, $features );
		if ( $features['gutenpack'] ) {
			debug( '%s: Installing Gutenberg blocks for Jetpack', $domain );
			$jetpack_dir = ( isset( $features['jetpack-beta'] ) && $features['jetpack-beta'] ) ? 'jetpack-dev' : 'jetpack';
			$calypsobranch = isset( $features['calypsobranch'] ) ? $features['calypsobranch'] : 'master';
			if ( $features['calypsobranch'] ) {
				debug( '%s: Adding Gutenpack blocks for wp-calypso branch %s. Jetpack dir is %s', $domain, $calypsobranch, $jetpack_dir );
			} else {
				debug( '%s: Adding latest stable Gutenpack blocks. Jetpack dir is %s', $domain, $jetpack_dir );
			}

			add_gutenpack( $calypsobranch, $jetpack_dir );
		}
	}, 100, 3 );

	add_filter( 'jurassic_ninja_rest_create_request_features', function( $features, $json_params ) {
		if ( isset( $json_params['gutenpack'] ) ) {
				$features['gutenpack'] = $json_params['gutenpack'];
		}
		if ( isset( $json_params['calypsobranch'] ) ) {
				$features['calypsobranch'] = $json_params['calypsobranch'];
		}
		return $features;
	}, 10, 2 );
} );

function add_gutenpack( $branch, $jetpack_dir = 'jetpack' ) {
	$cmd = 'curl https://gist.githubusercontent.com/oskosk/1b821e70548b065cef1d9c8e6f786089/raw/build-gutenpack.sh --output build-gutenpack.sh'
		. " && source build-gutenpack.sh $branch $jetpack_dir";
	add_filter( 'jurassic_ninja_feature_command', function ( $s ) use ( $cmd ) {
		return "$s && $cmd";
	} );
}
