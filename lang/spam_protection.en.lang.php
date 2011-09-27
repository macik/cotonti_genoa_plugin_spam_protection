<?php
defined('SED_CODE') or die('Wrong URL.');

$L['plu_sp_title'] = "Spam Protection";
$L['plu_sp_tool_title'] = "Spam Moderation";
$L['plu_sp_section_forum_title'] = "Forum topics & posts marked as spam";
$L['plu_sp_section_comment_title'] = "Comments marked as spam";

$L['sp_Id'] = "ID";
$L['sp_Legend'] = "Legend";
$L['sp_Showing'] = "Showing";
$L['sp_Content'] = "Content";
$L['sp_Mark_as_ham'] = "Mark as Ham";
$L['sp_Mark_as_spam'] = "Mark as Spam";
$L['sp_Commit_action'] = "Commit Action";
$L['sp_An_error_has_occurred'] = "An Error Has Occurred!";
$L['sp_Moderate_spam_for'] = "Moderate spam for";
$L['sp_View_more_information'] = "View more information";
$L['sp_All_information_stored'] = "Below is all the information stored for the selected item currently marked as spam.";
$L['sp_No_items_marked_as_spam'] = "No items currently marked as spam for this section.";
$L['sp_Really_mark_as_spam'] = "Really mark as spam ?";
$L['sp_Really_mark_as_ham'] = "Really mark as ham ?";
$L['sp_Check_api_key'] = 'Check API key';
$L['sp_Your_api_key_is'] = "Your API key is";
$L['sp_Valid_and_being_accepted'] = "valid and being accepted by";
$L['sp_Invalid_and_being_rejected'] = "invalid and being rejected by";
$L['sp_You_must_select_atleast_one_item'] = "You must select at least one item to do this action.";

$L['plu_sp_goback'] = "Go back";
$L['plu_sp_with_selected'] = "With selected:";
$L['plu_sp_select_sep'] = "--";
$L['plu_sp_markas_spam'] = "Mark as SPAM";
$L['plu_sp_markas_ham'] = "Mark as HAM";

$L['plu_sp_error_message_100'] = "Connection with your selected spam service has timed out, please retry in a few seconds.";
$L['plu_sp_error_message_200'] = "The API key configured for your spam service has returned invalid. Please check to ensure you have entered the right key in <a href=\"./admin.php?m=config&n=edit&o=plug&p=spam_protection\">Administration -> Plugins -> Spam Protection -> Configuration</a>";
$L['plu_sp_error_message_300'] = "Your spam service provided has encountered an error. Please try again in a few seconds.";
$L['plu_sp_error_message_400'] = "Unexpected parameter entry.";