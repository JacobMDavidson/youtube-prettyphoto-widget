<?php
	/*
	Plugin Name: Youtube Video Lightbox Widget
	Description: Widget that uses a PrettyPhoto lightbox to play the video.
	Version: 1.0
	Author: Jacob Davidson
	Author URI: http://jacobmdavidson.com
	License: MIT
	License URI: http://opensource.org/licenses/MIT
	*/

	class YouTube_Video_Lightbox_Widget extends WP_Widget{

		/**
	 	* Register the widget with WordPress.
	 	*/
		public function __construct() {
			parent::__construct(
				'prettyphoto-youtube_lightbox_widget',
				'YouTube with PrettyPhoto',
				array( 'description' => __( 'Display a video in a widget, and play it in a lightbox', 'text_domain' ),
				 			'classname' => 'youtube_lightbox_widget',
							'id_base' => 'prettyphoto-youtube_lightbox_widget'	)
			);
		}

		/**
		 * Front-end display of the widget.
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		function widget($args, $instance){
			extract($args);

			// Extract the title, video url, and image url
			$title = apply_filters('widget_title', $instance['title']);
			$youtube_lightbox_video_url = $instance['youtube_lightbox_video_url'];
			$video_thumbnail = $instance['youtube_lightbox_display_thumbnail'];

			// Extract the video id from the video url
			$video_id = str_replace("https://www.youtube.com/watch?v=", "", "$youtube_lightbox_video_url");
			if (strlen(strstr($video_id, '&', true) != '0')){
				$video_id = strstr($video_id, '&', true);
			}

			// Extract the video title
			$video_content = file_get_contents('https://youtube.com/get_video_info?video_id=' . $video_id);
			parse_str($video_content, $youtube_description_array);
			$video_title = str_replace('"', '&#34;', $youtube_description_array['title']);

			// Build the HTML string
			$html_string = '<div id="youtube_lightbox_widget_video" class="widget">';

			// Add the video thumbnail link
			if($video_thumbnail == 'yes'){
				$html_string .= '<a href="'.$youtube_lightbox_video_url.'" title="'.$video_title.'" rel="prettyPhoto"><img class="latest_yt" src="https://img.youtube.com/vi/'.$video_id.'/hqdefault.jpg" width="300px"/></a><br />';
			}

			// Add the video title link
			$html_string .= '<p><a href="'.$youtube_lightbox_video_url.'" title="'. $video_title .'" rel="prettyPhoto">'. $video_title .'</a></p>';
			$html_string .= '</div>';

			// Display the widget
			echo $before_widget;
			if ( ! empty( $title ) )
				echo $before_title . $title . $after_title;
			echo $html_string;
			?>

			<!-- Add the rel tag prettyPhoto is looking for -->
			<script type="text/javascript" charset="utf-8">
	  			$(document).ready(function(){
	    		$("a[rel^='prettyPhoto']").prettyPhoto({
						social_tools: false
					});
	  		});
			</script>

			<?php
			echo $after_widget;
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from the database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		function update($new_instance, $old_instance){
			$instance = $old_instance;

			/* Strip tags (if needed) and update the widget settings. */
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['youtube_lightbox_video_url'] = strip_tags($new_instance['youtube_lightbox_video_url']);
			$instance['youtube_lightbox_display_thumbnail'] = esc_attr($new_instance['youtube_lightbox_display_thumbnail']);

			return $instance;
		}

		/**
		 * Backend widget form.
		 *
		 * @param array $instance Previously saved values from the database.
		 */
		function form($instance){

			// Set up some default widget settings.
			$defaults = array(
							'title' => '',
							'youtube_lightbox_display_thumbnail' => 'yes',
							'youtube_lightbox_video_url' => ''
						);
			$instance = wp_parse_args((array) $instance, $defaults);
			?>

			<!-- Display the setting form -->
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php _e('Title:'); ?>
				</label>
				<input id="<?php echo $this->get_field_id('title'); ?>"
					class="widefat" name="<?php echo $this->get_field_name('title'); ?>"
					type="text" value="<?php echo $instance['title']; ?>"
				/>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('youtube_lightbox_video_url'); ?>"><?php _e('Video URL:'); ?>
				<input id="<?php echo $this->get_field_id('youtube_lightbox_video_url'); ?>"
					class="widefat" name="<?php echo $this->get_field_name('youtube_lightbox_video_url'); ?>"
					type="text" value="<?php echo $instance['youtube_lightbox_video_url']; ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('youtube_lightbox_display_thumbnail'); ?>">
					<?php _e('Display Thumbnail:'); ?>
				</label>
				<select name="<?php echo $this->get_field_name('youtube_lightbox_display_thumbnail'); ?>"
					class="widefat" id="<?php echo $this->get_field_id('youtube_lightbox_display_thumbnail'); ?>">
					<option value="yes"<?php if($instance['youtube_lightbox_display_thumbnail'] == "yes"){
							echo " selected='selected'";
						} ?>>
						<?php _e('Yes'); ?>
					</option>
					<option value="no"<?php if($instance['youtube_lightbox_display_thumbnail'] == "no"){
							echo " selected='selected'";
						} ?>>
						<?php _e('No'); ?>
					</option>
				</select>
			</p>
	<?php
		}
	}

	/* Register the prettyPhoto scripts and styles */
	function register_prettyphoto() {
		wp_enqueue_style( 'prettyphoto-style', plugin_dir_url(__FILE__) . 'css/prettyPhoto.css' );
		wp_deregister_script('jquery');
		wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js', false, null);
		wp_enqueue_script('jquery');
		wp_register_script( 'prettyphoto-script', plugin_dir_url(__FILE__) . 'js/jquery.prettyPhoto.js' );
		wp_enqueue_script('prettyphoto-script');
	}

	add_action( 'wp_enqueue_scripts', 'register_prettyphoto' );

	// Register the widget
	add_action( 'widgets_init', function(){
		 register_widget( 'YouTube_Video_Lightbox_Widget' );
	});
?>
