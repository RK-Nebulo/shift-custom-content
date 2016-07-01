<?php
/*
Plugin Name:	SHIFT - Custom Content
Plugin URI:		https://github.com/nebulodesign/shift-custom-content/
Description:	Custom Content Management
Version:			1.1
Author:				Nebulo Design
Author URI:		http://nebulodesign.com
License:			GPL
*/

/**
 * Initiate custom plugin class - fetches updates from our own public GitHub repository.
 */
if( is_admin() && class_exists( 'Shift_Plugin_Updater' ) ) new Shift_Plugin_Updater( __FILE__ );


register_activation_hook( __FILE__, function(){

	if( !file_exists( get_stylesheet_directory() . '/custom-post-types/' ) )
		mkdir( get_stylesheet_directory() . '/custom-post-types/' );

	if( !file_exists( get_stylesheet_directory() . '/custom-taxonomies/' ) )
		mkdir( get_stylesheet_directory() . '/custom-taxonomies/' );
	
});


include_once 'functions.php';


add_action( 'init', function(){

	$post_types_dir	= get_stylesheet_directory() . '/custom-post-types/';
	$taxonomies_dir	= get_stylesheet_directory() . '/custom-taxonomies/';
	$functions_dir	= dirname( __FILE__ ) . '/custom-functions/';
	$inc_dir				= dirname( __FILE__ ) . '/inc/';


	// register posts types

	$custom_post_types = array_combine(
		get_custom_post_types(),
		array_map( function( $cpt ) use( $post_types_dir ){
			return json_decode( file_get_contents( $post_types_dir . $cpt . '.json' ), true );
		}, get_custom_post_types() )
	);

	foreach( $custom_post_types as $post_type_name => $post_type_args )

		register_post_type( $post_type_name, $post_type_args );


	// set up relationships between post types and taxonomies

	$relationships = array_map( function( $post_type ){ return $post_type['taxonomies']; }, $custom_post_types );


	// register taxonomies

	$custom_taxonomies = array_combine(
		get_custom_taxonomies(),
		array_map( function( $taxonomy ) use( $taxonomies_dir ){
			return json_decode( file_get_contents( $taxonomies_dir . $taxonomy . '.json' ), true );
		}, get_custom_taxonomies() )
	);

	foreach( $custom_taxonomies as $taxonomy_name => $taxonomy_args )

		register_taxonomy(
			$taxonomy_name,
			array_keys( array_filter( $relationships, function( $taxonomies ) use( $taxonomy_name ){ return in_array( $taxonomy_name, $taxonomies ); } ) ),
			$taxonomy_args
		);

add_action( 'admin_enqueue_scripts', function(){
	wp_add_inline_style( 'common', '
		.dashicon-option {
			cursor: pointer;
			display: inline-block;
			margin-bottom: 0.25em;
			padding: 0.25em 0.5em 0 0.5em;
		}
		.dashicon-option.selected,
		.dashicon-option:hover {
			background-color: rgb(221, 221, 221);
		}' );

});


add_action( 'admin_menu', function(){

	/** Custom Post Types list */
	if( has_role( 'administrator' ) )
		add_menu_page( 'Custom Content', 'Custom Content', 'manage_options', 'shift-custom-content', function(){
			
			if( ! class_exists( 'WP_List_Table' ) ) require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

			class Custom_Post_Types_List_Table extends WP_List_Table {

				function get_columns(){

				  $columns = array(
				    'post_type_label'			=> 'Post Type',
				    'post_type_public'		=> 'Public',
				  );
				  return $columns;
				}

				function prepare_items() {

				  $columns = $this->get_columns();
				  $hidden = array();
				  $sortable = array();
				  $this->_column_headers = array($columns, $hidden, $sortable);
				  $this->items = array();

					$post_types = get_post_types( array( '_builtin' => false ), 'objects' );

					$i = 1;
					foreach( get_custom_post_types() as $post_type_name ) {
						$post_type = get_post_type_object( $post_type_name );
						$this->items[] = array(
							'ID'									=> $i++,
							'post_type_name'			=> $post_type->name,
							'post_type_label'			=> $post_type->label,
							'post_type_public'		=> '<input type="checkbox" ' . checked( $post_type->public, true, false ) . ' disabled="disabled">',
						);
						
					}
				} // end prepare_items

				function column_default( $item, $column_name ) {
					if( $column_name === 'post_type_label' )
				  $actions = array(
				            'edit'      => '<a href="' . add_query_arg( 'page', 'shift-edit-' . $item['post_type_name'], admin_url( 'admin.php' ) ) . '">Edit</a>',
//				            'delete'    => sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
				        );

					return ( in_array( $column_name, array_keys( $item ) ) ? $item[ $column_name ] : print_r( $item, true ) ) . ( isset( $actions ) ? $this->row_actions($actions ) : null );
				}

			}


			class Custom_Taxonomies_List_Table extends WP_List_Table {

				function get_columns(){

				  $columns = array(
				    'taxonomy_label'			=> 'Taxonomy',
				    'taxonomy_public'			=> 'Public',
				  );
				  return $columns;
				}

				function prepare_items() {

				  $columns = $this->get_columns();
				  $hidden = array();
				  $sortable = array();
				  $this->_column_headers = array($columns, $hidden, $sortable);
				  $this->items = array();

					$taxonomies = get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects', 'or' );

					$i = 1;
					foreach( get_custom_taxonomies() as $taxonomy_name ) {
						$taxonomy = get_taxonomy( $taxonomy_name );
						$this->items[] = array(
							'ID'									=> $i++,
							'taxonomy_name'				=> $taxonomy->name,
							'taxonomy_label'			=> $taxonomy->label,
							'taxonomy_public'			=> '<input type="checkbox" ' . checked( $taxonomy->public, true, false ) . ' disabled="disabled">',
						);
						
					}
				} // end prepare_items

				function column_default( $item, $column_name ) {
					if( $column_name === 'taxonomy_label' )
				  $actions = array(
				            'edit'      => '<a href="' . add_query_arg( 'page', 'shift-edit-' . $item['taxonomy_name'], admin_url( 'admin.php' ) ) . '">Edit</a>',
//				            'delete'    => sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
				        );

					return ( in_array( $column_name, array_keys( $item ) ) ? $item[ $column_name ] : print_r( $item, true ) ) . ( isset( $actions ) ? $this->row_actions($actions ) : null );
				}

			}

			// output
			echo '<div class="wrap">';
			echo '<h2>Custom Content</h2>';
			echo '<h3>Custom Post Types</h3>';
			$custom_post_types_list = new Custom_Post_Types_List_Table();
				$custom_post_types_list->prepare_items(); 
				$custom_post_types_list->display(); 
			echo '<h3>Custom Taxonomies</h3>';
			$custom_taxonomies_list = new Custom_Taxonomies_List_Table();
				$custom_taxonomies_list->prepare_items(); 
				$custom_taxonomies_list->display(); 
			echo '</div>';

		}, 'dashicons-admin-generic' );

	function admin_text_input( $id, $value = null, $return = false ) {
		$html = '<input name="' . $id . '" type="text" id="' . $id . '" value="' . $value . '" class="regular-text">';
		if( $return === false )
			print $html;
		elseif( $return === true )
			return $html;
	}

	function admin_checkbox_input( $id, $value = false, $return = false ) {
		$html = '<fieldset><legend class="screen-reader-text"><span>' . ucwords( $id ) . '</span></legend><label for="' . $id . '"><input name="' . $id . '" type="checkbox" id="' . $id . '" ' . checked( ( is_bool( $value ) && $value === true ), true, false ) . '></label></fieldset>';
		if( $return === false )
			print $html;
		elseif( $return === true )
			return $html;
	}

	function add_settings_field_textbox( $id, $label, $page, $section, $value = null ) {
		add_settings_field(
			$id, $label,
			function() use( $id, $label, $value ){
				print '<input name="' . $id . '" type="text" id="' . $id . '" value="' . $value . '" class="regular-text">';
			},
			$page, $section,
			array( 'label_for' => $id )
		);
	}


	function add_settings_field_checkbox( $id, $label, $page, $section, $value = false ) {
		add_settings_field(
			$id, $label,
			function() use( $id, $label, $value ){
				print '<fieldset><legend class="screen-reader-text"><span>' . $label . '</span></legend><label for="' . $id . '"><input name="' . $id . '" type="checkbox" id="' . $id . '" ' . checked( ( is_bool( $value ) && $value === true ), true, false ) . '></label></fieldset>';
			},
			$page, $section,
			array( 'label_for' => $id )
		);
	}


	foreach( get_post_types( '', 'objects' ) as $post_type ) {

		/** Basic setup section */
		add_settings_section(
			'shift_edit_' . $post_type->name . '_settings',
			'Basic Settings',
			function() use( $post_type ){ include 'settings-section.php'; },
			'shift_edit_' . $post_type->name
		);


		add_settings_field(
			'name',
			'Name',
			function() use( $post_type ){
				print '<input name="name" type="text" id="name" value="' . $post_type->name . '" class="regular-text" disabled="disabled"><p class="description" id="name-description">This can only be changed by renaming this post type\'s JSON file.</p><input type="hidden" name="name" value="' . $post_type->name . '">';
			},
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_settings'
		);

		add_settings_field_checkbox(
			'public',
			'Public',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_settings',
			$post_type->public
		);

		add_settings_field_checkbox(
			'hierarchical',
			'Hierarchical',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_settings',
			$post_type->hierarchical
		);

		add_settings_field(
			'supports',
			'Supports',
			function() use( $post_type ){ ?>
				<fieldset>
					<legend class="screen-reader-text"><span>Supports</span></legend>
					<?php foreach( array(
						'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats',
					) as $supports ): ?>
						<label title="<?php echo $supports; ?>">
							<input type="checkbox" name="supports[]" value="<?php echo $supports; ?>" <?php checked( post_type_supports( $post_type->name, $supports ) ); ?>>&nbsp;<?php echo $supports; ?>
						</label>
						<br />
					<?php endforeach; ?>
				</fieldset>
			<?php },
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_settings'
		);

		add_settings_field(
			'taxonomies',
			'Taxonomies',
			function() use( $post_type ){ ?>
				<fieldset>
					<legend class="screen-reader-text"><span>Taxonomies</span></legend>
					<?php foreach( get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects', 'or' ) as $taxonomy ): ?>
					<label title="<?php echo $taxonomy->label; ?>">
						<input type="checkbox" name="taxonomies[]" value="<?php echo $taxonomy->name; ?>" <?php checked( in_array( $taxonomy->name, $post_type->taxonomies ) ); ?>>&nbsp;<?php echo $taxonomy->label; ?>
					</label>
					<br />
					<?php endforeach; ?>
				</fieldset>
			<?php },
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_settings'
		);

		add_settings_field_textbox(
			'has_archive',
			'Archive Slug',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_settings',
			$post_type->has_archive
		);

		/** Rewrites section */
		add_settings_section(
			'shift_edit_' . $post_type->name . '_rewrite',
			'Rewrite Rules',
			function() use( $post_type ){ include 'settings-section.php'; },
			'shift_edit_' . $post_type->name
		);

		add_settings_field_textbox(
			'rewrite[slug]',
			'Posts Slug',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_rewrite',
			$post_type->rewrite['slug']
		);

		add_settings_field_checkbox(
			'rewrite[with_front]',
			'With Front',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_rewrite',
			$post_type->rewrite['with_front']
		);

		add_settings_field_checkbox(
			'rewrite[pages]',
			'Enable Pagination',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_rewrite',
			$post_type->rewrite['pages']
		);

		add_settings_field_checkbox(
			'rewrite[feeds]',
			'Enable Feeds',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_rewrite',
			$post_type->rewrite['feeds']
		);

		/** Labels section */
		add_settings_section(
			'shift_edit_' . $post_type->name . '_labels',
			'Labels',
			function() use( $post_type ){ include 'settings-section.php'; },
			'shift_edit_' . $post_type->name
		);

		add_settings_field_textbox(
			'label',
			'Plural',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_labels',
			$post_type->label
		);

		add_settings_field_textbox(
			'label_singular',
			'Singular',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_labels',
			$post_type->labels->singular_name
		);

		/** Admin menu options section */
		add_settings_section(
			'shift_edit_' . $post_type->name . '_admin_menu',
			'Admin Menu Setup',
			null,
			'shift_edit_' . $post_type->name
		);

		add_settings_field_checkbox(
			'show_ui',
			'Show UI',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_admin_menu',
			$post_type->show_ui
		);

		add_settings_field_checkbox(
			'show_in_menu',
			'Show in Admin Menu',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_admin_menu',
			$post_type->show_in_menu
		);

		add_settings_field_textbox(
			'menu_name',
			'Menu Label',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_admin_menu',
			$post_type->labels->menu_name
		);

		add_settings_field(
			'menu_position',
			'Menu Position',
			function() use( $post_type ){ ?>
				<select name="menu_position" id="menu_position">
					<option value="<?php echo $post_type->menu_position; ?>" selected="selected"><?php echo $post_type->menu_position; ?> (Current)</option>
					<?php foreach( array(
						5		=> 'below Posts',
						10	=> 'below Media',
						15	=> 'below Links',
						20	=> 'below Pages',
						25	=> 'below Comments',
						60	=> 'below first separator',
						65	=> 'below Plugins',
						70	=> 'below Users',
						75	=> 'below Tools',
						80	=> 'below Settings',
						100	=> 'below second separator',
					) as $position => $position_label ): ?>
					<option value="<?php echo $position; ?>"><?php echo $position_label; ?></option>
					<?php endforeach; ?>
				</select>
			<?php },
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_admin_menu',
			array( 'label_for' => 'menu_position' )
		);

		add_settings_field(
			'menu_icon',
			'Menu Icon',
			function() use( $post_type ){ ?>
				<fieldset>
					<legend class="screen-reader-text"><span>Menu Icon</span></legend>
					<?php $dashicons = array_map( function( $dashicon ){ return 'dashicons-' . $dashicon; }, array(
						'admin-post', 'admin-media', 'admin-page', 'format-status', 'format-quote', 'format-image', 'format-video', 'format-audio',
						'calendar', 'calendar-alt', 'email-alt', 'art', 'nametag', 'pressthis', 'cart', 'tag', 'star-filled', 'location', 'location-alt',
						'groups', 'businessman', 'products', 'awards', 'testimonial', 'portfolio', 'book', 'clock', 'store', 'album', 'tickets-alt',
						'money', 'thumbs-up', ) ); ?>
					<?php if( !in_array( $post_type->menu_icon, $dashicons ) ): ?>
						<?php $dashicons[] = $post_type->menu_icon; ?>
					<?php endif; ?>
					<?php foreach( $dashicons as $dashicon ): ?>
					<div class="dashicon-option <?php echo ( $dashicon === $post_type->menu_icon ) ? 'selected' : null; ?>">
						<input type="radio" name="menu_icon" id="menu_icon_<?php echo $dashicon; ?>" value="<?php echo $dashicon; ?>" <?php checked( $dashicon, $post_type->menu_icon ); ?>>
						<label for="menu_icon_<?php echo $dashicon; ?>"><span class="dashicons <?php echo $dashicon; ?>"></span></label>
					</div>
					<?php endforeach; ?>
				</fieldset>
			<?php },
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_admin_menu'
		);


		add_settings_field_checkbox(
			'show_in_admin_bar',
			'Show in Admin Bar',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_admin_menu',
			$post_type->show_in_admin_bar
		);


		/** Advanced options section */
		add_settings_section(
			'shift_edit_' . $post_type->name . '_advanced',
			'Advanced Options',
			function() use( $post_type ){ include 'settings-section.php'; },
			'shift_edit_' . $post_type->name
		);

		add_settings_field_checkbox(
			'exclude_from_search',
			'Exclude from Search',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_advanced',
			$post_type->exclude_from_search
		);

		add_settings_field_checkbox(
			'publicly_queryable',
			'Publicly Queryable',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_advanced',
			$post_type->publicly_queryable
		);

		add_settings_field_checkbox(
			'show_in_nav_menus',
			'Show in Navigation Menus',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_advanced',
			$post_type->show_in_nav_menus
		);

		add_settings_field_textbox(
			'register_meta_box_cb',
			'Meta Box Callback',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_advanced',
			$post_type->register_meta_box_cb
		);

		add_settings_field_checkbox(
			'query_var',
			'Enable Query Var URLs',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_advanced',
			$post_type->query_var
		);

		add_settings_field_checkbox(
			'can_export',
			'Can be Exported',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_advanced',
			$post_type->can_export
		);

		add_settings_field_checkbox(
			'show_in_rest',
			'Show in REST API',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_advanced',
			isset( $post_type->show_in_rest ) ? $post_type->show_in_rest : false
		);

		add_settings_field_checkbox(
			'use_custom_functions',
			'Generate PHP Functions',
			'shift_edit_' . $post_type->name,
			'shift_edit_' . $post_type->name . '_advanced',
			isset( $post_type->use_custom_functions ) ? $post_type->use_custom_functions : false
		);


		add_submenu_page( null, $post_type->label, $post_type->label, 'manage_options', 'shift-edit-' . $post_type->name, function() use( $post_type ){

			echo '<div class="wrap">';
			echo '<h2>Edit Post Type &mdash; ' . $post_type->label . '</h2>';
			echo '<form method="post" action="' . admin_url( 'admin-post.php' ) . '" novalidate="novalidate">';
			do_settings_sections( 'shift_edit_' . $post_type->name );
			echo '<input type="hidden" name="action" value="shift_edit_post_type">';
			echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>';
			echo '</form>';
			echo '</div>';
		});
	}

	foreach( get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects', 'or' ) as $taxonomy ) {

		/** Basic setup section */
		add_settings_section(
			'shift_edit_' . $taxonomy->name . '_settings',
			'Basic Settings',
			null,
			'shift_edit_' . $taxonomy->name
		);

		add_settings_field(
			'name',
			'Name',
			function() use( $taxonomy ){
				print '<input name="name" type="text" id="name" value="' . $taxonomy->name . '" class="regular-text" disabled="disabled"><p class="description" id="name-description">This can only be changed by renaming this taxonomy\'s JSON file.</p><input type="hidden" name="name" value="' . $taxonomy->name . '">';
			},
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_settings'
		);

		add_settings_field_checkbox(
			'public',
			'Public',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_settings',
			$taxonomy->public
		);

		add_settings_field_checkbox(
			'hierarchical',
			'Hierarchical',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_settings',
			$taxonomy->hierarchical
		);

		/** Rewrites section */
		add_settings_section(
			'shift_edit_' . $taxonomy->name . '_rewrite',
			'Rewrite Rules',
			null,
			'shift_edit_' . $taxonomy->name
		);

		add_settings_field_textbox(
			'rewrite[slug]',
			'Terms Slug',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_rewrite',
			$taxonomy->rewrite['slug']
		);

		add_settings_field_checkbox(
			'rewrite[with_front]',
			'With Front',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_rewrite',
			$taxonomy->rewrite['with_front']
		);

		add_settings_field_checkbox(
			'rewrite[hierarchical]',
			'Hierarchical',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_rewrite',
			$taxonomy->rewrite['hierarchical']
		);

		/** Labels section */
		add_settings_section(
			'shift_edit_' . $taxonomy->name . '_labels',
			'Labels',
			null,
			'shift_edit_' . $taxonomy->name
		);

		add_settings_field_textbox(
			'label',
			'Plural',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_labels',
			$taxonomy->label
		);

		add_settings_field_textbox(
			'label_singular',
			'Singular',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_labels',
			$taxonomy->labels->singular_name
		);


		/** Admin menu options section */
		add_settings_section(
			'shift_edit_' . $taxonomy->name . '_admin_menu',
			'Admin Menu Setup',
			null,
			'shift_edit_' . $taxonomy->name
		);

		add_settings_field_checkbox(
			'show_ui',
			'Show UI',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_admin_menu',
			$taxonomy->show_ui
		);

		add_settings_field_checkbox(
			'show_in_menu',
			'Show in Menu',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_admin_menu',
			$taxonomy->show_in_menu
		);

		add_settings_field_textbox(
			'menu_name',
			'Menu Label',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_admin_menu',
			$taxonomy->labels->menu_name
		);

		add_settings_field_checkbox(
			'show_admin_column',
			'Show Column',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_admin_menu',
			$taxonomy->show_admin_column
		);

		add_settings_field_checkbox(
			'show_in_quick_edit',
			'Show in Quick Edit',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_admin_menu',
			$taxonomy->show_in_quick_edit
		);

		/** Advanced options section */
		add_settings_section(
			'shift_edit_' . $taxonomy->name . '_advanced',
			'Advanced Options',
			null,
			'shift_edit_' . $taxonomy->name
		);

		add_settings_field_checkbox(
			'show_tagcloud',
			'Show Tag Cloud',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_advanced',
			$taxonomy->show_tagcloud
		);

		add_settings_field_checkbox(
			'show_in_nav_menus',
			'Show in Navigation Menus',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_advanced',
			$taxonomy->show_in_nav_menus
		);

		add_settings_field_textbox(
			'meta_box_cb',
			'Meta Box Callback',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_advanced',
			$taxonomy->meta_box_cb
		);

		add_settings_field_checkbox(
			'use_custom_functions',
			'Generate PHP Functions',
			'shift_edit_' . $taxonomy->name,
			'shift_edit_' . $taxonomy->name . '_advanced',
			isset( $taxonomy->use_custom_functions ) ? $taxonomy->use_custom_functions : false
		);

		add_submenu_page( null, $taxonomy->label, $taxonomy->label, 'manage_options', 'shift-edit-' . $taxonomy->name, function() use( $taxonomy ){

			echo '<div class="wrap">';
			echo '<h2>Edit Taxonomy &mdash; ' . $taxonomy->label . '</h2>';
			echo '<form method="post" action="' . admin_url( 'admin-post.php' ) . '" novalidate="novalidate">';
			do_settings_sections( 'shift_edit_' . $taxonomy->name );
			echo '<input type="hidden" name="action" value="shift_edit_taxonomy">';
			echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>';
			echo '</form>';
			echo '</div>';
		});
	}

});

	add_action( 'admin_post_shift_edit_post_type', function() use( $post_types_dir, $functions_dir, $inc_dir ){

		extract( array_combine(
			array_map( function( $key ){ return 'post_'.$key; }, array_keys( $_POST ) ),
			array_values( $_POST )
		) );

		$_CPT = get_object_vars( get_post_type_object( $post_name ) );
		extract( array_combine(
			array_map( function( $key ){ return 'cpt_'.$key; }, array_keys( $_CPT ) ),
			array_values( $_CPT )
		) );


		$_JSON = json_decode( file_get_contents( $post_types_dir . $post_name . '.json' ), true );
		extract( array_combine(
			array_map( function( $key ){ return 'json_'.$key; }, array_keys( $_JSON ) ),
			array_values( $_JSON )
		) );

//		ppr( array( $_POST, $_CPT, $_JSON ) ); exit;

		$args = array(
			'labels' => get_object_vars( get_post_type_labels( get_post_type_object( $post_name ) ) ),
		);

		if( isset( $post_label ) && $post_label !== $cpt_label ) {

			$args['labels'] = array_merge( $args['labels'], array_map(
				function( $label ) use( $post_label ){
					return str_replace( array( 'Posts', 'posts' ), array( $post_label, strtolower( $post_label ) ), $label );
				},
				array_filter( get_object_vars( get_post_type_labels( get_post_type_object( 'post' ) ) ), function( $label ){
					return ( strpos( $label, 'Posts' ) !== false || strpos( $label, 'posts' ) !== false );
				} )
			) );
			$args['label'] = $post_label;
		}

		if( isset( $post_label_singular ) && $post_label_singular !== $cpt_labels->singular_name ) {

			$args['labels'] = array_merge( $args['labels'], array_map(
				function( $label ) use( $post_label_singular ){
					return str_replace( array( 'Post', 'post' ), array( $post_label_singular, strtolower( $post_label_singular ) ), $label );
				},
				array_filter( get_object_vars( get_post_type_labels( get_post_type_object( 'post' ) ) ), function( $label ){
					return ( ( strpos( $label, 'Post' ) !== false && strpos( $label, 'Posts' ) === false ) || ( strpos( $label, 'post' ) !== false && strpos( $label, 'posts' ) === false ) );
				} )
			) );
			$args['labels']['singular_name'] = $post_label_singular;
		}

		if( isset( $post_menu_name ) )
			$args['labels']['menu_name'] = $post_menu_name;

		$args = array_merge( $_JSON, $args, array(
			'public' => ( isset( $post_public ) && $post_public === 'on' ),
			'hierarchical' => ( isset( $post_hierarchical ) && $post_hierarchical === 'on' ),
			'exclude_from_search' => ( isset( $post_exclude_from_search ) && $post_exclude_from_search === 'on' ),
			'publicly_queryable' => ( isset( $post_publicly_queryable ) && $post_publicly_queryable === 'on' ),
			'show_ui' => ( isset( $post_show_ui ) && $post_show_ui === 'on' ),
			'show_in_menu' => ( isset( $post_show_in_menu ) && $post_show_in_menu === 'on' ),
			'show_in_nav_menus' => ( isset( $post_show_in_nav_menus ) && $post_show_in_nav_menus === 'on' ),
			'show_in_admin_bar' => ( isset( $post_show_in_admin_bar ) && $post_show_in_admin_bar === 'on' ),
			'supports' => ( isset( $post_supports ) && is_array( $post_supports ) ) ? $post_supports : array(),
			'taxonomies' => ( isset( $post_taxonomies ) && is_array( $post_taxonomies ) ) ? $post_taxonomies : array(),
			'rewrite' => ( isset( $post_rewrite ) && is_array( $post_rewrite ) ) ? array_map( function( $rewrite ){ return $rewrite === 'on' ? true : $rewrite; }, $post_rewrite ) : false,
			'menu_position' => ( isset( $post_menu_position ) && intval( $post_menu_position ) > 0 ) ? intval( $post_menu_position ) : null,
			'capability_type' => 'post',
			'register_meta_box_cb' => ( isset( $post_register_meta_box_cb ) && !empty( $post_register_meta_box_cb ) ) ? $post_register_meta_box_cb : null,
			'has_archive' => ( isset( $post_has_archive ) && !empty( $post_has_archive ) ) ? $post_has_archive : null,
			'query_var' => ( isset( $post_query_var ) && $post_query_var === 'on' ),
			'can_export' => ( isset( $post_can_export ) && $post_can_export === 'on' ),
			'show_in_rest' => ( isset( $post_show_in_rest ) && $post_show_in_rest === 'on' ),
			'use_custom_functions' => ( isset( $post_use_custom_functions ) && $post_use_custom_functions === 'on' ),
		) );

		$args = array_merge( $_JSON, $args );
		$fp = fopen( $post_types_dir . $post_name . '.json', 'w' );
		fwrite( $fp, json_encode( $args, JSON_PRETTY_PRINT ) );
		fclose($fp);

		if( $args['use_custom_functions'] === true && ( !isset( $cpt_use_custom_functions ) || $cpt_use_custom_functions === false ) ) {

			$template = file_get_contents( $functions_dir . 'post-types.php' );

			$fp = fopen( $inc_dir . $post_name . '.php', 'w');
			fwrite($fp, str_replace(
				array( '{$post}', '{$posts}', '{$post_type}' ),
				array_map( function( $name ){ return strtolower( str_replace( ' ', '_', $name ) ); }, array( $post_label_singular, $post_label, $post_name ) ),
				$template
			) );
			fclose($fp);
		}
		elseif( $args['use_custom_functions'] === false && isset( $cpt_use_custom_functions ) && $cpt_use_custom_functions === true && file_exists( $inc_dir . $post_name . '.php' ) ) {
			unlink( $inc_dir . $post_name . '.php' );
		}

		wp_redirect( add_query_arg( 'page', 'shift-edit-'.$post_name, admin_url( 'admin.php' ) ) );
	});


	add_action( 'admin_post_shift_edit_taxonomy', function() use( $taxonomies_dir, $functions_dir, $inc_dir ){

		extract( array_combine(
			array_map( function( $key ){ return 'post_'.$key; }, array_keys( $_POST ) ),
			array_values( $_POST )
		) );

		$_CT = get_object_vars( get_taxonomy( $post_name ) );
		extract( array_combine(
			array_map( function( $key ){ return 'ct_'.$key; }, array_keys( $_CT ) ),
			array_values( $_CT )
		) );


		$_JSON = json_decode( file_get_contents( $taxonomies_dir . $post_name . '.json' ), true );
		extract( array_combine(
			array_map( function( $key ){ return 'json_'.$key; }, array_keys( $_JSON ) ),
			array_values( $_JSON )
		) );

		$args = array(
			'labels' => get_object_vars( get_taxonomy_labels( get_taxonomy( $post_name ) ) ),
		);

		if( isset( $post_label ) && $post_label !== $ct_label ) {

			$args['labels'] = array_merge( $args['labels'], array_map(
				function( $label ) use( $post_label ){
					return str_replace( array( 'Categories', 'categories' ), array( $post_label, strtolower( $post_label ) ), $label );
				},
				array_filter( get_object_vars( get_taxonomy_labels( get_taxonomy( 'category' ) ) ), function( $label ){
					return ( strpos( $label, 'Categories' ) !== false || strpos( $label, 'categories' ) !== false );
				} )
			) );
			$args['label'] = $post_label;
		}

		if( isset( $post_label_singular ) && $post_label_singular !== $ct_labels->singular_name ) {

			$args['labels'] = array_merge( $args['labels'], array_map(
				function( $label ) use( $post_label_singular ){
					return str_replace( array( 'Category', 'category' ), array( $post_label_singular, strtolower( $post_label_singular ) ), $label );
				},
				array_filter( get_object_vars( get_taxonomy_labels( get_taxonomy( 'category' ) ) ), function( $label ){
					return ( ( strpos( $label, 'Category' ) !== false && strpos( $label, 'Categories' ) === false ) || ( strpos( $label, 'category' ) !== false && strpos( $label, 'categories' ) === false ) );
				} )
			) );
			$args['labels']['singular_name'] = $post_label_singular;
		}

		if( isset( $post_menu_name ) )
			$args['labels']['menu_name'] = $post_menu_name;

		$args = array_merge( $_JSON, $args, array(
			'public' => ( isset( $post_public ) && $post_public === 'on' ),
			'hierarchical' => ( isset( $post_hierarchical ) && $post_hierarchical === 'on' ),
			'show_ui' => ( isset( $post_show_ui ) && $post_show_ui === 'on' ),
			'show_in_menu' => ( isset( $post_show_in_menu ) && $post_show_in_menu === 'on' ),
			'show_in_nav_menus' => ( isset( $post_show_in_nav_menus ) && $post_show_in_nav_menus === 'on' ),
			'show_tagcloud' => ( isset( $post_show_tagcloud ) && $post_show_tagcloud === 'on' ),
			'show_in_quick_edit' => ( isset( $post_show_in_quick_edit ) && $post_show_in_quick_edit === 'on' ),
			'show_admin_column' => ( isset( $post_show_admin_column ) && $post_show_admin_column === 'on' ),
			'rewrite' => ( isset( $post_rewrite ) && is_array( $post_rewrite ) ) ? array_map( function( $rewrite ){ return $rewrite === 'on' ? true : $rewrite; }, $post_rewrite ) : false,
			'meta_box_cb' => ( isset( $post_meta_box_cb ) && !empty( $post_meta_box_cb ) ) ? $post_meta_box_cb : null,
			'use_custom_functions' => ( isset( $post_use_custom_functions ) && $post_use_custom_functions === 'on' ),
		) );

		$args = array_merge( $_JSON, $args );
		$fp = fopen( $taxonomies_dir . $post_name . '.json', 'w' );
		fwrite( $fp, json_encode( $args, JSON_PRETTY_PRINT ) );
		fclose($fp);

		if( $args['use_custom_functions'] === true && ( !isset( $ct_use_custom_functions ) || $ct_use_custom_functions === false ) ) {

			$template = file_get_contents( $functions_dir . 'taxonomies.php' );

			$fp = fopen( $inc_dir . $post_name . '.php', 'w');
			fwrite($fp, str_replace(
				array( '{$term}', '{$terms}', '{$taxonomy}' ),
				array_map( function( $name ){ return strtolower( str_replace( ' ', '_', $name ) ); }, array( $post_label_singular, $post_label, $post_name ) ),
				$template
			) );
			fclose($fp);
		}
		elseif( $args['use_custom_functions'] === false && isset( $ct_use_custom_functions ) && $ct_use_custom_functions === true && file_exists( $inc_dir . $post_name . '.php' ) ) {
			unlink( $inc_dir . $post_name . '.php' );
		}

		wp_redirect( add_query_arg( 'page', 'shift-edit-'.$post_name, admin_url( 'admin.php' ) ) );
	});

});