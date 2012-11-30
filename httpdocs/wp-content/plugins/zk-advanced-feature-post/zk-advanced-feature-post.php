<?php 
/*
Plugin Name:  ZK Advanced Feature Post
Plugin URI: http://zinki.info/158-wordpress-plugin-zk-advanced-feature-post
Description:  Display Global Featured Post or Custom Category Featured Post. Can add anywhere in your template or using widget.
Version: 1.8.21
Author: vn.Zinki
Author URI: http://zinki.info
*/

// Get Featured Post Function
function zk_featured($options = array()) {
	$zkafp_opts = get_option('zkafp_opts');
	
	$out = ( $options['method'] == 'array') ? array() : '';
	
	if ( $options['cat'] ) {
		$zkafp_query = array(
			'cat' => $options['cat'],
			'post__in' => explode(',',$zkafp_opts['featured_cat']),
		);
	} else {
		$zkafp_query = array(
			'post__in' => explode(',',$zkafp_opts['featured_all']),
		);
		
	}
	
	if ( $options['count'] ) $zkafp_query += array('posts_per_page' => $options['count']);
	if ( $options['orderby'] ) $zkafp_query += array('orderby' => $options['orderby']);
	if ( $options['order'] ) $zkafp_query += array('order' => $options['order']);
	
	query_posts($zkafp_query);
	
	if ( $options['method'] == 'loop' ) {
		return;
	}
	
	if ( $options['method'] == 'array' ) {
		while (have_posts()) : the_post();
			$the_post = array(
						'id'      => get_the_ID(),
						'title'   => get_the_title(),
						'excerpt' => get_the_excerpt(),
						'url' => get_permalink(),
						'content' => get_the_content(),
						'author' => get_the_author(),
					);
			array_push( $out, $the_post);
		endwhile;
		return $out;
	}
}


// Trim paragraph function 

function trim_words($text, $lenght) {
	$text = explode(' ',$text);
	for ($i = 0; $i < $lenght; $i++) {
		$return_text .= $text[$i] . ' ';
	}
	return $return_text . '...';
}

// add featured column
function zkafp_add_posts_column($defaults) {
	$defaults['Featured_all'] = __('ALL');
	$defaults['Featured_cat'] = __('CAT');
	return $defaults;
}

// handle the post listing
function zkafp_posts_column($column_name, $id) {
    if ( $column_name == 'Featured_all' ) {

        $zkafp_opts = get_option('zkafp_opts');
        $featured_arr = explode(',', $zkafp_opts['featured_all']);
        
		$class = in_array( $id, $featured_arr ) ? 'class="zkafp_on"' : 'class="zkafp_off"';
		
        echo '<div id="zkafp_all_' .$id . '" onclick="zkafp_admin_ajax(' . $id . ',1)" ' . $class . '></div>';
    }
	
	if ( $column_name == 'Featured_cat' ) {

        $zkafp_opts = get_option('zkafp_opts');
        $featured_arr = explode(',', $zkafp_opts['featured_cat']);
        
		$class = in_array( $id, $featured_arr ) ? 'class="zkafp_on"' : 'class="zkafp_off"';
		
        echo '<div id="zkafp_cat_' .$id . '" onclick="zkafp_admin_ajax(' . $id . ',0)" ' . $class . '></div>';
    }
}

function zkafp_ajax_process() {
	$zkafp_opts = get_option('zkafp_opts');
	$id = $_POST['id'];
	$type = $_POST['type'];
	
	if ($type == 1) {
		$featured_arr = $zkafp_opts['featured_all'] ? explode(',', $zkafp_opts['featured_all']) : array();
		if (!in_array($id, $featured_arr)) {
			array_push($featured_arr, $id);
		} else {
			$key = array_search($id, $featured_arr);
			unset($featured_arr[$key]);
			$featured_arr = array_values($featured_arr);
			$is_on = true;
		}
    
		$featured_str = '';
		foreach ( $featured_arr as $post_id ) {
			// array to string
			$featured_str .= $post_id . ',';
		}
		
		if ($featured_str) $featured_str = substr($featured_str, 0, -1);
		
		$zkafp_opts['featured_all'] = $featured_str;
		
		update_option('zkafp_opts', $zkafp_opts);
		
		die( "var containerBox = document.getElementById('zkafp_all_$id'); containerBox.className = '" . ($is_on ? 'zkafp_off' : 'zkafp_on' ) . "';");
		
	} elseif ($type == 0) {
		$featured_arr = $zkafp_opts['featured_cat'] ? explode(',', $zkafp_opts['featured_cat']) : array();
		if (!in_array($id, $featured_arr)) {
			array_push($featured_arr, $id);
		} else {
			$key = array_search($id, $featured_arr);
			unset($featured_arr[$key]);
			$featured_arr = array_values($featured_arr);
			$is_on = true;
		}
		
		$featured_str = '';
		foreach ( $featured_arr as $post_id ) {
			// array to string
			$featured_str .= $post_id . ',';
		}
		
		if ($featured_str) $featured_str = substr($featured_str, 0, -1);
		
		$zkafp_opts['featured_cat'] = $featured_str;
		
		update_option('zkafp_opts', $zkafp_opts);
		
		die( "var containerBox = document.getElementById('zkafp_cat_$id'); containerBox.className = '" . ($is_on ? 'zkafp_off' : 'zkafp_on' ) . "';");
	} else {
		die( "alert('Error with feature type? ALL or CAT ?')" );
	}
	
}

class ZK_Advanced_Feature_Post extends WP_Widget {

	function ZK_Advanced_Feature_Post() {
		$widget_ops = array( 'classname' => 'zk-afp', 'description' => __('ZK Advanced Feature Post', 'genesis') );
		$this->WP_Widget( 'zk-afp', __('ZK Advanced Feature Post', 'genesis'), $widget_ops );
	}

	function widget($args, $instance) {
		extract($args);
		
		$include = '';
		if(!empty($instance['include'])) {
			foreach($instance['include'] as $cat) {
				$include .= $cat.',';
			}
			$include = substr($include,0,-1);
		} else {
			$include = 'all';
		}
		
		
		echo $before_widget;
		
			if (!empty($instance['title'])) echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;
				
			$options = array(	'method' => 'loop',
								'cat' => $include,
								'count' => $instance['count'],
								'orderby' => $instance['order_by'],
								'order' => $instance['order']);

			zk_featured($options);
			
			echo '<ul class="zk-afp">';			
			while (have_posts()) : the_post();
				//Get Image Thumb
				if (1 == $instance['image_type']) {
					preg_match('/<img[^>]+src\s*=\s*"([^"]*)"[^>]*>/',get_the_content(),$img);
					$img = ($img[1]) ? $img[1] : WP_PLUGIN_URL.'/zk-advanced-feature-post/img/no_thumb.png';
				} elseif (2 == $instance['image_type']) {
					preg_match('/<img[^>]+src\s*=\s*"([^"]*)"[^>]*>/',get_the_post_thumbnail(),$img);
					$img = ($img[1]) ? $img[1] : WP_PLUGIN_URL.'/zk-advanced-feature-post/img/no_thumb.png';
					$ing = get_the_post_thumbnail();
				}
				
				
				if (1 == $instance['display_type']) {
				echo '<li class="link-only">
					<a href="'.get_permalink().'" title="'.get_the_title().'">'.get_the_title().'</a>
					</li>';
				} elseif (2 == $instance['display_type']) {
				echo '<li class="thumb-only">
					<a href="'.get_permalink().'" title="'.get_the_title().'">
					<img class="imgborder" alt="'.get_the_title().'" src="'.$img.'" /></a>
					</li>';
				} elseif (3 == $instance['display_type']) {
				echo '<li class="exceprt">
					<b><a href="'.get_permalink().'" title="'.get_the_title().'">'.get_the_title().'</a></b><br/>
					<img alt="'.get_the_title().'" src="'.$img.'" /><p>'.trim_words(strip_tags(get_the_excerpt()),$instance['exceprt_lenght']).'</p>
					<div class="readmore"><a href="'.get_permalink().'" title="'.get_the_title().'">Readmore ...</a></div>
					</li>';
				}
			endwhile;
			echo '<div class="clear"></div></ul>';
			
		echo $after_widget;
		wp_reset_query();
	}

	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	function form($instance) { 	
		$instance = wp_parse_args( (array)$instance, array('count' => 5 ) );
		
		if(empty($instance['count'])) $instance['count'] = '5';
		
	?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'genesis'); ?>:</label><br />
		<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:95%;" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Number of post', 'genesis'); ?>:</label><br />
		<input type="text" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" value="<?php echo esc_attr( $instance['count'] ); ?>" style="width:95%;" />
		</p>
		
		<div id="categorydiv">
			<ul class="categorychecklist">
			<?php zk_category_checklist($this->get_field_name('include'), $instance['include']); ?>
			</ul>
		</div>
		
		<p><label for="<?php echo $this->get_field_id('display_type'); ?>"><?php _e('Display type', 'genesis'); ?>:</label>
		<select id="<?php echo $this->get_field_id('display_type'); ?>" name="<?php echo $this->get_field_name('display_type'); ?>">
			<option style="padding-right:10px;" value="1" <?php selected('1', $instance['display_type']); ?>><?php _e('Link only', 'genesis'); ?></option>
			<option style="padding-right:10px;" value="2" <?php selected('2', $instance['display_type']); ?>><?php _e('Thumb Only', 'genesis'); ?></option>
			<option style="padding-right:10px;" value="3" <?php selected('3', $instance['display_type']); ?>><?php _e('Exceprt', 'genesis'); ?></option>
		</select></p>
		
		<p><label for="<?php echo $this->get_field_id('image_type'); ?>"><?php _e('Image type', 'genesis'); ?>:</label>
		<select id="<?php echo $this->get_field_id('image_type'); ?>" name="<?php echo $this->get_field_name('image_type'); ?>">
			<option style="padding-right:10px;" value="1" <?php selected('1', $instance['image_type']); ?>><?php _e('First Image on Article', 'genesis'); ?></option>
			<option style="padding-right:10px;" value="2" <?php selected('2', $instance['image_type']); ?>><?php _e('Featured Image', 'genesis'); ?></option>
		</select></p>
		
		<p>
		<label for="<?php echo $this->get_field_id('exceprt_lenght'); ?>"><?php _e('Exceprt Lenght', 'genesis'); ?>:</label><br />
		<input type="text" id="<?php echo $this->get_field_id('exceprt_lenght'); ?>" name="<?php echo $this->get_field_name('exceprt_lenght'); ?>" value="<?php echo esc_attr( $instance['exceprt_lenght'] ); ?>" style="width:95%;" />
		</p>
		
		<p><label for="<?php echo $this->get_field_id('order_by'); ?>"><?php _e('Display order', 'genesis'); ?>:</label>
		<select id="<?php echo $this->get_field_id('order_by'); ?>" name="<?php echo $this->get_field_name('order_by'); ?>">
			<option style="padding-right:10px;" value="date" <?php selected('date', $instance['order_by']); ?>><?php _e('Date', 'genesis'); ?></option>
			<option style="padding-right:10px;" value="comment_count" <?php selected('comment_count', $instance['order_by']); ?>><?php _e('Comment Count', 'genesis'); ?></option>
			<option style="padding-right:10px;" value="rand" <?php selected('rand', $instance['order_by']); ?>><?php _e('Random', 'genesis'); ?></option>
		</select></p>
		
		<p><label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Sort', 'genesis'); ?>:</label>
		<select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
			<option style="padding-right:10px;" value="asc" <?php selected('asc', $instance['order']); ?>><?php _e('ASC', 'genesis'); ?></option>
			<option style="padding-right:10px;" value="desc" <?php selected('desc', $instance['order']); ?>><?php _e('DESC', 'genesis'); ?></option>
		</select></p>
		
	<?php
	}
}

//	My Custom Category Checklist
function zk_category_checklist($name = '', $selected = array()) {
	$name = esc_attr( $name );

	//	categories
	ob_start();
	wp_category_checklist(0,0, $selected, false, '', $checked_on_top = false);
	$checkboxes .= str_replace('name="post_category[]"', 'name="'.$name.'[]"', ob_get_clean());
		
	echo $checkboxes;
}

function zkafp_add_style() {
	//Import Style
	echo '<link media="screen" type="text/css" href="'. WP_PLUGIN_URL .'/zk-advanced-feature-post/css/style.css" id="zkafp_css" rel="stylesheet">';
}

function zkafp_add_admin_js() {
	// use JavaScript SACK library for Ajax
    wp_print_scripts( array( 'sack' ));
	//Import admin JS
	echo '<script src="'. WP_PLUGIN_URL .'/zk-advanced-feature-post/js/admin.js" type="text/javascript"></script>';
}

//add column to post listings
add_filter('manage_posts_columns', 'zkafp_add_posts_column');
add_filter('manage_posts_custom_column', 'zkafp_posts_column', 10, 2);

//add AJAX process
add_action('wp_ajax_zkafp_admin', 'zkafp_ajax_process' );

//Widget
add_action('widgets_init', create_function('', "register_widget('ZK_Advanced_Feature_Post');"));

//add style
add_action('admin_head','zkafp_add_style');
add_action('wp_head','zkafp_add_style');

//add js handle
add_action('admin_footer','zkafp_add_admin_js');