jQuery(document).ready(function($) {
	var loading_img = $("#wpecs-loading-img");
	$('#wpecs-form .wpecs-add-button').click(function(e){
		var clicked = $(this);
		clicked.attr("disabled", true);
		loading_img.show();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'wpecs_add_comment_ajax',
				post_id: $('#wpecs-form .wpecs-post-id').val(),
				comment: $('#wpecs-form .wpecs-textarea').val()
			},
			success:function(data, textStatus, XMLHttpRequest){
				loading_img.hide();
				clicked.attr("disabled", false);
				$('#wpecs-form .wpecs-textarea').val('');
				$('#wpecs-comments').html(data);
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});
	$('body').on('click', '#wpecs-comments .wpecs-edit-link', function(e){
		e.preventDefault();
		var comment_content = $(this).closest('.wpecs-comment-content');
		comment_content.hide();
		comment_content.siblings('.wpecs-comment-edit-form').first().fadeIn();
	});
	$('body').on('click', '#wpecs-comments .wpecs-comment-edit-form .wpecs-update-comment-button', function(e){
		e.preventDefault();
		var clicked = $(this);
		clicked.attr("disabled", true);
		loading_img.show();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'wpecs_update_comment_ajax',
				post_id: $('#wpecs-form .wpecs-post-id').val(),
				comment: clicked.siblings('textarea').first().val(),
				key: clicked.closest('.wpecs-comment').children('.wpecs-comment-id').first().val()
			},
			success:function(data, textStatus, XMLHttpRequest){
				loading_img.hide();
				clicked.attr("disabled", false);
				$('#wpecs-comments').html(data);
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});
	$('body').on('click', '#wpecs-comments .wpecs-comment-edit-form .wpecs-cancel-update-button', function(e){
		e.preventDefault();
		var edit_form = $(this).closest('.wpecs-comment-edit-form');
		edit_form.hide();
		edit_form.siblings('.wpecs-comment-content').first().fadeIn();
	});
	$('body').on('click', '#wpecs-comments .wpecs-delete-link', function(e){
		e.preventDefault();
		var clicked = $(this);
		loading_img.show();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'wpecs_delete_comment_ajax',
				post_id: $('#wpecs-form .wpecs-post-id').val(),
				key: clicked.closest('.wpecs-comment').children('.wpecs-comment-id').first().val()
			},
			success:function(data, textStatus, XMLHttpRequest){
				loading_img.hide();
				$('#wpecs-comments').html(data);
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});
	$('#wpecs-form .wpecs-delete-all-button').click(function(e){
		var clicked = $(this);
		clicked.attr("disabled", true);
		loading_img.show();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'wpecs_delete_all_ajax',
				post_id: $('#wpecs-form .wpecs-post-id').val()
			},
			success:function(data, textStatus, XMLHttpRequest){
				loading_img.hide();
				clicked.attr("disabled", false);
				$('#wpecs-comments').html('');
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});
});