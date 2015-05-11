<?php
/*
Plugin Name: A Moose Popular Post Plugin
Plugin URI: http://www.shourav.info/filterPlugin/
Description: This plugin/shorcode brings in JSON feed and displays.
Author: Da Moose
Version: 1.0
Author URI: http://www.shourav.info/

Copyright 2015  Da Moose

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

/*
* Dynamically inject counter into single posts
*
**/
function my_popular_post_views($postID) {

	$total_key = 'views';
	// Get current 'views field'
	$total = get_post_meta( $postID, $total_key, true );
	// if current 'views' field is empty, set it to zero 
	if ( $total == '' ) {
		delete_post_meta( $postID, $total_key, '0' );
		add_post_meta( $postID, $total_key, '0' );
	} else {
		// if current 'views' field has a value, add 1 to that value 
		$total++;
		update_post_meta( $postID, $total_key, $total );
	}
}

function my_count_popular_posts( $post_id ) {

	// Checks that this is a single post and that the user is a vistior
	if ( !is_single() ) return;
	if ( !is_user_logged_in() ) {
		// Get the post ID
		if ( empty( $post_id ) ) {
			global $post;
			$post_id = $post->ID;
		}

		// print_r($post_id);
		// echo "fuck you";

		// Run Popularity count on posts
		my_popular_post_views($post_id);

	}
}

add_action( 'wp_head', 'my_count_popular_posts' );


/*
 * Adds Popular Post function data to all posts table
 */

function my_add_views_columns($columns) {
	// print_r($defaults);	
	$columns['post_views'] = 'View Count';
	// echo $defaults;	

	return $columns;

}

add_filter( 'manage_posts_columns', 'my_add_views_columns' );

function my_display_views($column_name) {
	if ( $column_name == 'post_views' ) {
		echo (int) get_post_meta( get_the_ID(), 'views', true );
	}
}

add_action( 'manage_posts_custom_column', 'my_display_views', 5, 2 );

/*
 * Adds Popular Post widget.
 */
/**
 * Adds Popular_Posts widget.
 */
class Popular_Posts extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'popular_posts', // Base ID
			__( 'A Popular Post Widget', 'text_domain' ), // Name
			array( 'description' => __( 'Displays Popular Posts', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
			if ( ! empty( $instance['name'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['name'] ). $args['after_title'];
			}
			//echo __( 'Hello, World!', 'text_domain' );
		
			$query_args = array(
				'post_type' => 'post',
				'posts_per_page' => 5,
				'meta_key' => 'views',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'ignore_sticky_posts' => true
				
			);
			// The Query
			$the_query = new WP_Query( $query_args );

			// The Loop
			if ( $the_query->have_posts() ) {

				global $post;
				$meta_key = 'views';

				echo '<ul>';
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					// echo '<li>' . get_the_title() . '(' . get_post_meta( $post->ID, $meta_key, true ) .')</li>';
					echo '<li>';
					echo '<a href="' . get_the_permalink() . '">';
					echo get_the_title(); 
					echo '(' . get_post_meta( get_the_ID(), $meta_key, true ) .')';
					echo '</a>';
					echo '</li>';
				}
				echo '</ul>';
			} else {
				// no posts found
			}
			/* Restore original Post Data */
			wp_reset_postdata();

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$name = ! empty( $instance['name'] ) ? $instance['name'] : __( 'New title', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'name' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'name' ); ?>" name="<?php echo $this->get_field_name( 'name' ); ?>" type="text" value="<?php echo esc_attr( $name ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		// $instance = array();
		// $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$old_instance['name'] = strip_tags( stripslashes( $new_instance['name'] ) );

		return $old_instance;
	}

} // class Popular_Posts


// register Popular_Posts widget
function register_popular_posts_widget() {
    register_widget( 'Popular_Posts' );
}
add_action( 'widgets_init', 'register_popular_posts_widget' );
































