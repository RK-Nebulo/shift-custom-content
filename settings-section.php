<!--<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" novalidate="novalidate">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="name">Name</label></th>
				<td>
					<input name="name" type="text" id="name" value="<?php echo $post_type->name; ?>" class="regular-text" disabled="disabled">
					<p class="description" id="name-description">This can only be changed by renaming this post type's JSON file.</p>
					<input type="hidden" name="name" value="<?php echo $post_type->name; ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="label">Label (plural)</label></th>
				<td><input name="label" type="text" id="label" value="<?php echo $post_type->label; ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="label_singular">Label (singular)</label></th>
				<td><input name="label_singular" type="text" id="label_singular" value="<?php echo $post_type->labels->singular_name; ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="menu_name">Menu Label</label></th>
				<td><input name="menu_name" type="text" id="menu_name" value="<?php echo $post_type->labels->menu_name; ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="public">Public</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Public</span></legend>
						<label for="public"><input name="public" type="checkbox" id="public" <?php checked( $post_type->public ); ?>></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="hierarchical">Hierarchical</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Hierarchical</span></legend>
						<label for="hierarchical"><input name="hierarchical" type="checkbox" id="hierarchical" <?php checked( $post_type->hierarchical ); ?>></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="exclude_from_search">Exclude from Search</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Exclude from Search</span></legend>
						<label for="exclude_from_search"><input name="exclude_from_search" type="checkbox" id="exclude_from_search" <?php checked( $post_type->exclude_from_search ); ?>></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="publicly_queryable">Publicly Queryable</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Publicly Queryable</span></legend>
						<label for="publicly_queryable"><input name="publicly_queryable" type="checkbox" id="publicly_queryable" <?php checked( $post_type->publicly_queryable ); ?>></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="show_ui">Show UI</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Show UI</span></legend>
						<label for="show_ui"><input name="show_ui" type="checkbox" id="show_ui" <?php checked( $post_type->show_ui ); ?>></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="show_in_menu">Show in Menu</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Show in Menu</span></legend>
						<label for="show_in_menu"><input name="show_in_menu" type="checkbox" id="show_in_menu" <?php checked( $post_type->show_in_menu ); ?>></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="show_in_nav_menus">Show in Navigation Menus</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Show in Navigation Menus</span></legend>
						<label for="show_in_nav_menus"><input name="show_in_nav_menus" type="checkbox" id="show_in_nav_menus" <?php checked( $post_type->show_in_nav_menus ); ?>></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="show_in_admin_bar">Show in Admin Bar</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Show in Admin Bar</span></legend>
						<label for="show_in_admin_bar"><input name="show_in_admin_bar" type="checkbox" id="show_in_admin_bar" <?php checked( $post_type->show_in_admin_bar ); ?>></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="menu_position">Menu Position</label></th>
				<td>
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
				</td>
			</tr>
			<tr>
				<th scope="row">Menu Icon</th>
				<td>
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
						<div class="dashicon-option <?php echo ( $dashicon === $post_type->menu_icon ) ? 'selected original-selected' : null; ?>">
							<input type="radio" name="menu_icon" id="menu_icon_<?php echo $dashicon; ?>" value="<?php echo $dashicon; ?>" <?php checked( $dashicon, $post_type->menu_icon ); ?>>
							<label for="menu_icon_<?php echo $dashicon; ?>"><span class="dashicons <?php echo $dashicon; ?>"></span></label>
						</div>
						<?php endforeach; ?>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">Supports</th>
				<td>
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
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="register_meta_box_cb">Meta Box Callback</label></th>
				<td><input name="register_meta_box_cb" type="text" id="register_meta_box_cb" value="<?php echo $post_type->register_meta_box_cb; ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row">Taxonomies</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Taxonomies</span></legend>
						<?php foreach( get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects', 'or' ) as $taxonomy ): ?>
						<label title="<?php echo $taxonomy->label; ?>">
							<input type="checkbox" name="taxonomies[]" value="<?php echo $taxonomy->name; ?>" <?php checked( in_array( $taxonomy->name, $post_type->taxonomies ) ); ?>>&nbsp;<?php echo $taxonomy->label; ?>
						</label>
						<br />
						<?php endforeach; ?>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="has_archive">Archive</label></th>
				<td><input name="has_archive" type="text" id="has_archive" value="<?php echo $post_type->has_archive; ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="rewrite">Slug</label></th>
				<td><input name="rewrite[slug]" type="text" id="rewrite_slug" value="<?php echo $post_type->rewrite['slug']; ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="rewrite_with_front">With Front</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>With Front</span></legend>
						<label for="rewrite_with_front">
							<input name="rewrite[with_front]" type="checkbox" id="rewrite_with_front" <?php checked( $post_type->rewrite['with_front'] ); ?>>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="rewrite_feeds">Feeds</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Feeds</span></legend>
						<label for="rewrite_feeds">
							<input name="rewrite[feeds]" type="checkbox" id="rewrite_feeds" <?php checked( $post_type->rewrite['feeds'] ); ?>>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="rewrite_pages">Pages</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Pages</span></legend>
						<label for="rewrite_pages">
							<input name="rewrite[pages]" type="checkbox" id="rewrite_pages" <?php checked( $post_type->rewrite['pages'] ); ?>>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="query_var">Query Var</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Query Var</span></legend>
						<label for="query_var"><input name="query_var" type="checkbox" id="query_var" <?php checked( $post_type->query_var ); ?>></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="can_export">Can Export</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Can Export</span></legend>
						<label for="can_export"><input name="can_export" type="checkbox" id="can_export" <?php checked( $post_type->can_export ); ?>></label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="show_in_rest">Show in REST</label></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Show in REST</span></legend>
						<label for="show_in_rest"><input name="show_in_rest" type="checkbox" id="show_in_rest" <?php checked( isset( $post_type->show_in_rest ) && $post_type->show_in_rest ); ?>></label>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>-->
<!--</form>-->