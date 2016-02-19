<?php
/**
 * This file defines the WP Resonate widget, which outputs a list of sermon series'.
 * The list is hyperlinked to the user's chosen page (which should have the wp resonate shortcode to work).
 */
class WPResonate_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'wpresonate_widget', // Base ID
			__( 'WP Resonate', 'wordpress' ), // Name
			array( 'description' => __( 'Links to your sermons page, filtered by sermon series.', 'wordpress' ), ) // Args
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
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		
		echo '<ul>';        
        echo get_option(WPRESONATE_STORE, '<!-- WPRESONATE_STORE is empty -->');
		
		$is_show_allsermons_link = $instance[ 'show_allsermons_link' ] ? true : false;
		if ($is_show_allsermons_link) {
			$page_id = get_option ( WPRESONATE_PAGE_ID );
			$page_url = get_permalink ( $page_id );
			echo '<li><a href="'.esc_url ( $page_url ).'">Show all sermons</a></li>';
		}
		echo '</ul>';
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
		$defaults = array( 'title' => __( 'Filter by series', 'wordpress' ), 'show_allsermons_link' => 'on' );
		$instance = wp_parse_args( ( array ) $instance, $defaults );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
		
		<p><?php echo __('This widget will display links to your sermons page, filtered by their series.', 'wordpress'); ?></p>
		
		<input class="checkbox" type="checkbox" <?php checked( $instance[ 'show_allsermons_link' ], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_allsermons_link' ); ?>" name="<?php echo $this->get_field_name( 'show_allsermons_link' ); ?>" /> 
		<label for="<?php echo $this->get_field_id( 'show_allsermons_link' ); ?>">Display the "Show all sermons" link.</label>
		
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
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['show_allsermons_link'] = $new_instance[ 'show_allsermons_link' ];
		return $instance;
	}

} // class WPResonate_Widget
 
 
 /* Widget registration */
 // register WPResonate_Widget widget
function register_wpresonate_widget() {
    register_widget( 'WPResonate_Widget' );
}
add_action( 'widgets_init', 'register_wpresonate_widget' );
