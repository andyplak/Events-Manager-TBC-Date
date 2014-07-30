<?php
/**
 * Plugin Name: Events Manager - Event Date TBC
 * Plugin URI: http://www.andyplace.co.uk
 * Description: Plugin for Events Manager that allows an event to have a date displayed as "To Be Confirmed"
 * Version: 1.0
 * Author: Andy Place
 * Author URI: http://www.andyplace.co.uk
 * License: GPL2
 */

/**
 * Modify Event post admin date metabox.
 * Need to de-register and re-register with our mods as there are no hooks that allow us to modify it
 */
function em_tbc_modify_meta_box() {
  global $pagenow, $post;

  if($post->post_type == EM_POST_TYPE_EVENT && ($pagenow == 'post.php' || $pagenow == 'post-new.php') ){
    // Remove the existing one
    remove_meta_box('em-event-when', EM_POST_TYPE_EVENT, 'side');

    // Recreate with our TBC checkbox
    add_meta_box('em-event-when', __('When','dbem'), 'em_tbc_meta_box_date', EM_POST_TYPE_EVENT, 'side', 'high');
  }
}
add_action( 'add_meta_boxes', 'em_tbc_modify_meta_box', 50);


/**
 * Markup for our metabox. Make use of existing EM tempalte and add our checkbox
 */
function em_tbc_meta_box_date() {
  global $post;

  $checked = '_event_date_tbc';
  if( get_post_meta( $post->ID, '_event_date_tbc', true ) ) {
    $checked = 'checked=checked';
  }

  ?>
  <input type="hidden" name="_emnonce" value="<?php echo wp_create_nonce('edit_event'); ?>" />
  <input type="checkbox" class="em-date-tbc" name="event_date_tbc" id="event_date_tbc" <?php echo $checked ?>>
  Show event date as <strong>To Be Confirmed</strong>.
  <?php
  em_locate_template('forms/event/when.php', true);
}

/**
 * Save tbc option setting from out metabox
 */
function em_tbc_save_post($post_id, $post) {

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if ( !wp_verify_nonce( $_POST['_emnonce'], 'edit_event' ) ) {
    return $post->ID;
  }

  // Is the user allowed to edit the post or page?
  if ( !current_user_can( 'edit_post', $post->ID ))
    return $post->ID;

  if( $post->post_type == 'revision' )
    return $post->ID; // Don't store custom data twice

  if( isset( $_POST['event_date_tbc'] ) ) {
    update_post_meta( $post->ID, '_event_date_tbc', $_POST['event_date_tbc'] );
  }else{
    delete_post_meta( $post->ID, '_event_date_tbc' );
  }

}
add_action('save_post', 'em_tbc_save_post', 1, 2);


// @TODO Hook into event display date placeholder