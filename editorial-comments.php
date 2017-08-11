<?php
/*
Plugin Name: Editorial Comments
Plugin URI: http://blogcubes.com/
Description: Facilitates discussion between authors and editors by inserting a comment system below the post creation form.
Version: 1.0
Author: Paul Rabeel
Author URI: http://blogcubes.com/
License: GPL2
*/

function wpecs_enqueue_files($hook){
	if($hook != 'post.php')
		return;

	wp_enqueue_script('wpecs-metabox-script', plugins_url('js/metabox.js', __FILE__), array('jquery'));
	wp_enqueue_style('wpecs-metabox-style', plugins_url('css/metabox.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'wpecs_enqueue_files');

function wpecs_post_meta_boxes_setup() {
	add_action( 'add_meta_boxes', 'wpecs_add_post_meta_boxes' );
}
add_action( 'load-post.php', 'wpecs_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'wpecs_post_meta_boxes_setup' );

function wpecs_add_post_meta_boxes() {
	global $post;
	if(($post->post_status == 'publish' || $post->post_status == 'pending' || $post->post_status == 'draft') && current_user_can('edit_post', $post->ID))
		add_meta_box(
			'wpecs-editorial-comments',			// Unique ID
			'Editorial Comments',				// Title
			'wpecs_meta_box_callback',			// Callback function
			null,								// Admin page (or post type)
			'normal',							// Context
			'default'							// Priority
		);
}

function wpecs_meta_box_callback( $post, $box ) {
?>
	<table id="wpecs-comments">
		<tbody>
			<?php wpecs_print_comments($post->ID); ?>
		</tbody>
	</table>
	<div id="wpecs-form">
		<textarea class="wpecs-textarea"></textarea>
		<input type="hidden" class="wpecs-post-id" value="<?php echo $post->ID; ?>" />
		<button class="wpecs-add-button button" type="button">Add Comment</button>
		<?php if(current_user_can('moderate_comments')): ?><button class="wpecs-delete-all-button button" type="button">Delete All</button><?php endif; ?>
		<img src="<?php echo plugins_url('img/loading.gif', __FILE__); ?>" id="wpecs-loading-img" />
	</div>
<?php
}

function wpecs_print_comments($post_id, $comments = false){
	if(!$comments)
		$comments = get_post_meta( $post_id, 'wpecs_editorial_comments', true );
?>
		<?php if(!empty($comments) && count($comments)>0): ?>
			<?php foreach ($comments as $key => $comment): ?>
				<tr class="wpecs-row">
					<th class="wpecs-user">
						<img id="wpecs-image" src="http://www.gravatar.com/avatar/<?=md5(trim(get_the_author_meta('user_email', $comment['user_id'])))?>?s=64"/>
						<?php echo get_the_author_meta('display_name', $comment['user_id']); ?>
					</th>
					<td class="wpecs-comment">
						<div class="wpecs-comment-content">
							<?php echo wpautop(stripslashes($comment['comment'])); ?>
							<?php if($comment['user_id'] == get_current_user_id()): ?>
								<a href="#" class="wpecs-edit-link">Edit</a> <a href="#" class="wpecs-delete-link">Delete</a>
							<?php endif; ?>
						</div>
						<?php if($comment['user_id'] == get_current_user_id()): ?>
							<div class="wpecs-comment-edit-form">
								<textarea><?php echo stripslashes($comment['comment']); ?></textarea>
								<button type="button" class="wpecs-update-comment-button button">Update</button>
								<button type="button" class="wpecs-cancel-update-button button">Cancel</button>
							</div>
						<?php endif; ?>
						<input type="hidden" class="wpecs-comment-id" value="<?php echo $key; ?>" />
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
<?php
}

function wpecs_add_comment($post_id, $comment){
	$user_id = get_current_user_id();
	$comments = get_post_meta( $post_id, 'wpecs_editorial_comments', true );
	$comments = (!$comments)?array():$comments;
	$comments[] = array(
					'user_id' => $user_id,
					'comment' => $comment
				);
	update_post_meta($post_id, 'wpecs_editorial_comments', $comments);
	return $comments;
}

function wpecs_add_comment_ajax(){
	$post_id = $_POST['post_id'];
	$comment = $_POST['comment'];
	$updated_comments = wpecs_add_comment($post_id, $comment);
	wpecs_print_comments($post_id, $updated_comments);
	die();
}
add_action( 'wp_ajax_wpecs_add_comment_ajax', 'wpecs_add_comment_ajax' );

function wpecs_update_comment($post_id, $comment, $key){
	$comments = get_post_meta( $post_id, 'wpecs_editorial_comments', true );
	if($comments[$key]['user_id'] != get_current_user_id())
		return $comments;
	$comments = (!$comments)?array():$comments;
	$comments[$key]['comment'] = $comment;
	update_post_meta($post_id, 'wpecs_editorial_comments', $comments);
	return $comments;
}
function wpecs_update_comment_ajax(){
	$post_id = $_POST['post_id'];
	$comment = $_POST['comment'];
	$key = $_POST['key'];
	$updated_comments = wpecs_update_comment($post_id, $comment, $key);
	wpecs_print_comments($post_id, $updated_comments);
	die();
}
add_action( 'wp_ajax_wpecs_update_comment_ajax', 'wpecs_update_comment_ajax' );

function wpecs_delete_comment($post_id, $key){
	$comments = get_post_meta( $post_id, 'wpecs_editorial_comments', true );
	if($comments[$key]['user_id'] != get_current_user_id())
		return $comments;
	$comments = (!$comments)?array():$comments;
	if(isset($comments[$key]))
		unset($comments[$key]);
	$comments = array_values($comments);
	update_post_meta($post_id, 'wpecs_editorial_comments', $comments);
	return $comments;
}

function wpecs_delete_comment_ajax(){
	$post_id = $_POST['post_id'];
	$key = $_POST['key'];
	$updated_comments = wpecs_delete_comment($post_id, $key);
	wpecs_print_comments($post_id, $updated_comments);
	die();
}
add_action( 'wp_ajax_wpecs_delete_comment_ajax', 'wpecs_delete_comment_ajax' );

function wpecs_delete_all($post_id){
	delete_post_meta($post_id, 'wpecs_editorial_comments');
}

function wpecs_delete_all_ajax(){
	$post_id = $_POST['post_id'];
	wpecs_delete_all($post_id);
	die();
}
add_action( 'wp_ajax_wpecs_delete_all_ajax', 'wpecs_delete_all_ajax' );

function wpecs_add_admin_column_headers($headers, $something){
	$headers['wpecs_e_comments'] = "Editorial Comments";
	return $headers;
}
add_filter( 'manage_posts_columns', 'wpecs_add_admin_column_headers', 10, 2 );

function wpecs_add_admin_column_contents($header, $post_id){
	$comments = get_post_meta( $post_id, 'wpecs_editorial_comments', true );
	if($header == 'wpecs_e_comments')
		echo (!empty($comments))?count($comments):0;
}
add_filter( 'manage_posts_custom_column', 'wpecs_add_admin_column_contents', 10, 2 );