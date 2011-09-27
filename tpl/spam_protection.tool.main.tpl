<!-- BEGIN: MAIN -->

<link href="{PHP.tool_path}/inc/spam_protection.tool.css" type="text/css" rel="stylesheet" />
<!-- IF {PHP.cfg.jquery} == "1" -->
<script type="text/javascript">
	var tool_prompt_actions = "{PHP.cfg.plugin.spam_protection.tool_prompt_actions}";
	var sp_lang = {
		'mark_as_ham': '{PHP.L.sp_Really_mark_as_ham}',
		'mark_as_spam': '{PHP.L.sp_Really_mark_as_spam}',
		'must_select_atleast_one': '{PHP.L.sp_You_must_select_atleast_one_item}',
	};
</script>
<script src="{PHP.tool_path}/inc/spam_protection.tool.js" type="text/javascript"></script>
<!-- ENDIF -->

<div style="margin-bottom: 15px; clear: both;">
	<h3>{SPAM_PROTECTION_TOOL_TITLE}</h3>

	<div class="sp_list" style="overflow: hidden; margin-bottom: 10px;">
		<div class="sp_list sp_left">
			{PHP.L.sp_Moderate_spam_for}: &nbsp; {SPAM_PROTECTION_SECTION_LINK_COMMENTS} &nbsp;-&nbsp; {SPAM_PROTECTION_SECTION_LINK_FORUMS}<br />
		</div>
		<div class="sp_list" style="float: right;">
			<a href="{SPAM_PROTECTION_LINK_CONFIGURATION}">{PHP.L.Configuration}</a>
			&nbsp;-&nbsp;
			<a href="{SPAM_PROTECTION_LINK_CHECK_API_KEY}">{PHP.L.sp_Check_api_key}</a>
		</div>
	</div>

	<!-- BEGIN: VIEW_SPAM_ITEM -->
		<div style="margin-top: 20px; margin-bottom: 20px;">
			{PHP.L.sp_All_information_stored}
		</div>
		<div style="margin-bottom: 20px;">
			{PHP.L.Options}:&nbsp; {SPAM_PROTECTION_DATA_MARKAS_SPAM_LINK} 
			&nbsp;|&nbsp; {SPAM_PROTECTION_DATA_MARKAS_HAM_LINK}
			&nbsp;|&nbsp; {SPAM_PROTECTION_DATA_GOBACK}
		</div>
		<hr />
		<!-- BEGIN: LIST_SPAM_DATA -->
		<ul class="sp_list" style="overflow: hidden; padding: 5px;">
			<li class="sp_list sp_left" style="width: 35%;">{SPAM_PROTECTION_DATA_NAME}:</li>
			<li class="sp_list sp_left" style="width: 60%;">{SPAM_PROTECTION_DATA_VALUE}</li>
		</ul>
		<hr />
		<!-- END: LIST_SPAM_DATA -->
	<!-- END: VIEW_SPAM_ITEM -->
	<!-- BEGIN: ERROR_MESSAGE -->
		<h3>{PHP.L.sp_An_error_has_occurred}</h3>
		<p>
			<span style="line-height: 180%;">
				{SPAM_PROTECTION_ERROR_MESSAGE}
			</span>
		</p>
	<!-- END: ERROR_MESSAGE -->
		<!-- BEGIN: IS_IN_VIEW_SECTION -->
		<div style="overflow: hidden; clear: both; padding-top: 8px; padding-bottom: 12px;">
			<div style="float: left;">
				<h3 style="margin: 0px; padding: 0px; margin-bottom: 5px;">{SPAM_PROTECTION_SECTION_TITLE}</h3>
			</div>
			<div style="float: right;">
				{PHP.L.Page}: {SPAM_PROTECTION_SPAM_PAGE} &nbsp;-&nbsp; {PHP.L.sp_Showing} {SPAM_PROTECTION_SPAM_ITEMS_SELECTED_COUNT} {PHP.L.Of} {SPAM_PROTECTION_SPAM_TOTAL_COUNT}
			</div>
		</div>

		<!-- END: IS_IN_VIEW_SECTION -->

	<!-- BEGIN: EMPTY_MESSAGE -->
		<p>
			{PHP.L.sp_No_items_marked_as_spam}
		</p>
	<!-- END: EMPTY_MESSAGE -->
	<!-- BEGIN: VIEW_SECTION_SPAM -->

		<ul class="sp_list" style="margin-top: 5px; margin-bottom: 10px; width: 100%;">
			<li class="sp_list sp_left" style="width: 5%; vertical-align: left;">
				<input class="sp_checkall" type="checkbox" /> 
			</li>
			<li class="sp_list sp_left" style="width: 14%;">
				<strong>{PHP.L.sp_Id}</strong> &nbsp;
				<a href="{SPAM_PROTECTION_SPAM_ORDERBY_ID_ASC}"><img style="vertical-align: top;" src="{PHP.tool_path}/img/arrow-up.gif" /></a>&nbsp;
				<a href="{SPAM_PROTECTION_SPAM_ORDERBY_ID_DESC}"><img style="vertical-align: top;" src="{PHP.tool_path}/img/arrow-down.gif" /></a>
			</li>
			<li class="sp_list sp_left" style="width: 23%;">
				<strong>{PHP.L.Username}</strong> &nbsp;
				<a href="{SPAM_PROTECTION_SPAM_ORDERBY_USERNAME_ASC}"><img style="vertical-align: top;" src="{PHP.tool_path}/img/arrow-up.gif" /></a>&nbsp;
				<a href="{SPAM_PROTECTION_SPAM_ORDERBY_USERNAME_DESC}"><img style="vertical-align: top;" src="{PHP.tool_path}/img/arrow-down.gif" /></a>
			</li>
			<li class="sp_list sp_left" style="width: 43%;">
				<strong>{PHP.L.sp_Content}</strong> 
			</li>
			<li class="sp_list sp_left" style="width: 14%;">
				<div style="margin-left: 15px;">
					<strong>{PHP.L.Options}</strong>
				</div>
			</li>						
		</ul>

		{SPAM_PROTECTION_FORM_START}
		<!-- BEGIN: LIST_SPAM -->

			<ul class="sp_list sp_mouseover" style="padding-top: 6px; padding-bottom: 6px; width: 100%;">
				<li class="sp_list sp_left" style="width: 5%; vertical-align: left;">
					{SPAM_PROTECTION_SPAM_CHECKBOX}
				</li>
				<li class="sp_list sp_left sp_selectclick" style="width: 14%;">
					{SPAM_PROTECTION_SPAM_ID}
				</li>
				<li class="sp_list sp_left sp_selectclick" style="width: 23%;">
					{SPAM_PROTECTION_SPAM_USERNAME}
				</li>
				<li class="sp_list sp_left sp_selectclick" title="{SPAM_PROTECTION_SPAM_CONTENT}" style="width: 43%;">
					{SPAM_PROTECTION_SPAM_CONTENT_REDUCED}
				</li>					
				<li class="sp_list sp_left" style="width: 14%;">
					<div style="margin-left: 15px;">
						<a class="view_more_information" href="{SPAM_PROTECTION_SPAM_VIEW_ITEM_URL}"><img title="{PHP.L.sp_View_more_information}" src="{PHP.tool_path}/img/database_table.png" /></a>
						&nbsp; &nbsp; &nbsp;					
						<a class="confirm_mark_as_spam" href="{SPAM_PROTECTION_SPAM_MARKAS_SPAM_URL}"><img title="{PHP.L.sp_Mark_as_spam}" src="{PHP.tool_path}/img/database_delete.png" /></a>
						&nbsp; &nbsp; &nbsp;
						<a class="confirm_mark_as_ham" href="{SPAM_PROTECTION_SPAM_MARKAS_HAM_URL}"><img title="{PHP.L.sp_Mark_as_ham}" src="{PHP.tool_path}/img/database_add.png" /></a>
					</div>
				</li>			
			</ul>		
		
		<!-- END: LIST_SPAM -->

		<ul class="sp_list" style="margin-top: 30px; margin-bottom: 15px;">
			<li class="sp_list sp_left">
				{SPAM_PROTECTION_SPAM_PAGINATION}
			</li>		
			<li class="sp_list" style="float: right;">
				{SPAM_PROTECTION_WITH_SELECTED}
				&nbsp; {SPAM_PROTECTION_WITH_SELECTED_SUBMIT}
			</li>
		</ul>
		{SPAM_PROTECTION_FORM_END}
		<hr />
		<div style="clear: both; margin-bottom: 15px; margin-top: 15px;">
			<strong>{PHP.L.sp_Legend}:</strong>
		</div>
		<ul class="sp_list">
			<li class="sp_list sp_left" style="margin-right: 25px;">
				<img title="{PHP.L.sp_View_more_information}" src="{PHP.tool_path}/img/database_table.png" /> &nbsp;{PHP.L.sp_View_more_information}
			</li>
			<li class="sp_list sp_left" style="margin-right: 25px;">
				<img title="{PHP.L.sp_Mark_as_spam}" src="{PHP.tool_path}/img/database_delete.png" /> &nbsp;{PHP.L.sp_Mark_as_spam}
			</li>
			<li class="sp_list sp_left" style="margin-right: 25px;">
				<img title="{PHP.L.sp_Mark_as_ham}" src="{PHP.tool_path}/img/database_add.png" /> &nbsp;{PHP.L.sp_Mark_as_ham}
			</li>								
		</ul>

	<!-- END: VIEW_SECTION_SPAM -->
	
	<!-- BEGIN: CHECK_KEY -->
		<div style="margin-top: 15px;">
		<h3>{PHP.cfg.plugin.spam_protection.spam_service}</h3>
			<!-- BEGIN: IS_VALID -->
			<p style="margin-top: 20px; line-height: 180%;">
				{PHP.L.sp_Your_api_key_is} <strong>{PHP.L.sp_Valid_and_being_accepted} {PHP.cfg.plugin.spam_protection.spam_service}</strong>.
			</p>
			<!-- END: IS_VALID -->
			
			<!-- BEGIN: IS_INVALID -->
			<p style="margin-top: 20px; line-height: 180%;">
				{PHP.L.sp_Your_api_key_is} <strong>{PHP.L.sp_Invalid_and_being_rejected} {PHP.cfg.plugin.spam_protection.spam_service}</strong>.
			</p>
			<!-- END: IS_INVALID -->			
		</div>
	<!-- END: CHECK_KEY -->
	
</div>

<!-- END: MAIN -->
