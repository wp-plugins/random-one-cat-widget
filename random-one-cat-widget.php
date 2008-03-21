<?php
/*
Plugin Name: Random One Cat Widget
Description: This Widget shows a single random post from a given category.
Author: BrokenCrust
Version: 0.2
Author URI: http://www.brokencrust.eu/
Plugin URI: http://www.brokencrust.eu/index.php?name=Forums&file=viewforum&f=21
*/

function widget_random_one_cat_init() {

	if (!function_exists('register_sidebar_widget')) { return; }

	function widget_random_one_cat($args, $number = 1) {
		if ($output = wp_cache_get('widget_random_one_cat')) { return print($output); }

		extract($args);

		$options = get_option('widget_random_one_cat');

		$title = $options[$number]['title'] ? $options[$number]['title'] : __('Random Post');
		$show_post_title = $options[$number]['show_post_title'] ? 1 : 0;
		$category = $options[$number]['category'] ? intval($options[$number]['category']) : 1;

		$rand = get_posts('numberposts=1&category='.$category.'&orderby=RAND()');

		echo $before_widget;
		echo $before_title . $title . $after_title;
		if ($show_post_title == 1) {
			?>
			<h3><?php echo $rand[0]->post_title; ?></h3>
			<?php
		}
		echo $rand[0]->post_content;
		echo $after_widget;
        }

	function widget_random_one_cat_control($number) {

		$options = $newoptions = get_option('widget_random_one_cat');

		if ( $_POST['random_one_cat-submit-'.$number] ) {
			$newoptions[$number]['title'] = strip_tags(stripslashes($_POST["random_one_cat-title-$number"]));
			$newoptions[$number]['category'] = intval($_POST["random_one_cat-category-$number"]);
			$newoptions[$number]['show_post_title'] = ($_POST["random_one_cat-show_post_title-$number"]);
		}

		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_random_one_cat', $options);
		}

		$title = attribute_escape($options[$number]['title']);
		$category = $options[$number]['category'] ? $options[$number]['category'] : 1;
		$show_post_title = $options[$number]['show_post_title'] ? 'checked="checked"' : '';

?>
	<p>
		<label for="random_one_cat-title-<?php echo "$number"; ?>">
			<?php _e('Title:'); ?>
			<input style="width: 250px;" id="random_one_cat-title-<?php echo "$number"; ?>" name="random_one_cat-title-<?php echo "$number"; ?>" type="text" value="<?php echo $title; ?>" />
		</label>
	</p>
	<p>
		<label for="random_one_cat-category-<?php echo "$number"; ?>">
			<?php _e('Category:'); ?>
			<select name="random_one_cat-category-<?php echo "$number"; ?>" id="random_one_cat-category-<?php echo "$number"; ?>">
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
	<p>
		<label for="random_one_cat-show_post_title-<?php echo "$number"; ?>">
			<?php _e('Show Post Title'); ?>
			<input class="checkbox" type="checkbox" <?php echo $show_post_title; ?> id="random_one_cat-show_post_title-<?php echo "$number"; ?>" name="random_one_cat-show_post_title-<?php echo "$number"; ?>" />
		</label>
	</p>
	<input type="hidden" id="random_one_cat-submit-<?php echo "$number"; ?>" name="random_one_cat-submit-<?php echo "$number"; ?>" value="1" />
<?php
	}
	widget_random_one_cat_register();
}

function widget_random_one_cat_setup() {
	$options = $newoptions = get_option('widget_random_one_cat');
	if ( isset($_POST['random_one_cat-number-submit']) ) {
		$number = (int) $_POST['random_one_cat-number'];
		if ( $number > 9 ) $number = 9;
		if ( $number < 1 ) $number = 1;
		$newoptions['number'] = $number;
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_random_one_cat', $options);
		widget_random_one_cat_register($options['number']);
	}
}

function widget_random_one_cat_page() {
	$options = $newoptions = get_option('widget_random_one_cat');
?>
	<div class="wrap">
		<form method="POST">
		<h2>Random One Cat Widgets</h2>
		<p style="line-height: 30px;"><?php _e('How many Random One Cat widgets would you like?'); ?>
		<select id="random_one_cat-number" name="random_one_cat-number" value="<?php echo $options['number']; ?>">
<?php for ( $i = 1; $i < 10; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
		</select>
		<span class="submit"><input type="submit" name="random_one_cat-number-submit" id="random_one_cat-number-submit" value="<?php _e('Save'); ?>" /></span></p>
		</form>
	</div>
<?php
}

function widget_random_one_cat_register() {
	$options = get_option('widget_random_one_cat');
	$number = $options['number'];
	if ( $number < 1 ) $number = 1;
	if ( $number > 9 ) $number = 9;
	for ($i = 1; $i <= 9; $i++) {
		$name = array('Random One Cat %s', null, $i);
		register_sidebar_widget($name, $i <= $number ? 'widget_random_one_cat' : /* unregister */ '', $i);
		register_widget_control($name, $i <= $number ? 'widget_random_one_cat_control' : /* unregister */ '', 90, 300, $i);
	}
	add_action('sidebar_admin_setup', 'widget_random_one_cat_setup');
	add_action('sidebar_admin_page', 'widget_random_one_cat_page');
}

add_action('widgets_init', 'widget_random_one_cat_init');

?>
