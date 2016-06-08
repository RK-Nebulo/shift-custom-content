<?php

	if( !function_exists( 'has_{$term}' ) ) {
		function has_{$term}( $post_id = null ){
			$post_id = isset( $post_id ) ? $post_id : get_the_ID();
			return has_term( '', '{$taxonomy}', $post_id );
		}
	}
