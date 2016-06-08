<?php

	if( !function_exists( 'get_{$posts}' ) ) {
		function get_{$posts}( $args ){
			return get_posts( array_merge( array( 'post_type' => '{$post_type}' ), $args ) );
		}
	}

	if( !function_exists( 'is_{$post}' ) ) {
		function is_{$post}( $post_id = null ){
			$post_id = isset( $post_id ) ? $post_id : get_the_ID();
			return ( get_post_type( $post_id ) === '{$post_type}' );
		}
	}
