<?php
/*
Plugin Name: Random One Cat Widget
Description: This Widget shows a single random post from a given category.
Author: BrokenCrust
Version: 1.1
Author URI: http://not-a-sheep.com/
Plugin URI: http://not-a-sheep.com/random-one-cat-widget
*/

function widget_random_one_cat_init() {

	if (!function_exists('register_sidebar_widget')) { return; }

	function widget_random_one_cat($args, $widget_args = 1) {
		extract($args, EXTR_SKIP);
		if (is_numeric($widget_args)) {
			$widget_args = array( 'number' => $widegt_args );
		}
		$widget_args = wp_parse_args($widget_args, array('number' => -1));
		extract($widget_args, EXTR_SKIP);

		$options = get_option('widget_random_one_cat');

		if (!isset($options[$number])) { return; }

		if (isset($options[$number]['error']) && $options[$number]['error']) { return; }

		$title = $options[$number]['title'];
		$category = intval($options[$number]['category']);
		$show_post_title = $options[$number]['show_post_title'];
                $show_custom_fields = $options[$number]['show_custom_fields'];

		$rand = get_posts('numberposts=1&category='.$category.'&orderby=rand');

		echo $before_widget;
		echo $before_title . $title . $after_title;
		if ($show_post_title == 1) {
			?>
			<h3><?php echo $rand[0]->post_title; ?></h3>
			<?php
		}
		echo $rand[0]->post_content;

		if ($show_custom_fields == 1) {
			$custom_fields = get_post_custom($rand[0]->ID);
			if (count($custom_fields) > 0) {
				echo '<ul class="post-meta">';
				foreach ($custom_fields as $key => $values) {
					if (substr($key, 0, 1) != '_') {
						$valueline = '';
						foreach ($values as $i => $value) {
							$valueline .= $value.', ';
						}
						echo '<li><span class="post-meta-key">'.$key.': </span>'.trim($valueline, ', ').'</li>';
					}
				}
				echo '</ul>';
			}
		}
		echo $after_widget;
	}

	function widget_random_one_cat_control($widget_args) {
		global $wp_registered_widgets;
		static $updated = false;

		if (is_numeric($widget_args)) {
			$widget_args = array( 'number' => $widget_args );
		}
		$widget_args = wp_parse_args($widget_args, array('number' => -1));
		extract($widget_args, EXTR_SKIP);

		$options = get_option('widget_random_one_cat');
		if (!is_array($options)) {
			$options = array();
		}
		if (!$updated && 'POST' == $_SERVER['REQUEST_METHOD'] && !empty($_POST['sidebar'])) {
			$sidebar = (string) $_POST['sidebar'];

			$sidebars_widgets = wp_get_sidebars_widgets();
			if (isset($sidebars_widgets[$sidebar])) {
				$this_sidebar =& $sidebars_widgets[$sidebar];
			} else {
				$this_sidebar = array();
			}

			foreach ($this_sidebar as $_widget_id) {
				if ('widget_random_one_cat' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if (!in_array("random_one_cat-$widget_number", $_POST['widget-id'])) {
						unset($options[$widget_number]);
					}
				}
			}

			foreach((array) $_POST['widget-random_one_cat'] as $widget_number => $widget_random_one_cat) {
				$widget_random_one_cat = stripslashes_deep($widget_random_one_cat);
				$options[$widget_number] = widget_random_one_cat_process($widget_random_one_cat);
			}

			update_option('widget_random_one_cat', $options);
			$updated = true;
		}

		if (-1 == $number) {
			$title              = __('Random Post');
			$category           = 1;
			$show_post_title    = 1;
			$show_custom_fields = 1;
			$error              = false;
			$number             = '%i%';
		} else {
			extract((array) $options[$number]);
		}

		widget_random_one_cat_form(compact('number', 'title', 'category', 'show_post_title', 'show_custom_fields'));
	}
	widget_random_one_cat_register();
}

function widget_random_one_cat_process($widget_random_one_cat) {
	$title              = trim(strip_tags($widget_random_one_cat['title']));
	$category           = (int) $widget_random_one_cat['category'];
	$show_post_title    = (int) $widget_random_one_cat['show_post_title'];
	$show_custom_fields = (int) $widget_random_one_cat['show_custom_fields'];

	return compact('title', 'category', 'show_post_title', 'show_custom_fields');
}

function widget_random_one_cat_form($args, $inputs = null) {
	$default_inputs = array('title' => true, 'category' => true, 'show_post_title' => true, 'show_custom_fields' => true);
	$inputs = wp_parse_args($inputs, $default_inputs);
	extract( $args );
	$number             = attribute_escape($number);
	$title              = attribute_escape($title);
	$category           = attribute_escape($category);
	$show_post_title    = (int) $show_post_title;
	$show_custom_fields = (int) $show_custom_fields;

	if ($inputs['title']) {
		?>
			<p>
				<label for="random_one_cat-title-<?php echo $number; ?>"><?php _e('Enter the widget title:'); ?>
				<input class="widefat" id="random_one_cat-title-<?php echo $number; ?>" name="widget-random_one_cat[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
			</label>
			</p>
		<?php
	}
	if ($inputs['category']) {
		?>
			<p>
				<label for="random_one_cat-category-<?php echo $number; ?>"><?php _e('Select a category:'); ?><br />
					<select id="random_one_cat-category-<?php echo $number; ?>" name="widget-random_one_cat[<?php echo $number; ?>][category]">
						<?php
							$categories = get_categories('');
							foreach ($categories as $cat) {
								if ($cat->cat_ID == $category) {
									$option = '<option selected="selected" value="'.$cat->cat_ID.'">';
								} else {
									$option = '<option value="'.$cat->cat_ID.'">';
								}
								$option .= $cat->cat_name.'</option>';
								echo $option;
							}
						?>
					</select>
				</label>
			</p>
		<?php
	}
	if ($inputs['show_post_title']) {
		?>
			<p>
				<label for="random_one_cat-show_post_title-<?php echo $number; ?>">
					<input id="random_one_cat-show_post_title-<?php echo $number; ?>" name="widget-random_one_cat[<?php echo $number; ?>][show_post_title]" type="checkbox" value="1" <?php if ( $show_post_title ) echo 'checked="checked"'; ?>/>
					<?php _e('Show Post Title?'); ?>
				</label>
			</p>
		<?php
	}
	if ($inputs['show_custom_fields']) {
		?>
			<p>
				<label for="random_one_cat-show_custom_fields-<?php echo $number; ?>">
					<input id="random_one_cat-show_custom_fields-<?php echo $number; ?>" name="widget-random_one_cat[<?php echo $number; ?>][show_custom_fields]" type="checkbox" value="1" <?php if ( $show_custom_fields ) echo 'checked="checked"'; ?>/>
					<?php _e('Show Custom Fields?'); ?>
				</label>
			</p>
		<?php
	}
	foreach (array_keys($default_inputs) as $input) {
		if ( 'hidden' === $inputs[$input] ) {
			$id = str_replace('_', '-', $input);
			?>
				<input type="hidden" id="random_one_cat-<?php echo $id; ?>-<?php echo $number; ?>" name="widget-random_one_cat[<?php echo $number; ?>][<?php echo $input; ?>]" value="<?php echo $$input; ?>" />
			<?php
		}
	}
}

function widget_random_one_cat_register() {
	if (!$options = get_option('widget_random_one_cat')) {
		$options = array();
	}
	$widget_ops = array('classname' => 'widget_random_one_cat', 'description' => __( 'Display a random post from one category.' ));
	$control_ops = array('id_base' => 'random_one_cat');
	$name = __('Random One Cat');
	$id = false;
	foreach (array_keys($options) as $o) {
		if (!isset($options[$o]['title']) || !isset($options[$o]['category']) || !isset($options[$o]['show_post_title']) || !isset($options[$o]['show_custom_fields'])) { continue; }
		$id = "random_one_cat-$o";
		wp_register_sidebar_widget($id, $name, 'widget_random_one_cat', $widget_ops, array( 'number' => $o ));
		wp_register_widget_control($id, $name, 'widget_random_one_cat_control', $control_ops, array( 'number' => $o ));
	}
	if (!$id) {
		wp_register_sidebar_widget('random_one_cat-1', $name, 'widget_random_one_cat', $widget_ops, array( 'number' => -1 ));
		wp_register_widget_control('random_one_cat-1', $name, 'widget_random_one_cat_control', $control_ops, array( 'number' => -1 ));
	}
}

add_action('widgets_init', 'widget_random_one_cat_init');

?>
