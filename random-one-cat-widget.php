<?php
/*
Plugin Name: Random One Cat Widget
Description: This Widget shows up to five random posts from a given category.
Author: BrokenCrust
Version: 2.2
Author URI: http://brokencrust.com/
Plugin URI: http://brokencrust.com/plugins/random-one-cat-widget/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class RandomOneCatWidget extends WP_Widget {

  function RandomOneCatWidget() {
    $widget_ops = array('classname' => 'widget_random_one_cat', 'description' => __('Shows up to five random posts from a given category.', 'random_one_cat') );
    $this->WP_Widget('random_one_cat_widget', __('Random One Cat', 'random_one_cat'), $widget_ops);
  }

  function widget($args, $instance) {
    extract($args, EXTR_SKIP);

    $title = apply_filters('widget_title', empty($instance['title'] ) ? __('Random Posts', 'random_one_cat') : $instance['title'], $instance, $this->id_base);
    $category = empty($instance['category']) ? 1 : $instance['category'];
    $show_post_title = empty($instance['show_post_title']) ? 4 : $instance['show_post_title'];
    $show_custom_fields = empty($instance['show_custom_fields']) ? 0 : $instance['show_custom_fields'];
    $number_of_posts = empty($instance['number_of_posts']) ? 1 : $instance['number_of_posts'];
    $title_links = empty($instance['title_links']) ? 0 : $instance['title_links'];

    $rand = get_posts('numberposts='.$number_of_posts.'&category='.$category.'&orderby=rand');

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
          echo '<div>'.do_shortcode($rand[$r]->post_content).'</div>';

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

    $instance['title'] = trim(strip_tags($new_instance['title']));
    $instance['category'] = (int) $new_instance['category'];
    $instance['show_post_title'] = (int) $new_instance['show_post_title'];
    $instance['show_custom_fields'] = (int) $new_instance['show_custom_fields'];
    $instance['number_of_posts'] = (int) $new_instance['number_of_posts'];
    $instance['title_links'] = (int) $new_instance['title_links'];

    return $instance;
  }

  function form($instance) {

    $instance = wp_parse_args((array) $instance, array('title' => __('Random Posts', 'random_one_cat'), 'category' => 1, 'show_post_title' => 4, 'show_custom_fields' => 0, 'number_of_posts' => 1, 'title_links' => 0));

    ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget title:', 'random_one_cat'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']) ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category:', 'random_one_cat'); ?></label>
        <select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
          <?php
            $categories = get_categories('');
            foreach ($categories as $cat) {
              if ($cat->cat_ID == $instance['category']) {
                $option = '<option selected="selected" value="'.$cat->cat_ID.'">';
              } else {
                $option = '<option value="'.$cat->cat_ID.'">';
              }
              $option .= $cat->cat_name.'</option>';
              echo $option;
            }
          ?>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('show_post_title'); ?>"><?php _e('Which Titles:', 'random_one_cat'); ?></label>
        <select id="<?php echo $this->get_field_id('show_post_title'); ?>" name="<?php echo $this->get_field_name('show_post_title'); ?>">
          <?php
            $option = '';
            $labels = array(1 => __('Neither', 'random_one_cat'), 2 => __('Widget Title', 'random_one_cat'), 3 => __('Post Title', 'random_one_cat'), 4 => __('Both', 'random_one_cat'));
            for ($p = 1; $p < 5; $p++) {
              if ($p == $instance['show_post_title']) {
                $option .= '<option selected="selected" value="'.$p.'">'.$labels[$p].'</option>';
              } else {
                $option .= '<option value="'.$p.'">'.$labels[$p].'</option>';
              }
            }
            echo $option;
          ?>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('number_of_posts'); ?>"><?php _e('Number of Posts:', 'random_one_cat'); ?>
          <select id="<?php echo $this->get_field_id('number_of_posts'); ?>" name="<?php echo $this->get_field_name('number_of_posts'); ?>">
            <?php
              $option = '';
              for ($p = 1; $p < 6; $p++) {
                if ($p == $instance['number_of_posts']) {
                  $option .= '<option selected="selected" value="'.$p.'">'.$p.'</option>';
                } else {
                  $option .= '<option value="'.$p.'">'.$p.'&nbsp;&nbsp;</option>';
                }
              }
              echo $option;
            ?>
          </select>
        </label>
      </p>
      <p>
        <input id="<?php echo $this->get_field_id('show_custom_fields'); ?>" name="<?php echo $this->get_field_name('show_custom_fields'); ?>" type="checkbox" value="1" <?php if ($instance['show_custom_fields']) echo 'checked="checked"'; ?>/>
        <label for="<?php echo $this->get_field_id('show_custom_fields'); ?>"><?php _e('Show Custom Fields?', 'random_one_cat'); ?></label>
      </p>
      <p>
        <input id="<?php echo $this->get_field_id('title_links'); ?>" name="<?php echo $this->get_field_name('title_links'); ?>" type="checkbox" value="1" <?php if ($instance['title_links']) echo 'checked="checked"'; ?>/>
        <label for="<?php echo $this->get_field_id('title_links'); ?>"><?php _e('Show Title Links Only?', 'random_one_cat'); ?></label>
      </p>
      <p>
    <?php
  }
}

add_action('widgets_init', create_function('', 'return register_widget("RandomOneCatWidget");'));

?>
