<?php
/*
Plugin Name: Random One Cat Widget
Description: This widget shows random posts from a given category.
Author: BrokenCrust
Version: 2.4
Author URI: http://brokencrust.com/
Plugin URI: http://brokencrust.com/plugins/random-one-cat-widget/
License: GPLv2 or later
Text Domain: random-one-cat-widget
*/

/*  Copyright 2009-14  BrokenCrust  (email : bc@brokencrust.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class random_one_cat_widget extends WP_Widget {

	function __construct() {
		parent::__construct('random_one_cat_widget', __('Random One Cat', 'random-one-cat-widget'), array('classname' => 'widget_random_one_cat', 'description' => __('Shows random posts from a given category.', 'random-one-cat-widget')));
	}

	function widget($args, $instance) {
		global $more;

		extract($args, EXTR_SKIP);

		$title = apply_filters('widget_title', empty($instance['title'] ) ? __('Random Posts', 'random-one-cat-widget') : $instance['title'], $instance, $this->id_base);
		$category = empty($instance['category']) ? 1 : $instance['category'];
		$show_post_title = empty($instance['show_post_title']) ? 4 : $instance['show_post_title'];
		$show_custom_fields = empty($instance['show_custom_fields']) ? 0 : $instance['show_custom_fields'];
		$number_of_posts = empty($instance['number_of_posts']) ? 1 : $instance['number_of_posts'];
		$title_links = empty($instance['title_links']) ? 0 : $instance['title_links'];
		$shorten_posts = empty($instance['shorten_posts']) ? 0 : $instance['shorten_posts'];

		if ($shorten_posts == 1) { $more = 1; }

		$rand = get_posts(array('numberposts' => $number_of_posts, 'category' => $category, 'orderby' => 'rand'));

		if (!empty($rand)) {
			echo $before_widget;
			switch ($show_post_title) {
				case 2:
				case 4:
					echo $before_title.$title.$after_title;
					break;
			}
			if ($title_links == 1) { echo '<ul>'; }
			foreach ($rand as $r => $values) {
				if ($title_links == 0) {
					switch ($show_post_title) {
						case 3:
							echo $before_title.$rand[$r]->post_title.$after_title;
							break;
						case 4:
							echo '<h4>'.$rand[$r]->post_title.'</h4>';
							break;
					}
				}
				if ($title_links == 1) {
					echo '<li><a href="'.get_permalink($rand[$r]->ID).'">'.$rand[$r]->post_title.'</a></li>';
				} else {

					$content = $rand[$r]->post_content;

					if ($shorten_posts == 1) {
						$excerpt = explode('<!--more-->', $rand[$r]->post_content, 2);
						$content = $excerpt[0];
					} else {
						$content = $rand[$r]->post_content;
					}

					echo '<div>'.do_shortcode($content).'</div>';

					if ($show_custom_fields == 1) {
						$custom_fields = get_post_custom($rand[$r]->ID);

						foreach($custom_fields as $key => $value) {
							if (substr($key, 0, 1) == '_') {
								unset($custom_fields[$key]);
							}
						}
						array_values($custom_fields);

						if ($custom_fields) {
							echo '<ul class="post-meta">';
							foreach ($custom_fields as $c => $values) {
								$valueline = '';
								foreach ($values as $i => $value) {
									$valueline .= $value.', ';
								}
								echo '<li><span class="post-meta-key">'.$c.': </span>'.trim($valueline, ', ').'</li>';
							}
							echo '</ul>';
						}
					}
				}
			}
			if ($title_links == 1) { echo '</ul>'; }
			echo $after_widget;
		}
	}

	function update($new_instance, $old_instance) {

		$instance = $old_instance;

		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['category'] = (int) $new_instance['category'];
		$instance['show_post_title'] = (int) $new_instance['show_post_title'];
		$instance['show_custom_fields'] = (int) $new_instance['show_custom_fields'];
		$instance['number_of_posts'] = (int) $new_instance['number_of_posts'];
		$instance['title_links'] = (int) $new_instance['title_links'];
		$instance['shorten_posts'] = (int) $new_instance['shorten_posts'];

		return $instance;
	}

	function form($instance) {

		$instance = wp_parse_args((array) $instance, array('title' => __('Random Posts', 'random-one-cat-widget'), 'category' => 1, 'show_post_title' => 4, 'show_custom_fields' => 0, 'number_of_posts' => 1, 'title_links' => 0, 'shorten_posts' => 0));

		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'random-one-cat-widget'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']) ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category:', 'random-one-cat-widget'); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
					<?php
						$categories = get_categories(array('hide_empty' => 0));
						foreach ($categories as $cat) {
							$selected = $cat->cat_ID == $instance['category'] ? ' selected="selected"' : '';
							echo '<option'.$selected.' value="'.$cat->cat_ID.'">'.$cat->cat_name.'</option>';
						}
					?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('show_post_title'); ?>"><?php _e('Display which titles:', 'random-one-cat-widget'); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id('show_post_title'); ?>" name="<?php echo $this->get_field_name('show_post_title'); ?>">
					<?php
						$labels = array(1 => __('Neither Title', 'random-one-cat-widget'), 2 => __('Widget Title', 'random-one-cat-widget'), 3 => __('Post Title', 'random-one-cat-widget'), 4 => __('Both Titles', 'random-one-cat-widget'));
						for ($p = 1; $p <= 4; $p++) {
							$selected = $p == $instance['show_post_title'] ? ' selected="selected"' : '';
							echo '<option'.$selected.' value="'.$p.'">'.$labels[$p].'</option>';
						}
					?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('number_of_posts'); ?>"><?php _e('Number of posts:', 'random-one-cat-widget'); ?>
				<select class="widefat" id="<?php echo $this->get_field_id('number_of_posts'); ?>" name="<?php echo $this->get_field_name('number_of_posts'); ?>">
					<?php
						for ($p = 1; $p <= 8; $p++) {
							$selected = $p == $instance['number_of_posts'] ? ' selected="selected"' : '';
							echo '<option'.$selected.' value="'.$p.'">'.sprintf(__('Show up to %d posts', 'random-one-cat-widget' ), $p).'</option>';
						}
					?>
				</select>
				</label>
			</p>
			<p>
				<input id="<?php echo $this->get_field_id('show_custom_fields'); ?>" name="<?php echo $this->get_field_name('show_custom_fields'); ?>" type="checkbox" value="1" <?php if ($instance['show_custom_fields']) echo 'checked="checked"'; ?>/>
				<label for="<?php echo $this->get_field_id('show_custom_fields'); ?>"><?php _e('Show custom fields', 'random-one-cat-widget'); ?></label>
				<br>
				<input id="<?php echo $this->get_field_id('title_links'); ?>" name="<?php echo $this->get_field_name('title_links'); ?>" type="checkbox" value="1" <?php if ($instance['title_links']) echo 'checked="checked"'; ?>/>
				<label for="<?php echo $this->get_field_id('title_links'); ?>"><?php _e('Show title links only', 'random-one-cat-widget'); ?></label>
				<br>
				<input id="<?php echo $this->get_field_id('shorten_posts'); ?>" name="<?php echo $this->get_field_name('shorten_posts'); ?>" type="checkbox" value="1" <?php if ($instance['shorten_posts']) echo 'checked="checked"'; ?>/>
				<label for="<?php echo $this->get_field_id('shorten_posts'); ?>"><?php _e('Shorten post to More&hellip; tag', 'random-one-cat-widget'); ?></label>
			</p>
		<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("random_one_cat_widget");'));

load_plugin_textdomain('random-one-cat-widget', false, basename(dirname(__FILE__)).'/languages');

?>
