
function sp_reset_with_selected() {
	$("#sp_action_with_selected option:first").attr('selected', 'selected');
}

if(tool_prompt_actions==undefined) {
	var tool_prompt_actions = 'Yes';
}

function spam_protection_create_confirm_action(rclass, text) {
	$("."+rclass).bind('click', function() {
		
		var confirm_mark = confirm(text);

		if(!confirm_mark) {
			return false;
		}
		else {
			window.location = window.link_original_url;
			return true;
		}
	}).bind('mouseover', function() {
		window.link_original_url = $(this).attr('href');
		$(this).attr('href', document.location+'#'+rclass);
	}).bind('mouseout', function() {
		$(this).attr('href', window.link_original_url);
	});	
}

$(document).ready(function() {
	
	spam_protection_create_confirm_action('confirm_mark_as_spam', sp_lang['mark_as_spam']);
	spam_protection_create_confirm_action('confirm_mark_as_ham', sp_lang['mark_as_ham']);
	
	$("#with_selected_button").remove();
	
	$("#sp_action_with_selected").change(function() {
		
		var total_selected = $(".sp_checkbox:checked").length;
		var with_selected_option = $("#sp_action_with_selected option:selected").val();

		if(total_selected==0 && with_selected_option!='null') {
			alert(sp_lang['must_select_atleast_one']);
		}
		
		if(with_selected_option!='null' && total_selected>0) {
			if(tool_prompt_actions=='Yes') {
				var box = confirm('Really "'+$("#sp_action_with_selected option:selected").attr('text')+'" with '+total_selected+' selected items ?');
			
				if(box) {
					$("#sp_with_selected").submit();
				}
			}
			else {
				$("#sp_with_selected").submit();
			}
		}
		
		sp_reset_with_selected();
	});

	$(".sp_mouseover").click(function(event){
		
		var is_checkbox = false;
		var event_html = $(event.target).html();
		var has_selectclick = $(event.target).hasClass('sp_selectclick');
		var has_input_box = event_html.indexOf("<input ");
		
		if(event_html=="") {
			is_checkbox = true;
		}
		else {
			has_selectclick = true;
		}
		
		if(has_input_box>-1) {
			has_selectclick = true;
		}

		if($(".sp_checkbox:checkbox", this).attr('checked')!=true && !is_checkbox && has_selectclick==true) {
			$(".sp_checkbox:checkbox", this).attr('checked', 'checked');
		}
		else if($(".sp_checkbox:checkbox", this).attr('checked')==true && !is_checkbox && has_selectclick==true) {
			$(".sp_checkbox:checkbox", this).attr('checked', '');
		}
		
	});

	$(".sp_checkall").click(function() {
		if($(".sp_checkall:checked").val()=='on') {
			$(".sp_checkbox").attr('checked', 'checked');
		}
		else {
			$(".sp_checkbox").attr('checked', '');
		}
	});
});