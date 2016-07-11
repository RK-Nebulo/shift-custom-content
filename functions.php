<?php

if( !function_exists( 'get_custom_post_types' ) ) {

	function get_custom_post_types() {

		$custom_post_types = file_exists( get_stylesheet_directory() . '/settings/custom-post-types/' ) ? array_map(

			function( $file ){ return pathinfo( $file, PATHINFO_FILENAME ); },

			array_filter(

				scandir( get_stylesheet_directory() . '/settings/custom-post-types/' ),

				function( $f ){ return ( !is_dir( $f ) && pathinfo( $f, PATHINFO_EXTENSION ) === 'json' && strpos( $f, '_' ) !== 0 ); }

			)

		) : array();

		return $custom_post_types;

	}

}


if( !function_exists( 'get_custom_taxonomies' ) ) {

	function get_custom_taxonomies() {

		$custom_taxonomies = file_exists( get_stylesheet_directory() . '/settings/custom-taxonomies/' ) ? array_map(

			function( $file ){ return pathinfo( $file, PATHINFO_FILENAME ); },

			array_filter(

				scandir( get_stylesheet_directory() . '/settings/custom-taxonomies/' ),

				function( $f ){ return ( !is_dir( $f ) && pathinfo( $f, PATHINFO_EXTENSION ) === 'json' && strpos( $f, '_' ) !== 0 ); }

			)

		) : array();

		return $custom_taxonomies;

	}

}
