<?php
/*
 * This file is part of HOAM, copyright (C) 2002-2020 Robert Butler
 *
 * HOAM is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option)
 * any later version.
 *
 * HOAM is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with HOAM; if not, see http://www.gnu.org/licenses or write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301
 *
 * Questions specific to HOAM should be directed to ARP Realty. Please see the
 * HOAM web site at http://hoam.arprs.com/
 *
 * Some portions of HOAM incorporate ideas and/or code from other sources, and
 * those portions are explicitly mentioned and attributed in the relevant
 * section of HOAM source code. Questions about that code should be directed to
 * the original authors.
 *
 */

function HOAM_adminAdvertising ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;
	
	$group_members_class = new HOAM_group_members ('group_members');
	
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['advertising'])));

	if (isset ($GLOBALS['global_url_array'][2])) {
		switch ($GLOBALS['global_url_array'][2]) {
			case 'add':
				$_HOAM_log -> add ('Loading /website/advertising/add/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_add'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['advertising']['add'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
					
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
					$HTML = '<h1>' . $_HOAM_language ['field_names']['advertising']['add'] . '</h1>';

					$advertising_class = new HOAM_advertising ('advertising');
					$attachment_class = new HOAM_attachments ('attachments');
					// OK, we're checking to see that the attachment we're trying to add is the one that we
					// have information on in the attachment_information variable.
					// If the referrer doesn't match this current page, then we know we came from
					// somewhere else on the site, and thus the information is suspect.
					if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
						if (isset ($_SESSION['error_class_item'])) {
							unset ($_SESSION['error_class_item']);
						}
						if (isset ($_SESSION['advertising_information'])) {
							unset ($_SESSION['advertising_information']);
						}
					}

					$HTML .= '<form id="advertising_add" action="/hoam/scripts/advertising/edit_script.php" enctype="multipart/form-data" method="post">';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['advertisement']['add'] . '</button>';
					$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="advertisement_add" title="' . $_HOAM_language ['form_tabs']['advertising']['add'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= '<label for="advertising|file">' . $_HOAM_language ['field_names']['advertising']['filename'] . '</label>';
					$HTML .= '<input name="attachment|file" type="file" />';
					$HTML .= HOAM_formText ('advertising|url', $_HOAM_language ['field_names']['advertising']['url'], $_SESSION['advertising_information']['trusted_advertising|url'], '', $advertising_class -> returnColumnSize ('url'), $_HOAM_setting ['advertising']['minimum_url_length']);
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';						
					$HTML .= HOAM_formText ('advertising|description', $_HOAM_language ['field_names']['advertising']['description'], $_SESSION['advertising_information']['trusted_advertising|description'], '', $advertising_class -> returnColumnSize ('description'), $_HOAM_setting ['advertising']['minimum_description_length']);				$HTML .= '</div>';
					$HTML .= '<div class="mandatory">';						
					$HTML .= HOAM_formSelect ('advertising|location', $_HOAM_language ['field_names']['advertising']['location']['location']);
					$flags = $_SESSION['advertising_information']['trusted_advertising|location'];
					switch ($flags) {
						case 1 :
							$HTML .= '<option selected="selected" value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
							$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
							$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
							$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
							break;
						case 2 :
							$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
							$HTML .= '<option selected="selected" value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
							$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
							$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
							break;
						case 3 :
							$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
							$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
							$HTML .= '<option selected="selected" value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
							$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
							break;
						case 4 :
							$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
							$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
							$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
							$HTML .= '<option selected="selected" value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
							break;
						default:
							$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
							$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
							$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
							$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
							break;
					}
					$HTML .= '</select>';
					$HTML .= HOAM_formDate ('advertising|poststart', $_HOAM_language ['field_names']['advertising']['poststart'], $_SESSION['advertising_information']['trusted_advertising|poststart'], '');
					$HTML .= HOAM_formDate ('advertising|postend', $_HOAM_language ['field_names']['advertising']['postend'], $_SESSION['advertising_information']['trusted_advertising|postend'], '');
					
					$HTML .= '</div>'; // End new advertisement tab

					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'delete':
				$_HOAM_log -> add ('Loading /website/advertising/delete/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_delete'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					if ($GLOBALS['global_url_array'][3]) {
						$advertising_class = new HOAM_advertising ('advertising');
						$trusted_id = $advertising_class -> superClean ($GLOBALS['global_url_array'][3], $advertising_class -> returnColumnSize ('id'));
						if ($advertising_class -> idExists ($trusted_id)) {
							HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['advertising']['delete'])));
				
							require_once (HOAM_PATH_FUNCTION . 'attachments.php');
							require_once (HOAM_PATH_FUNCTION . 'forms.php');
							$HTML = '<h1>' . $_HOAM_language ['field_names']['advertising']['delete'] . '</h1>';

							$attachment_class = new HOAM_attachments ('attachments');
							// OK, we're checking to see that the attachment we're trying to add is the one that we
							// have information on in the attachment_information variable.
							// If the referrer doesn't match this current page, then we know we came from
							// somewhere else on the site, and thus the information is suspect.
							if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
								if (isset ($_SESSION['error_class_item'])) {
									unset ($_SESSION['error_class_item']);
								}
								if (isset ($_SESSION['advertisement_information'])) {
									unset ($_SESSION['advertisement_information']);
								}
							}

							$HTML .= '<form id="advertising_delete" action="/hoam/scripts/advertising/edit_script.php" enctype="multipart/form-data" method="post">';
							$HTML .= '<input type="hidden" name="advertising|id" id="advertising|id" value="' . $trusted_id . '" />';
						
							if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
								$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
							}
							$HTML .= '<fieldset>';
							$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['delete'] . '</button>';
							$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
							$HTML .= '</fieldset>';
							$HTML .= $_HOAM_language ['help_popups']['mandatory'];
							$HTML .= '<div class="tabber">';

							$HTML .= '<div class="tabbertab" id="advertisement_edit" title="' . $_HOAM_language ['form_tabs']['advertising']['information'] . '">';
							$HTML .= '<div class="mandatory">';
							$HTML .= '<label for="advertising|file">' . $_HOAM_language ['field_names']['advertising']['filename'] . '</label>';
							$attachment_list = $attachment_class -> listWithOwner ($trusted_id, $_HOAM_setting ['attachment']['flags']['advertising']);
							if (!empty ($attachment_list)) {
								foreach ($attachment_list as $attachment) {
									$HTML .= HOAM_attachmentReturnHTML ($attachment);
								}
								$HTML .= '<br class="clear" />';
							}
							$HTML .= HOAM_formTextDisabled ('advertising|url', $_HOAM_language ['field_names']['advertising']['url'], $advertising_class -> returnURL ($trusted_id), $advertising_class -> returnColumnSize ('url'), $_HOAM_setting ['advertising']['minimum_url_length']);
							$HTML .= '</div>';
							$HTML .= '<div class="optional">';						
							$HTML .= HOAM_formTextDisabled ('advertising|description', $_HOAM_language ['field_names']['advertising']['description'], $advertising_class -> returnDescription ($trusted_id), $advertising_class -> returnColumnSize ('description'), $_HOAM_setting ['advertising']['minimum_description_length']);
							$HTML .= '</div>';
							$HTML .= '<div class="mandatory">';						
							$HTML .= HOAM_formSelectDisabled ('advertising|location', $_HOAM_language ['field_names']['advertising']['location']['location']);
							switch ($advertising_class -> returnFlags ($trusted_id)) {
								case $_HOAM_setting ['advertising']['flags']['position1'] :
									$HTML .= '<option selected="selected" value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
									$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
									$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
									$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
									break;
								case $_HOAM_setting ['advertising']['flags']['position2'] :
									$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
									$HTML .= '<option selected="selected" value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
									$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
									$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
									break;
								case $_HOAM_setting ['advertising']['flags']['position3'] :
									$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
									$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
									$HTML .= '<option selected="selected" value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
									$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
									break;
								case $_HOAM_setting ['advertising']['flags']['position4'] :
									$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
									$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
									$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
									$HTML .= '<option selected="selected" value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
									break;
								default:
									$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
									$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
									$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';	
									$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
									break;
							}
							$HTML .= '</select>';
							$HTML .= HOAM_formDateDisabled ('advertising|poststart', $_HOAM_language ['field_names']['advertising']['poststart'], $advertising_class -> returnDatePostStart ($trusted_id));
							$HTML .= HOAM_formDateDisabled ('advertising|postend', $_HOAM_language ['field_names']['advertising']['postend'], $advertising_class -> returnDatePostEnd ($trusted_id));
				
							$HTML .= '</div>'; // End advertisement tab

							$HTML .= '</div>'; // End Tabber
							$HTML .= '</form>';
						} else {
							header ('Location: /error/unknown_id/');
							exit ();
						}
					} else {
						header ('Location: /website/advertising/list/');
						exit ();
					}
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'edit':
				$_HOAM_log -> add ('Loading /website/advertising/edit/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					if ($GLOBALS['global_url_array'][3]) {
						$advertising_class = new HOAM_advertising ('advertising');
						$trusted_id = $advertising_class -> superClean ($GLOBALS['global_url_array'][3], $advertising_class -> returnColumnSize ('id'));
						if ($advertising_class -> idExists ($trusted_id)) {
							HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['advertising']['edit'])));
							HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
				
							require_once (HOAM_PATH_FUNCTION . 'attachments.php');
							require_once (HOAM_PATH_FUNCTION . 'forms.php');
							$HTML = '<h1>' . $_HOAM_language ['field_names']['advertising']['edit'] . '</h1>';

							$attachment_class = new HOAM_attachments ('attachments');
							// OK, we're checking to see that the attachment we're trying to add is the one that we
							// have information on in the attachment_information variable.
							// If the referrer doesn't match this current page, then we know we came from
							// somewhere else on the site, and thus the information is suspect.
							if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
								if (isset ($_SESSION['error_class_item'])) {
									unset ($_SESSION['error_class_item']);
								}
								if (isset ($_SESSION['advertisement_information'])) {
									unset ($_SESSION['advertisement_information']);
								}
							}

							$HTML .= '<form id="advertising_edit" action="/hoam/scripts/advertising/edit_script.php" enctype="multipart/form-data" method="post">';
							$HTML .= '<input type="hidden" name="advertising|id" id="advertising|id" value="' . $trusted_id . '" />';
						
							if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
								$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
							}
							$HTML .= '<fieldset>';
							$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
							$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
							$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
							$HTML .= '</fieldset>';
							$HTML .= $_HOAM_language ['help_popups']['mandatory'];
							$HTML .= '<div class="tabber">';

							$HTML .= '<div class="tabbertab" id="advertisement_edit" title="' . $_HOAM_language ['form_tabs']['advertising']['information'] . '">';
							$HTML .= '<div class="mandatory">';
							$HTML .= '<label for="advertising|file">' . $_HOAM_language ['field_names']['advertising']['filename'] . '</label>';
							$attachment_list = $attachment_class -> listWithOwner ($trusted_id, $_HOAM_setting ['attachment']['flags']['advertising']);
							if (!empty ($attachment_list)) {
								foreach ($attachment_list as $attachment) {
									$HTML .= HOAM_attachmentReturnHTML ($attachment);
								}
								$HTML .= '<br class="clear" />';
							}
							$HTML .= HOAM_formText ('advertising|url', $_HOAM_language ['field_names']['advertising']['url'], $_SESSION['advertisement_information']['trusted_advertising|url'], $advertising_class -> returnURL ($trusted_id), $advertising_class -> returnColumnSize ('url'), $_HOAM_setting ['advertising']['minimum_url_length']);
							$HTML .= '</div>';
							$HTML .= '<div class="optional">';						
							$HTML .= HOAM_formText ('advertising|description', $_HOAM_language ['field_names']['advertising']['description'], $_SESSION['advertisement_information']['trusted_advertising|description'], $advertising_class -> returnDescription ($trusted_id), $advertising_class -> returnColumnSize ('description'), $_HOAM_setting ['advertising']['minimum_description_length']);						$HTML .= '</div>';
							$HTML .= '<div class="mandatory">';						
							$HTML .= HOAM_formSelect ('advertising|location', $_HOAM_language ['field_names']['advertising']['location']['location']);
							switch ($advertising_class -> returnFlags ($trusted_id)) {
								case $_HOAM_setting ['advertising']['flags']['position1'] :
									$HTML .= '<option selected="selected" value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
									$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
									$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
									$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
									break;
								case $_HOAM_setting ['advertising']['flags']['position2'] :
									$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
									$HTML .= '<option selected="selected" value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
									$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
									$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
									break;
								case $_HOAM_setting ['advertising']['flags']['position3'] :
									$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
									$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
									$HTML .= '<option selected="selected" value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
									$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
									break;
								case $_HOAM_setting ['advertising']['flags']['position4'] :
									$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
									$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
									$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
									$HTML .= '<option selected="selected" value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
									break;
								default:
									$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['advertising']['flags']['position1'] . '</option>';
									$HTML .= '<option value="2">' . $_HOAM_language ['field_names']['advertising']['flags']['position2'] . '</option>';
									$HTML .= '<option value="3">' . $_HOAM_language ['field_names']['advertising']['flags']['position3'] . '</option>';
									$HTML .= '<option value="4">' . $_HOAM_language ['field_names']['advertising']['flags']['position4'] . '</option>';
									break;
							}
							$HTML .= '</select>';
							$HTML .= HOAM_formDate ('advertising|poststart', $_HOAM_language ['field_names']['advertising']['poststart'], $_SESSION['advertising_information']['trusted_advertising|poststart'], $advertising_class -> returnDatePostStart ($trusted_id));
							$HTML .= HOAM_formDate ('advertising|postend', $_HOAM_language ['field_names']['advertising']['postend'], $_SESSION['advertising_information']['trusted_advertising|postend'], $advertising_class -> returnDatePostEnd ($trusted_id));

							$HTML .= '</div>'; // End advertisement tab

							$HTML .= '</div>'; // End Tabber
							$HTML .= '</form>';
						} else {
							header ('Location: /error/unknown_id/');
							exit ();
						}
					} else {
						header ('Location: /website/advertising/list/');
						exit ();
					}
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'list':
				$_HOAM_log -> add ('Loading /website/advertising/list/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['advertising']['list'])));

					$HTML = '<h1>' . $_HOAM_language ['field_names']['advertising']['list'] . '</h1>';

					$HTML .= '<table class="center">';
					$thead = '<thead><tr>';
					$thead .= '<th>' . $_HOAM_language ['field_names']['advertising']['attachment'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['field_names']['advertising']['url'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['field_names']['advertising']['description'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['field_names']['advertising']['location']['location'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['field_names']['advertising']['impressions'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['field_names']['advertising']['clicks'] . '</th>';
					$thead .= '</tr></thead>';
					$tbody = '<tbody id="advertisement_list">';
					$advertising_class = new HOAM_advertising ('advertising');
					$attachment_class = new HOAM_attachments ('attachments');
					$advertisement_list = $advertising_class -> ListAll ();
					if (is_array ($advertisement_list)) {
						foreach ($advertisement_list as $advertisement) {
							$tbody .= '<tr>'; 
							$tbody .= '<td>';
							$attachment_list = $attachment_class -> listWithOwner ($advertisement['id'], $_HOAM_setting ['attachment']['flags']['advertising']);
							if (!empty ($attachment_list)) {
								foreach ($attachment_list as $attachment) {
									$tbody .= HOAM_attachmentReturnHTML ($attachment);
								}
							}
							$tbody .= '</td>';
							$tbody .= '<td>' . $advertisement['url'] . '</td>';
							$tbody .= '<td>' . HOAM_returnTruncatedString ($advertisement['description'], 60) . '</td>';
							$tbody .= '<td>';
							switch ($advertisement['flags']) {
								case ((int) $advertisement['flags'] & (int) $_HOAM_setting ['advertising']['flags']['position1']) :
									$tbody .= $_HOAM_language ['field_names']['advertising']['flags']['position1'];
									break;
								case ((int) $advertisement['flags'] & (int) $_HOAM_setting ['advertising']['flags']['position2']) :
									$tbody .= $_HOAM_language ['field_names']['advertising']['flags']['position2'];
									break;
								case ((int) $advertisement['flags'] & (int) $_HOAM_setting ['advertising']['flags']['position3']) :
									$tbody .= $_HOAM_language ['field_names']['advertising']['flags']['position3'];
									break;
								case ((int) $advertisement['flags'] & (int) $_HOAM_setting ['advertising']['flags']['position4']) :
									$tbody .= $_HOAM_language ['field_names']['advertising']['flags']['position4'];
									break;
								default :
									$tbody .= $_HOAM_language ['common']['na'];
									break;
							}
							$tbody .= '</td>';
							$tbody .= '<td>';
							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
								$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_edit'], $_SESSION['current_user']['id']) OR
								($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
								$tbody .= '<small><a href="/website/advertising/edit/' . $advertisement['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
							}
							$tbody .= ' &nbsp; ';
							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
								$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_delete'], $_SESSION['current_user']['id']) OR
								($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
								$tbody .= '<small><a class="error" href="/website/advertising/delete/' . $advertisement['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
							}
							$tbody .= '</td>';
							$tbody .= '<td>' . $advertisement['impressions'] . '</td>';
							$tbody .= '<td>' . $advertisement['clicks'] . '</td>';
							$tbody .= '</tr>'; 
						}
					} else {
						$tbody .= '<tr><td colspan="7">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
					}
					$tbody .= '</tbody>';
					$HTML .= $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody;
					$HTML .= '</table>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			default:
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_add'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_delete'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_edit'], $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
			$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['advertising'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/film.png" alt="' . $_HOAM_language ['field_names']['admin']['advertising'] . '" class="LHS" /><a href="/website/advertising/">' . $_HOAM_language ['field_names']['admin']['advertising'] . '</a>';
			$HTML .= '<ul>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/film_add.png\')"><a href="/website/advertising/add/">' . $_HOAM_language ['field_names']['advertising']['add'] . '</a></li>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/film_go.png\')"><a href="/website/advertising/list/">' . $_HOAM_language ['field_names']['advertising']['list'] . '</a></li>';
			$HTML .= '</ul>';
		} else {
			header ('Location: /error/403/');
			exit ();
		}
	}
	return ($HTML);
}

function HOAM_adminAttachment ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$budget_class = new HOAM_budget ('budget');
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$user_class = new HOAM_user ('users');
	$violation_class = new HOAM_violation ('violations');

	switch ($GLOBALS['global_url_array'][2]) {
		case 'add' :
			$_HOAM_log -> add ('Loading /website/attachment/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['attachment']['add'])));
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['attachment']['add'] . '</h1>';

				$attachment_class = new HOAM_attachments ('attachments');
				// OK, we're checking to see that the attachment we're trying to add is the one that we
				// have information on in the attachment_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['attachment_information'])) {
						unset ($_SESSION['attachment_information']);
					}
				}

				$HTML .= '<form id="attachment_add" action="/hoam/scripts/attachment/edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['attachment']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="attachment_add" title="' . $_HOAM_language ['form_tabs']['attachment']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= '<label for="attachment|file">' . $_HOAM_language ['field_names']['attachment']['filename'] . '</label>';
				$HTML .= '<input name="attachment|file" type="file" />';
				
				$HTML .= HOAM_formText ('attachment|description', $_HOAM_language ['field_names']['attachment']['description'], $_SESSION['attachment_information']['trusted_attachment|description'], '', $attachment_class -> returnColumnSize ('description'), $_HOAM_setting ['attachment']['minimum_description_length']);
				$HTML .= '</div>'; // End new attachment tab

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'delete' :
			$_HOAM_log -> add ('Loading /website/attachment/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$attachment_class = new HOAM_attachments ('attachments');
					$trusted_attachment_id = $attachment_class -> superClean ($GLOBALS['global_url_array'][3], $attachment_class -> returnColumnSize ('id'));
					if ($attachment_class -> idExists ($trusted_attachment_id)) {
				
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['attachment']['delete'])));
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['attachment']['delete'] . '</h1>';

						// OK, we're checking to see that the attachment we're trying to add is the one that we
						// have information on in the attachment_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['attachment_information'])) {
								unset ($_SESSION['attachment_information']);
							}
						}

						$HTML .= '<form id="attachment_delete" action="/hoam/scripts/attachment/edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="attachment|id" id="attachment|id" value="' . $trusted_attachment_id . '" />';
						
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="attachment_delete" title="' . $_HOAM_language ['form_tabs']['attachment']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formTextDisabled ('attachment|file', $_HOAM_language ['field_names']['attachment']['filename'], $attachment_class -> returnFilename ($trusted_attachment_id));

						$HTML .= HOAM_formTextDisabled ('attachment|description', $_HOAM_language ['field_names']['attachment']['description'], $attachment_class -> returnDescription ($trusted_attachment_id));
						$HTML .= '</div>'; // End new attachment tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/news/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'edit' :
			$_HOAM_log -> add ('Loading /website/attachment/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$attachment_class = new HOAM_attachments ('attachments');
					$trusted_attachment_id = $attachment_class -> superClean ($GLOBALS['global_url_array'][3], $attachment_class -> returnColumnSize ('id'));
					if ($attachment_class -> idExists ($trusted_attachment_id)) {
				
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['attachment']['edit'])));
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['attachment']['edit'] . '</h1>';

						// OK, we're checking to see that the attachment we're trying to add is the one that we
						// have information on in the attachment_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['attachment_information'])) {
								unset ($_SESSION['attachment_information']);
							}
						}

						$HTML .= '<form id="attachment_edit" action="/hoam/scripts/attachment/edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="attachment|id" id="attachment|id" value="' . $trusted_attachment_id . '" />';
						
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="attachment_edit" title="' . $_HOAM_language ['form_tabs']['attachment']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formText ('attachment|file', $_HOAM_language ['field_names']['attachment']['filename'], $_SESSION['attachment_information']['trusted_attachment|file'], $attachment_class -> returnFilename ($trusted_attachment_id), $attachment_class -> returnColumnSize ('filename'), NULL);
										
						$HTML .= HOAM_formText ('attachment|description', $_HOAM_language ['field_names']['attachment']['description'], $_SESSION['attachment_information']['trusted_attachment|description'], $attachment_class -> returnDescription ($trusted_attachment_id), $attachment_class -> returnColumnSize ('description'), $_HOAM_setting ['attachment']['minimum_description_length']);
						$HTML .= '</div>'; // End new attachment tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/news/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'list' :
			$_HOAM_log -> add ('Loading /website/attachment/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['attachment']['list'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['attachment']['list'] . '</h1>';
				$colgroup  = '<colgroup>';
				$colgroup .= '<col width="20%" />';
				$colgroup .= '<col width="30%" />';
				$colgroup .= '<col width="30%" />';
				$colgroup .= '<col width="10%" />';
				$colgroup .= '<col width="10%" />';
				$colgroup .= '</colgroup>';
				$thead  = '<thead>';
				$thead .= '<tr>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['attachment']['filename'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['attachment']['description'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['attachment']['owner'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['created_by'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '</tr>';
				$thead .= '</thead>';
				$HTML .= '<table class="center">';
				$HTML .= $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead);
				$HTML .= '<tbody id="fillme">';
				$HTML .= '</tbody>';
				$HTML .= '</table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'list_old' :
			$_HOAM_log -> add ('Loading /website/attachment/list_old/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['attachment']['list'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['attachment']['list'] . '</h1>';
				$attachment_class = new HOAM_attachments ('attachments');
				$colgroup  = '<colgroup>';
				$colgroup .= '<col width="18%" />';
				$colgroup .= '<col width="19%" />';
				$colgroup .= '<col width="18%" />';
				$colgroup .= '<col span="4" width="10%" />';
				$colgroup .= '<col width="5%" />';
				$colgroup .= '</colgroup>';
				$thead  = '<thead>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['attachment']['filename'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['attachment']['description'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['attachment']['owner'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['created_by'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['attachment']['mimetype'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['attachment']['resolution'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['flags'] . '</th>';
				$thead .= '</thead>';
				$attachment_list = $attachment_class -> listAll ();
				if (is_array ($attachment_list)) {
					$year = date ('Y', strtotime ($attachment_list[0]['datecreated']));
					$HTML .= '<span style="font-weight: bold" field="year">&#x0229F; ' . $year . '</span>';
					$HTML .= '<div>';
					$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead);
					$HTML .= '<tbody>';
					foreach ($attachment_list as $attachment) {
						if (mb_substr ($attachment['datecreated'], 0, 4) == $year) {
						} else {
							$year = mb_substr ($attachment['datecreated'], 0, 4);
							$HTML .= '</tbody>';
							$HTML .= '</table>';
							$HTML .= '</div>';
							$HTML .= '<span style="display: block" field="year">&#x0229E; ' . $year . '</span>';
							$HTML .= '<div style="display: none">';
							$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead);
							$HTML .= '<tbody>';
						}
						$HTML .= '<tr>';
						$HTML .= '<td><a href="/hoam/scripts/attachment/view.php?id=' . $attachment['id'] . '">' . $attachment['filename'] . '</a></td>';
						$HTML .= '<td>' . html_encode (stripslashes ($attachment['description'])) . '</td>';
						$HTML .= '<td>';
						$owners = unserialize ($attachment ['owners']);
						switch (TRUE) {
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['advertising'] :
								if (is_array ($owners)) {
									$HTML .= '<ul>';
									foreach ($owners as $owner) {
										$HTML .= '<li><a href="#">' . $owner . '</a></li>';
									}
									$HTML .= '</ul>';
								} elseif ($owners != NULL) {
									$HTML .= '<a href="/website/advertising/view/' . $owners . '/">' . $owners . '</a>';
								} else {
									$HTML .= $_HOAM_language ['common']['na'];
								}
								break;
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['budget'] :
								if (is_array ($owners)) {
									$HTML .= '<ul>';
									foreach ($owners as $owner) {
										$HTML .= '<li><a href="#">' . $owner . '</a></li>';
									}
									$HTML .= '</ul>';
								} elseif ($owners != NULL) {
									$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
									$invoice_id = $budget_class -> returnInvoiceId ($owners);
									$HTML .= '<a href="/admin/financial/view/' . $invoice_id . '/">' . $budget_invoice_class -> returnNumber ($invoice_id) . '</a>';
								} else {
									$HTML .= $_HOAM_language ['common']['na'];
								}
								break;
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['homeowner'] :
								if (is_array ($owners)) {
									$HTML .= '<ul>';
									foreach ($owners as $owner) {
										$HTML .= '<li><a href="/admin/homeowner/edit/' . $owner . '/">' . html_encode (stripslashes ($homeowner_class -> returnName ($owner))) . '</a></li>';
									}
									$HTML .= '</ul>';
								} elseif ($owners != NULL) {
									$HTML .= '<a href="/admin/homeowner/edit/' . $owners . '/">' . html_encode (stripslashes ($homeowner_class -> returnName ($owners))) . '</a>';
								} else {
									$HTML .= $_HOAM_language ['common']['na'];
								}
								break;
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['lot'] :
								if (is_array ($owners)) {
									$HTML .= '<ul>';
									foreach ($owners as $owner) {
										$HTML .= '<li><a href="/admin/lot/edit/' . $owner . '/">' . HOAM_returnAddress ($owner) . '</a></li>';
									}
									$HTML .= '</ul>';
								} elseif ($owners != NULL) {
									$HTML .= '<a href="/admin/lot/edit/' . $owners . '/">' . HOAM_returnAddress ($owners) . '</a>';
								} else {
									$HTML .= $_HOAM_language ['common']['na'];
								}
								break;
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['violation'] :
								if (is_array ($owners)) {
									$HTML .= '<ul>';
									foreach ($owners as $owner) {
										$HTML .= '<li><a href="/admin/violation/edit/' . $owner . '/">' . HOAM_returnAddress ($violation_class -> returnLotId ($owner)) . '</a></li>';
									}
									$HTML .= '</ul>';
								} elseif ($owners != NULL) {
									$HTML .= '<a href="/admin/violation/letter/' . $owners . '/">' . HOAM_returnAddress ($violation_class -> returnLotId ($owners)) . '</a>';
								} else {
									$HTML .= $_HOAM_language ['common']['na'];
								}
								break;
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['vote'] :
								if (is_array ($owners)) {
									$HTML .= '<ul>';
									foreach ($owners as $owner) {
										$HTML .= '<li>' . HOAM_returnTruncatedString ($_SESSION['vote_class_item'] -> returnQuestion ($owner), 40) . '</li>';
									}
									$HTML .= '</ul>';
								} elseif ($owners != NULL) {
									$HTML .= HOAM_returnTruncatedString ($_SESSION['vote_class_item'] -> returnQuestion ($owners), 40);
								} else {
									$HTML .= $_HOAM_language ['common']['na'];	
								}
								break;
							default:
								$HTML .= $_HOAM_language ['common']['na'];
								break;
						}
						$HTML .= '</td>';
						$HTML .= '<td>' . $user_class -> returnAccountname ($attachment['user_id']) . '</td>';
						$HTML .= '<td>' . $attachment['mimetype'] . '</td>';
						$HTML .= '<td>';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_delete'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$HTML .= '<small><a href="/website/attachment/edit/' . $attachment['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
							$HTML .= ' &nbsp; ';
						}
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$HTML .= '<small><a class="error" href="/website/attachment/delete/' . $attachment['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
						}
						$HTML .= '</td>';
						if (mb_substr ($attachment['mimetype'], 0, 5) == 'image') {
							$HTML .= '<td>' . $attachment['width'] . ' x ' . $attachment['height'] . '</td>';
						} else {
							$HTML .= '<td class="less_emphasis">' . $_HOAM_language ['common']['na'] . '</td>';
						}
						$HTML .= '<td>';
						switch (TRUE) {
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['advertising'] :
								$HTML .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['attachment']['flags']['advertising'] . '">A</span> ';
								break;
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['budget'] :
								$HTML .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['attachment']['flags']['budget'] . '">B</span> ';
								break;
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['homeowner'] :
								$HTML .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['attachment']['flags']['homeowner'] . '">H</span> ';
								break;
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['lot'] :
								$HTML .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['attachment']['flags']['lot'] . '">L</span> ';
								break;
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['violation'] :
								$HTML .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['attachment']['flags']['violation'] . '">V</span> ';
								break;
							case (int) $attachment['flags'] & (int) $_HOAM_setting ['attachment']['flags']['vote'] :
								$HTML .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['attachment']['flags']['vote'] . '">O</span> ';
								break;
							default:
								$HTML .= '&nbsp;';
								break;
						}
						$HTML .= '</td>';
						$HTML .= '</tr>';
					}
				} else {
					$HTML .= '<tr><td colspan="8">' . $_HOAM_language['errors']['attachment']['none-found'] . '</td></tr>';
				}
				$HTML .= '</tbody>';
				$HTML .= '</table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['attachment'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['attachment'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/attach.png" alt="' . $_HOAM_language ['field_names']['admin']['attachment'] . '" class="LHS" /><a href="/website/attachment/">' . $_HOAM_language ['field_names']['admin']['attachment'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_add'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/attach.png\')"><a href="/website/attachment/add/">' . $_HOAM_language ['field_names']['attachment']['add'] . '</a></li>';
					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/attach.png\')"><a href="/website/attachment/list/">' . $_HOAM_language ['field_names']['attachment']['list'] . '</a></li>';
//					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/attach.png\')"><a href="/website/attachment/list_old/">' . $_HOAM_language ['field_names']['attachment']['list_old'] . '</a></li>';
					$HTML .= '</ul>';
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminConfig ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$group_members_class = new HOAM_group_members ('group_members');

	switch ($GLOBALS['global_url_array'][2]) {
		case 'export' :
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['config']['export'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminConfigJS');
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['config']['export'] . '</h1>';
				$HTML .= '<form>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="optional">';
				$HTML .= '<label>' . $_HOAM_language ['field_names']['config']['export_button'];
				$HTML .= HOAM_formDisplayHelp ('config|export_button');
				$HTML .= '</label>';
				$HTML .= $_HOAM_language ['help_pages']['config']['export'];
				$HTML .= '<fieldset id="config|export|fieldset">';
				$HTML .= HOAM_formCheckbox ('config|export_attachments', $_HOAM_language ['field_names']['config']['export_attachments'], 1, 1, 1);
				$HTML .= '<br /><br /><button id="config|export_button">' . $_HOAM_language ['form_buttons']['config']['export_button'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'modify' :
			$_HOAM_log -> add ('Loading /website/config/modify/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {

				$budget_account_class = new HOAM_budget_account ('budget_account');
				$budget_category_class = new HOAM_budget_category ('budget_category');
				$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
				$group_class = new HOAM_group ('group_list');
                $messageboard_class = new HOAM_messageboard ('messageboard');
				$violation_severity_class = new HOAM_violation_severity ('violation_severity');

				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['config']['modify'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminConfigJS');
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['config']['modify'] . '</h1>';
				// OK, we're checking to see that the homeowner we're trying to edit is the one that we
				// have information on in the homeowner_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				// Also, trusted_id will /only/ be set if a homeowner was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['config_information'])) {
						unset ($_SESSION['config_information']);
					}
				}

				$HTML .= '<form id="configuration_form" action="/hoam/scripts/config/edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';
				
				$HTML .= '<div class="tabbertab" id="admin_advertising" title="' . $_HOAM_language ['form_tabs']['config']['advertising'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formFieldEnable ('advertising|minimum_description_length', $_HOAM_language ['field_names']['advertising']['minimum_description_length'], $_SESSION['config_information']['trusted_advertising|minimum_description_length'], $_HOAM_setting ['advertising']['minimum_description_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('advertising|minimum_url_length', $_HOAM_language ['field_names']['advertising']['minimum_url_length'], $_SESSION['config_information']['trusted_advertising|minimum_url_length'], $_HOAM_setting ['advertising']['minimum_url_length'], 5, 1);

				$HTML .= $_HOAM_language ['help_pages']['config']['advertising']['flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('advertising|flags|position1', $_HOAM_language ['field_names']['advertising']['flags']['position1'], $_SESSION['config_information']['trusted_advertising|flags|position1'], $_HOAM_setting ['advertising']['flags']['position1'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('advertising|flags|position2', $_HOAM_language ['field_names']['advertising']['flags']['position2'], $_SESSION['config_information']['trusted_advertising|flags|position2'], $_HOAM_setting ['advertising']['flags']['position2'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('advertising|flags|position3', $_HOAM_language ['field_names']['advertising']['flags']['position3'], $_SESSION['config_information']['trusted_advertising|flags|position3'], $_HOAM_setting ['advertising']['flags']['position3'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('advertising|flags|position4', $_HOAM_language ['field_names']['advertising']['flags']['position4'], $_SESSION['config_information']['trusted_advertising|flags|position4'], $_HOAM_setting ['advertising']['flags']['position4'], 20, 1);
				$HTML .= '</fieldset>';
				
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Advertisements tab

				$HTML .= '<div class="tabbertab" id="admin_attachment" title="' . $_HOAM_language ['form_tabs']['config']['attachments'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formFieldEnable ('attachment|maximum_file_size', $_HOAM_language ['field_names']['attachment']['maximum_file_size'], $_SESSION['config_information']['trusted_attachment|maximum_file_size'], $_HOAM_setting ['attachment']['maximum_file_size'], 10, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|minimum_description_length', $_HOAM_language ['field_names']['attachment']['minimum_description_length'], $_SESSION['config_information']['trusted_attachment|minimum_description_length'], $_HOAM_setting ['attachment']['minimum_description_length'], 5, 1);

				$HTML .= $_HOAM_language ['help_pages']['config']['attachment']['preview'];
				$HTML .= HOAM_formConfigCheckbox ('attachment|preview|enable', $_HOAM_language ['field_names']['attachment']['preview']['enable'], 1, $_SESSION['config_information']['trusted_attachment|preview|enable'], $_HOAM_setting ['attachment']['preview']['enable']);
				$HTML .= '<fieldset id="preview_enable">';
				$HTML .= HOAM_formFieldEnable ('attachment|preview|height', $_HOAM_language ['field_names']['attachment']['preview']['height'], $_SESSION['config_information']['trusted_attachment|preview|height'], $_HOAM_setting ['attachment']['preview']['height'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|preview|width', $_HOAM_language ['field_names']['attachment']['preview']['width'], $_SESSION['config_information']['trusted_attachment|preview|width'], $_HOAM_setting ['attachment']['preview']['width'], 5, 1);
				$HTML .= '<label>' . $_HOAM_language ['field_names']['config']['attachments']['existing'];
				$HTML .= HOAM_formDisplayHelp ('config|attachments|existing');
				$HTML .= '</label>';
				$HTML .= $_HOAM_language ['field_names']['config']['attachments']['occupying'];
				$HTML .= '<button id="attachment|preview|delete">' . ucfirst ($_HOAM_language ['common']['delete']) . '</button>';
				$HTML .= '</fieldset>';
				
				$HTML .= $_HOAM_language ['help_pages']['config']['attachment']['flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('attachment|flags|advertising', $_HOAM_language ['field_names']['attachment']['flags']['advertising'], $_SESSION['config_information']['trusted_attachment|flags|advertising'], $_HOAM_setting ['attachment']['flags']['advertising'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|flags|budget', $_HOAM_language ['field_names']['attachment']['flags']['budget'], $_SESSION['config_information']['trusted_attachment|flags|budget'], $_HOAM_setting ['attachment']['flags']['budget'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|flags|budget_vendor', $_HOAM_language ['field_names']['attachment']['flags']['budget_vendor'], $_SESSION['config_information']['trusted_attachment|flags|budget_vendor'], $_HOAM_setting ['attachment']['flags']['budget_vendor'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|flags|homeowner', $_HOAM_language ['field_names']['attachment']['flags']['homeowner'], $_SESSION['config_information']['trusted_attachment|flags|homeowner'], $_HOAM_setting ['attachment']['flags']['homeowner'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|flags|homeowner_sale', $_HOAM_language ['field_names']['attachment']['flags']['homeowner_sale'], $_SESSION['config_information']['trusted_attachment|flags|homeowner_sale'], $_HOAM_setting ['attachment']['flags']['homeowner_sale'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|flags|insurance', $_HOAM_language ['field_names']['attachment']['flags']['insurance'], $_SESSION['config_information']['trusted_attachment|flags|insurance'], $_HOAM_setting ['attachment']['flags']['insurance'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|flags|lot', $_HOAM_language ['field_names']['attachment']['flags']['lot'], $_SESSION['config_information']['trusted_attachment|flags|lot'], $_HOAM_setting ['attachment']['flags']['lot'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|flags|property', $_HOAM_language ['field_names']['attachment']['flags']['property'], $_SESSION['config_information']['trusted_attachment|flags|property'], $_HOAM_setting ['attachment']['flags']['property'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|flags|violation', $_HOAM_language ['field_names']['attachment']['flags']['violation'], $_SESSION['config_information']['trusted_attachment|flags|violation'], $_HOAM_setting ['attachment']['flags']['violation'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|flags|vote', $_HOAM_language ['field_names']['attachment']['flags']['vote'], $_SESSION['config_information']['trusted_attachment|flags|vote'], $_HOAM_setting ['attachment']['flags']['vote'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('attachment|flags|work_request', $_HOAM_language ['field_names']['attachment']['flags']['work_request'], $_SESSION['config_information']['trusted_attachment|flags|work_request'], $_HOAM_setting ['attachment']['flags']['work_request'], 20, 1);
				$HTML .= '</fieldset>';
				
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Attachments tab

				$HTML .= '<div class="tabbertab" id="admin_budget" title="' . $_HOAM_language ['form_tabs']['config']['budget'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['assessment'];

				$HTML .= HOAM_formSelectEnable ('budget|assessment|frequency', $_HOAM_language ['field_names']['budget']['assessment']['frequency']);
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|frequency'])) {
					$default_unit = $_SESSION['config_information']['trusted_budget|assessment|frequency'];
				} else {
					$default_unit = $_HOAM_setting['budget']['assessment']['frequency'];
				}
				switch ($default_unit) {
					case 'annual' :
						$HTML .= '<option selected="selected" value="annual">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
						$HTML .= '<option value="biannual">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_biannual'] . '</option>';
						$HTML .= '<option value="quarterly">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_quarterly'] . '</option>';
						$HTML .= '<option value="monthly">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
						break;
					case 'biannual' :
						$HTML .= '<option value="annual">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
						$HTML .= '<option selected="selected" value="biannual">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_biannual'] . '</option>';
						$HTML .= '<option value="quarterly">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_quarterly'] . '</option>';
						$HTML .= '<option value="monthly">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
						break;
					case 'quarterly' :
						$HTML .= '<option value="annual">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
						$HTML .= '<option value="biannual">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_biannual'] . '</option>';
						$HTML .= '<option selected="selected" value="quarterly">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_quarterly'] . '</option>';
						$HTML .= '<option value="monthly">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
						break;
					case 'monthly' :
						$HTML .= '<option value="annual">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
						$HTML .= '<option value="biannual">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_biannual'] . '</option>';
						$HTML .= '<option value="quarterly">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_quarterly'] . '</option>';
						$HTML .= '<option selected="selected" value="monthly">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
						break;
					default :
						$HTML .= '<option value="annual">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
						$HTML .= '<option value="biannual">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_biannual'] . '</option>';
						$HTML .= '<option value="quarterly">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_quarterly'] . '</option>';
						$HTML .= '<option value="monthly">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
						break;
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelectEnable ('budget|assessment|unit', $_HOAM_language ['field_names']['budget']['assessment']['unit']);
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|unit'])) {
					$default_unit = $_SESSION['config_information']['trusted_budget|assessment|unit'];
				} else {
					$default_unit = $_HOAM_setting['budget']['assessment']['unit'];
				}
				switch ($default_unit) {
					case 'single' :
						$HTML .= '<option selected="selected" value="single">' . $_HOAM_language ['field_names']['budget']['assessment']['unit_single'] . '</option>';
						$HTML .= '<option value="sqft">' . $_HOAM_language ['field_names']['budget']['assessment']['unit_sqft'] . '</option>';
						break;
					case 'sqft' :
						$HTML .= '<option value="single">' . $_HOAM_language ['field_names']['budget']['assessment']['unit_single'] . '</option>';
						$HTML .= '<option selected="selected" value="sqft">' . $_HOAM_language ['field_names']['budget']['assessment']['unit_sqft'] . '</option>';
						break;
					default :
						$HTML .= '<option value="single">' . $_HOAM_language ['field_names']['budget']['assessment']['unit_single'] . '</option>';
						$HTML .= '<option value="sqft">' . $_HOAM_language ['field_names']['budget']['assessment']['unit_sqft'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formFieldEnable ('budget|assessment|amount', $_HOAM_language ['field_names']['budget']['assessment']['amount'], $_SESSION['config_information']['trusted_budget|assessment|amount'], $_HOAM_setting ['budget']['assessment']['amount'], 6, 1);
				// Need to add some JS so that the assessment date is not shown
				// unless the frequency of assessments is set for 'annual'.
				$HTML .= HOAM_formDateEnable ('budget|assessment|date', $_HOAM_language ['field_names']['budget']['assessment']['date'], $_SESSION['config_information']['trusted_budget|assessment|date'], HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($_HOAM_setting ['budget']['assessment']['date'])));
				$HTML .= HOAM_formSelectEnable ('budget|assessment|time', $_HOAM_language ['field_names']['budget']['assessment']['time']);
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|time'])) {
					$default_unit = $_SESSION['config_information']['trusted_budget|assessment|time'];
				} else {
					$default_unit = $_HOAM_setting['budget']['assessment']['time'];
				}
				switch ($default_unit) {
					case 'days' :
						$HTML .= '<option selected="selected" value="days">' . $_HOAM_language ['field_names']['budget']['assessment']['time_days'] . '</option>';
						$HTML .= '<option value="months">' . $_HOAM_language ['field_names']['budget']['assessment']['time_months'] . '</option>';
						break;
					case 'months' :
						$HTML .= '<option value="days">' . $_HOAM_language ['field_names']['budget']['assessment']['time_days'] . '</option>';
						$HTML .= '<option selected="selected" value="months">' . $_HOAM_language ['field_names']['budget']['assessment']['time_months'] . '</option>';
						break;
					default :
						$HTML .= '<option value="days">' . $_HOAM_language ['field_names']['budget']['assessment']['time_days'] . '</option>';
						$HTML .= '<option value="months">' . $_HOAM_language ['field_names']['budget']['assessment']['time_months'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formFieldEnable ('budget|assessment|days_due', $_HOAM_language ['field_names']['budget']['assessment']['days_due'], $_SESSION['config_information']['trusted_budget|assessment|days_due'], $_HOAM_setting ['budget']['assessment']['days_due'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('budget|assessment|days_late', $_HOAM_language ['field_names']['budget']['assessment']['days_late'], $_SESSION['config_information']['trusted_budget|assessment|days_late'], $_HOAM_setting ['budget']['assessment']['days_late'], 5, 1);
				$HTML .= HOAM_formFieldEnableHidden ('budget|assessment|months_due', $_HOAM_language ['field_names']['budget']['assessment']['months_due'], $_SESSION['config_information']['trusted_budget|assessment|months_due'], $_HOAM_setting ['budget']['assessment']['months_due'], 3, 1);
				$HTML .= HOAM_formFieldEnableHidden ('budget|assessment|months_late', $_HOAM_language ['field_names']['budget']['assessment']['months_late'], $_SESSION['config_information']['trusted_budget|assessment|months_late'], $_HOAM_setting ['budget']['assessment']['months_late'], 3, 1);

				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['delinquent'];								
				$HTML .= HOAM_formFieldEnable ('budget|fee|collection', $_HOAM_language ['field_names']['budget']['fee']['collection'], $_SESSION['config_information']['trusted_budget|fee|collection'], $_HOAM_setting ['budget']['fee']['collection'], 6, 1);
				$HTML .= HOAM_formFieldEnable ('budget|fee|late', $_HOAM_language ['field_names']['budget']['fee']['late'], $_SESSION['config_information']['trusted_budget|fee|late'], $_HOAM_setting ['budget']['fee']['late'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('budget|fee|returned', $_HOAM_language ['field_names']['budget']['fee']['returned'], $_SESSION['config_information']['trusted_budget|fee|returned'], $_HOAM_setting ['budget']['fee']['returned'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('budget|interest_rate', $_HOAM_language ['field_names']['budget']['interest_rate'], $_SESSION['config_information']['trusted_budget|interest_rate'], $_HOAM_setting ['budget']['interest_rate'], 5, 1);
				$HTML .= HOAM_formConfigCheckbox ('config|budget|payment_plan|interest', $_HOAM_language ['field_names']['config']['budget']['payment_plan']['interest'], 1, $_SESSION['config_information']['trusted_config|budget|payment_plan|interest'], $_HOAM_setting ['budget']['payment_plan']['interest']);
								
				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['category'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formSelectEnable ('config|budget|category|administrative', $_HOAM_language ['field_names']['config']['budget']['category']['administrative']);
				if (isset ($_SESSION['config_information']['trusted_budget|category|administrative'])) {
					$default_category = $_SESSION['config_information']['trusted_budget|category|administrative'];
				} else {
					$default_category = $_HOAM_setting ['budget']['category']['administrative'];
				}
				$category_list = $budget_category_class -> listCategoryAll ();
				$optgroup = FALSE;
				if (is_array ($category_list)) {
					foreach ($category_list as $category) {
						// Is this a parent category?
						if ($category[1] == 0) {
							if ($optgroup) {
								$HTML .= '</optgroup>';
							} else {
								$optgroup = TRUE;
							}
							$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
							continue;
						}
						$HTML .= '<option ';
						if ($category[0] == $default_category) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
					}
					if ($optgroup) {
						$HTML .= '</optgroup>';
					}
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelectEnable ('config|budget|category|credit', $_HOAM_language ['field_names']['config']['budget']['category']['credit']);
				if (isset ($_SESSION['config_information']['trusted_budget|category|credit'])) {
					$default_category = $_SESSION['config_information']['trusted_budget|category|credit'];
				} else {
					$default_category = $_HOAM_setting ['budget']['category']['credit'];
				}
				$optgroup = FALSE;
				if (is_array ($category_list)) {
					foreach ($category_list as $category) {
						// Is this a parent category?
						if ($category[1] == 0) {
							if ($optgroup) {
								$HTML .= '</optgroup>';
							} else {
								$optgroup = TRUE;
							}
							$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
							continue;
						}
						$HTML .= '<option ';
						if ($category[0] == $default_category) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
					}
					if ($optgroup) {
						$HTML .= '</optgroup>';
					}
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelectEnable ('config|budget|category|dues', $_HOAM_language ['field_names']['config']['budget']['category']['dues']);
				if (isset ($_SESSION['config_information']['trusted_budget|category|dues'])) {
					$default_category = $_SESSION['config_information']['trusted_budget|category|dues'];
				} else {
					$default_category = $_HOAM_setting ['budget']['category']['dues'];
				}
				$optgroup = FALSE;
				if (is_array ($category_list)) {
					foreach ($category_list as $category) {
						// Is this a parent category?
						if ($category[1] == 0) {
							if ($optgroup) {
								$HTML .= '</optgroup>';
							} else {
								$optgroup = TRUE;
							}
							$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
							continue;
						}
						$HTML .= '<option ';
						if ($category[0] == $default_category) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
					}
					if ($optgroup) {
						$HTML .= '</optgroup>';
					}
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelectEnable ('config|budget|category|interest', $_HOAM_language ['field_names']['config']['budget']['category']['interest']);
				if (isset ($_SESSION['config_information']['trusted_budget|category|interest'])) {
					$default_category = $_SESSION['config_information']['trusted_budget|category|interest'];
				} else {
					$default_category = $_HOAM_setting ['budget']['category']['interest'];
				}
				$optgroup = FALSE;
				if (is_array ($category_list)) {
					foreach ($category_list as $category) {
						// Is this a parent category?
						if ($category[1] == 0) {
							if ($optgroup) {
								$HTML .= '</optgroup>';
							} else {
								$optgroup = TRUE;
							}
							$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
							continue;
						}
						$HTML .= '<option ';
						if ($category[0] == $default_category) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
					}
					if ($optgroup) {
						$HTML .= '</optgroup>';
					}
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelectEnable ('config|budget|category|late', $_HOAM_language ['field_names']['config']['budget']['category']['late']);
				if (isset ($_SESSION['config_information']['trusted_budget|category|late'])) {
					$default_category = $_SESSION['config_information']['trusted_budget|category|late'];
				} else {
					$default_category = $_HOAM_setting ['budget']['category']['late'];
				}
				$optgroup = FALSE;
				if (is_array ($category_list)) {
					foreach ($category_list as $category) {
						// Is this a parent category?
						if ($category[1] == 0) {
							if ($optgroup) {
								$HTML .= '</optgroup>';
							} else {
								$optgroup = TRUE;
							}
							$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
							continue;
						}
						$HTML .= '<option ';
						if ($category[0] == $default_category) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
					}
					if ($optgroup) {
						$HTML .= '</optgroup>';
					}
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelectEnable ('config|budget|category|property', $_HOAM_language ['field_names']['config']['budget']['category']['property']);
				if (isset ($_SESSION['config_information']['trusted_budget|category|property'])) {
					$default_category = $_SESSION['config_information']['trusted_budget|category|property'];
				} else {
					$default_category = $_HOAM_setting ['budget']['category']['property'];
				}
				$optgroup = FALSE;
				if (is_array ($category_list)) {
					foreach ($category_list as $category) {
						// Is this a parent category?
						if ($category[1] == 0) {
							if ($optgroup) {
								$HTML .= '</optgroup>';
							} else {
								$optgroup = TRUE;
							}
							$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
							continue;
						}
						$HTML .= '<option ';
						if ($category[0] == $default_category) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
					}
					if ($optgroup) {
						$HTML .= '</optgroup>';
					}
				}
				$HTML .= '</select>';
				$HTML .= '</fieldset>';

				$HTML .= '<fieldset>';
				$HTML .= HOAM_formSelectEnable ('config|budget|vendor|management', $_HOAM_language ['field_names']['config']['budget']['vendor']['management']);
				if (isset ($_SESSION['config_information']['trusted_budget|vendor|management'])) {
					$default_category = $_SESSION['config_information']['trusted_budget|vendor|management'];
				} else {
					$default_category = $_HOAM_setting ['budget']['ids']['management'];
				}
				$vendor_list = $budget_vendor_class -> listAll (NULL, 'name ASC');
				foreach ($vendor_list as $vendor) {
					$HTML .= '<option ';
					if ($default_category == $vendor['id']) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $vendor['id'] . '">';
					$HTML .= $vendor['name'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelectEnable ('config|budget|vendor|organization', $_HOAM_language ['field_names']['config']['budget']['vendor']['organization']);
				if (isset ($_SESSION['config_information']['trusted_budget|vendor|organization'])) {
					$default_category = $_SESSION['config_information']['trusted_budget|vendor|organization'];
				} else {
					$default_category = $_HOAM_setting ['budget']['ids']['organization'];
				}
				foreach ($vendor_list as $vendor) {
					$HTML .= '<option ';
					if ($default_category == $vendor['id']) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $vendor['id'] . '">';
					$HTML .= $vendor['name'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</fieldset>';
				
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formSelectEnable ('config|budget|account|income', $_HOAM_language ['field_names']['config']['budget']['account']['income']);
				if (isset ($_SESSION['config_information']['trusted_budget|account|income'])) {
					$default_account = $_SESSION['config_information']['trusted_budget|account|income'];
				} else {
					$default_account = $_HOAM_setting ['budget']['account']['income'];
				}
				$account_list = $budget_account_class -> listAll (NULL, 'name ASC');
				foreach ($account_list as $account) {
					$HTML .= '<option ';
					if ($default_account == $account['id']) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $account['id'] . '">';
					$HTML .= $account['name'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelectEnable ('config|budget|account|expense', $_HOAM_language ['field_names']['config']['budget']['account']['expense']);
				if (isset ($_SESSION['config_information']['trusted_budget|account|expense'])) {
					$default_account = $_SESSION['config_information']['trusted_budget|account|expense'];
				} else {
					$default_account = $_HOAM_setting ['budget']['account']['expense'];
				}
				foreach ($account_list as $account) {
					$HTML .= '<option ';
					if ($default_account == $account['id']) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $account['id'] . '">';
					$HTML .= $account['name'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</fieldset>';
				
				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['insurance'];
				$HTML .= HOAM_formConfigCheckbox ('config|budget|insurance|enable', $_HOAM_language ['field_names']['config']['budget']['insurance']['enable'], 1, $_SESSION['config_information']['trusted_config|budget|insurance|enable'], $_HOAM_setting ['budget']['insurance']['enable']);
				$HTML .= '<fieldset id="insurance_enable" style="display: none">';
				$HTML .= HOAM_formFieldEnable ('config|budget|insurance|minimum_policy_length', $_HOAM_language ['field_names']['config']['budget']['insurance']['minimum_policy_length'], $_SESSION['config_information']['trusted_insurance|minimum_policy_length'], $_HOAM_setting ['budget']['insurance']['minimum_policy_length'], 4, 1);
				$HTML .= HOAM_formConfigCheckbox ('config|budget|insurance|reminders', $_HOAM_language ['field_names']['config']['budget']['insurance']['reminders'], 1, $_SESSION['config_information']['trusted_config|budget|insurance|reminders'], $_HOAM_setting ['budget']['insurance']['reminders']);
				$HTML .= HOAM_formFieldEnable ('config|budget|insurance|days', $_HOAM_language ['field_names']['config']['budget']['insurance']['days'], $_SESSION['config_information']['trusted_config|budget|insurance|days'], $_HOAM_setting ['budget']['insurance']['days'], 3, 1);
				$HTML .= HOAM_formFieldEnable ('config|budget|insurance|flags|replaced', $_HOAM_language ['field_names']['config']['budget']['insurance']['flags']['replaced'], $_SESSION['config_information']['trusted_config|budget|insurance|flags|replaced'], $_HOAM_setting ['budget']['insurance']['flags']['replaced'], 20, 1);
				$HTML .= '</fieldset>';

				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['paypal'];
				$HTML .= HOAM_formConfigCheckbox ('config|budget|paypal|enable', $_HOAM_language ['field_names']['config']['budget']['paypal']['enable'], 1, $_SESSION['config_information']['trusted_config|budget|paypal|enable'], $_HOAM_setting ['budget']['paypal']['enable']);
				$HTML .= '<fieldset id="paypal_enable" style="display: none">';
				$HTML .= HOAM_formTextEnable ('config|budget|paypal|email', $_HOAM_language ['field_names']['config']['budget']['paypal']['email'], $_SESSION['config_information']['trusted_config|budget|paypal|email'], $_HOAM_setting ['budget']['paypal']['email'], 256, 1);
				$HTML .= HOAM_formFieldEnable ('config|budget|paypal|surcharge_amount', $_HOAM_language ['field_names']['config']['budget']['paypal']['surcharge_amount'], $_SESSION['config_information']['trusted_config|budget|paypal|surcharge_amount'], $_HOAM_setting ['budget']['paypal']['surcharge']['amount'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('config|budget|paypal|surcharge_percent', $_HOAM_language ['field_names']['config']['budget']['paypal']['surcharge_percent'], $_SESSION['config_information']['trusted_config|budget|paypal|surcharge_percent'], $_HOAM_setting ['budget']['paypal']['surcharge']['percent'], 5, 1);
				$HTML .= '</fieldset>';

				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['miscellaneous'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formTextareaEnable ('budget|invoice|fineprint', $_HOAM_language ['field_names']['budget']['invoice']['fineprint'], $_SESSION['config_information']['trusted_budget|invoice|fineprint'], $_HOAM_setting ['budget']['invoice']['fineprint'], $_SESSION['current_user']['edit']['row'], 4096, 0);
				$HTML .= HOAM_formFieldEnable ('budget|sales_tax_rate', $_HOAM_language ['field_names']['budget']['sales_tax_rate'], $_SESSION['config_information']['trusted_budget|sales_tax_rate'], $_HOAM_setting ['budget']['sales_tax_rate'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('budget|minimum_description_length', $_HOAM_language ['field_names']['budget']['category']['minimum_description_length'], $_SESSION['config_information']['trusted_budget|minimum_description_length'], $_HOAM_setting ['budget']['minimum_description_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('budget|minimum_name_length', $_HOAM_language ['field_names']['budget']['category']['minimum_name_length'], $_SESSION['config_information']['trusted_budget|minimum_name_length'], $_HOAM_setting ['budget']['minimum_name_length'], 5, 1);
				$HTML .= HOAM_formConfigCheckbox ('config|budget|zero_amount|enable', $_HOAM_language ['field_names']['config']['budget']['zero_amount']['enable'], 1, $_SESSION['config_information']['trusted_config|budget|zero_amount|enable'], $_HOAM_setting ['budget']['zero_amount']['enable']);
				$HTML .= '</fieldset>';

				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['vendor'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formSelectEnable ('config|budget|vendor|percent_different', $_HOAM_language ['field_names']['config']['budget']['vendor']['percent_different']);
				if (isset ($_SESSION['config_information']['trusted_budget|vendor|percent_different'])) {
					$default_amount = $_SESSION['config_information']['trusted_budget|vendor|percent_different'];
				} else {
					$default_amount = $_HOAM_setting ['budget']['vendor']['percent_different'];
				}
				for ($i = 0; $i <= 100; $i++) {
					$HTML .= '<option ';
					if ($default_amount == $i) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $i . '">' . $i . '</option>';
				}
				$HTML .= '</select>';
				
				$HTML .= '</fieldset>';
				
				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('budget|flags|assessment', $_HOAM_language ['field_names']['budget']['flags']['assessment'], $_SESSION['config_information']['trusted_budget|flags|assessment'], $_HOAM_setting ['budget']['flags']['assessment'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|assessment_other', $_HOAM_language ['field_names']['budget']['flags']['assessment_other'], $_SESSION['config_information']['trusted_budget|flags|assessment_other'], $_HOAM_setting ['budget']['flags']['assessment_other'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|assessment_special', $_HOAM_language ['field_names']['budget']['flags']['assessment_special'], $_SESSION['config_information']['trusted_budget|flags|assessment_special'], $_HOAM_setting ['budget']['flags']['assessment_special'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|credit', $_HOAM_language ['field_names']['budget']['flags']['credit'], $_SESSION['config_information']['trusted_budget|flags|credit'], $_HOAM_setting ['budget']['flags']['credit'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|fee_administration', $_HOAM_language ['field_names']['budget']['flags']['fee_administration'], $_SESSION['config_information']['trusted_budget|flags|fee_administration'], $_HOAM_setting ['budget']['flags']['fee_administration'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|fee_fine', $_HOAM_language ['field_names']['budget']['flags']['fee_fine'], $_SESSION['config_information']['trusted_budget|flags|fee_fine'], $_HOAM_setting ['budget']['flags']['fee_fine'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|fee_interest', $_HOAM_language ['field_names']['budget']['flags']['fee_interest'], $_SESSION['config_information']['trusted_budget|flags|fee_interest'], $_HOAM_setting ['budget']['flags']['fee_interest'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|fee_late', $_HOAM_language ['field_names']['budget']['flags']['fee_late'], $_SESSION['config_information']['trusted_budget|flags|fee_late'], $_HOAM_setting ['budget']['flags']['fee_late'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|fee_legal', $_HOAM_language ['field_names']['budget']['flags']['fee_legal'], $_SESSION['config_information']['trusted_budget|flags|fee_legal'], $_HOAM_setting ['budget']['flags']['fee_legal'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|payment', $_HOAM_language ['field_names']['budget']['flags']['payment'], $_SESSION['config_information']['trusted_budget|flags|payment'], $_HOAM_setting ['budget']['flags']['payment'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|sales_tax', $_HOAM_language ['field_names']['budget']['flags']['sales_tax'], $_SESSION['config_information']['trusted_budget|flags|sales_tax'], $_HOAM_setting ['budget']['flags']['sales_tax'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|apply_current_homeowner', $_HOAM_language ['field_names']['budget']['flags']['apply_current_homeowner'], $_SESSION['config_information']['trusted_budget|flags|apply_current_homeowner'], $_HOAM_setting ['budget']['flags']['apply_current_homeowner'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|apply_fee_late', $_HOAM_language ['field_names']['budget']['flags']['apply_fee_late'], $_SESSION['config_information']['trusted_budget|flags|apply_fee_late'], $_HOAM_setting ['budget']['flags']['apply_fee_late'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|apply_interest', $_HOAM_language ['field_names']['budget']['flags']['apply_interest'], $_SESSION['config_information']['trusted_budget|flags|apply_interest'], $_HOAM_setting ['budget']['flags']['apply_interest'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|apply_sales_tax', $_HOAM_language ['field_names']['budget']['flags']['apply_sales_tax'], $_SESSION['config_information']['trusted_budget|flags|apply_sales_tax'], $_HOAM_setting ['budget']['flags']['apply_sales_tax'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|category_income', $_HOAM_language ['field_names']['budget']['flags']['category_income'], $_SESSION['config_information']['trusted_budget|flags|category_income'], $_HOAM_setting ['budget']['flags']['category_income'], 20, 1);
				$HTML .= '</fieldset>';
				
				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['homeowner_flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('budget|flags|late_notice', $_HOAM_language ['field_names']['budget']['flags']['late_notice'], $_SESSION['config_information']['trusted_budget|flags|late_notice'], $_HOAM_setting ['budget']['flags']['late_notice'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|late_delinquent', $_HOAM_language ['field_names']['budget']['flags']['late_delinquent'], $_SESSION['config_information']['trusted_budget|flags|late_delinquent'], $_HOAM_setting ['budget']['flags']['late_delinquent'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|late_default', $_HOAM_language ['field_names']['budget']['flags']['late_default'], $_SESSION['config_information']['trusted_budget|flags|late_default'], $_HOAM_setting ['budget']['flags']['late_default'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|late_lien', $_HOAM_language ['field_names']['budget']['flags']['late_lien'], $_SESSION['config_information']['trusted_budget|flags|late_lien'], $_HOAM_setting ['budget']['flags']['late_lien'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|late_attorney', $_HOAM_language ['field_names']['budget']['flags']['late_attorney'], $_SESSION['config_information']['trusted_budget|flags|late_attorney'], $_HOAM_setting ['budget']['flags']['late_attorney'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|late_attorney_demand', $_HOAM_language ['field_names']['budget']['flags']['late_attorney_demand'], $_SESSION['config_information']['trusted_budget|flags|late_attorney_demand'], $_HOAM_setting ['budget']['flags']['late_attorney_demand'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|late_attorney_foreclosure', $_HOAM_language ['field_names']['budget']['flags']['late_attorney_foreclosure'], $_SESSION['config_information']['trusted_budget|flags|late_attorney_foreclosure'], $_HOAM_setting ['budget']['flags']['late_attorney_foreclosure'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|late_attorney_eviction', $_HOAM_language ['field_names']['budget']['flags']['late_attorney_eviction'], $_SESSION['config_information']['trusted_budget|flags|late_attorney_eviction'], $_HOAM_setting ['budget']['flags']['late_attorney_eviction'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|payment_plan', $_HOAM_language ['field_names']['budget']['flags']['payment_plan'], $_SESSION['config_information']['trusted_budget|flags|payment_plan'], $_HOAM_setting ['budget']['flags']['payment_plan'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|payment_plan_default', $_HOAM_language ['field_names']['budget']['flags']['payment_plan_default'], $_SESSION['config_information']['trusted_budget|flags|payment_plan_default'], $_HOAM_setting ['budget']['flags']['payment_plan_default'], 20, 1);
				$HTML .= '</fieldset>';

				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['vendor_flags'];
				$HTML .= HOAM_formFieldEnable ('budget|flags|due_15', $_HOAM_language ['budget']['term'][1]['name'], $_SESSION['config_information']['trusted_budget|flags|due_15'], $_HOAM_setting ['budget']['flags']['due_15'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|due_20', $_HOAM_language ['budget']['term'][2]['name'], $_SESSION['config_information']['trusted_budget|flags|due_20'], $_HOAM_setting ['budget']['flags']['due_20'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|due_30', $_HOAM_language ['budget']['term'][3]['name'], $_SESSION['config_information']['trusted_budget|flags|due_30'], $_HOAM_setting ['budget']['flags']['due_30'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|due_45', $_HOAM_language ['budget']['term'][4]['name'], $_SESSION['config_information']['trusted_budget|flags|due_45'], $_HOAM_setting ['budget']['flags']['due_45'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|due_recipt', $_HOAM_language ['budget']['term'][0]['name'], $_SESSION['config_information']['trusted_budget|flags|due_receipt'], $_HOAM_setting ['budget']['flags']['due_receipt'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|ignore_average', $_HOAM_language ['field_names']['budget']['flags']['ignore_average'], $_SESSION['config_information']['trusted_budget|flags|ignore_average'], $_HOAM_setting ['budget']['flags']['ignore_average'], 20, 1);
				
				$HTML .= $_HOAM_language ['help_pages']['config']['budget']['budget_flags'];
				$HTML .= HOAM_formFieldEnable ('budget|flags|annual', $_HOAM_language ['field_names']['budget']['flags']['annual'], $_SESSION['config_information']['trusted_budget|flags|annual'], $_HOAM_setting ['budget']['flags']['annual'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('budget|flags|public', $_HOAM_language ['field_names']['budget']['flags']['public'], $_SESSION['config_information']['trusted_budget|flags|public'], $_HOAM_setting ['budget']['flags']['public'], 20, 1);
				
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Budget tab

				$HTML .= '<div class="tabbertab" id="admin_email" title="' . $_HOAM_language ['form_tabs']['config']['email'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formTextEnable ('organization|email|board', $_HOAM_language ['field_names']['organization']['email']['board'], $_SESSION['config_information']['trusted_organization|email|board'], $_HOAM_setting ['organization']['email']['board'], 256, 1);
				$HTML .= HOAM_formTextEnable ('organization|email|officers', $_HOAM_language ['field_names']['organization']['email']['officers'], $_SESSION['config_information']['trusted_organization|email|officers'], $_HOAM_setting ['organization']['email']['officers'], 256, 1);
				$HTML .= HOAM_formTextEnable ('organization|email|management', $_HOAM_language ['field_names']['organization']['email']['management'], $_SESSION['config_information']['trusted_organization|email|management'], $_HOAM_setting ['organization']['email']['management'], 256, 1);
				$HTML .= '<fieldset>';
//				$HTML .= HOAM_formConfigCheckbox ('config|email|automated|enable', $_HOAM_language ['field_names']['config']['email']['automated'], 1, $_SESSION['config_information']['trusted_config|email|automated|enable'], $_HOAM_setting ['email']['automated']);
				$HTML .= HOAM_formConfigCheckbox ('config|email|automated', $_HOAM_language ['field_names']['config']['email']['automated'], 1, $_SESSION['config_information']['trusted_email|automated'], $_HOAM_setting ['email']['automated']);
				if ($_HOAM_setting ['email']['automated']) {
					$HTML .= '<fieldset id="email_automated_enable">';
				} else {
					$HTML .= '<fieldset style="display: none" id="email_automated_enable">';
				}
				$HTML .= HOAM_formFieldEnable ('config|email|server', $_HOAM_language ['field_names']['config']['email']['server'], $_SESSION['config_information']['trusted_email|server'], $_HOAM_setting ['email']['server'], 64, 1);
				$HTML .= HOAM_formFieldEnable ('config|email|port', $_HOAM_language ['field_names']['config']['email']['port'], $_SESSION['config_information']['trusted_email|port'], $_HOAM_setting ['email']['port'], 5, 1);
				$HTML .= HOAM_formConfigCheckbox ('config|email|auth|enable', $_HOAM_language ['field_names']['config']['email']['auth'], 1, $_SESSION['config_information']['trusted_config|email|auth|enable'], $_HOAM_setting ['email']['auth']);
				if ($_HOAM_setting ['email']['auth']) {
					$HTML .= '<fieldset id="email_auth_enable">';
				} else {
					$HTML .= '<fieldset style="display: none" id="email_auth_enable">';
				}
				$HTML .= HOAM_formFieldEnable ('config|email|username', $_HOAM_language ['field_names']['config']['email']['username'], $_SESSION['config_information']['trusted_email|username'], $_HOAM_setting ['email']['username'], 32, 1);
				$HTML .= HOAM_formPasswordEnable ('config|email|password', $_HOAM_language ['field_names']['config']['email']['password'], $_SESSION['config_information']['trusted_email|password'], $_HOAM_setting ['email']['password'], 32, 1);
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formConfigCheckbox ('config|email|do_not_reply', $_HOAM_language ['field_names']['config']['email']['do_not_reply'], 1, $_SESSION['config_information']['trusted_config|email|do_not_reply'], $_HOAM_setting ['email']['do_not_reply']);
				$HTML .= '</fieldset>';
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Email tab
				
				$HTML .= '<div class="tabbertab" id="admin_files" title="' . $_HOAM_language ['form_tabs']['config']['files'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formTextEnable ('files|banned_username', $_HOAM_language ['field_names']['files']['banned_username'], $_SESSION['config_information']['trusted_files|banned_username'], $_HOAM_setting ['files']['banned_username'], 128, 1);
				$HTML .= HOAM_formTextEnable ('files|censored_words', $_HOAM_language ['field_names']['files']['censored_words'], $_SESSION['config_information']['trusted_files|censored_words'], $_HOAM_setting ['files']['censored_words'], 128, 1);
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Files tab
				
				$HTML .= '<div class="tabbertab" id="admin_group" title="' . $_HOAM_language ['form_tabs']['config']['groups'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formFieldEnable ('group|minimum_description_length', $_HOAM_language ['field_names']['group']['minimum_description_length'], $_SESSION['config_information']['trusted_group|minimum_description_length'], $_HOAM_setting ['group']['minimum_description_length'], 3, 1);
				$HTML .= HOAM_formFieldEnable ('group|minimum_name_length', $_HOAM_language ['field_names']['group']['minimum_name_length'], $_SESSION['config_information']['trusted_group|minimum_name_length'], $_HOAM_setting ['group']['minimum_name_length'], 3, 1);

				$HTML .= HOAM_formSelectGroupEnable ('group|ids|admin', $_HOAM_language ['field_names']['group']['ids']['admin'], $_SESSION['config_information']['trusted_group|ids|admin'], $_HOAM_setting ['group']['ids']['admin']);
				$HTML .= HOAM_formSelectGroupEnable ('group|ids|anonymous', $_HOAM_language ['field_names']['group']['ids']['anonymous'], $_SESSION['config_information']['trusted_group|ids|anonymous'], $_HOAM_setting ['group']['ids']['anonymous']);
				$HTML .= HOAM_formSelectGroupEnable ('group|ids|board', $_HOAM_language ['field_names']['group']['ids']['board'], $_SESSION['config_information']['trusted_group|ids|board'], $_HOAM_setting ['group']['ids']['board']);
				$HTML .= HOAM_formSelectGroupEnable ('group|ids|everyone', $_HOAM_language ['field_names']['group']['ids']['everyone'], $_SESSION['config_information']['trusted_group|ids|everyone'], $_HOAM_setting ['group']['ids']['everyone']);
				$HTML .= HOAM_formSelectGroupEnable ('group|ids|homeowner', $_HOAM_language ['field_names']['group']['ids']['homeowner'], $_SESSION['config_information']['trusted_group|ids|homeowner'], $_HOAM_setting ['group']['ids']['homeowner']);
				$HTML .= HOAM_formSelectGroupEnable ('group|ids|officer', $_HOAM_language ['field_names']['group']['ids']['officer'], $_SESSION['config_information']['trusted_group|ids|officer'], $_HOAM_setting ['group']['ids']['officer']);
				$HTML .= HOAM_formSelectGroupEnable ('group|ids|registered', $_HOAM_language ['field_names']['group']['ids']['registered'], $_SESSION['config_information']['trusted_group|ids|registered'], $_HOAM_setting ['group']['ids']['registered']);
				$HTML .= HOAM_formSelectGroupEnable ('group|ids|resident', $_HOAM_language ['field_names']['group']['ids']['resident'], $_SESSION['config_information']['trusted_group|ids|resident'], $_HOAM_setting ['group']['ids']['resident']);

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['advertising'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|advertising_add', $_HOAM_language ['field_names']['group']['ids']['advertising_add'], $_SESSION['config_information']['trusted_group|ids|budget_add'], $_HOAM_setting ['group']['ids']['advertising_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|advertising_delete', $_HOAM_language ['field_names']['group']['ids']['advertising_delete'], $_SESSION['config_information']['trusted_group|ids|budget_delete'], $_HOAM_setting ['group']['ids']['advertising_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|advertising_edit', $_HOAM_language ['field_names']['group']['ids']['advertising_edit'], $_SESSION['config_information']['trusted_group|ids|budget_edit'], $_HOAM_setting ['group']['ids']['advertising_edit']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['article'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|article_add', $_HOAM_language ['field_names']['group']['ids']['article_add'], $_SESSION['config_information']['trusted_group|ids|budget_add'], $_HOAM_setting ['group']['ids']['article_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|article_delete', $_HOAM_language ['field_names']['group']['ids']['article_delete'], $_SESSION['config_information']['trusted_group|ids|budget_delete'], $_HOAM_setting ['group']['ids']['article_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|article_edit', $_HOAM_language ['field_names']['group']['ids']['article_edit'], $_SESSION['config_information']['trusted_group|ids|budget_edit'], $_HOAM_setting ['group']['ids']['article_edit']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['attachment'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|attachment_add', $_HOAM_language ['field_names']['group']['ids']['attachment_add'], $_SESSION['config_information']['trusted_group|ids|attachment_add'], $_HOAM_setting ['group']['ids']['attachment_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|attachment_delete', $_HOAM_language ['field_names']['group']['ids']['attachment_delete'], $_SESSION['config_information']['trusted_group|ids|attachment_delete'], $_HOAM_setting ['group']['ids']['attachment_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|attachment_edit', $_HOAM_language ['field_names']['group']['ids']['attachment_edit'], $_SESSION['config_information']['trusted_group|ids|attachment_edit'], $_HOAM_setting ['group']['ids']['attachment_edit']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|attachment_view', $_HOAM_language ['field_names']['group']['ids']['attachment_view'], $_SESSION['config_information']['trusted_group|ids|attachment_view'], $_HOAM_setting ['group']['ids']['attachment_view']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['budget'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|budget_add', $_HOAM_language ['field_names']['group']['ids']['budget_add'], $_SESSION['config_information']['trusted_group|ids|budget_add'], $_HOAM_setting ['group']['ids']['budget_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|budget_approve', $_HOAM_language ['field_names']['group']['ids']['budget_approve'], $_SESSION['config_information']['trusted_group|ids|budget_approve'], $_HOAM_setting ['group']['ids']['budget_approve']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|budget_delete', $_HOAM_language ['field_names']['group']['ids']['budget_delete'], $_SESSION['config_information']['trusted_group|ids|budget_delete'], $_HOAM_setting ['group']['ids']['budget_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|budget_edit', $_HOAM_language ['field_names']['group']['ids']['budget_edit'], $_SESSION['config_information']['trusted_group|ids|budget_edit'], $_HOAM_setting ['group']['ids']['budget_edit']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|budget_view', $_HOAM_language ['field_names']['group']['ids']['budget_view'], $_SESSION['config_information']['trusted_group|ids|budget_view'], $_HOAM_setting ['group']['ids']['budget_view']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['group'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|group_add', $_HOAM_language ['field_names']['group']['ids']['group_add'], $_SESSION['config_information']['trusted_group|ids|group_add'], $_HOAM_setting ['group']['ids']['group_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|group_delete', $_HOAM_language ['field_names']['group']['ids']['group_delete'], $_SESSION['config_information']['trusted_group|ids|group_delete'], $_HOAM_setting ['group']['ids']['group_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|group_edit', $_HOAM_language ['field_names']['group']['ids']['group_edit'], $_SESSION['config_information']['trusted_group|ids|group_edit'], $_HOAM_setting ['group']['ids']['group_edit']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['homeowner'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|homeowner_add', $_HOAM_language ['field_names']['group']['ids']['homeowner_add'], $_SESSION['config_information']['trusted_group|ids|homeowner_add'], $_HOAM_setting ['group']['ids']['homeowner_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|homeowner_delete', $_HOAM_language ['field_names']['group']['ids']['homeowner_delete'], $_SESSION['config_information']['trusted_group|ids|homeowner_delete'], $_HOAM_setting ['group']['ids']['homeowner_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|homeowner_edit', $_HOAM_language ['field_names']['group']['ids']['homeowner_edit'], $_SESSION['config_information']['trusted_group|ids|homeowner_edit'], $_HOAM_setting ['group']['ids']['homeowner_edit']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['insurance'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|insurance_add', $_HOAM_language ['field_names']['group']['ids']['insurance_add'], $_SESSION['config_information']['trusted_group|ids|insurance_add'], $_HOAM_setting ['group']['ids']['insurance_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|insurance_delete', $_HOAM_language ['field_names']['group']['ids']['insurance_delete'], $_SESSION['config_information']['trusted_group|ids|insurance_delete'], $_HOAM_setting ['group']['ids']['insurance_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|insurance_edit', $_HOAM_language ['field_names']['group']['ids']['insurance_edit'], $_SESSION['config_information']['trusted_group|ids|insurance_edit'], $_HOAM_setting ['group']['ids']['insurance_edit']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|insurance_view', $_HOAM_language ['field_names']['group']['ids']['insurance_view'], $_SESSION['config_information']['trusted_group|ids|insurance_vew'], $_HOAM_setting ['group']['ids']['insurance_view']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['lot'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|lot_add', $_HOAM_language ['field_names']['group']['ids']['lot_add'], $_SESSION['config_information']['trusted_group|ids|lot_add'], $_HOAM_setting ['group']['ids']['lot_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|lot_delete', $_HOAM_language ['field_names']['group']['ids']['lot_delete'], $_SESSION['config_information']['trusted_group|ids|lot_delete'], $_HOAM_setting ['group']['ids']['lot_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|lot_edit', $_HOAM_language ['field_names']['group']['ids']['lot_edit'], $_SESSION['config_information']['trusted_group|ids|lot_edit'], $_HOAM_setting ['group']['ids']['lot_edit']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['messageboard'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|messageboard_add', $_HOAM_language ['field_names']['group']['ids']['messageboard_add'], $_SESSION['config_information']['trusted_group|ids|messageboard_add'], $_HOAM_setting ['group']['ids']['messageboard_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|messageboard_delete', $_HOAM_language ['field_names']['group']['ids']['messageboard_delete'], $_SESSION['config_information']['trusted_group|ids|messageboard_delete'], $_HOAM_setting ['group']['ids']['messageboard_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|messageboard_edit', $_HOAM_language ['field_names']['group']['ids']['messageboard_edit'], $_SESSION['config_information']['trusted_group|ids|messageboard_edit'], $_HOAM_setting ['group']['ids']['messageboard_edit']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|messageboard_post', $_HOAM_language ['field_names']['group']['ids']['messageboard_post'], $_SESSION['config_information']['trusted_group|ids|messageboard_post'], $_HOAM_setting ['group']['ids']['messageboard_post']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|messageboard_view', $_HOAM_language ['field_names']['group']['ids']['messageboard_view'], $_SESSION['config_information']['trusted_group|ids|messageboard_view'], $_HOAM_setting ['group']['ids']['messageboard_view']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['news'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|news_add', $_HOAM_language ['field_names']['group']['ids']['news_add'], $_SESSION['config_information']['trusted_group|ids|news_add'], $_HOAM_setting ['group']['ids']['news_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|news_delete', $_HOAM_language ['field_names']['group']['ids']['news_delete'], $_SESSION['config_information']['trusted_group|ids|news_delete'], $_HOAM_setting ['group']['ids']['news_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|news_edit', $_HOAM_language ['field_names']['group']['ids']['news_edit'], $_SESSION['config_information']['trusted_group|ids|news_edit'], $_HOAM_setting ['group']['ids']['news_edit']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['user'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|user_add', $_HOAM_language ['field_names']['group']['ids']['user_add'], $_SESSION['config_information']['trusted_group|ids|user_add'], $_HOAM_setting ['group']['ids']['user_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|user_delete', $_HOAM_language ['field_names']['group']['ids']['user_delete'], $_SESSION['config_information']['trusted_group|ids|user_delete'], $_HOAM_setting ['group']['ids']['user_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|user_edit', $_HOAM_language ['field_names']['group']['ids']['user_edit'], $_SESSION['config_information']['trusted_group|ids|user_edit'], $_HOAM_setting ['group']['ids']['user_edit']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['violation'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|violation_add', $_HOAM_language ['field_names']['group']['ids']['violation_add'], $_SESSION['config_information']['trusted_group|ids|violation_add'], $_HOAM_setting ['group']['ids']['violation_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|violation_approve', $_HOAM_language ['field_names']['group']['ids']['violation_approve'], $_SESSION['config_information']['trusted_group|ids|violation_approve'], $_HOAM_setting ['group']['ids']['violation_approve']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|violation_delete', $_HOAM_language ['field_names']['group']['ids']['violation_delete'], $_SESSION['config_information']['trusted_group|ids|violation_delete'], $_HOAM_setting ['group']['ids']['violation_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|violation_edit', $_HOAM_language ['field_names']['group']['ids']['violation_edit'], $_SESSION['config_information']['trusted_group|ids|violation_edit'], $_HOAM_setting ['group']['ids']['violation_edit']);
                $HTML .= '</fieldset>';

                $HTML .= $_HOAM_language ['help_pages']['config']['group']['work_request'];
                $HTML .= '<fieldset>';
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|work_request_add', $_HOAM_language ['field_names']['group']['ids']['work_request_add'], $_SESSION['config_information']['trusted_group|ids|work_request_add'], $_HOAM_setting ['group']['ids']['work_request_add']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|work_request_delete', $_HOAM_language ['field_names']['group']['ids']['work_request_delete'], $_SESSION['config_information']['trusted_group|ids|work_request_delete'], $_HOAM_setting ['group']['ids']['work_request_delete']);
                $HTML .= HOAM_formSelectGroupEnable ('group|ids|work_request_edit', $_HOAM_language ['field_names']['group']['ids']['work_request_edit'], $_SESSION['config_information']['trusted_group|ids|work_request_edit'], $_HOAM_setting ['group']['ids']['work_request_edit']);
                $HTML .= '</fieldset>';

				$HTML .= $_HOAM_language ['help_pages']['config']['group']['flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('group|flags|group', $_HOAM_language ['field_names']['group']['flags']['group'], $_SESSION['config_information']['trusted_group|flags|group'], $_HOAM_setting ['group']['flags']['group'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('group|flags|individual', $_HOAM_language ['field_names']['group']['flags']['individual'], $_SESSION['config_information']['trusted_group|flags|individual'], $_HOAM_setting ['group']['flags']['individual'], 20, 1);
				$HTML .= '</fieldset>';

				$HTML .= '</div>';
				$HTML .= '</div>'; // End Groups tab

				$HTML .= '<div class="tabbertab" id="admin_homeowner" title="' . $_HOAM_language ['form_tabs']['config']['homeowners'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formFieldEnable ('homeowner|default|city', $_HOAM_language ['field_names']['homeowner']['default']['city'], $_SESSION['config_information']['trusted_homeowner|default|city'], $_HOAM_setting ['homeowner']['default']['city'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('homeowner|default|postalcode', $_HOAM_language ['field_names']['homeowner']['default']['postalcode'], $_SESSION['config_information']['trusted_homeowner|default|postalcode'], $_HOAM_setting ['homeowner']['default']['postalcode'], $_HOAM_country ['postalcode']['length'], 1);
				$HTML .= '<label for="homeowner|default|state">' . $_HOAM_language ['field_names']['homeowner']['default']['state'];
				$HTML .= '</label>';
				$HTML .= '<select disabled="disabled" name="homeowner|default|state" id="homeowner|default|state" field="enable">';
				if (isset ($_SESSION['config_information']['trusted_homeowner|default|state'])) {
					$default_state = $_SESSION['config_information']['trusted_homeowner|default|state'];
				} else {
					$default_state = $_HOAM_setting ['homeowner']['default']['state'];
				}
				foreach ($_HOAM_country ['state'] as $state) {
					$HTML .= '<option ';
					if ($state['value'] == $default_state) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $state['value'] . '">' . $state['description'] . '</option>';
				}
				$HTML .= '</select>';

				$HTML .= HOAM_formFieldEnable ('homeowner|minimum_comments_length', $_HOAM_language ['field_names']['homeowner']['minimum_comments_length'], $_SESSION['config_information']['trusted_homeowner|minimum_comments_length'], $_HOAM_setting ['homeowner']['minimum_comments_length'], 5, 1);
				$HTML .= $_HOAM_language ['help_pages']['config']['homeowner']['flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('homeowner|flags|bankrupt', $_HOAM_language ['field_names']['budget']['flags']['bankrupt'], $_SESSION['config_information']['trusted_homeowner|flags|bankrupt'], $_HOAM_setting ['homeowner']['flags']['bankrupt'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('homeowner|flags|ignore_budget', $_HOAM_language ['field_names']['homeowner']['flags']['ignore_budget'], $_SESSION['config_information']['trusted_homeowner|flags|ignore_budget'], $_HOAM_setting ['homeowner']['flags']['ignore_budget'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('homeowner|flags|ignore_violations', $_HOAM_language ['field_names']['homeowner']['flags']['ignore_violations'], $_SESSION['config_information']['trusted_homeowner|flags|ignore_violations'], $_HOAM_setting ['homeowner']['flags']['ignore_violations'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('homeowner|flags|ignore_violations_temporary', $_HOAM_language ['field_names']['homeowner']['flags']['ignore_violations_temporary'], $_SESSION['config_information']['trusted_homeowner|ignore_violations_temporary'], $_HOAM_setting ['homeowner']['flags']['ignore_violations_temporary'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|homeowner|flags|no_fees', $_HOAM_language ['field_names']['config']['homeowner']['flags']['no_fees'], $_SESSION['config_information']['trusted_config|homeowner|flags|no_fees'], $_HOAM_setting ['homeowner']['flags']['no_fees'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('homeowner|flags|residence_mailing', $_HOAM_language ['field_names']['homeowner']['flags']['residence_mailing'], $_SESSION['config_information']['trusted_homeowner|flags|residence_mailing'], $_HOAM_setting ['homeowner']['flags']['residence_mailing'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('homeowner|flags|resident', $_HOAM_language ['field_names']['homeowner']['flags']['resident'], $_SESSION['config_information']['trusted_homeowner|flags|resident'], $_HOAM_setting ['homeowner']['flags']['resident'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('homeowner|flags|service_member', $_HOAM_language ['field_names']['homeowner']['flags']['service_member'], $_SESSION['config_information']['trusted_homeowner|flags|service_member'], $_HOAM_setting ['homeowner']['flags']['service_member'], 20, 1);
				$HTML .= '</fieldset>';
				
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Homeowner tab

				require_once (HOAM_PATH_LETTER . 'hoam-envelope.php');
				require_once (HOAM_PATH_LETTER . 'hoam-paper.php');
				$HTML .= '<div class="tabbertab" id="admin_letter" title="' . $_HOAM_language ['form_tabs']['config']['letters'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formConfigCheckbox ('config|homeowner|send_current_resident', $_HOAM_language ['field_names']['config']['homeowner']['send_current_resident'], 1, $_SESSION['config_information']['trusted_homeowner|send_current_resident'], $_HOAM_setting ['homeowner']['send_current_resident']);
				$HTML .= '<label for="config|letter|envelope">' . $_HOAM_language ['field_names']['config']['letter']['envelope'];
				$HTML .= '</label>';
				$HTML .= '<select disabled="disabled" name="config|letter|envelope" id="config|letter|envelope" field="enable">';
				if (isset ($_SESSION['config_information']['trusted_letter|envelope'])) {
					$default_envelope = $_SESSION['config_information']['trusted_letter|envelope'];
				} else {
					$default_envelope = $_HOAM_setting ['letter']['envelope'];
				}
				foreach ($_HOAM_envelope as $envelope) {
					$HTML .= '<option ';
					if ($envelope['value'] == $default_envelope) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $envelope['value'] . '">' . $envelope['description'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '<label for="config|letter|paper">' . $_HOAM_language ['field_names']['config']['letter']['paper'];
				$HTML .= '</label>';
				$HTML .= '<select disabled="disabled" name="config|letter|paper" id="config|letter|paper" field="enable">';
				if (isset ($_SESSION['config_information']['trusted_letter|paper'])) {
					$default_paper = $_SESSION['config_information']['trusted_letter|paper'];
				} else {
					$default_paper = $_HOAM_setting ['letter']['paper'];
				}
				foreach ($_HOAM_paper as $paper) {
					$HTML .= '<option ';
					if ($paper['value'] == $default_paper) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $paper['value'] . '">' . $paper['description'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formTextareaEnable ('organization|letter|head', $_HOAM_language ['field_names']['organization']['letter']['head'], $_SESSION['config_information']['trusted_organization|letter|head'], $_HOAM_setting ['organization']['letter']['head'], $_SESSION['current_user']['edit']['row'], 4096, 0);
				$HTML .= HOAM_formTextareaEnable ('organization|letter|salutation', $_HOAM_language ['field_names']['organization']['letter']['salutation'], $_SESSION['config_information']['trusted_organization|letter|salutation'], $_HOAM_setting ['organization']['letter']['salutation'], $_SESSION['current_user']['edit']['row'], 512, 0);
				$HTML .= HOAM_formTextareaEnable ('organization|letter|signature', $_HOAM_language ['field_names']['organization']['letter']['signature'], $_SESSION['config_information']['trusted_organization|letter|signature'], $_HOAM_setting ['organization']['letter']['signature'], $_SESSION['current_user']['edit']['row'], 512, 0);
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formTextareaEnable ('organization|letter|footer', $_HOAM_language ['field_names']['organization']['letter']['footer'], $_SESSION['config_information']['trusted_organization|letter|footer'], $_HOAM_setting ['organization']['letter']['footer'], $_SESSION['current_user']['edit']['row'], 4096, 0);
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Letters tab
				
				$HTML .= '<div class="tabbertab" id="admin_log" title="' . $_HOAM_language ['form_tabs']['config']['log'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formConfigCheckbox ('log|destination|db', $_HOAM_language ['field_names']['log']['db'], 1, $_SESSION['config_information']['trusted_log|destination|db'], (int) $_HOAM_setting ['log']['destinations'] & (int) HOAM_LOG_DB);
				$HTML .= HOAM_formConfigCheckbox ('log|destination|file', $_HOAM_language ['field_names']['log']['file'], 1, $_SESSION['config_information']['trusted_log|destination|file'], (int) $_HOAM_setting ['log']['destinations'] & (int) HOAM_LOG_FILE);
				$HTML .= '<fieldset id="log_file_enable">';
				$HTML .= HOAM_formFieldEnable ('log|destination|filename', $_HOAM_language ['field_names']['log']['filename'], $_SESSION['config_information']['trusted_log|destination|filename'], $_HOAM_setting ['log']['destination']['file'], 20, 1);
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formConfigCheckbox ('log|destination|syslog', $_HOAM_language ['field_names']['log']['syslog'], 1, $_SESSION['config_information']['trusted_log|destination|syslog'], (int) $_HOAM_setting ['log']['destinations'] & (int) HOAM_LOG_SYSLOG);
				$HTML .= '<fieldset id="log_syslog_enable">';
				$HTML .= HOAM_formFieldEnable ('log|destination|syslog_server', $_HOAM_language ['field_names']['log']['syslog_server'], $_SESSION['config_information']['trusted_log|destination|syslog_server'], $_HOAM_setting ['log']['destination']['syslog'], 20, 1);
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formConfigCheckbox ('config|log|browser', $_HOAM_language ['field_names']['config']['log']['browser'], 1, $_SESSION['config_information']['trusted_config|log|browser'], (int) $_HOAM_setting ['log']['browser']);
				$HTML .= $_HOAM_language ['help_pages']['config']['log']['levels'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formConfigCheckbox ('log|level|emergency', $_HOAM_language ['field_names']['log']['emergency'], 1, $_SESSION['config_information']['trusted_log|level|emergency'], (int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_EMERGENCY);
				$HTML .= HOAM_formConfigCheckbox ('log|level|critical', $_HOAM_language ['field_names']['log']['critical'], 1, $_SESSION['config_information']['trusted_log|level|critical'], (int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_CRITICAL);
				$HTML .= HOAM_formConfigCheckbox ('log|level|alert', $_HOAM_language ['field_names']['log']['alert'], 1, $_SESSION['config_information']['trusted_log|level|alert'], (int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_ALERT);
				$HTML .= HOAM_formConfigCheckbox ('log|level|error', $_HOAM_language ['field_names']['log']['error'], 1, $_SESSION['config_information']['trusted_log|level|error'], (int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_ERROR);
				$HTML .= HOAM_formConfigCheckbox ('log|level|warning', $_HOAM_language ['field_names']['log']['warning'], 1, $_SESSION['config_information']['trusted_log|level|warning'], (int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_WARNING);
				$HTML .= HOAM_formConfigCheckbox ('log|level|notice', $_HOAM_language ['field_names']['log']['notice'], 1, $_SESSION['config_information']['trusted_log|level|notice'], (int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_NOTICE);
				$HTML .= HOAM_formConfigCheckbox ('log|level|informational', $_HOAM_language ['field_names']['log']['informational'], 1, $_SESSION['config_information']['trusted_log|level|informational'], (int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_INFORMATIONAL);
				$HTML .= HOAM_formConfigCheckbox ('log|level|debug', $_HOAM_language ['field_names']['log']['debug'], 1, $_SESSION['config_information']['trusted_log|level|debug'], (int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_DEBUG);
				$HTML .= '</fieldset>';

				$HTML .= $_HOAM_language ['help_pages']['config']['log']['flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('config|log|flags|advertisement', $_HOAM_language ['field_names']['config']['log']['flags']['advertisement'], $_SESSION['config_information']['trusted_log|flags|advertisement'], $_HOAM_setting ['log']['flags']['advertisement'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|article', $_HOAM_language ['field_names']['config']['log']['flags']['article'], $_SESSION['config_information']['trusted_log|flags|article'], $_HOAM_setting ['log']['flags']['article'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|attachment', $_HOAM_language ['field_names']['config']['log']['flags']['attachment'], $_SESSION['config_information']['trusted_log|flags|attachment'], $_HOAM_setting ['log']['flags']['attachment'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|budget_category', $_HOAM_language ['field_names']['config']['log']['flags']['budget']['category'], $_SESSION['config_information']['trusted_log|flags|budget_category'], $_HOAM_setting ['log']['flags']['budget_category'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|budget_invoice', $_HOAM_language ['field_names']['config']['log']['flags']['budget']['invoice'], $_SESSION['config_information']['trusted_log|flags|budget_invoice'], $_HOAM_setting ['log']['flags']['budget_invoice'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|budget_vendor', $_HOAM_language ['field_names']['config']['log']['flags']['budget']['vendor'], $_SESSION['config_information']['trusted_log|flags|budget_vendor'], $_HOAM_setting ['log']['flags']['budget_vendor'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|group', $_HOAM_language ['field_names']['config']['log']['flags']['group'], $_SESSION['config_information']['trusted_log|flags|group'], $_HOAM_setting ['log']['flags']['group'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|homeowner', $_HOAM_language ['field_names']['config']['log']['flags']['homeowner'], $_SESSION['config_information']['trusted_log|flags|homeowner'], $_HOAM_setting ['log']['flags']['homeowner'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|insurance', $_HOAM_language ['field_names']['config']['log']['flags']['insurance'], $_SESSION['config_information']['trusted_log|flags|insurance'], $_HOAM_setting ['log']['flags']['insurance'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|lot', $_HOAM_language ['field_names']['config']['log']['flags']['lot'], $_SESSION['config_information']['trusted_log|flags|lot'], $_HOAM_setting ['log']['flags']['lot'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|messageboard', $_HOAM_language ['field_names']['config']['log']['flags']['messageboard'], $_SESSION['config_information']['trusted_log|flags|messageboard'], $_HOAM_setting ['log']['flags']['messageboard'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|news', $_HOAM_language ['field_names']['config']['log']['flags']['news'], $_SESSION['config_information']['trusted_log|flags|news'], $_HOAM_setting ['log']['flags']['news'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|property', $_HOAM_language ['field_names']['config']['log']['flags']['property'], $_SESSION['config_information']['trusted_log|flags|property'], $_HOAM_setting ['log']['flags']['property'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|upgrade', $_HOAM_language ['field_names']['config']['log']['flags']['upgrade'], $_SESSION['config_information']['trusted_log|flags|upgrade'], $_HOAM_setting ['log']['flags']['upgrade'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|user', $_HOAM_language ['field_names']['config']['log']['flags']['user'], $_SESSION['config_information']['trusted_log|flags|user'], $_HOAM_setting ['log']['flags']['user'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|violation', $_HOAM_language ['field_names']['config']['log']['flags']['violation'], $_SESSION['config_information']['trusted_log|flags|violation'], $_HOAM_setting ['log']['flags']['violation'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|violation_category', $_HOAM_language ['field_names']['config']['log']['flags']['violation_category'], $_SESSION['config_information']['trusted_log|flags|violation_category'], $_HOAM_setting ['log']['flags']['violation_category'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|violation_severity', $_HOAM_language ['field_names']['config']['log']['flags']['violation_severity'], $_SESSION['config_information']['trusted_log|flags|violation_severity'], $_HOAM_setting ['log']['flags']['violation_severity'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|log|flags|work_request', $_HOAM_language ['field_names']['config']['log']['flags']['work_request'], $_SESSION['config_information']['trusted_log|flags|work_request'], $_HOAM_setting ['log']['flags']['work_request'], 20, 1);
				$HTML .= '</fieldset>';

				$HTML .= '</div>';
				$HTML .= '</div>'; // End Log tab

				$HTML .= '<div class="tabbertab" id="admin_lot" title="' . $_HOAM_language ['form_tabs']['config']['lots'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formFieldEnable ('lot|minimum_address_length', $_HOAM_language ['field_names']['lot']['minimum_address_length'], $_SESSION['config_information']['trusted_lot|minimum_address_length'], $_HOAM_setting ['lot']['minimum_address_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('lot|minimum_building_length', $_HOAM_language ['field_names']['lot']['minimum_building_length'], $_SESSION['config_information']['trusted_lot|minimum_building_length'], $_HOAM_setting ['lot']['minimum_building_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('lot|minimum_street_length', $_HOAM_language ['field_names']['lot']['minimum_street_length'], $_SESSION['config_information']['trusted_lot|minimum_street_length'], $_HOAM_setting ['lot']['minimum_street_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('lot|minimum_suite_length', $_HOAM_language ['field_names']['lot']['minimum_suite_length'], $_SESSION['config_information']['trusted_lot|minimum_suite_length'], $_HOAM_setting ['lot']['minimum_suite_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('lot|minimum_block_length', $_HOAM_language ['field_names']['lot']['minimum_block_length'], $_SESSION['config_information']['trusted_lot|minimum_block_length'], $_HOAM_setting ['lot']['minimum_block_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('lot|minimum_lot_length', $_HOAM_language ['field_names']['lot']['minimum_lot_length'], $_SESSION['config_information']['trusted_lot|minimum_lot_length'], $_HOAM_setting ['lot']['minimum_lot_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('lot|minimum_plat_length', $_HOAM_language ['field_names']['lot']['minimum_plat_length'], $_SESSION['config_information']['trusted_lot|minimum_plat_length'], $_HOAM_setting ['lot']['minimum_plat_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('lot|minimum_latitude_length', $_HOAM_language ['field_names']['lot']['minimum_latitude_length'], $_SESSION['config_information']['trusted_lot|minimum_longitude_length'], $_HOAM_setting ['lot']['minimum_longitude_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('lot|minimum_longitude_length', $_HOAM_language ['field_names']['lot']['minimum_longitude_length'], $_SESSION['config_information']['trusted_lot|minimum_latitude_length'], $_HOAM_setting ['lot']['minimum_latitude_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('lot|minimum_sqft_size', $_HOAM_language ['field_names']['lot']['minimum_sqft_size'], $_SESSION['config_information']['trusted_lot|minimum_sqft_size'], $_HOAM_setting ['lot']['minimum_sqft_size'], 5, 1);

				$HTML .= $_HOAM_language ['help_pages']['config']['lot']['common_areas'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formConfigCheckbox ('lot|common|area', $_HOAM_language ['field_names']['lot']['common']['area'], 1, $_SESSION['config_information']['trusted_lot|common|area'], (int) $_HOAM_setting ['lot']['common']['area']);
				$HTML .= HOAM_formConfigCheckbox ('lot|common|clubhouse', $_HOAM_language ['field_names']['lot']['common']['clubhouse'], 1, $_SESSION['config_information']['trusted_lot|common|clubhouse'], (int) $_HOAM_setting ['lot']['common']['clubhouse']);
				$HTML .= HOAM_formConfigCheckbox ('lot|common|pool', $_HOAM_language ['field_names']['lot']['common']['pool'], 1, $_SESSION['config_information']['trusted_lot|common|pool'], (int) $_HOAM_setting ['lot']['common']['pool']);
				$HTML .= '</fieldset>';

				$HTML .= $_HOAM_language ['help_pages']['config']['lot']['property'];
				$HTML .= HOAM_formConfigCheckbox ('config|lot|property', $_HOAM_language ['field_names']['config']['lot']['property'], 1, $_SESSION['config_information']['trusted_lot|property'], (int) $_HOAM_setting ['lot']['property']);

				$HTML .= $_HOAM_language ['help_pages']['config']['lot']['flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('config|property|flags|renew', $_HOAM_language ['field_names']['config']['property']['flags']['renew'], $_SESSION['config_information']['trusted_property|flags|renew'], $_HOAM_setting ['property']['flags']['renew'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|property|flags|rented', $_HOAM_language ['field_names']['config']['property']['flags']['rented'], $_SESSION['config_information']['trusted_property|flags|rented'], $_HOAM_setting ['property']['flags']['rented'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|property|flags|annual', $_HOAM_language ['field_names']['config']['property']['flags']['annual'], $_SESSION['config_information']['trusted_property|flags|annual'], $_HOAM_setting ['property']['flags']['annual'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|property|flags|monthly', $_HOAM_language ['field_names']['config']['property']['flags']['monthly'], $_SESSION['config_information']['trusted_property|flags|monthly'], $_HOAM_setting ['property']['flags']['monthly'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|property|flags|weekly', $_HOAM_language ['field_names']['config']['property']['flags']['weekly'], $_SESSION['config_information']['trusted_property|flags|weekly'], $_HOAM_setting ['property']['flags']['weekly'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|property|flags|daily', $_HOAM_language ['field_names']['config']['property']['flags']['daily'], $_SESSION['config_information']['trusted_property|flags|daily'], $_HOAM_setting ['property']['flags']['daily'], 20, 1);
				$HTML .= '</fieldset>';
				
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Lots tab

				$HTML .= '<div class="tabbertab" id="admin_messageboard" title="' . $_HOAM_language ['form_tabs']['config']['messageboard'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
                $HTML .= $_HOAM_language ['help_pages']['config']['messageboard']['boards'];
                $messageboard_list = $messageboard_class -> ListRootId ('0');
                if (is_array ($messageboard_list)) {
                    foreach ($messageboard_list as $board) {
                        $HTML .= '<input readonly="readonly" type="text" id="messageboard_' . $board . '" value="' . $messageboard_class -> returnSubject ($board) . '" size="' . $messageboard_class -> returnColumnSize ('subject') . '" maxlength="' . $messageboard_class -> returnColumnSize ('subject') . '" />';
						$HTML .= '<button type="button" id="messageboard_' . $board . '_delete">' . $_HOAM_language ['form_buttons']['delete'] . '</button><br />';
                    }
                } else {
                    $HTML .= $_HOAM_language ['errors']['messageboard']['no-boards-found'];
                }
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="config|messageboard|add" value="' . $_HOAM_language ['form_buttons']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= $_HOAM_language ['help_pages']['config']['messageboard']['flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('config|messageboard|flags|display_top', $_HOAM_language ['field_names']['config']['messageboard']['flags']['display_top'], $_SESSION['config_information']['trusted_messageboard|flags|display_top'], $_HOAM_setting ['messageboard']['flags']['display_top'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|messageboard|flags|do_not_delete', $_HOAM_language ['field_names']['config']['messageboard']['flags']['do_not_delete'], $_SESSION['config_information']['trusted_messageboard|flags|do_not_delete'], $_HOAM_setting ['messageboard']['flags']['do_not_delete'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|messageboard|flags|no_more_comments', $_HOAM_language ['field_names']['config']['messageboard']['flags']['no_more_comments'], $_SESSION['config_information']['trusted_messageboard|flags|do_not_delete'], $_HOAM_setting ['messageboard']['flags']['no_more_comments'], 20, 1);
				$HTML .= '</fieldset>';
								
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Messageboard tab
				$HTML .= '<div class="tabbertab" id="admin_news" title="' . $_HOAM_language ['form_tabs']['config']['news'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formFieldEnable ('news|minimum_article_length', $_HOAM_language ['field_names']['news']['minimum_article_length'], $_SESSION['config_information']['trusted_news|minimum_article_length'], $_HOAM_setting ['news']['minimum_article_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('news|minimum_title_length', $_HOAM_language ['field_names']['news']['minimum_title_length'], $_SESSION['config_information']['trusted_news|minimum_title_length'], $_HOAM_setting ['news']['minimum_title_length'], 5, 1);
				$HTML .= '</div>';
				$HTML .= '</div>'; // End News tab
				
				$HTML .= '<div class="tabbertab" id="admin_organization" title="' . $_HOAM_language ['form_tabs']['config']['organization'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formTextEnable ('organization|name', $_HOAM_language ['field_names']['organization']['name'], $_SESSION['config_information']['trusted_organization|name'], $_HOAM_setting ['organization']['name']);
				$HTML .= HOAM_formTextEnable ('organization|property_name', $_HOAM_language ['field_names']['organization']['property_name'], $_SESSION['config_information']['trusted_organization|property_name'], $_HOAM_setting ['organization']['property_name']);
				$HTML .= HOAM_formTextEnable ('organization|mailing_address|line1', $_HOAM_language ['field_names']['organization']['mailing_address']['line1'], $_SESSION['config_information']['trusted_organization|mailing_address|line1'], $_HOAM_setting ['organization']['mailing_address']['line1']);
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formTextEnable ('organization|mailing_address|line2', $_HOAM_language ['field_names']['organization']['mailing_address']['line2'], $_SESSION['config_information']['trusted_organization|mailing_address|line2'], $_HOAM_setting ['organization']['mailing_address']['line2']);
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formTextEnable ('organization|mailing_address|line3', $_HOAM_language ['field_names']['organization']['mailing_address']['line3'], $_SESSION['config_information']['trusted_organization|mailing_address|line3'], $_HOAM_setting ['organization']['mailing_address']['line3']);
				$HTML .= HOAM_formTextEnable ('organization|mailing_address|city', $_HOAM_language ['field_names']['organization']['mailing_address']['city'], $_SESSION['config_information']['trusted_organization|mailing_address|city'], $_HOAM_setting ['organization']['mailing_address']['city']);
				$HTML .= '<label for="organization|mailing_address|state">' . $_HOAM_language ['field_names']['organization']['mailing_address']['state'];
				$HTML .= '</label>';
				$HTML .= '<select disabled="disabled" name="organization|mailing_address|state" id="organization|mailing_address|state" field="enable">';
				if (isset ($_SESSION['homeowner_information']['trusted_organization|state'])) {
					$default_state = $_SESSION['homeowner_information']['trusted_organization|state'];
				} else {
					$default_state = $_HOAM_setting ['organization']['mailing_address']['state'];
				}
				foreach ($_HOAM_country ['state'] as $state) {
					$HTML .= '<option ';
					if ($state['value'] == $default_state) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $state['value'] . '">' . $state['description'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formFieldEnable ('organization|mailing_address|postalcode', $_HOAM_language ['field_names']['organization']['mailing_address']['postalcode'], $_SESSION['config_information']['trusted_organization|postalcode'], $_HOAM_setting ['organization']['mailing_address']['postalcode'], $_HOAM_country ['postalcode']['length'], $_HOAM_country ['postalcode']['minlength']);
				
				$HTML .= HOAM_formFieldEnable ('organization|phone|telephone', $_HOAM_language ['field_names']['organization']['phone']['telephone'], $_SESSION['config_information']['trusted_organization|phone|telephone'], $_HOAM_setting ['organization']['telephone'], $_HOAM_country ['telephone']['length'], $_HOAM_country ['telephone']['length']);
				$HTML .= HOAM_formFieldEnable ('organization|phone|fax', $_HOAM_language ['field_names']['organization']['phone']['fax'], $_SESSION['config_information']['trusted_organization|phone|fax'], $_HOAM_setting ['organization']['fax'], $_HOAM_country ['telephone']['length'], $_HOAM_country ['telephone']['length']);
								
				$HTML .= HOAM_formTextEnable ('organization|physical_address|line1', $_HOAM_language ['field_names']['organization']['physical_address']['line1'], $_SESSION['config_information']['trusted_organization|physical_address|line1'], $_HOAM_setting ['organization']['physical_address']['line1']);
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formTextEnable ('organization|physical_address|line2', $_HOAM_language ['field_names']['organization']['physical_address']['line2'], $_SESSION['config_information']['trusted_organization|physical_address|line2'], $_HOAM_setting ['organization']['physical_address']['line2']);
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formTextEnable ('organization|physical_address|line3', $_HOAM_language ['field_names']['organization']['physical_address']['line3'], $_SESSION['config_information']['trusted_organization|physical_address|line3'], $_HOAM_setting ['organization']['physical_address']['line3']);
				$HTML .= HOAM_formTextEnable ('organization|physical_address|city', $_HOAM_language ['field_names']['organization']['physical_address']['city'], $_SESSION['config_information']['trusted_organization|physical_address|city'], $_HOAM_setting ['organization']['physical_address']['city']);
				$HTML .= '<label for="organization|physical_address|state">' . $_HOAM_language ['field_names']['organization']['physical_address']['state'];
				$HTML .= '</label>';
				$HTML .= '<select disabled="disabled" name="organization|physical_address|state" id="organization|physical_address|state" field="enable">';
				if (isset ($_SESSION['homeowner_information']['trusted_organization|state'])) {
					$default_state = $_SESSION['homeowner_information']['trusted_organization|state'];
				} else {
					$default_state = $_HOAM_setting ['organization']['physical_address']['state'];
				}
				foreach ($_HOAM_country ['state'] as $state) {
					$HTML .= '<option ';
					if ($state['value'] == $default_state) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $state['value'] . '">' . $state['description'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formFieldEnable ('organization|physical_address|postalcode', $_HOAM_language ['field_names']['organization']['physical_address']['postalcode'], $_SESSION['config_information']['trusted_organization|postalcode'], $_HOAM_setting ['organization']['physical_address']['postalcode'], $_HOAM_country ['postalcode']['length'], $_HOAM_country ['postalcode']['minlength']);
				
				$HTML .= HOAM_formTextEnable ('organization|physical_address|county', $_HOAM_language ['field_names']['organization']['physical_address']['county'], $_SESSION['config_information']['trusted_organization|physical_address|county'], $_HOAM_setting ['organization']['physical_address']['county']);
				$HTML .= HOAM_formFieldEnable ('organization|fee|expedite', $_HOAM_language ['field_names']['organization']['fee']['expedite'], $_SESSION['config_information']['trusted_organization|fee|expedite'], $_HOAM_setting ['organization']['fee']['expedite'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('organization|fee|refinance', $_HOAM_language ['field_names']['organization']['fee']['refinance'], $_SESSION['config_information']['trusted_organization|fee|refinance'], $_HOAM_setting ['organization']['fee']['refinance'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('organization|fee|resale', $_HOAM_language ['field_names']['organization']['fee']['resale'], $_SESSION['config_information']['trusted_organization|fee|resale'], $_HOAM_setting ['organization']['fee']['resale'], 5, 1);
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Organization tab

				$HTML .= '<div class="tabbertab" id="admin_section" title="' . $_HOAM_language ['form_tabs']['config']['sections'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Sections tab

				$HTML .= '<div class="tabbertab" id="admin_user" title="' . $_HOAM_language ['form_tabs']['config']['users'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formUser ('config|user|ids|root', $_HOAM_language ['field_names']['config']['user']['ids']['root'], $_SESSION['config_information']['trusted_user|ids|root'], $_HOAM_setting ['user']['ids']['root']);
				$HTML .= HOAM_formUser ('config|user|ids|system', $_HOAM_language ['field_names']['config']['user']['ids']['system'], $_SESSION['config_information']['trusted_user|ids|system'], $_HOAM_setting ['user']['ids']['system']);
				$HTML .= $_HOAM_language ['help_pages']['config']['user']['security'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formConfigCheckbox ('config|user|email_validation', $_HOAM_language ['field_names']['config']['user']['email_validation'], 1, $_SESSION['config_information']['trusted_user|email_validation'], $_HOAM_setting ['user']['email_validation']);
				$HTML .= HOAM_formFieldEnable ('config|user|days_before_disable', $_HOAM_language ['field_names']['config']['user']['days_before_disable'], $_SESSION['config_information']['trusted_user|days_before_disable'], $_HOAM_setting ['user']['days_before_disable'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('config|user|days_before_delete', $_HOAM_language ['field_names']['config']['user']['days_before_delete'], $_SESSION['config_information']['trusted_user|days_before_delete'], $_HOAM_setting ['user']['days_before_delete'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('config|user|days_password_age', $_HOAM_language ['field_names']['config']['user']['days_password_age'], $_SESSION['config_information']['trusted_user|days_password_age'], $_HOAM_setting ['user']['days_password_age'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('config|user|days_password_warn', $_HOAM_language ['field_names']['config']['user']['days_password_warn'], $_SESSION['config_information']['trusted_user|days_password_warn'], $_HOAM_setting ['user']['days_password_warn'], 5, 1);
				$HTML .= '</fieldset>';

				$HTML .= $_HOAM_language ['help_pages']['config']['user']['flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('config|user|flags|acronyms', $_HOAM_language ['field_names']['config']['user']['flags']['acronyms'], $_SESSION['config_information']['trusted_user|flags|acronyms'], $_HOAM_setting ['user']['flags']['acronyms'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|user|flags|censor', $_HOAM_language ['field_names']['config']['user']['flags']['censor'], $_SESSION['config_information']['trusted_user|flags|censor'], $_HOAM_setting ['user']['flags']['censor'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|user|flags|convert_links', $_HOAM_language ['field_names']['config']['user']['flags']['convert_links'], $_SESSION['config_information']['trusted_user|flags|convert_links'], $_HOAM_setting ['user']['flags']['convert_links'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|user|flags|email_validated', $_HOAM_language ['field_names']['config']['user']['flags']['email_validated'], $_SESSION['config_information']['trusted_user|flags|email_validated'], $_HOAM_setting ['user']['flags']['email_validated'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|user|flags|disabled', $_HOAM_language ['field_names']['config']['user']['flags']['disabled'], $_SESSION['config_information']['trusted_user|flags|disabled'], $_HOAM_setting ['user']['flags']['disabled'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|user|flags|holidays', $_HOAM_language ['field_names']['config']['user']['flags']['holidays'], $_SESSION['config_information']['trusted_user|flags|holidays'], $_HOAM_setting ['user']['flags']['holidays'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|user|flags|password_old', $_HOAM_language ['field_names']['config']['user']['flags']['password_old'], $_SESSION['config_information']['trusted_user|flags|password_old'], $_HOAM_setting ['user']['flags']['password_old'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|user|flags|smileys', $_HOAM_language ['field_names']['config']['user']['flags']['smileys'], $_SESSION['config_information']['trusted_user|flags|smileys'], $_HOAM_setting ['user']['flags']['smileys'], 20, 1);
				$HTML .= '</fieldset>';
				
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Users tab

				$HTML .= '<div class="tabbertab" id="admin_violation" title="' . $_HOAM_language ['form_tabs']['config']['violations'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formFieldEnable ('violation|days_to_pad_resolveby', $_HOAM_language ['field_names']['violation']['days_to_pad_resolveby'], $_SESSION['config_information']['trusted_violation|days_to_pad_resolveby'], $_HOAM_setting ['violation']['days_to_pad_resolveby'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('violation|days_to_reset', $_HOAM_language ['field_names']['violation']['days_to_reset'], $_SESSION['config_information']['trusted_violation|days_to_reset'], $_HOAM_setting ['violation']['days_to_reset'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('config|violation|grace_after_purchase', $_HOAM_language ['field_names']['config']['violation']['grace_after_purchase'], $_SESSION['config_information']['trusted_violation|grace_after_purchase'], $_HOAM_setting ['violation']['grace_after_purchase'], 5, 1);
				$HTML .= HOAM_formConfigCheckbox ('config|violation|require_approval', $_HOAM_language ['field_names']['config']['violation']['require_approval'], 1, $_SESSION['config_information']['trusted_violation|require_approval'], (int) $_HOAM_setting ['violation']['require_approval']);
				$HTML .= '<fieldset id="violation|approval">';
				$HTML .= HOAM_formSelectGroupEnable ('config|violation|approval_group', $_HOAM_language ['field_names']['config']['violation']['approval_group'], $_SESSION['config_information']['trusted_violation|approval_group'], $_HOAM_setting ['violation']['approval_group']);
				$HTML .= '</fieldset>';

				$HTML .= $_HOAM_language ['help_pages']['config']['violation']['category'];
				$HTML .= HOAM_formFieldEnable ('violation|category|minimum_category_length', $_HOAM_language ['field_names']['violation']['category']['minimum_category_length'], $_SESSION['config_information']['trusted_violation|category|minimum_category_length'], $_HOAM_setting ['violation']['category']['minimum_category_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('violation|category|minimum_description_length', $_HOAM_language ['field_names']['violation']['category']['minimum_description_length'], $_SESSION['config_information']['trusted_violation|category|minimum_category_length'], $_HOAM_setting ['violation']['category']['minimum_description_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('violation|category|minimum_detail_length', $_HOAM_language ['field_names']['violation']['category']['minimum_detail_length'], $_SESSION['config_information']['trusted_violation|category|minimum_category_length'], $_HOAM_setting ['violation']['category']['minimum_detail_length'], 5, 1);
				
				$HTML .= $_HOAM_language ['help_pages']['config']['violation']['severity'];
				$HTML .= '<label for="violation|default_severity">' . $_HOAM_language ['field_names']['violation']['default_severity'];
				$HTML .= '</label>';
				$severity_list = $violation_severity_class -> ListSeverity ();
				$HTML .= '<select disabled="disabled" name="violation|default_severity" id="violation|default_severity" field="enable">';
				if (isset ($_SESSION['config_information']['trusted_violation|default_severity'])) {
					$default_severity = $_SESSION['config_information']['trusted_violation|default_severity'];
				} else {
					$default_severity = NULL;
				}
				foreach ($severity_list as $severity) {
					$HTML .= '<option ';
					if ($severity == $default_severity) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formFieldEnable ('violation|severity|minimum_closing_length', $_HOAM_language ['field_names']['violation']['severity']['minimum_closing_length'], $_SESSION['config_information']['trusted_violation|severity|minimum_closing_length'], $_HOAM_setting ['violation']['severity']['minimum_closing_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('violation|severity|minimum_days_resolution', $_HOAM_language ['field_names']['violation']['severity']['minimum_days_resolution'], $_SESSION['config_information']['trusted_violation|severity|minimum_days_resolution'], $_HOAM_setting ['violation']['severity']['minimum_days_resolution'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('violation|severity|minimum_name_length', $_HOAM_language ['field_names']['violation']['severity']['minimum_name_length'], $_SESSION['config_information']['trusted_violation|severity|minimum_name_length'], $_HOAM_setting ['violation']['severity']['minimum_name_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('violation|severity|minimum_preamble_length', $_HOAM_language ['field_names']['violation']['severity']['minimum_preamble_length'], $_SESSION['config_information']['trusted_violation|severity|minimum_preamble_length'], $_HOAM_setting ['violation']['severity']['minimum_preamble_length'], 5, 1);

				$HTML .= '<fieldset id="violation|approval|flags">';
				$HTML .= $_HOAM_language ['help_pages']['config']['violation']['flags_approval'];
				$HTML .= HOAM_formFieldEnable ('config|violation|flags|needs_approval', $_HOAM_language ['field_names']['violation']['flags']['needs_approval'], $_SESSION['config_information']['trusted_violation|flags|needs_approval'], $_HOAM_setting ['violation']['flags']['needs_approval'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|violation|flags|more_information', $_HOAM_language ['field_names']['violation']['flags']['more_information'], $_SESSION['config_information']['trusted_violation|flags|more_information'], $_HOAM_setting ['violation']['flags']['more_information'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|violation|flags|approved', $_HOAM_language ['field_names']['violation']['flags']['approved'], $_SESSION['config_information']['trusted_violation|flags|approved'], $_HOAM_setting ['violation']['flags']['approved'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('config|violation|flags|not_approved', $_HOAM_language ['field_names']['violation']['flags']['not_approved'], $_SESSION['config_information']['trusted_violation|flags|not_approved'], $_HOAM_setting ['violation']['flags']['not_approved'], 20, 1);
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_pages']['config']['violation']['flags'];
				$HTML .= HOAM_formFieldEnable ('config|violation|flags|assume_resolved', $_HOAM_language ['field_names']['config']['violation']['flags']['assume_resolved'], $_SESSION['config_information']['trusted_violation|flags|assume_resolved'], $_HOAM_setting ['violation']['flags']['assume_resolved'], 20, 1);
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Violations tab

				$HTML .= '<div class="tabbertab" id="admin_website" title="' . $_HOAM_language ['form_tabs']['config']['website'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formTextareaEnable ('config|website|blurb', $_HOAM_language ['field_names']['config']['website']['blurb'], $_SESSION['config_information']['trusted_website|blurb'], $_HOAM_setting ['website']['blurb'], $_SESSION['current_user']['edit']['row'], 4096, 0);
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formTextEnable ('website|hostname', $_HOAM_language ['field_names']['website']['hostname'], $_SESSION['config_information']['trusted_website|hostname'], $_HOAM_setting ['website']['hostname'], 64, 1);
				$HTML .= HOAM_formTextEnable ('website|title', $_HOAM_language ['field_names']['website']['title'], $_SESSION['config_information']['trusted_website|title'], $_HOAM_setting ['website']['title'], 64, 1);
				$HTML .= HOAM_formTextEnable ('website|url', $_HOAM_language ['field_names']['website']['url'], $_SESSION['config_information']['trusted_website|url'], $_HOAM_setting ['website']['url'], 256, 1);
				$HTML .= HOAM_formFieldEnable ('website|locale', $_HOAM_language ['field_names']['website']['locale'], $_SESSION['config_information']['trusted_website|locale'], $_HOAM_setting ['website']['locale'], 32, 1);
				$HTML .= HOAM_timezoneEnable ('website|timezone', $_HOAM_language ['field_names']['website']['timezone'], $_SESSION['config_information']['trusted_website|timezone'], $_HOAM_setting ['website']['timezone']);
				$HTML .= HOAM_formTextEnable ('website|email|abuse', $_HOAM_language ['field_names']['website']['email']['abuse'], $_SESSION['config_information']['trusted_website|email|abuse'], $_HOAM_setting ['website']['email']['abuse'], 256, 1);
				$HTML .= HOAM_formTextEnable ('website|email|root', $_HOAM_language ['field_names']['website']['email']['root'], $_SESSION['config_information']['trusted_website|email|root'], $_HOAM_setting ['website']['email']['root'], 256, 1);
				$HTML .= HOAM_formTextEnable ('website|email|webmaster', $_HOAM_language ['field_names']['website']['email']['webmaster'], $_SESSION['config_information']['trusted_website|email|webmaster'], $_HOAM_setting ['website']['email']['webmaster'], 256, 1);
				$HTML .= HOAM_formFieldEnable ('website|mask_character', $_HOAM_language ['field_names']['website']['mask_character'], $_SESSION['config_information']['trusted_website|mask_character'], $_HOAM_setting ['website']['mask_character'], 1, 1);
				$HTML .= HOAM_formFieldEnable ('config|website|online_time', $_HOAM_language ['field_names']['config']['website']['online_time'], $_SESSION['config_information']['trusted_website|online_time'], $_HOAM_setting ['website']['online_time'], 3, 1);
				$HTML .= HOAM_formFieldEnable ('config|website|idle_time', $_HOAM_language ['field_names']['config']['website']['idle_time'], $_SESSION['config_information']['trusted_website|idle_time'], $_HOAM_setting ['website']['idle_time'], 3, 1);
				$HTML .= HOAM_formConfigCheckbox ('config|website|record', $_HOAM_language ['field_names']['config']['website']['record'], 1, $_SESSION['config_information']['trusted_website|record'], $_HOAM_setting ['website']['record']);
				
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Website tab

				$HTML .= '<div class="tabbertab" id="admin_wiki" title="' . $_HOAM_language ['form_tabs']['config']['wiki'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formFieldEnable ('article|minimum_article_length', $_HOAM_language ['field_names']['article']['minimum_article_length'], $_SESSION['config_information']['trusted_article|minimum_article_length'], $_HOAM_setting ['article']['minimum_article_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('article|minimum_keywords_length', $_HOAM_language ['field_names']['article']['minimum_keywords_length'], $_SESSION['config_information']['trusted_article|minimum_keywords_length'], $_HOAM_setting ['article']['minimum_keywords_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('article|minimum_leadin_length', $_HOAM_language ['field_names']['article']['minimum_leadin_length'], $_SESSION['config_information']['trusted_article|minimum_leadin_length'], $_HOAM_setting ['article']['minimum_leadin_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('article|minimum_summary_length', $_HOAM_language ['field_names']['article']['minimum_summary_length'], $_SESSION['config_information']['trusted_article|minimum_summary_length'], $_HOAM_setting ['article']['minimum_summary_length'], 5, 1);

				$HTML .= $_HOAM_language ['help_pages']['config']['wiki']['default_groups'];
				$HTML .= '<table style="background: transparent">';
				$HTML .= '<tbody class="nostripe">';
				$HTML .= '<tr><td>';
				$HTML .= '<label for="config|wiki|default_groups">' . $_HOAM_language ['field_names']['article']['view_groups'];
				$HTML .= HOAM_formDisplayError ('config|wiki|default_groups');
				$HTML .= '</label>';
				$HTML .= '<select multiple="multiple" name="config|wiki|default_groups[]" id="config|wiki|default_groups" size="' . $_SESSION['current_user']['items_per_page'] . '">';
				if (isset ($_SESSION['config_information']['trusted_wiki|default_groups'])) {
					$default_groups = $_SESSION['config_information']['trusted_wiki|default_groups'];
				} else {
					$default_groups = unserialize ($_HOAM_setting ['wiki']['default_groups']);
				}
				if (!is_array ($default_groups)) {
					$default_groups = array ($_HOAM_setting ['group']['ids']['admin']);
				}
				foreach ($default_groups as $group) {
					$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</td><td>';
				$HTML .= '<button type="button" id="config|wiki|group|remove">&lt;&lt;</button><br />';
				$HTML .= '<button type="button" id="config|wiki|group|add">&gt;&gt;</button>';
				$HTML .= '</td><td>';
				$HTML .= '<label for="config|wiki|all_groups">' . $_HOAM_language ['field_names']['article']['all_groups'];
				$HTML .= HOAM_formDisplayError ('config|wiki|all_groups');
				$HTML .= '</label>';
				$HTML .= '<select multiple="multiple" name="config|wiki|all_groups" id="config|wiki|all_groups" size="' . $_SESSION['current_user']['items_per_page'] . '">';
				$group_list = $group_class -> listAllId ("name", "ASC");
				foreach ($group_list as $group) {
					if (!in_array ($group, $default_groups)) {
						// Only show groups that the current user has access to
						// If the user is root or a system adminstrator, skip
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION ['current_user']['id'])) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						} elseif ($group_members_class -> isMember ($group, $_SESSION['current_user']['id'])) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						}
					}
				}
				$HTML .= '</select>';
				$HTML .= '</td></tr>';
				$HTML .= '</tbody>';
				$HTML .= '</table>';

				$HTML .= HOAM_formFieldEnable ('article|root_id', $_HOAM_language ['field_names']['article']['root_id'], $_SESSION['config_information']['trusted_article|root_id'], $_HOAM_setting ['article']['ids']['root'], 32, 1);
				
				$HTML .= $_HOAM_language ['help_pages']['config']['wiki']['flags'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('article|flags|comments', $_HOAM_language ['field_names']['article']['flags']['comments'], $_SESSION['config_information']['trusted_article|flags|comments'], $_HOAM_setting ['article']['flags']['comments'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('article|flags|draft', $_HOAM_language ['field_names']['article']['flags']['draft'], $_SESSION['config_information']['trusted_article|flags|draft'], $_HOAM_setting ['article']['flags']['draft'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('article|flags|redirect', $_HOAM_language ['field_names']['article']['flags']['redirect'], $_SESSION['config_information']['trusted_article|flags|redirect'], $_HOAM_setting ['article']['flags']['redirect'], 20, 1);
				$HTML .= '</fieldset>';
				
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Wiki tab

				$HTML .= '<div class="tabbertab" id="admin_work_request" title="' . $_HOAM_language ['form_tabs']['config']['work_request'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= $_HOAM_language ['help_pages']['config']['global'];
				$HTML .= HOAM_formConfigCheckbox ('work_request|allow_user_requests', $_HOAM_language ['field_names']['work_request']['user_requests'], 1, $_SESSION['config_information']['trusted_work_request|allow_user_requests'], $_HOAM_setting ['work_request']['allow_user_requests']);
				$HTML .= HOAM_formConfigCheckbox ('config|work_request|require_approval', $_HOAM_language ['field_names']['config']['work_request']['require_approval'], 1, $_SESSION['config_information']['trusted_work_request|require_approval'], (int) $_HOAM_setting ['work_request']['require_approval']);
				$HTML .= '<fieldset id="work_request|approval">';
				$HTML .= HOAM_formSelectGroupEnable ('config|work_request|approval_group', $_HOAM_language ['field_names']['config']['work_request']['approval_group'], $_SESSION['config_information']['trusted_work_request|approval_group'], $_HOAM_setting ['work_request']['approval_group']);
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formFieldEnable ('work_request|minimum_title_length', $_HOAM_language ['field_names']['work_request']['minimum_title_length'], $_SESSION['config_information']['trusted_work_request|minimum_title_length'], $_HOAM_setting ['work_request']['minimum_title_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|minimum_description_length', $_HOAM_language ['field_names']['work_request']['minimum_description_length'], $_SESSION['config_information']['trusted_work_request|minimum_description_length'], $_HOAM_setting ['work_request']['minimum_description_length'], 5, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|minimum_notes_length', $_HOAM_language ['field_names']['work_request']['minimum_notes_length'], $_SESSION['config_information']['trusted_work_request|minimum_notes_length'], $_HOAM_setting ['work_request']['minimum_notes_length'], 5, 1);

				$HTML .= $_HOAM_language ['help_pages']['config']['work_request']['flags_priority'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('work_request|flags|low', $_HOAM_language ['field_names']['work_request']['flags']['low'], $_SESSION['config_information']['trusted_work_request|flags|low'], $_HOAM_setting ['work_request']['flags']['low'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|normal', $_HOAM_language ['field_names']['work_request']['flags']['normal'], $_SESSION['config_information']['trusted_work_request|flags|normal'], $_HOAM_setting ['work_request']['flags']['normal'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|high', $_HOAM_language ['field_names']['work_request']['flags']['high'], $_SESSION['config_information']['trusted_work_request|flags|high'], $_HOAM_setting ['work_request']['flags']['high'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|urgent', $_HOAM_language ['field_names']['work_request']['flags']['urgent'], $_SESSION['config_information']['trusted_work_request|flags|urgent'], $_HOAM_setting ['work_request']['flags']['urgent'], 20, 1);
				$HTML .= '</fieldset>';
				$HTML .= '<fieldset id="work_request|approval|flags">';
				$HTML .= $_HOAM_language ['help_pages']['config']['work_request']['flags_approval'];
				$HTML .= HOAM_formFieldEnable ('work_request|flags|needs_approval', $_HOAM_language ['field_names']['work_request']['flags']['needs_approval'], $_SESSION['config_information']['trusted_work_request|flags|needs_approval'], $_HOAM_setting ['work_request']['flags']['needs_approval'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|more_information', $_HOAM_language ['field_names']['work_request']['flags']['more_information'], $_SESSION['config_information']['trusted_work_request|flags|more_information'], $_HOAM_setting ['work_request']['flags']['more_information'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|approved', $_HOAM_language ['field_names']['work_request']['flags']['approved'], $_SESSION['config_information']['trusted_work_request|flags|approved'], $_HOAM_setting ['work_request']['flags']['approved'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|not_approved', $_HOAM_language ['field_names']['work_request']['flags']['not_approved'], $_SESSION['config_information']['trusted_work_request|flags|not_approved'], $_HOAM_setting ['work_request']['flags']['not_approved'], 20, 1);
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_pages']['config']['work_request']['flags_status'];
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('work_request|flags|new', $_HOAM_language ['field_names']['work_request']['flags']['new'], $_SESSION['config_information']['trusted_work_request|flags|new'], $_HOAM_setting ['work_request']['flags']['new'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|assigned', $_HOAM_language ['field_names']['work_request']['flags']['assigned'], $_SESSION['config_information']['trusted_work_request|flags|assigned'], $_HOAM_setting ['work_request']['flags']['assigned'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|planning', $_HOAM_language ['field_names']['work_request']['flags']['planning'], $_SESSION['config_information']['trusted_work_request|flags|planning'], $_HOAM_setting ['work_request']['flags']['planning'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|in_progress', $_HOAM_language ['field_names']['work_request']['flags']['in_progress'], $_SESSION['config_information']['trusted_work_request|flags|in_progress'], $_HOAM_setting ['work_request']['flags']['in_progress'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|on_hold', $_HOAM_language ['field_names']['work_request']['flags']['on_hold'], $_SESSION['config_information']['trusted_work_request|flags|on_hold'], $_HOAM_setting ['work_request']['flags']['on_hold'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|cancelled', $_HOAM_language ['field_names']['work_request']['flags']['cancelled'], $_SESSION['config_information']['trusted_work_request|flags|cancelled'], $_HOAM_setting ['work_request']['flags']['cancelled'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|rejected', $_HOAM_language ['field_names']['work_request']['flags']['rejected'], $_SESSION['config_information']['trusted_work_request|flags|rejected'], $_HOAM_setting ['work_request']['flags']['rejected'], 20, 1);
				$HTML .= HOAM_formFieldEnable ('work_request|flags|complete', $_HOAM_language ['field_names']['work_request']['flags']['complete'], $_SESSION['config_information']['trusted_work_request|flags|complete'], $_HOAM_setting ['work_request']['flags']['complete'], 20, 1);
				$HTML .= '</fieldset>';

				$HTML .= '</div>';
				$HTML .= '</div>'; // End Wiki tab

				$HTML .= '</div>'; // End tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'upgrade' :
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['config']['upgrade'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminUpgradeJS');
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['config'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/server_uncompressed.png" alt="' . $_HOAM_language ['field_names']['config']['upgrade'] . '" class="LHS" /><a href="/website/config/">' . $_HOAM_language ['field_names']['config']['upgrade'] . '</a>';
				$HTML .= '<form id="attachment_add" action="/hoam/scripts/attachment/edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<br />';
				$HTML .= '<fieldset id="upgrade|cancel|fieldset" style="display:none">';
				$HTML .= '<button id="upgrade|cancel">Cancel Upgrade</button>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '</fieldset>';
				if (version_compare (HOAM_VERSION, $_HOAM_setting ['hoam']['latest'], '<')) {
					$HTML .= '<fieldset id="upgrade|download|fieldset">';
					$HTML .= 'An upgrade is available for your system. Would you like to download it and install?<br />';
					$HTML .= '<button id="upgrade|download|yes">' . ucwords ($_HOAM_language ['common']['yes']) . '</button>';
					$HTML .= '<button id="upgrade|download|no">' . ucwords ($_HOAM_language ['common']['no']) . '</button>';
					$HTML .= '</fieldset>';
				} else {
					$HTML .= '<div id="upgrade|custom_package" class="optional">';
					$HTML .= '<p>An upgrade has not been released for the version of HOAM currently running. However, you have the option of providing a custom URL if one has been provided to you by the support team.</p>';
					$HTML .= HOAM_formField ('upgrade|custom_url', 'Custom Package URL', '', '', 200);
					$HTML .= '<br /><button id="upgrade|custom_download">Download</button>';
					$HTML .= '</div>';
				}
				$HTML .= '<div id="upgrade|tabber" class="tabber"style="display:none">';

				$HTML .= '<div class="tabbertab" id="upgrade|notice" title="' . $_HOAM_language ['form_tabs']['upgrade']['notice'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End notice tab

				$HTML .= '<div class="tabbertab" id="upgrade|changes" title="' . $_HOAM_language ['form_tabs']['upgrade']['changes'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End change tab

				$HTML .= '<div class="tabbertab" id="upgrade|details" title="' . $_HOAM_language ['form_tabs']['upgrade']['details'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End details tab

				$HTML .= '<div class="tabbertab" id="upgrade|export" title="' . $_HOAM_language ['form_tabs']['upgrade']['export'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= $_HOAM_language ['help_pages']['config']['export'];
				$HTML .= '<fieldset id="upgrade|export|fieldset">';
				$HTML .= HOAM_formCheckbox ('upgrade|export_attachments', $_HOAM_language ['field_names']['upgrade']['export_attachments'], 1, 1, 1);
				$HTML .= '<br /><br /><button id="upgrade|export_button">' . $_HOAM_language ['form_buttons']['config']['export_button'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End export tab

				$HTML .= '<div class="tabbertab" id="upgrade|perform" title="' . $_HOAM_language ['form_tabs']['upgrade']['perform'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '<fieldset>';
				$HTML .= '<button id="upgrade|start">' . $_HOAM_language ['form_buttons']['upgrade']['start'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= '<ul id="upgrade|status">';
				$HTML .= '</ul>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End perform tab

				$HTML .= '<div class="tabbertab" id="upgrade|log" title="' . $_HOAM_language ['form_tabs']['upgrade']['log'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End log tab
				
				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['config'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['config'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/server.png" alt="' . $_HOAM_language ['field_names']['admin']['config'] . '" class="LHS" /><a href="/website/config/">' . $_HOAM_language ['field_names']['admin']['config'] . '</a>';
					$HTML .= '<ul>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/server_edit.png\')"><a href="/website/config/modify/">Modify System Configuration</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/server_compressed.png\')"><a href="/website/config/export/">' . $_HOAM_language ['field_names']['config']['export'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/server_uncompressed.png\')">Import System Backup</li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/server_uncompressed.png\')"><a href="/website/config/upgrade/">Upgrade HOAM</a></li>';
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminDiag ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$group_members_class = new HOAM_group_members ('group_members');
	if (isset ($GLOBALS['global_url_array'][2])) {
		switch ($GLOBALS['global_url_array'][2]) {
			case 'email' :
				$_HOAM_log -> add ('Loading /website/diag/email/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['diag']['email'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminDiagJS');
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
	
					$HTML = '<h1>' . $_HOAM_language ['field_names']['diag']['email'] . '</h1>';
					$HTML .= '<form method="post">';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
			
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formField ('address', $_HOAM_language ['field_names']['diag']['address'], '', '', 30, 1);
					$HTML .= '&nbsp;<button type="button" id="send">Send</button>';
					$HTML .= '</div>';
					$HTML .= '</form>';
					$HTML .= '<div id="status"></div>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'hoaminfo' :
				$_HOAM_log -> add ('Loading /website/diag/hoaminfo/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['diag']['hoaminfo'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['diag']['hoaminfo'] . '</h1>';
					$HTML .= HOAM_info ();
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'maintenance' :
				$_HOAM_log -> add ('Loading /website/diag/maintenance/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					// Manually run the nightly maintenance script.
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['diag']['maintenance'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['diag']['maintenance'] . '</h1>';
					require_once (HOAM_PATH_SCRIPT . 'diag/maintenance.php');
					$HTML .= $maintenance_report;
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'md5' :
				$_HOAM_log -> add ('Loading /website/diag/md5/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['diag']['md5'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['diag']['md5'] . '</h1>';
					for ($i = 0; $i < 10; $i++) {
						$HTML .= '<tt>' . md5 (mt_rand()) . '</tt><br />';
					}
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'monthly' :
				$_HOAM_log -> add ('Loading /website/diag/monthly/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					// Manually create the monthly status report.
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['diag']['monthly'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['diag']['monthly'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'report.php');
					$HTML .= HOAM_reportMonthly ();
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'phpinfo' :
				$_HOAM_log -> add ('Loading /website/diag/phpinfo/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['diag']['phpinfo'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['diag']['phpinfo'] . '</h1>';
					ob_start();
					phpinfo ();
					$HTML .= ob_get_contents();
					ob_end_clean();
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'toggle' :
				$_HOAM_log -> add ('Loading /website/diag/toggle/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['diag']['toggle'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminDiagJS');
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
	
					$HTML = '<h1>' . $_HOAM_language ['field_names']['diag']['toggle'] . '</h1>';
					$HTML .= '<form method="post">';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="mandatory">';
					$HTML .= $_HOAM_language ['field_names']['diag']['mode'];
					if ($_HOAM_setting['hoam']['updating'] == 1) {
						$HTML .= '<input type="hidden" id="maintenance" value="0" />';
						$HTML .= '<button type="button" id="toggle">' . ucfirst ($_HOAM_language ['common']['enabled']) . '</button>';
					} else {
						$HTML .= '<input type="hidden" id="maintenance" value="1" />';
						$HTML .= '<button type="button" id="toggle">' . ucfirst ($_HOAM_language ['common']['disabled']) . '</button>';
					}
					$HTML .= '</div>';
					$HTML .= '</form>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			default:
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['diag'])));
			$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['diag'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/cog.png" alt="' . $_HOAM_language ['field_names']['admin']['diag'] . '" class="LHS" /><a href="/website/diag/">' . $_HOAM_language ['field_names']['admin']['diag'] . '</a>';
			$HTML .= '<ul>';
			if ($_HOAM_setting ['email']['automated']) {
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/email.png\')"><a href="/website/diag/email/">' . $_HOAM_language ['field_names']['diag']['email'] . '</a></li>';
			}
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/bullet_wrench.png\')"><a href="/website/diag/maintenance/">' . $_HOAM_language ['field_names']['diag']['maintenance'] . '</a></li>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/website/diag/monthly/">' . $_HOAM_language ['field_names']['diag']['monthly'] . '</a></li>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/magnifier.png\')"><a href="/website/diag/md5/">' . $_HOAM_language ['field_names']['diag']['md5'] . '</a></li>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/magnifier.png\')"><a href="/website/diag/hoaminfo/">' . $_HOAM_language ['field_names']['diag']['hoaminfo'] . '</a></li>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/magnifier.png\')"><a href="/website/diag/phpinfo/">' . $_HOAM_language ['field_names']['diag']['phpinfo'] . '</a></li>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/bullet_wrench.png\')"><a href="/website/diag/toggle/">' . $_HOAM_language ['field_names']['diag']['toggle'] . '</a></li>';
			$HTML .= '</ul>';
		} else {
			header ('Location: /error/403/');
			exit ();
		} 
	}
	return ($HTML);
}

function HOAM_adminExport ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$budget_class = new HOAM_budget ('budget');
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$violation_class = new HOAM_violation ('violations');

	if (!empty ($GLOBALS['_HOAM_setting'] ['website']['title'])) {
		$fname = $GLOBALS['_HOAM_setting'] ['website']['title'];
	} else {
		$fname = 'HOAM';
	}
	
	switch ($GLOBALS['global_url_array'][2]) {
		case 'email' :
			$_HOAM_log -> add ('Loading /website/export/email/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['email'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['email'] . '</h1>';
				$homeowner_list = $homeowner_class -> ListCurrentHomeowners ();
				$email_HTML = '';
				$header = $_HOAM_language ['field_names']['homeowner']['email'];
				$header .= '<br />';
				foreach ($homeowner_list as $homeowner) {
					$homeowner_info = $homeowner_class -> ListAll ($homeowner);
					$homeowner_info = $homeowner_info[0];

					if (!empty ($homeowner_info['email'])) {
						$email_HTML .= '"' . stripslashes ($homeowner_info['name']) . '"';
						$email_HTML .= ' &lt;' . stripslashes ($homeowner_info['email']) . '&gt;,';
						$email_HTML .= '<br />';
					}
				}
				if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
					header ('Content-type: text/csv');
					header ('Content-Disposition: attachment; filename=' . $fname . '_all_email_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
					// Replace all '<br />' with '\n'
					// NOTE! Echo doesn't process the \n correctly if single
					// quotes are used.
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $header);
					$email_HTML = preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $email_HTML);
					$email_HTML = htmlspecialchars_decode ($email_HTML);
					echo $email_HTML;
					exit ();
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $email_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $email_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'homeowners' :
			$_HOAM_log -> add ('Loading /website/export/homeowners/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['homeowners'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['homeowners'] . '</h1>';
				$homeowner_list = $homeowner_class -> ListCurrentHomeowners ();
				$homeowner_HTML = '';
				$header = $_HOAM_language ['field_names']['homeowner']['name'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address1'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address2'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address3'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['city'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['state'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['postalcode'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['fax'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['email'];
				$header .= '<br />';
				foreach ($homeowner_list as $homeowner) {
					$homeowner_info = $homeowner_class -> ListAll ($homeowner);
					$homeowner_info = $homeowner_info[0];
	
					$homeowner_HTML .= '"' . stripslashes ($homeowner_info['name']) . '",';
					$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address1']) . '",';
					$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address2']) . '",';
					$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address3']) . '",';
					$homeowner_HTML .= '"' . stripslashes ($homeowner_info['city']) . '",';
					$homeowner_HTML .= $homeowner_info['state'] . ',';
					$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_info['zipcode'], 1) . ',';
					$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_home'], 1) . ',';
					$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_work'], 1) . ',';
					$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_mobile'], 1) . ',';
					$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_fax'], 1) . ',';
					$homeowner_HTML .= $homeowner_info['email'];
					$homeowner_HTML .= '<br />';
				}
				if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
					header ('Content-type: text/csv');
					header ('Content-Disposition: attachment; filename=' . $fname . '_all_homeowners_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
					// Replace all '<br />' with '\n'
					// NOTE! Echo doesn't process the \n correctly if single
					// quotes are used.
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $header);
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $homeowner_HTML);
					exit ();
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $homeowner_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $homeowner_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'homeowners_current' :
			$_HOAM_log -> add ('Loading /website/export/homeowners_current/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['homeowners_current'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['homeowners_current'] . '</h1>';
				$homeowner_list = $homeowner_class -> ListCurrentHomeowners ();
				$homeowner_HTML = '';
				$header = $_HOAM_language ['field_names']['homeowner']['name'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address1'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address2'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address3'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['city'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['state'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['postalcode'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['fax'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['email'];
				$header .= '<br />';
				foreach ($homeowner_list as $homeowner) {
					$homeowner_info = $homeowner_class -> ListAll ($homeowner);
					$homeowner_info = $homeowner_info[0];

					$homeowner_HTML .= '"' . stripslashes ($homeowner_info['name']) . '",';
					$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address1']) . '",';
					$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address2']) . '",';
					$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address3']) . '",';
					$homeowner_HTML .= '"' . stripslashes ($homeowner_info['city']) . '",';
					$homeowner_HTML .= $homeowner_info['state'] . ',';
					$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_info['zipcode'], 1) . ',';
					$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_home'], 1) . ',';
					$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_work'], 1) . ',';
					$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_mobile'], 1) . ',';
					$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_fax'], 1) . ',';
					$homeowner_HTML .= $homeowner_info['email'];
					$homeowner_HTML .= '<br />';
					// If this homeowner does NOT receive mail at their
					// residence, also send a copy to the residence.
					if ((int) $homeowner_info ['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['residence_mailing']) {
					} else {
						$homeowner_HTML .= '"' . $_HOAM_language ['common']['current-resident'] . '",';
						$homeowner_HTML .= '"' . $_HOAM_language ['common']['current-resident'] . '",';
						$homeowner_HTML .= '"';
						$lot = $lot_class -> ListAll ($homeowner_info ['lot_id']);
						$lot = $lot[0];
						if (!empty($lot['building'])) {
							$bldg = 'Bldg. ' . $lot['building'];
						} else {
							$bldg = '';
						}
						if (!empty($lot['suite'])) {
							$suite = 'Suite ' . $lot['suite'];
						} else {
							$suite = '';
						}
						if ($bldg AND $suite) {
							$homeowner_HTML .= $bldg . ' ' . $suite;
						} else {
							if ($bldg || $suite) {
								$homeowner_HTML .= $bldg . $suite;
							}
						}
						$homeowner_HTML .= '",';
						$homeowner_HTML .= '"' . $lot['address'] . " " . $lot['street'] . '",';
						$homeowner_HTML .= '"' . stripslashes ($_HOAM_setting ['homeowner']['default']['city']) . '",';
						$homeowner_HTML .= $_HOAM_setting ['homeowner']['default']['state'] . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $_HOAM_setting ['homeowner']['default']['postalcode'], 1) . ',';
						$homeowner_HTML .= ',';
						$homeowner_HTML .= ',';
						$homeowner_HTML .= ',';
						$homeowner_HTML .= ',';
						$homeowner_HTML .= ',';
						$homeowner_HTML .= '<br />';
					}
				}
				if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
					header ('Content-type: text/csv');
					header ('Content-Disposition: attachment; filename=' . $fname . '_all_homeowners_and_current_residents_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
					// Replace all '<br />' with '\n'
					// NOTE! Echo doesn't process the \n correctly if single
					// quotes are used.
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $vendor);
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $homeowner_HTML);
					exit ();
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $homeowner_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $homeowner_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'paid' :
			$_HOAM_log -> add ('Loading /website/export/paid/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['paid'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['paid'] . '</h1>';
				$homeowner_list = $homeowner_class -> ListCurrentHomeowners ();
				$homeowner_HTML = '';
				$header = $_HOAM_language ['field_names']['homeowner']['name'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address1'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address2'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address3'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['city'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['state'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['postalcode'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['fax'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['email'];
				$header .= '<br />';
				foreach ($homeowner_list as $homeowner) {
					// Only list the if the accounts don't owe anything.
					if ($budget_class -> returnTotalAmountDue ($homeowner) <= 0) {
						$homeowner_info = $homeowner_class -> ListAll ($homeowner);
						$homeowner_info = $homeowner_info[0];

						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address1']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address2']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address3']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['city']) . '",';
						$homeowner_HTML .= $homeowner_info['state'] . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_info['zipcode'], 1) . ',';;
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_home'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_work'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_mobile'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_fax'], 1) . ',';
						$homeowner_HTML .= $homeowner_info['email'];
						$homeowner_HTML .= '<br />';
					}
				}
				if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
					header ('Content-type: text/csv');
					header ('Content-Disposition: attachment; filename=' . $fname . '_paid_homeowners_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
					// Replace all '<br />' with '\n'
					// NOTE! Echo doesn't process the \n correctly if single
					// quotes are used.
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $header);
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $homeowner_HTML);
					exit ();
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $homeowner_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $homeowner_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'residents' :
			$_HOAM_log -> add ('Loading /website/export/residents/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['residents'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['residents'] . '</h1>';
				$homeowner_list = $homeowner_class -> listBitNotSet ($_HOAM_setting ['homeowner']['flags']['residence_mailing']);
				$homeowner_HTML = '';
				$header = $_HOAM_language ['field_names']['homeowner']['name'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address1'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address2'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address3'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['city'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['state'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['postalcode'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['fax'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['email'];
				$header .= '<br />';
				foreach ($homeowner_list as $homeowner) {
					// Make sure we know who the owner was on the HOAM_date of
					// the violation
					$homeowner_info = $homeowner_class -> ListAll ($homeowner);
					$homeowner_info = $homeowner_info[0];

					$current_owner = $homeowner_class -> returnCurrentOwner ($homeowner_info['lot_id']);
					// Only list this homeowner if they're the current homeowner
					// (eg, the original list contains ALL homeowners ever that
					// don't receive mail at the residece.
					if ($homeowner_info['id'] == $current_owner) {
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['name']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address1']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address2']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address3']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['city']) . '",';
						$homeowner_HTML .= $homeowner_info['state'] . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_info['zipcode'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_home'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_work'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_mobile'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_fax'], 1) . ',';
						$homeowner_HTML .= $homeowner_info['email'];
						$homeowner_HTML .= '<br />';
					}
				}
				if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
					header ('Content-type: text/csv');
					header ('Content-Disposition: attachment; filename=' . $fname . '_non_resident_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
					// Replace all '<br />' with '\n'
					// NOTE! Echo doesn't process the \n correctly if single
					// quotes are used.
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $header);
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $homeowner_HTML);
					exit ();
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $homeowner_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $homeowner_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'residents_current' :
			$_HOAM_log -> add ('Loading /website/export/residents_current/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['residents_current'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['residents_current'] . '</h1>';
				$homeowner_list = $homeowner_class -> listBitNotSet ($_HOAM_setting ['homeowner']['flags']['residence_mailing']);
				$homeowner_HTML = '';
				$header = $_HOAM_language ['field_names']['homeowner']['name'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address1'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address2'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['address3'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['city'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['state'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['postalcode'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['fax'] . ',';
				$header .= $_HOAM_language ['field_names']['homeowner']['email'];
				$header .= '<br />';
				foreach ($homeowner_list as $homeowner) {
					// Make sure we know who the owner was on the HOAM_date of
					// the violation
					$homeowner_info = $homeowner_class -> ListAll ($homeowner);
					$homeowner_info = $homeowner_info[0];

					$current_owner = $homeowner_class -> returnCurrentOwner ($homeowner_info['lot_id']);
					// Only list this homeowner if they're the current homeowner
					// (eg, the original list contains ALL homeowners ever that
					// don't receive mail at the residece.
					if ($homeowner_info['id'] == $current_owner) {
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['name']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address1']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address2']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address3']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['city']) . '",';
						$homeowner_HTML .= $homeowner_info['state'] . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_info['zipcode'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_home'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_work'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_mobile'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_fax'], 1) . ',';
						$homeowner_HTML .= $homeowner_info['email'];
						$homeowner_HTML .= '<br />';
						$homeowner_HTML .= '"' . $_HOAM_language ['common']['current-resident'] . '",';
						$homeowner_HTML .= '"' . $_HOAM_language ['common']['current-resident'] . '",';
						$lot = $lot_class -> ListAll ($homeowner_info ['lot_id']);
						$lot = $lot[0];
						$homeowner_HTML .= '"';
						if (!empty($lot['building'])) {
							$bldg = 'Bldg. ' . $lot['building'];
						} else {
							$bldg = '';
						}
						if (!empty($lot['suite'])) {
							$suite = 'Suite ' . $lot['suite'];
						} else {
							$suite = '';
						}
						if ($bldg AND $suite) {
							$homeowner_HTML .= $bldg . ' ' . $suite;
						} else {
							if ($bldg || $suite) {
								$homeowner_HTML .= $bldg . $suite;
							}
						}
						$homeowner_HTML .= '",';
						$homeowner_HTML .= '"' . $lot['address'] . " " . $lot['street'] . '",';
						$homeowner_HTML .= '"' . $_HOAM_setting ['homeowner']['default']['city'] . '",';
						$homeowner_HTML .= $_HOAM_setting ['homeowner']['default']['state'] . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $_HOAM_setting ['homeowner']['default']['postalcode'], 1);
						$homeowner_HTML .= ',';
						$homeowner_HTML .= ',';
						$homeowner_HTML .= ',';
						$homeowner_HTML .= ',';
						$homeowner_HTML .= ',';
						$homeowner_HTML .= '<br />';
					}
				}
				if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
					header ('Content-type: text/csv');
					header ('Content-Disposition: attachment; filename=' . $fname . '_non_resident_with_current_residents_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
					// Replace all '<br />' with '\n'
					// NOTE! Echo doesn't process the \n correctly if single
					// quotes are used.
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $header);
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $homeowner_HTML);
					exit ();
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $homeowner_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $homeowner_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'unpaid' :
			$_HOAM_log -> add ('Loading /website/export/unpaid/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['unpaid'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['unpaid'] . '</h1>';
				$lot_class = new HOAM_lot ('lots');
				if (!empty ($lot_class -> listStreets ())) {
					// We're performing this check in case no streets have been configured.
					// This is extremely unlikely, however it will cause a MySQL error if
					// the maintenance script is set to run but no lots have been entered
					// yet (fresh install).
					$homeowner_list = $unpaid_list = $budget_class -> listCustomersWithOpenCharges ();
					$homeowner_HTML = '';
					$header = $_HOAM_language ['field_names']['homeowner']['name'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address1'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address2'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address3'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['city'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['state'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['postalcode'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['fax'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['email'];
					$header .= '<br />';
					foreach ($homeowner_list as $homeowner) {
						$homeowner_info = $homeowner_class -> ListAll ($homeowner);
						$homeowner_info = $homeowner_info[0];

						// We don't want to list people with credits, only with
						// funds due
						if ($budget_class -> returnTotalAmountDue ($homeowner_info['id']) > 0) {
							$homeowner_HTML .= '"' . stripslashes ($homeowner_info['name']) . '",';
							$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address1']) . '",';
							$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address2']) . '",';
							$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address3']) . '",';
							$homeowner_HTML .= '"' . stripslashes ($homeowner_info['city']) . '",';
							$homeowner_HTML .= $homeowner_info['state'] . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_info['zipcode'], 1) . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_home'], 1) . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_work'], 1) . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_mobile'], 1) . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_fax'], 1) . ',';
							$homeowner_HTML .= $homeowner_info['email'];
							$homeowner_HTML .= '<br />';
						}
					}
					if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
						header ('Content-type: text/csv');
						header ('Content-Disposition: attachment; filename=' . $fname . '_unpaid_homeowners_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
						// Replace all '<br />' with '\n'
						// NOTE! Echo doesn't process the \n correctly if single
						// quotes are used.
						echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $header);
						echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $homeowner_HTML);
						exit ();
					}
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $homeowner_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $homeowner_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'unpaid_current' :
			$_HOAM_log -> add ('Loading /website/export/unpaid_current/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['unpaid_current'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['unpaid_current'] . '</h1>';
				$lot_class = new HOAM_lot ('lots');
				if (!empty ($lot_class -> listStreets ())) {
					// We're performing this check in case no streets have been configured.
					// This is extremely unlikely, however it will cause a MySQL error if
					// the maintenance script is set to run but no lots have been entered
					// yet (fresh install).
					$homeowner_list = $unpaid_list = $budget_class -> listCustomersWithOpenCharges ();
					$homeowner_HTML = '';
					$header = $_HOAM_language ['field_names']['homeowner']['name'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address1'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address2'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address3'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['city'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['state'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['postalcode'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['fax'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['email'];
					$header .= '<br />';
					foreach ($homeowner_list as $homeowner) {
						$homeowner_info = $homeowner_class -> ListAll ($homeowner);
						$homeowner_info = $homeowner_info[0];

						// We don't want to list people with credits, only with
						// funds due
						if ($budget_class -> returnTotalAmountDue ($homeowner_info['id']) > 0) {
							$homeowner_HTML .= '"' . stripslashes ($homeowner_info['name']) . '",';
							$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address1']) . '",';
							$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address2']) . '",';
							$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address3']) . '",';
							$homeowner_HTML .= '"' . stripslashes ($homeowner_info['city']) . '",';
							$homeowner_HTML .= $homeowner_info['state'] . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_info['zipcode'], 1) . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_home'], 1) . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_work'], 1) . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_mobile'], 1) . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_fax'], 1) . ',';
							$homeowner_HTML .= $homeowner_info['email'];
							$homeowner_HTML .= '<br />';
							// If this homeowner does NOT receive mail at their residence, also send a copy to the residence.
							if ((int) $homeowner_info ['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['residence_mailing']) {
							} else {
								$homeowner_HTML .= '"' . $_HOAM_language ['common']['current-resident'] . '",';
								$homeowner_HTML .= '"' . $_HOAM_language ['common']['current-resident'] . '",';
								$lot = $lot_class -> ListAll ($homeowner_info ['lot_id']);
								$lot = $lot[0];
								$homeowner_HTML .= '"';
								if (!empty($lot['building'])) {
									$bldg = 'Bldg. ' . $lot['building'];
								} else {
								$bldg = '';
								}
								if (!empty($lot['suite'])) {
									$suite = 'Suite ' . $lot['suite'];
								} else {
									$suite = '';
								}
								if ($bldg AND $suite) {
									$homeowner_HTML .= $bldg . ' ' . $suite;
								} else {
									if ($bldg || $suite) {
										$homeowner_HTML .= $bldg . $suite;
									}
								}
								$homeowner_HTML .= '",';
								$homeowner_HTML .= '"' . $lot['address'] . " " . $lot['street'] . '",';
								$homeowner_HTML .= '"' . $_HOAM_setting ['homeowner']['default']['city'] . '",';
								$homeowner_HTML .= $_HOAM_setting ['homeowner']['default']['state'] . ',';
								$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $_HOAM_setting ['homeowner']['default']['postalcode'], 1);
								$homeowner_HTML .= ',';
								$homeowner_HTML .= ',';
								$homeowner_HTML .= ',';
								$homeowner_HTML .= ',';
								$homeowner_HTML .= ',';
								$homeowner_HTML .= '<br />';
							}
						}
					}
				}
				if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
					header ('Content-type: text/csv');
					header ('Content-Disposition: attachment; filename=' . $fname . '_unpaid_homeowners_and_current_residents_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
					// Replace all '<br />' with '\n'
					// NOTE! Echo doesn't process the \n correctly if single
					// quotes are used.
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $header);
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $homeowner_HTML);
					exit ();
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $homeowner_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $homeowner_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'vendors' :
			$_HOAM_log -> add ('Loading /website/export/vendors/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['vendors'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['vendors'] . '</h1>';
				$vendor_list = $budget_vendor_class -> listVendors ();
				$vendor_HTML = '';
				$header = $_HOAM_language ['field_names']['budget']['vendor']['name'] . ',';
				$header .= $_HOAM_language ['field_names']['budget']['vendor']['address1'] . ',';
				$header .= $_HOAM_language ['field_names']['budget']['vendor']['address2'] . ',';
				$header .= $_HOAM_language ['field_names']['budget']['vendor']['address3'] . ',';
				$header .= $_HOAM_language ['field_names']['budget']['vendor']['city'] . ',';
				$header .= $_HOAM_language ['field_names']['budget']['vendor']['state'] . ',';
				$header .= $_HOAM_language ['field_names']['budget']['vendor']['postalcode'] . ',';
				$header .= $_HOAM_language ['field_names']['budget']['vendor']['telephone']['work'] . ',';
				$header .= $_HOAM_language ['field_names']['budget']['vendor']['telephone']['fax'] . ',';
				$header .= $_HOAM_language ['field_names']['budget']['vendor']['email'] . ',';
				$header .= $_HOAM_language ['field_names']['budget']['vendor']['website'];
				$header .= '<br />';
				foreach ($vendor_list as $vendor) {
					$vendor_info = $budget_vendor_class -> ListAll ($vendor);
					$vendor_info = $vendor_info[0];

					$vendor_HTML .= '"' . stripslashes ($vendor_info['name']) . '",';
					$vendor_HTML .= '"' . stripslashes ($vendor_info['address1']) . '",';
					$vendor_HTML .= '"' . stripslashes ($vendor_info['address2']) . '",';
					$vendor_HTML .= '"' . stripslashes ($vendor_info['address3']) . '",';
					$vendor_HTML .= '"' . stripslashes ($vendor_info['city']) . '",';
					$vendor_HTML .= $vendor_info['state'] . ',';
					$vendor_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $vendor_info['postalcode'], 1) . ',';
					$vendor_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $vendor_info['telephone_work'], 1) . ',';
					$vendor_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $vendor_info['telephone_fax'], 1) . ',';
					$vendor_HTML .= '' . $vendor_info['email'] . ',';
					$vendor_HTML .= '' . $vendor_info['website'];
					$vendor_HTML .= '<br />';
				}
				if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
					header ('Content-type: text/csv');
					header ('Content-Disposition: attachment; filename=' . $fname . '_vendors_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
					// Replace all '<br />' with '\n'
					// NOTE! Echo doesn't process the \n correctly if single
					// quotes are used.
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $header);
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $vendor_HTML);
					exit ();
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $vendor_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $vendor_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'violations' :
			$_HOAM_log -> add ('Loading /website/export/violations/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['violations'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['violations'] . '</h1>';
				if ($violation_class -> returnCountAll ()) {		// We're performing this check in case no streets have been configured.
					// This is extremely unlikely, however it will cause a
					// MySQL error if no violations have been entered yet.
					$recent_violation_date = $violation_class -> itemQuery ('SELECT DISTINCT(dateviolation), datecreated FROM violations ORDER BY datecreated DESC LIMIT 1');
					$violation_list = $violation_class -> listSimpleQuery ('SELECT id FROM violations WHERE dateviolation="' . $recent_violation_date . '"');
					$homeowner_HTML = '';
					$header = $_HOAM_language ['field_names']['homeowner']['name'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address1'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address2'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address3'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['city'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['state'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['postalcode'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['fax'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['email'];
					$header .= '<br />';
					foreach ($violation_list as $violation) {
						// Make sure we know who the owner was on the HOAM_date of
						// the violation
						$homeowner_id = $homeowner_class -> returnOwner ($recent_violation_date, $violation_class -> returnLotId ($violation));
						$homeowner_info = $homeowner_class -> ListAll ($homeowner_id);
						$homeowner_info = $homeowner_info[0];

						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['name']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address1']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address2']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address3']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['city']) . '",';
						$homeowner_HTML .= $homeowner_info['state'] . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_info['zipcode'], 1) . ',';;
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_home'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_work'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_mobile'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_fax'], 1) . ',';
						$homeowner_HTML .= $homeowner_info['email'];
						$homeowner_HTML .= '<br />';
					}
				}
				if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
					header ('Content-type: text/csv');
					header ('Content-Disposition: attachment; filename=' . $fname . '_recent_violations_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
					// Replace all '<br />' with '\n'
					// NOTE! Echo doesn't process the \n correctly if single
					// quotes are used.
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $header);
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $homeowner_HTML);
					exit ();
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $homeowner_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $homeowner_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'violations_current' :
			$_HOAM_log -> add ('Loading /website/export/violations_current/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['export']['violations_current'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['export']['violations_current'] . '</h1>';
				if ($violation_class -> returnCountAll ()) {		// We're performing this check in case no streets have been configured.
					// This is extremely unlikely, however it will cause a
					// MySQL error if no violations have been entered yet.
					$recent_violation_date = $violation_class -> itemQuery ('SELECT DISTINCT(dateviolation), datecreated FROM violations ORDER BY datecreated DESC LIMIT 1');
					$violation_list = $violation_class -> listSimpleQuery ('SELECT id FROM violations WHERE dateviolation="' . $recent_violation_date . '"');
					$homeowner_HTML = '';
					$header = $_HOAM_language ['field_names']['homeowner']['name'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address1'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address2'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['address3'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['city'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['state'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['postalcode'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['telephone']['fax'] . ',';
					$header .= $_HOAM_language ['field_names']['homeowner']['email'];
					$header .= '<br />';
					foreach ($violation_list as $violation) {
						// Make sure we know who the owner was on the HOAM_date of
						// the violation
						$homeowner_id = $homeowner_class -> returnOwner ($recent_violation_date, $violation_class -> returnLotId ($violation));
						$homeowner_info = $homeowner_class -> ListAll ($homeowner_id);
						$homeowner_info = $homeowner_info[0];

						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['name']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address1']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address2']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['address3']) . '",';
						$homeowner_HTML .= '"' . stripslashes ($homeowner_info['city']) . '",';
						$homeowner_HTML .= $homeowner_info['state'] . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_info['zipcode'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_home'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_work'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_mobile'], 1) . ',';
						$homeowner_HTML .= preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_fax'], 1) . ',';
						$homeowner_HTML .= $homeowner_info['email'];
						$homeowner_HTML .= '<br />';
						// If this homeowner does NOT receive mail at their
						// residence, also send a copy to the residence.
						if ((int) $homeowner_info ['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['residence_mailing']) {
						} else {
							$homeowner_HTML .= '"' . $_HOAM_language ['common']['current-resident'] . '",';
							$homeowner_HTML .= '"' . $_HOAM_language ['common']['current-resident'] . '",';
							$lot = $lot_class -> ListAll ($homeowner_info ['lot_id']);
							$lot = $lot[0];
							$homeowner_HTML .= '"';
							if (!empty($lot['building'])) {
								$bldg = 'Bldg. ' . $lot['building'];
							} else {
								$bldg = '';
							}
							if (!empty($lot['suite'])) {
								$suite = 'Suite ' . $lot['suite'];
							} else {
								$suite = '';
							}
							if ($bldg AND $suite) {
								$homeowner_HTML .= $bldg . ' ' . $suite;
							} else {
								if ($bldg || $suite) {
									$homeowner_HTML .= $bldg . $suite;
								}
							}
							$homeowner_HTML .= '",';
							$homeowner_HTML .= '"' . $lot['address'] . " " . $lot['street'] . '",';
							$homeowner_HTML .= '"' . $_HOAM_setting ['homeowner']['default']['city'] . '",';
							$homeowner_HTML .= $_HOAM_setting ['homeowner']['default']['state'] . ',';
							$homeowner_HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $_HOAM_setting ['homeowner']['default']['postalcode'], 1);
							$homeowner_HTML .= ',';
							$homeowner_HTML .= ',';
							$homeowner_HTML .= ',';
							$homeowner_HTML .= ',';
							$homeowner_HTML .= ',';
							$homeowner_HTML .= '<br />';
						}
					}
				}
				if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
					header ('Content-type: text/csv');
					header ('Content-Disposition: attachment; filename=' . $fname . '_recent_violations_and_current_residents_' . date ('Ymd') . '.csv; modification-date="' . HOAM_date ('r') . '";');
					// Replace all '<br />' with '\n'
					// NOTE! Echo doesn't process the \n correctly if single
					// quotes are used.
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $header);
					echo preg_replace ('/<\/?br \/(.|\s)*?>/', "\n", $homeowner_HTML);
					exit ();
				}
				// No actions, just display the normal page content.
				$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['csv'] . '</a></div>';
				$HTML .= '<div class="center"><small>' . (count (explode ('<br />', $homeowner_HTML)) - 1) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</small></div>';
				$HTML .= $homeowner_HTML;
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officers'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['export'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['export'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/door_out.png" alt="' . $_HOAM_language ['field_names']['admin']['export'] . '" class="LHS" /><a href="/website/export/">' . $_HOAM_language ['field_names']['admin']['export'] . '</a>';
					$HTML .= '<ul>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/homeowners/">' . $_HOAM_language ['field_names']['export']['homeowners'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/homeowners_current/">' . $_HOAM_language ['field_names']['export']['homeowners_current'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/email/">' . $_HOAM_language ['field_names']['export']['email'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/paid/">' . $_HOAM_language ['field_names']['export']['paid'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/residents/">' . $_HOAM_language ['field_names']['export']['residents'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/residents_current/">' . $_HOAM_language ['field_names']['export']['residents_current'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/unpaid/">' . $_HOAM_language ['field_names']['export']['unpaid'] . '</a></li>';
					if ($_HOAM_setting ['homeowner']['send_current_resident']) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/unpaid_current/">' . $_HOAM_language ['field_names']['export']['unpaid_current'] . '</a></li>';
					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/vendors/">' . $_HOAM_language ['field_names']['export']['vendors'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/violations/">' . $_HOAM_language ['field_names']['export']['violations'] . '</a></li>';
					if ($_HOAM_setting ['homeowner']['send_current_resident']) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/disk.png\')"><a href="/website/export/violations_current/">' . $_HOAM_language ['field_names']['export']['violations_current'] . '</a></li>';
					}
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminGroup ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$group_class = new HOAM_group ('group_list');
	$group_members_class = new HOAM_group_members ('group_members');
	$user_class = new HOAM_user ('users');

	switch ($GLOBALS['global_url_array'][2]) {
		case 'add' :
			$_HOAM_log -> add ('Loading /website/group/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				require_once (HOAM_PATH_FUNCTION . 'forms.php');

				// OK, we're checking to see that the group we're trying to edit
				// is the one that we have information on in the
				// group_information variable. If the referrer doesn't match
				// this current page, then we know we came from somewhere else
				// on the site, and thus the information is suspect. Also,
				// trusted_id will /only/ be set if a group was being modified,
				// not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['group_information']['trusted_id']) AND ($group_id != $_SESSION['group_information']['trusted_id']))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['group_information'])) {
						unset ($_SESSION['group_information']);
					}
				}
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['group']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminGroupJS');

				$HTML = '<h1>' . $_HOAM_language ['field_names']['group']['add'] . '</h1>';
				$HTML .= '<form id="group" action="/hoam/scripts/group/edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['group']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];

				$HTML .= '<div class="tabber">';
				$HTML .= '<div class="tabbertab" id="group_add" title="' . $_HOAM_language ['form_tabs']['group']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formField ('group|name', $_HOAM_language ['field_names']['group']['name'], $_SESSION['group_information']['trusted_group|name'], '', $group_class -> returnColumnSize ('name'), 1);
				$HTML .= HOAM_formTextarea ('group|description', $_HOAM_language ['field_names']['group']['description'], $_SESSION['group_information']['trusted_group|description'], '', $_SESSION['current_user']['edit']['row'], $group_class -> returnColumnSize ('description'), 1);
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End New Group Tab
				$HTML .= '<div class="tabbertab" id="group_list" title="' . $_HOAM_language ['form_tabs']['group']['list'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formSelect ('show|group', $_HOAM_language ['field_names']['group']['name']);
				$group_list = $group_class -> listAllId ("name", "ASC");
				foreach ($group_list as $group) {
					$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formTextareaReadonly ('show|description', $_HOAM_language ['field_names']['group']['description'], '', $_SESSION['current_user']['edit']['row'], $group_class -> returnColumnSize ('description'));
				$HTML .= HOAM_formFieldReadonly ('show|member_count', $_HOAM_language ['field_names']['group']['member_count'], '', 4);
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formCheckboxReadonly ('show_flag_system', $_HOAM_language ['field_names']['group']['flag'], 0);
				$HTML .= '</fieldset>';
				$HTML .= '</div>'; // End Group List Tab
				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'delete' :
			$_HOAM_log -> add ('Loading /website/group/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_group_id = $group_class -> superClean ($GLOBALS['global_url_array'][3], $group_class -> returnColumnSize ('id'));
					if ($group_class -> idExists ($trusted_group_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['group']['delete'] . ' (' . html_encode (stripslashes ($group_class -> returnName ($trusted_group_id))) . ')')));
						$HTML = '<h1>' . $_HOAM_language ['field_names']['group']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');

						// OK, we're checking to see that the group we're trying
						// to edit is the one that we have information on in the
						// group_information variable. If the referrer doesn't
						// match this current page, then we know we came from
						// somewhere else on the site, and thus the information
						// is suspect. Also, trusted_id will /only/ be set if a
						// group was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['group_information']['trusted_id']) AND ($group_id != $_SESSION['group_information']['trusted_id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['group_information'])) {
								unset ($_SESSION['group_information']);
							}
						}
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminGroupJS');

						$HTML .= '<form id="group" action="/hoam/scripts/group/edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="group|id" id="group|id" value="' . $trusted_group_id  . '">';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['group']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];

						$HTML .= '<div class="tabber">';
						$HTML .= '<div class="tabbertab" id="group_delete" title="' . $_HOAM_language ['form_tabs']['group']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formFieldDisabled ('group|name', $_HOAM_language ['field_names']['group']['name'], html_encode (stripslashes ($group_class -> returnName ($trusted_group_id))));
						$HTML .= HOAM_formTextareaDisabled ('group|description', $_HOAM_language ['field_names']['group']['description'], $_SESSION['group_information']['trusted_group|description'], html_encode (stripslashes ($group_class -> returnDescription ($trusted_group_id))), $_SESSION['current_user']['edit']['row'], $group_class -> returnColumnSize ("description"), 1);
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End New Group Tab

						$HTML .= '<div class="tabbertab" id="group_list" title="' . $_HOAM_language ['form_tabs']['group']['list'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<label for="group|name">' . $_HOAM_language ['field_names']['group']['name'];
						$HTML .= '</label>';
						$HTML .= '<select id="show|group">';
						$group_list = $group_class -> listAllId ("name", "ASC");
						foreach ($group_list as $group) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '<label for="group|description">' . $_HOAM_language ['field_names']['group']['description'];
						$HTML .= '</label>';
						$HTML .= '<textarea readonly="readonly" id="show|description" rows="' . $_SESSION['current_user']['edit']['row'] . '"></textarea>';
						$HTML .= '<label for="group|member_count">' . $_HOAM_language ['field_names']['group']['member_count'];
						$HTML .= '</label>';
						$HTML .= '<input readonly="readonly" id="show|member_count" size="4" />';
						$HTML .= '<label for="group|flag">' . $_HOAM_language ['field_names']['group']['flag'];
						$HTML .= '</label>';
						$HTML .= '<fieldset>';
						$HTML .= '<div class="checkbox_option">';
						$HTML .= '<input readonly="readonly" type="checkbox" id="show_flag_system" />';
						$HTML .= '<label for="group|flags|system">' . $_HOAM_language ['field_names']['group']['flags']['system'];
						$HTML .= '</label>';
						$HTML .= '</div>';
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Group List Tab

						$HTML .= '<div class="tabbertab" id="group_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End Group History tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /website/group/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'edit' :
			$_HOAM_log -> add ('Loading /website/group/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_group_id = $group_class -> superClean ($GLOBALS['global_url_array'][3], $group_class -> returnColumnSize ('id'));
					if ($group_class -> idExists ($trusted_group_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['group']['edit'] . ' (' . html_encode (stripslashes ($group_class -> returnName ($trusted_group_id))) . ')')));
						$HTML = '<h1>' . $_HOAM_language ['field_names']['group']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');

						// OK, we're checking to see that the group we're trying
						// to edit is the one that we have information on in the
						// group_information variable. If the referrer doesn't
						// match this current page, then we know we came from
						// somewhere else on the site, and thus the information
						// is suspect. Also, trusted_id will /only/ be set if a
						// group was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['group_information']['trusted_id']) AND ($group_id != $_SESSION['group_information']['trusted_id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['group_information'])) {
								unset ($_SESSION['group_information']);
							}
						}
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminGroupJS');

						$HTML .= '<form id="group" action="/hoam/scripts/group/edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="group|id" id="group|id" value="' . $trusted_group_id  . '">';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];

						$HTML .= '<div class="tabber">';
						$HTML .= '<div class="tabbertab" id="group_modify" title="' . $_HOAM_language ['form_tabs']['group']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formField ('group|name', $_HOAM_language ['field_names']['group']['name'], $_SESSION['group_information']['trusted_group|name'], html_encode (stripslashes ($group_class -> returnName ($trusted_group_id))), $group_class -> returnColumnSize ("name"), 1);
						$HTML .= HOAM_formTextarea ('group|description', $_HOAM_language ['field_names']['group']['description'], $_SESSION['group_information']['trusted_group|description'], html_encode (stripslashes ($group_class -> returnDescription ($trusted_group_id))), $_SESSION['current_user']['edit']['row'], $group_class -> returnColumnSize ('description'), 1);
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End New Group Tab

						$HTML .= '<div class="tabbertab" id="group_list" title="' . $_HOAM_language ['form_tabs']['group']['list'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<label for="group|name">' . $_HOAM_language ['field_names']['group']['name'];
						$HTML .= '</label>';
						$HTML .= '<select id="show|group">';
						$group_list = $group_class -> listAllId ("name", "ASC");
						foreach ($group_list as $group) {
							$HTML .= '<option value="' . $group . '">' . html_encode ($group_class -> returnName ($group)) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '<label for="group|description">' . $_HOAM_language ['field_names']['group']['description'];
						$HTML .= '</label>';
						$HTML .= '<textarea readonly="readonly" id="show|description" rows="' . $_SESSION['current_user']['edit']['row'] . '"></textarea>';
						$HTML .= '<label for="group|member_count">' . $_HOAM_language ['field_names']['group']['member_count'];
						$HTML .= '</label>';
						$HTML .= '<input readonly="readonly" id="show|member_count" size="4" />';
						$HTML .= '<label for="group|flag">' . $_HOAM_language ['field_names']['group']['flag'];
						$HTML .= '</label>';
						$HTML .= '<fieldset>';
						$HTML .= '<div class="checkbox_option">';
						$HTML .= '<input readonly="readonly" type="checkbox" id="show_flag_system" />';
						$HTML .= '<label for="group|flags|system">' . $_HOAM_language ['field_names']['group']['flags']['system'];
						$HTML .= '</label>';
						$HTML .= '</div>';
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Group List Tab
						
						$HTML .= '<div class="tabbertab" id="group_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End Group History tab
						
						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /website/group/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'ismember' :
			$_HOAM_log -> add ('Loading /website/group/ismember/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {

				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['group']['is_member'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['group']['is_member'] . '</h1>';

				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminGroupJS');

				$HTML .= '<form>';
				$HTML .= '<div class="tabber">';
				$HTML .= '<div class="tabbertab" id="group_ismember" title="' . $_HOAM_language ['form_tabs']['group']['information'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= $_HOAM_language ['help_pages']['group']['is_member'];
				$HTML .= '<label for="user|accountname">' . $_HOAM_language ['field_names']['user']['accountname'];
				$HTML .= '</label>';
				$HTML .= '<select id="user|accountname">';
				$user_list = $user_class -> listAll (NULL, 'accountname ASC');
				if (is_array ($user_list)) {
					foreach ($user_list as $user) {
						$HTML .= '<option value="' . $user['id'] . '">' . html_encode ($user['accountname']) . '</option>';
					}
				}
				$HTML .= '</select>';
				$HTML .= '<label for="group|name">' . $_HOAM_language ['field_names']['group']['name'];
				$HTML .= '</label>';
				$HTML .= '<select id="group|name">';
				$group_list = $group_class -> listAll (NULL, 'name ASC');
				if (is_array ($group_list)) {
					foreach ($group_list as $group) {
						$HTML .= '<option value="' . $group['id'] . '">' . html_encode ($group['name']) . '</option>';
					}
				}
				$HTML .= '</select>';
				$HTML .= '<br /><div id="fillme"></div>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Group List Tab
				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'list' :
			$_HOAM_log -> add ('Loading /website/group/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['group']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['group']['list'] . '</h1>';
				$group_list = $group_class -> listAll (NULL, 'name ASC');
				$count = count ($group_list);
				$HTML .= "<p>There are " . $count . " groups in the system.</p>";
				$HTML .= '<table class="center">';
				$thead = '<thead>';
				$thead .= '<tr><th>' . $_HOAM_language ['field_names']['group']['name'] . '</th><th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th><th>' . $_HOAM_language ['field_names']['group']['description'] . '</th><th>' . $_HOAM_language ['field_names']['group']['member_count'] . '</th><th>' . $_HOAM_language ['tables']['column']['generic']['created'] . '</th><th>' . $_HOAM_language ['tables']['column']['generic']['flags'] . '</th></tr>';
				$thead .= '</thead>';
				$HTML .= $thead;
				$HTML .= preg_replace ('/thead/', 'tfoot', $thead);
				$HTML .= '<tbody>';
				foreach ($group_list as $group) {
					$HTML .= '<tr>';
					$HTML .= '<td>' . $group['name'] . '</td>';
					$HTML .= '<td>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<small><a href="/website/group/edit/' . $group['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
					}
					$HTML .= ' &nbsp; ';
					$HTML .= '<small><a href="/website/group/membership/' . $group['id'] . '/">' . $_HOAM_language ['action']['group']['membership'] . '</a></small>';
					$HTML .= ' &nbsp; ';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_delete'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<small><a class="error" href="/website/group/delete/' . $group['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
					}
					$HTML .= '</td>';
					$HTML .= '<td>' . $group['description'] . '</td>';
					$HTML .= '<td>' . $group_members_class -> returnCountMembers ($group['id']) . '</td>';
					$HTML .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($group['datecreated'])) . '</td>';
					$HTML .= '<td></td>';
					$HTML .= '</tr>';
				}
				$HTML .= '</tbody>';
				$HTML .= '</table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'membership' :
			$_HOAM_log -> add ('Loading /website/group/membership/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_group_id = $group_class -> superClean ($GLOBALS['global_url_array'][3], $group_class -> returnColumnSize ('id'));
					if ($group_class -> idExists ($trusted_group_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['group']['membership'] . ' (' . html_encode (stripslashes ($group_class -> returnName ($trusted_group_id))) . ')')));
						$HTML = '<h1>' . $_HOAM_language ['field_names']['group']['membership'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');

						$all_group_members = $group_members_class -> listByGroup ($trusted_group_id);
						$default_group_members = array ();
						$default_user_members = array ();
						foreach ($all_group_members as $member) {
							if ($group_members_class -> isGroup ($member)) {
								array_push ($default_group_members, $member);
							} else {
								array_push ($default_user_members, $member);
							}
						}
						
						// OK, we're checking to see that the group we're trying
						// to edit is the one that we have information on in the
						// group_information variable. If the referrer doesn't
						// match this current page, then we know we came from
						// somewhere else on the site, and thus the information
						// is suspect. Also, trusted_id will /only/ be set if a
						// group was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['group_information']['trusted_id']) AND ($group_id != $_SESSION['group_information']['trusted_id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['group_information'])) {
								unset ($_SESSION['group_information']);
							}
						}
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminGroupJS');

						$HTML .= '<form id="group|membership" action="/hoam/scripts/group/edit_membership.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="group|id" id="group|id" value="' . $trusted_group_id  . '" />';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];

						$HTML .= '<div class="tabber">';
						$HTML .= '<div class="tabbertab" id="group_modify" title="' . $_HOAM_language ['form_tabs']['group']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= $_HOAM_language ['common']['Modifying'] . ' <span class="group_name">' . $group_class -> returnName ($trusted_group_id) . '</span>';
						$HTML .= '<table style="background: transparent">';
						$HTML .= '<tbody class="nostripe">';
						$HTML .= '<tr><td width="40%">';
						$HTML .= HOAM_formSelectMultiple ('group|groups', $_HOAM_language ['field_names']['group']['groups']['current'], 5);
						if (isset ($_SESSION['group_information']['trusted_group|groups'])) {
							$default_group_members = $_SESSION['group_information']['trusted_group|groups'];
						}
						foreach ($default_group_members as $group) {
							$HTML .= '<option value="' . $group_members_class -> returnMemberId ($group) . '">' . $group_class -> returnName ($group_members_class -> returnMemberId ($group)) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</td><td width="20%">';
						$HTML .= '<button type="button" id="group|groups|add">&lt;&lt;</button><br />';
						$HTML .= '<button type="button" id="group|groups|remove">&gt;&gt;</button>';
						$HTML .= '</td><td>';
						$HTML .= HOAM_formSelectMultiple ('group|groups_all', $_HOAM_language ['field_names']['group']['groups']['all'], 5);
						$group_list = $group_class -> listAllId ("name", "ASC");
						foreach ($group_list as $group) {
							if (!in_array ($group, $default_group_members)) {
								// Only show groups that the current user has
								// access to. If the user is root or a system
								// administrator, skip
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) or 
									$_HOAM_setting ['user']['ids']['root'] == $_SESSION ['current_user']['id']) {
									$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
								} elseif ($group_members_class -> isMember ($group, $_SESSION['current_user']['id'])) {
									$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
								}
							}
						}
						$HTML .= '</select>';
						$HTML .= '</td></tr>';
						$HTML .= '</tbody>';
						$HTML .= '</table>';
						$HTML .= '<table style="background: transparent">';
						$HTML .= '<tbody class="nostripe">';
						$HTML .= '<tr><td width="40%">';
						$HTML .= HOAM_formSelectMultiple ('group|users', $_HOAM_language ['field_names']['group']['users']['current'], 5);
						if (isset ($_SESSION['group_information']['trusted_group|users'])) {
							$default_user_members = $_SESSION['group_information']['trusted_group|users'];
						}
						foreach ($default_user_members as $user) {
							$HTML .= '<option value="' . $group_members_class -> returnMemberId ($user) . '">' . $user_class -> returnAccountName ($group_members_class -> returnMemberId ($user)) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</td><td width="20%">';
						$HTML .= '<button type="button" id="group|users|add">&lt;&lt;</button><br />';
						$HTML .= '<button type="button" id="group|users|remove">&gt;&gt;</button>';
						$HTML .= '</td><td>';
						$HTML .= HOAM_formSelectMultiple ('group|users_all', $_HOAM_language ['field_names']['group']['users']['all'], 5);
						$user_list = $user_class -> listAllId ('accountname', 'ASC');
						foreach ($user_list as $user) {
							if (!in_array ($user, $default_user_members)) {
								// Only show groups that the current user has
								// access to. If the user is root or a system
								// administrator, skip
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) or 
								$_HOAM_setting ['user']['ids']['root'] == $_SESSION ['current_user']['id']) {
									$HTML .= '<option value="' . $user . '">' . $user_class -> returnAccountName ($user) . '</option>';
								}
							}
						}
						$HTML .= '</select>';
						$HTML .= '</td></tr>';
						$HTML .= '</tbody>';
						$HTML .= '</table>';
						
						$HTML .= '</div>';
						$HTML .= '</div>'; // End of Membership change tab
						
						$HTML .= '<div class="tabbertab" id="group_modify" title="' . $_HOAM_language ['form_tabs']['group']['membership'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= 'Members of <span class="group_name">' . $group_class -> returnName ($trusted_group_id) . '</span> :';
						
						function list_Members ($group_id) {
							$group_members = $group_members_class -> listByGroup ($group_id);
							$HTML = '';
							if (is_array ($group_members)) {
								$HTML .= '<ul>';
								foreach ($group_members as $member) {
									if ($group_members_class -> isGroup ($member)) {
										$HTML .= '<li><span class="group_name">' . html_encode ($group_class -> returnName ($group_members_class -> returnMemberId ($member))) . '</span>';
										list_Members ($group_members_class -> returnMemberId ($member));
										$HTML .= '</li>';
									} else {
										$HTML .= '<li><a href="/website/user/edit/' . $group_members_class -> returnMemberId ($member) . '/">' . html_encode ($user_class -> returnAccountName ($group_members_class -> returnMemberId ($member))) . '</a></li>';
									}
								}
								$HTML .= '</ul>';
							} else {
								$HTML .= 'No members of this group.';
							}
							return ($HTML);
						}

// There's a recursion problem happening here, need to take a little time to
// figure out.						
//						$HTML .= list_Members ($trusted_group_id);
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Membership List Tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /website/group/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['group'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['group'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/group.png" alt="' . $_HOAM_language ['field_names']['admin']['group'] . '" class="LHS" /><a href="/website/group/">' . $_HOAM_language ['field_names']['admin']['group'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_add'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/group_add.png\')"><a href="/website/group/add/">' . $_HOAM_language ['field_names']['group']['add'] . '</a></li>';
					}
//					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
//						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
//						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
//						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/group_edit.png\')"><a href="/website/group/edit/">' . $_HOAM_language ['field_names']['group']['edit'] . '</a></li>';
//					}
//					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
//						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_delete'], $_SESSION['current_user']['id']) OR
//						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
//						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/group_delete.png\')"><a href="/website/group/delete/">' . $_HOAM_language ['field_names']['group']['delete'] . '</a></li>';
//					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/group.png\')"><a href="/website/group/list/">' . $_HOAM_language ['field_names']['group']['list'] . '</a></li>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/chart_organisation.png\')"><a href="/website/group/membership/">' . $_HOAM_language ['field_names']['group']['membership'] . '</a></li>';
					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/chart_organisation.png\')"><a href="/website/group/ismember/">' . $_HOAM_language ['field_names']['group']['is_member'] . '</a></li>';
					$HTML .= '</ul>';
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminLog ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_log, $_HOAM_queue_head;
	
	$group_members_class = new HOAM_group_members ('group_members');
	
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['log'])));

	switch ($GLOBALS['global_url_array'][2]) {
		case ('current'):
			$_HOAM_log -> add ('Loading /website/log/current/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['log']['current'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['log']['current'] . '</h1>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;		
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['log'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/chart_curve.png" alt="' . $_HOAM_language ['field_names']['admin']['log'] . '" class="LHS" /><a href="/website/log/">' . $_HOAM_language ['field_names']['admin']['log'] . '</a>';
				$HTML .= '<ul>';
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/chart_curve.png\')">Base this off of some of the report code? Generating pie charts, etc?</li>';
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/chart_curve.png\')">Activity Logs</li>';
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/chart_curve.png\')">Error Logs</li>';
				$HTML .= '</ul>';
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminNews ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$group_members_class = new HOAM_group_members ('group_members');
	$news_class = new HOAM_news ('news');
	$user_class = new HOAM_user ('users');
	
	switch ($GLOBALS['global_url_array'][2]) {
		case ('add'):
			$_HOAM_log -> add ('Loading /website/news/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['news']['add'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['news']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				// OK, we're checking to see that the news article we're trying
				// to edit is the one that we have information on in the
				// news_information variable. If the referrer doesn't match this
				// current page, then we know we came from somewhere else on the
				// site, and thus the information is suspect. Also, trusted_id
				// will /only/ be set if a news article was being modified, not
				// added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['news_information'])) {
						unset ($_SESSION['news_information']);
					}
				}

				$HTML .= '<form id="news_form" action="/hoam/scripts/news/edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['news']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="news_information" title="' . $_HOAM_language ['form_tabs']['news']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formText ('news|title', $_HOAM_language ['field_names']['news']['title'], $_SESSION['news_information']['trusted_news|title'], '', $news_class -> returnColumnSize ('title'), $_HOAM_setting ['news']['minimum_title_length']);
				$HTML .= HOAM_formTextarea ('news|article', $_HOAM_language ['field_names']['news']['article'], $_SESSION['news_information']['trusted_news|article'], '', $_SESSION['current_user']['edit']['row'], $news_class -> returnColumnSize ('article'), $_HOAM_setting ['news']['minimum_article_length']);
				$HTML .= '</div>';
				$HTML .= '</div>'; // End New news tab

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'delete' :
			$_HOAM_log -> add ('Loading /website/news/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_news_id = $news_class -> superClean ($GLOBALS['global_url_array'][3], $news_class -> returnColumnSize ('id'));
					if ($news_class -> idExists ($trusted_news_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['news']['delete'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminNewsJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['news']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the news article we're
						// trying to edit is the one that we have information on
						// in the news_information variable. If the referrer
						// doesn't match this current page, then we know we came
						// from somewhere else on the site, and thus the
						// information is suspect. Also, trusted_id will /only/
						// be set if a news article was being modified, not
						// added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['news_information'])) {
								unset ($_SESSION['news_information']);
							}
						}

						$HTML .= '<form id="news_form" action="/hoam/scripts/news/edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="news|id" id="news|id" value="' . $trusted_news_id . '" />';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['news']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="news_information" title="' . $_HOAM_language ['form_tabs']['news']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formTextDisabled ('news|title', $_HOAM_language ['field_names']['news']['title'], $news_class -> returnTitle ($trusted_news_id));
						$HTML .= HOAM_formTextareaDisabled ('news|article', $_HOAM_language ['field_names']['news']['article'], $news_class -> returnArticle ($trusted_news_id), $_SESSION['current_user']['edit']['row'], $news_class -> returnColumnSize ('article'), $_HOAM_setting ['news']['minimum_article_length']);
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Edit news tab

						$HTML .= '<div class="tabbertab" id="news_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End news history
						
						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /website/news/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'edit' :
			$_HOAM_log -> add ('Loading /website/news/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_news_id = $news_class -> superClean ($GLOBALS['global_url_array'][3], $news_class -> returnColumnSize ('id'));
					if ($news_class -> idExists ($trusted_news_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['news']['edit'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminNewsJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['news']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the news article we're
						// trying to edit is the one that we have information on
						// in the news_information variable. If the referrer
						// doesn't match this current page, then we know we came
						// from somewhere else on the site, and thus the
						// information is suspect. Also, trusted_id will /only/
						// be set if a news article was being modified, not
						// added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['news_information'])) {
								unset ($_SESSION['news_information']);
							}
						}

						$HTML .= '<form id="news_form" action="/hoam/scripts/news/edit_script.php" enctype="multipart/form-data" method="post">';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<input type="hidden" name="news|id" id="news|id" value="' . $trusted_news_id . '" />';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="news_information" title="' . $_HOAM_language ['form_tabs']['news']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formText ('news|title', $_HOAM_language ['field_names']['news']['title'], $_SESSION['news_information']['trusted_news|title'], $news_class -> returnTitle ($trusted_news_id), $news_class -> returnColumnSize ('title'), $_HOAM_setting ['news']['minimum_title_length']);
						$HTML .= HOAM_formTextarea ('news|article', $_HOAM_language ['field_names']['news']['article'], $_SESSION['news_information']['trusted_news|article'], $news_class -> returnArticle ($trusted_news_id), $_SESSION['current_user']['edit']['row'], $news_class -> returnColumnSize ('article'), $_HOAM_setting ['news']['minimum_article_length']);
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Edit news tab

						$HTML .= '<div class="tabbertab" id="news_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End news history
						
						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /website/news/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			$_HOAM_log -> add ('Loading /website/news/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['news']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['news']['list'] . '</h1>';
				$news_list = $news_class -> listAll ();

				$colgroup = '<colgroup>';
				$colgroup .= '<col width="10%" />';
				$colgroup .= '<col width="50%" />';
				$colgroup .= '<col width="10%" />';
				$colgroup .= '<col width="15%" />';
				$colgroup .= '<col width="15%" />';
				$colgroup .= '</colgroup>';
				$thead = '<thead><tr>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['date']['created'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['news']['title'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['created_by'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['date']['modified'] . '</th>';
				$thead .= '</tr></thead>';
				$tbody = '<tbody id="news_list">';
				if (is_array ($news_list)) {
					foreach ($news_list as $news) {
						$tbody .= '<tr>';
						$tbody .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($news ['datecreated'])) . '</td>';
						$tbody .= '<td>' . stripslashes ($news ['title']) . '</td>';
						$tbody .= '<td>';
						$tbody .= '<small><a href="/news/view/' . $news['id'] . '/">' . $_HOAM_language ['action']['generic']['view'] . '</a></small>';
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/website/news/edit/' . $news['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_delete'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a class="error" href="/website/news/delete/' . $news['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
						}
						$tbody .= '</td>';

						$tbody .= '<td>' . $user_class -> returnAccountName ($news['user_id']) . '</td>';
						if ($news['datecreated'] != $news['datemodified']) {
							$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'] . ' ' . $_HOAM_country['time']['format_long'], strtotime ($news['datemodified'])) . '</td>';
						} else {
							$tbody .= '<td>&nbsp;</td>';
						}
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="5">' . $_HOAM_language['errors']['news']['none-found'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>' . $tbody . '</tbody></table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['news'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['news'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/newspaper.png" alt="' . $_HOAM_language ['field_names']['admin']['news'] . '" class="LHS" /><a href="/admin/news/">' . $_HOAM_language ['field_names']['admin']['news'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_add'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/newspaper_add.png\')"><a href="/website/news/add/">' . $_HOAM_language ['field_names']['news']['add'] . '</a></li>';
					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/newspaper.png\')"><a href="/website/news/list/">' . $_HOAM_language ['field_names']['news']['list'] . '</a></li>';
					$HTML .= '</ul>';
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminPlugin ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_log, $_HOAM_plugin_list, $_HOAM_queue_head, $_HOAM_setting;

	$group_members_class = new HOAM_group_members ('group_members');

	if (isset ($GLOBALS['global_url_array'][2])) {
		switch ($GLOBALS['global_url_array'][2]) {
			case 'config' :
				$_HOAM_log -> add ('Loading /website/plugin/config/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					$plugin = $_HOAM_log -> superClean ($GLOBALS['global_url_array'][3], 32);
					$plugin_found = FALSE;
					foreach ($_HOAM_plugin_list as $current_plugin) {
						if ($current_plugin['path'] == $plugin) {
							$plugin_found = TRUE;
							$_HOAM_log -> add ('Loading /website/plugin/config/' . $plugin . '/ page.', HOAM_MESSAGE_DEBUG);
							$HTML = call_user_func ($current_plugin['config']);
							break;
						}
					}
					if (!$plugin_found) {
						header ('Location: /error/404/');
						exit ();
					}
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'install' :
				$_HOAM_log -> add ('Loading /website/plugin/install/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			default :
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['plugin'])));
			$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['plugin'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/plugin.png" alt="' . $_HOAM_language ['field_names']['admin']['plugin'] . '" class="LHS" /><a href="/website/plugin/">' . $_HOAM_language ['field_names']['admin']['plugin'] . '</a>';
			$HTML .= '<ul>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/plugin_add.png\')">Install New Plugin</li>';
			if (!empty ($GLOBALS['_HOAM_plugin_list'])) {
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/plugin_edit.png\')">Configure Installed Plugins';
				$HTML .= '<ul>';
				foreach ($_HOAM_plugin_list as $current_plugin) {
					// Display a different icon depending whether the plugin is
					// currently enabled or disabled.
					if ($_HOAM_setting ['plugin'][$current_plugin['path']]['enabled'] == 1) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/plugin_edit.png\')">';
					} else {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/plugin_disabled.png\')">';
					}
					$HTML .= '<a href="/website/plugin/config/' . $current_plugin['path'] . '/">' . $current_plugin['name'] . '</a></li>';
				}
				$HTML .= '</ul>';
				$HTML .= '</li>';
			}
			$HTML .= '</ul>';
		} else {
			header ('Location: /error/403/');
			exit ();
		}
	}
	return ($HTML);
}

function HOAM_adminUser ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$group_class = new HOAM_group ('group_list');
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$user_class = new HOAM_user ('users');

	switch ($GLOBALS['global_url_array'][2]) {
		case 'add' :
			$_HOAM_log -> add ('Loading /website/user/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminUserJS');
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['add'] . '</h1>';
				$HTML .= '<form id="user_new" action="/hoam/scripts/user/edit_script.php" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['user']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';
				$HTML .= '<div class="tabbertab" id="add_user" title="' . $_HOAM_language ['form_tabs']['user']['information'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= '<fieldset id="general">';
				$HTML .= HOAM_formField ('user|accountname', $_HOAM_language ['field_names']['user']['accountname'], $_SESSION['user_information']['trusted_user|accountname'], '', $user_class -> returnColumnSize ("accountname"), $_HOAM_setting ['user']['minimum_accountname_length']);
				// Note! The field size for the password has been set to just 32
				// chars, however the system is able to handle passwords up to
				// 255 chars long. This field limitation is just for appearance.
				$HTML .= HOAM_formConfigPassword ('user|password|new', $_HOAM_language ['field_names']['newuser']['password'], $_SESSION['user_information']['trusted_user|password|new'], '', 32, $_HOAM_setting ['user']['minimum_password_length']);
				$HTML .= '<span id="user|password|strength"></span>';
				$HTML .= HOAM_formConfigPassword ('user|password|verify', $_HOAM_language ['field_names']['user']['password']['verify'], $_SESSION['user_information']['trusted_user|password|verify'], '', 32);
				$HTML .= '</fieldset>';
				$HTML .= '<fieldset id="verification|hint">';
				$HTML .= HOAM_formSelect ('user|password|hint-name', $_HOAM_language ['field_names']['user']['password']['hint-name']);
				if (isset ($_SESSION['user_information']['trusted_user|password|hint-name'])) {
					$default_phrase = $_SESSION['user_information']['trusted_user|password|hint-name'];
				} else {
					$default_phrase = NULL;
				}
				foreach ($_HOAM_language ['password_hint_phrases'] as $phrase) {
					$HTML .= "<option ";
					if ($phrase['value'] == $default_phrase) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= "value=\"" . $phrase['value'] . "\">" . $phrase['description'] . "</option>\n";
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formField ('user|password|hint', $_HOAM_language ['field_names']['user']['password']['hint'], $_SESSION['user_information']['trusted_user|password|hint'], '', $user_class -> returnColumnSize ("password_hint"), $_HOAM_setting ['user']['minimum_password_hint_length']);
				$HTML .= '</fieldset>';
				$HTML .= '<table style="background: transparent">';
				$HTML .= '<tbody class="nostripe">';
				$HTML .= '<tr><td>';
				$HTML .= HOAM_formSelectMultiple ('user|groups', $_HOAM_language ['field_names']['user']['groups'], 5);
				if (isset ($_SESSION['user_information']['trusted_user|groups'])) {
					$default_groups = $_SESSION['user_information']['trusted_user|groups'];
				} else {
					$default_groups = NULL;
				}
				if (!is_array ($default_groups)) {
					$default_groups = array ($_HOAM_setting ['group']['ids']['registered']);
				}
				foreach ($default_groups as $group) {
					$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</td><td>';
				$HTML .= '<button type="button" id="user|group|add">&lt;&lt;</button><br />';
				$HTML .= '<button type="button" id="user|group|remove">&gt;&gt;</button>';
				$HTML .= '</td><td>';
				$HTML .= '<label for="user|groups_all">' . $_HOAM_language ['field_names']['user']['groups_all'];
				$HTML .= '</label>';
				$HTML .= '<select multiple="multiple" id="user|groups_list" size="5">';
				$group_list = $group_class -> listAllId ("name", "ASC");
				foreach ($group_list as $group) {
					if (!in_array ($group, $default_groups)) {
						// Only show groups that the current user has access to
						// If the user is root or a system adminstrator, skip
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) or 
						$_HOAM_setting ['user']['ids']['root'] == $_SESSION ['current_user']['id']) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						} elseif ($group_members_class -> isMember ($group, $_SESSION['current_user']['id'])) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						}
					}
				}
				$HTML .= '</select>';
				$HTML .= '</td></tr>';
				$HTML .= '</tbody>';
				$HTML .= '</table>';
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formSelect ('newuser|heard-about', $_HOAM_language ['field_names']['newuser']['heard-about']);
				if (isset ($_SESSION['user_information']['trusted_newuser|heard-about'])) {
					$default_option = $_SESSION['user_information']['trusted_newuser|heard-about'];
				} else {
					$default_option = NULL;
				}
				$HTML .= '<option>' . $_HOAM_language ['common']['please-select'] . '</option>';
				foreach ($_HOAM_language ['heard_about_options'] as $option) {
					$HTML .= "<option ";
					if ($option['value'] == $default_option) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= "value=\"" . $option['value'] . "\">" . $option['description'] . "</option>\n";
				}
				$HTML .= '</select>';
				$HTML .= '<fieldset id="personal">';
				$HTML .= HOAM_formField ('user|name|first', $_HOAM_language ['field_names']['user']['name']['first'], $_SESSION['user_information']['trusted_user|name|first'], '', $user_class -> returnColumnSize ("firstname"), $_HOAM_setting ['user']['minimum_firstname_length']);
				$HTML .= HOAM_formField ('user|name|last', $_HOAM_language ['field_names']['user']['name']['last'], $_SESSION['user_information']['trusted_user|name|last'], '', $user_class -> returnColumnSize ("lastname"), $_HOAM_setting ['user']['minimum_lastname_length']);
				if ($_HOAM_setting ['user']['email_validation']) {
					$HTML .= '</div>';
					$HTML .= '<div class="article_note RHS">' . $_HOAM_language ['field_names']['user']['email_validation'] . '</div>';
					$HTML .= '<div class="mandatory">';
				}
				$HTML .= HOAM_formField ('user|email', $_HOAM_language ['field_names']['user']['email'], $_SESSION['user_information']['trusted_user|email'], '', $user_class -> returnColumnSize ("email"));
				if ($_HOAM_setting ['user']['email_validation']) {
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
				}
				$HTML .= '</fieldset>';
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formCheckbox ('user|resident', $_HOAM_language ['field_names']['user']['resident'], 1, isset ($_SESSION['user_information']['trusted_user|resident']) AND $_SESSION['user_information']['trusted_user|resident'], '');
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory" id="user|show-address"';
				if (isset ($_SESSION['user_information']['trusted_user|resident']) AND $_SESSION['user_information']['trusted_user|resident']) {
				} else {
					$HTML .= ' style="display: none"';
				}
				$HTML .= '>';
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formSelect ('user|address', $_HOAM_language ['field_names']['user']['address']);
				if (isset ($_SESSION['user_information']['trusted_user|address'])) {
					$default_lot = $_SESSION['user_information']['trusted_user|address'];
				} else {
					$default_lot = NULL;
				}
				$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
				foreach ($lot_list as $lot) {
					$HTML .= '<option ';
					if ($lot['id'] == $default_lot) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
					$HTML .= '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End new user tab

				$HTML .= '<div class="tabbertab" id="user|customization" title="' . $_HOAM_language ['form_tabs']['user']['customization'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '<fieldset id="advanced">';
				$HTML .= '<label for="user|messageboard|options">' . $_HOAM_language ['field_names']['user']['messageboard']['options'];
				$HTML .= '</label>';
				$HTML .= '<fieldset id="user|messageboard|options" class="advanced_options">';
				$HTML .= HOAM_formCheckbox ('user|messageboard|acronyms', $_HOAM_language ['field_names']['user']['messageboard']['acronyms'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['acronyms']), '');
				$HTML .= HOAM_formCheckbox ('user|messageboard|censor', $_HOAM_language ['field_names']['user']['messageboard']['censor'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['censor']), '');
				$HTML .= HOAM_formCheckbox ('user|messageboard|links', $_HOAM_language ['field_names']['user']['messageboard']['links'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['convert_links']), '');
				$HTML .= HOAM_formCheckbox ('user|messageboard|smileys', $_HOAM_language ['field_names']['user']['messageboard']['smileys'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['smileys']), '');
				$HTML .= '</fieldset>';
				$HTML .= '<label for="user|display|options">' . $_HOAM_language ['field_names']['user']['display']['options'];
				$HTML .= '</label>';
				$HTML .= '<fieldset id="user|display|options" class="advanced_options">';
				$HTML .= HOAM_formCheckbox ('user|display|holidays', $_HOAM_language ['field_names']['user']['display']['holidays'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['holidays']), '');
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formField ('user|edit_cols', $_HOAM_language ['field_names']['user']['edit_cols'], $_SESSION['user_information']['trusted_user|edit_cols'], 80, 5, 40);
				$HTML .= HOAM_formField ('user|edit_rows', $_HOAM_language ['field_names']['user']['edit_rows'], $_SESSION['user_information']['trusted_user|edit_rows'], 10, 5, 2);
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End optional settings tab

				$HTML .= '</div>'; // End tabber
				$HTML .= '</form>';
			} else {
				$HTML = '<img src="/hoam/images/icons/user_add.png" alt="' . $_HOAM_language ['field_names']['user']['add'] . '" class="LHS" /><a href="/user/new/">' . $_HOAM_language ['field_names']['user']['add'] . '</a>';
			}
			break;
		case 'delete' :
			$_HOAM_log -> add ('Loading /website/user/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if (isset ($GLOBALS['global_url_array'][3])) {
					$trusted_id = $user_class -> superClean ($GLOBALS['global_url_array'][3], $user_class -> returnColumnSize ('id'));
					if ($user_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['delete'] . ' (' . $user_class -> returnAccountName ($trusted_id) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminUserJS');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['delete'] . '</h1>';
						if ($trusted_id === $_HOAM_setting ['user']['ids']['root']) { 
							$HTML .= '<div class="box_warning"><strong class="warning">' . $_HOAM_language ['errors']['user']['root'] . '</strong></div>';
						}
						if ($trusted_id === $_HOAM_setting ['user']['ids']['system']) { 
							$HTML .= $_HOAM_language ['help_pages']['generic']['no_changes'];
							$HTML .= '<div class="box_warning"><strong class="warning">' . $_HOAM_language ['errors']['user']['system'] . '</strong></div>';
						}
						$HTML .= '<form id="user_delete" action="/hoam/scripts/user/edit_script.php" method="post">';
						$HTML .= '<input type="hidden" name="user|id" id="user|id" value="' . $trusted_id . '" />';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['user']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';
						$HTML .= '<div class="tabbertab" id="add_user" title="' . $_HOAM_language ['form_tabs']['user']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formCheckboxDisabled ('user|flags|disabled', $_HOAM_language ['field_names']['user']['flags']['disabled'], 1, $_SESSION['user_information']['trusted_user|flags|disabled'], (int) $user_class -> returnFlags ($trusted_id) & (int) $_HOAM_setting ['user']['flags']['disabled']);
						$HTML .= '</fieldset>';
						$HTML .= '<fieldset id="general">';
						$HTML .= HOAM_formFieldDisabled ('user|accountname', $_HOAM_language ['field_names']['user']['accountname'], $user_class -> returnAccountName ($trusted_id));
						$HTML .= '<label for="user|password|show-change">' . $_HOAM_language ['field_names']['user']['password']['change'];
						$HTML .= '</label>';
						$HTML .= '<input disabled ';
						if (isset ($_SESSION['error_class_item'])) {
							if ($_SESSION['error_class_item'] -> ShowCategory ("user|password|new") OR
							$_SESSION['error_class_item'] -> ShowCategory ("user|password|verify")) {
								$HTML .= 'checked ';
							}
						}
						$HTML .= 'type="checkbox" id="user|password|show-change" name="user|password|show-change" value="1" />';
						$HTML .= '<fieldset id="user|change-password"';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION['error_class_item'] -> ShowCategory ("user|password|new") OR
						$_SESSION['error_class_item'] -> ShowCategory ("user|password|verify"))) {
						} else {
							$HTML .= ' style="display: none;"';
						}
						$HTML .= '>';
						// Note! The field size for the password has been set to
						// just 32 chars, however the system is able to handle
						// passwords up to 255 chars long. This field limitation
						// is just for appearance.
						$HTML .= HOAM_formConfigPassword ('user|password|new', $_HOAM_language ['field_names']['user']['password']['new'], $_SESSION['user_information']['trusted_user|password|new'], '', 32, $_HOAM_setting ['user']['minimum_password_length']);
						$HTML .= HOAM_formConfigPassword ('user|password|verify', $_HOAM_language ['field_names']['user']['password']['verify'], $_SESSION['user_information']['trusted_user|password|verify'], '', 32);
						$HTML .= '</fieldset>';
						$HTML .= '<fieldset id="verification|hint">';
						$HTML .= '<label for="user|password|hint-name">' . $_HOAM_language ['field_names']['user']['password']['hint-name'];
						$HTML .= HOAM_formDisplayError ('user|password|hint-name');
						$HTML .= '</label>';
						$HTML .= '<select disabled name="user|password|hint-name">';
						if (isset ($_SESSION['user_information']['trusted_user|password|hint-name'])) {
							$default_phrase = $_SESSION['user_information']['trusted_user|password|hint-name'];
						} else {
							$default_phrase = $user_class -> returnPasswordHintName ($trusted_id);
						}
						foreach ($_HOAM_language ['password_hint_phrases'] as $phrase) {
							$HTML .= "<option ";
							if ($phrase['value'] == $default_phrase) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= "value=\"" . $phrase['value'] . "\">" . $phrase['description'] . "</option>\n";
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formFieldDisabled ('user|password|hint', $_HOAM_language ['field_names']['user']['password']['hint'], $user_class -> returnPasswordHint ($trusted_id));
						$HTML .= '</fieldset>';
						$HTML .= '<table style="background: transparent">';
						$HTML .= '<tbody class="nostripe">';
						$HTML .= '<tr><td>';
						$HTML .= HOAM_formSelectMultipleDisabled ('user|groups', $_HOAM_language ['field_names']['user']['groups']);
						if (isset ($_SESSION['user_information']['trusted_user|groups'])) {
							$default_groups = $_SESSION['user_information']['trusted_user|groups'];
						} else {
							$member_list = $group_members_class -> listbyMember ($trusted_id);
							$default_groups = array ();
							foreach ($member_list as $member) {
								array_push ($default_groups, $group_members_class -> returnGroupId ($member));
							}
						}
						foreach ($default_groups as $group) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</td><td>';
						$HTML .= '<button type="button" id="user|group|add">&lt;&lt;</button><br />';
						$HTML .= '<button type="button" id="user|group|remove">&gt;&gt;</button>';
						$HTML .= '</td><td>';
						$HTML .= '<label for="user|groups_all">' . $_HOAM_language ['field_names']['user']['groups_all'];
						$HTML .= '</label>';
						$HTML .= '<select disabled multiple="multiple" id="user|groups_list" size="5">';
						$group_list = $group_class -> listAllId ("name", "ASC");
						foreach ($group_list as $group) {
							if (!in_array ($group, $default_groups)) {
								// Only show groups that the current user has access to
								// If the user is root or a system adminstrator, skip
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) or 
								$_HOAM_setting ['user']['ids']['root'] == $_SESSION ['current_user']['id']) {
									$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
								} elseif ($group_members_class -> isMember ($group, $_SESSION['current_user']['id'])) {
									$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
								}
							}
						}
						$HTML .= '</select>';
						$HTML .= '</td></tr>';
						$HTML .= '</tbody>';
						$HTML .= '</table>';
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= '<fieldset id="personal">';
						$HTML .= HOAM_formFieldDisabled ('user|name|first', $_HOAM_language ['field_names']['user']['name']['first'], $user_class -> returnFirstName ($trusted_id));
						$HTML .= HOAM_formFieldDisabled ('user|name|last', $_HOAM_language ['field_names']['user']['name']['last'], $user_class -> returnLastName ($trusted_id));
						if ($_HOAM_setting ['user']['email_validation']) {
							$HTML .= '</div>';
							$HTML .= '<div class="article_note RHS">' . $_HOAM_language ['field_names']['user']['email_validation'] . '</div>';
							$HTML .= '<div class="mandatory">';
						}
						$HTML .= HOAM_formFieldDisabled ('user|email', $_HOAM_language ['field_names']['user']['email'], $user_class -> returnEmail ($trusted_id));
						if ($_HOAM_setting ['user']['email_validation']) {
							$HTML .= '</div>';
							$HTML .= '<div class="optional">';
						}
						$HTML .= '</fieldset>';
						$HTML .= '<label for="user|resident">' . $_HOAM_language ['field_names']['user']['resident'];
						if (isset ($_SESSION['error_class_item'])) {
							$HTML .= '<span class="warning">';
							$HTML .= $_SESSION['error_class_item'] -> ShowCategory ('user|resident');
							$HTML .= '</span>';
						} else {
							$homeowner_id = $user_class -> returnHomeownerId ($trusted_id);
							$lot_id = $homeowner_class -> returnLotId ($homeowner_id);
							// Does this user have a homeowner_id associated with them?
							if ($homeowner_id) {
								// Is this user a validated resident or homeowner in the neighborhood?
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['resident'], $trusted_id) OR
									$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $trusted_id)) {
									$validated_resident = TRUE;
								} else {
									$validated_resident = FALSE;
									$HTML .= '<span class="warning">';
									// The user has a homeowner_id, but has not been added to either the resident or homeowner group yet.
									$HTML .= $_HOAM_language ['errors']['user']['not-validated-resident'];
									$HTML .= '</span>';
								}
							} else {
								$validated_resident = FALSE;
							}
						}
						$HTML .= '</label>';
						$HTML .= '<input disabled type="checkbox" id="user|resident" name="user|resident" value="1" />';
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '<div class="mandatory" id="user|show-address"';
						if (!$validated_resident) {
							$HTML .= 'style="display: none"';
						}
						$HTML .= '>';
						$HTML .= '<fieldset>';
						$HTML .= '<label for="user|address">' . $_HOAM_language ['field_names']['user']['address'];
						$HTML .= HOAM_formDisplayError ('user|address');
						$HTML .= '</label>';
						$HTML .= '<select disabled name="user|address">';
						if (isset ($_SESSION['user_information']['trusted_user|address'])) {
							$default_lot = $_SESSION['user_information']['trusted_user|address'];
						} elseif (isset ($lot_id) AND $lot_id) {
							$default_lot = $lot_id;
						} else {
							$default_lot = NULL;
						}
						$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
						foreach ($lot_list as $lot) {
							$HTML .= '<option ';
							if ($lot['id'] == $default_lot) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
							$HTML .= '</option>';
						}
						$HTML .= '</select>';
						if ($default_lot != NULL) {
							$HTML .= '&nbsp;&nbsp;' . $homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($default_lot));
							if ((int) $homeowner_class -> returnFlags ($homeowner_class -> returnCurrentOwner ($default_lot)) & (int) $_HOAM_setting ['homeowner']['flags']['resident']) {
							} else {
								$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['abbr']['lr']) . '">R</span> ';
							}
							
						}
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Edit user tab

						$HTML .= '<div class="tabbertab" id="user|customization" title="' . $_HOAM_language ['form_tabs']['user']['customization'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<fieldset id="advanced">';
						$HTML .= '<label for="user|messageboard|options">' . $_HOAM_language ['field_names']['user']['messageboard']['options'];
						$HTML .= '</label>';
						$HTML .= '<fieldset id="user|messageboard|options" class="advanced_options">';
						$HTML .= HOAM_formCheckboxDisabled ('user|messageboard|acronyms', $_HOAM_language ['field_names']['user']['messageboard']['acronyms'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['acronyms']), $user_class -> returnPreferenceAcronyms ($trusted_id));
						$HTML .= HOAM_formCheckboxDisabled ('user|messageboard|censor', $_HOAM_language ['field_names']['user']['messageboard']['censor'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['censor']), $user_class -> returnPreferenceCensor ($trusted_id));
						$HTML .= HOAM_formCheckboxDisabled ('user|messageboard|links', $_HOAM_language ['field_names']['user']['messageboard']['links'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['convert_links']), $user_class -> returnPreferenceLinks ($trusted_id));
						$HTML .= HOAM_formCheckboxDisabled ('user|messageboard|smileys', $_HOAM_language ['field_names']['user']['messageboard']['smileys'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['smileys']), $user_class -> returnPreferenceSmileys ($trusted_id));
						$HTML .= '</fieldset>';
						$HTML .= '<label for="user|display|options">' . $_HOAM_language ['field_names']['user']['display']['options'];
						$HTML .= '</label>';
						$HTML .= '<fieldset id="user|display|options" class="advanced_options">';
						$HTML .= HOAM_formCheckboxDisabled ('user|display|holidays', $_HOAM_language ['field_names']['user']['display']['holidays'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['holidays']), $user_class -> returnPreferenceHolidays ($trusted_id));
						$HTML .= '</fieldset>';
						$HTML .= HOAM_formFieldDisabled ('user|edit_cols', $_HOAM_language ['field_names']['user']['edit_cols'], $user_class -> returnPreferenceEditCol ($trusted_id));
						$HTML .= HOAM_formFieldDisabled ('user|edit_rows', $_HOAM_language ['field_names']['user']['edit_rows'], $user_class -> returnPreferenceEditRow ($trusted_id));
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End optional settings tab

						$HTML .= '<div class="tabbertab" id="user_postings" title="' . $_HOAM_language ['form_tabs']['user']['postings'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<table>';
						$HTML .= '<colgroup>';
						$HTML .= '<col width="20%" />';
						$HTML .= '<col />';
						$HTML .= '<col width="15%" />';
						$HTML .= '</colgroup>';
						$HTML .= '<thead>';
						$HTML .= '<tr><th>' . $_HOAM_language ['field_names']['messageboard']['date'] . '</th><th>' . $_HOAM_language ['common']['subject'] . '</th><th>' . $_HOAM_language ['field_names']['messageboard']['replies'] . '</th></tr>';
						$HTML .= '</thead>';
						$HTML .= '<tbody id="user_messageboard_postings">';
						$messageboard_class = new HOAM_messageboard ('messageboard');
						$message_list = $messageboard_class -> listAuthor ($trusted_id);
						if (!empty ($message_list)) {
							foreach ($message_list as $message) {
								$HTML .= '<tr>';
								$HTML .= '<td style="text-align: center">' . HOAM_date ($_HOAM_country ['date']['format_long'], strtotime ($message['datecreated'])) . '</td>';
								$HTML .= '<td><a href="/apps/messageboard/view/' . $message['id'] . '/">' . $message['subject'] . '</a></td>';
								$HTML .= '<td style="text-align: center">' . $message['replies'] . '</td>';
								$HTML .= '</tr>';
							}
						} else {
							$HTML .= '<tr><td style="text-align: center" colspan="3">' . $_HOAM_language['errors']['messageboard']['no-messages-found'] . '</td></tr>';
						}
						$HTML .= '</tbody>';
						$HTML .= '</table>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End messageboard postings tab

						$HTML .= '<div class="tabbertab" id="user_groups" title="' . $_HOAM_language ['form_tabs']['user']['group_membership'] . '">';
						$HTML .= '<div class="optional">' . $_HOAM_language ['help_pages']['user']['group_membership'];
						$group_list = $group_class -> listAll (NULL, 'name ASC');
						$HTML .= '<table class="center">';
						$thead = '<thead>';
						$thead .= '<tr><th>' . $_HOAM_language ['field_names']['group']['name'] . '</th><th>' . $_HOAM_language ['field_names']['group']['member_count'] = 'Membership Count' . '</th></tr>';
						$thead .= '</thead>';
						$HTML .= $thead;
						$HTML .= preg_replace ('/thead/', 'tfoot', $thead);
						$HTML .= '<tbody>';
						foreach ($group_list as $group) {
							if ($group_members_class -> isMember ($group['id'], $trusted_id)) {
								$HTML .= '<tr>';
								$HTML .= '<td>' . $group['name'] . '</td>';
								$HTML .= '<td>' . $group_members_class -> returnCountMembers ($group['id']) . '</td>';
								$HTML .= '</tr>';
							}
						}
						$HTML .= '</table>';
						$HTML .= '</div>';					
						$HTML .= '</div>'; // End User Group Membership
						
						$HTML .= '<div class="tabbertab" id="user_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End User History

						$HTML .= '</div>'; // End tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /website/user/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'edit' :
			$_HOAM_log -> add ('Loading /website/user/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_edit'], $_SESSION['current_user']['id']) OR
				$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
				if (isset ($GLOBALS['global_url_array'][3])) {
					$trusted_id = $user_class -> superClean ($GLOBALS['global_url_array'][3], $user_class -> returnColumnSize ('id'));
					if ($user_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['edit'] . ' (' . $user_class -> returnAccountName ($trusted_id) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminUserJS');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['edit'] . '</h1>';
						if ($trusted_id === $_HOAM_setting ['user']['ids']['root']) { 
							$HTML .= '<div class="box_warning"><strong class="warning">' . $_HOAM_language ['errors']['user']['root'] . '</strong></div>';
						}
						if ($trusted_id == $_HOAM_setting ['user']['ids']['system']) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['no_changes'];
							$HTML .= '<div class="box_warning"><strong class="warning">' . $_HOAM_language ['errors']['user']['system'] . '</strong></div>';
						}
						$HTML .= '<form id="user_modify" action="/hoam/scripts/user/edit_script.php" method="post">';
						$HTML .= '<input type="hidden" name="user|id" id="user|id" value="' . $trusted_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';
						$HTML .= '<div class="tabbertab" id="add_user" title="' . $_HOAM_language ['form_tabs']['user']['information'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formCheckbox ('user|flags|disabled', $_HOAM_language ['field_names']['user']['flags']['disabled'], 1, $_SESSION['user_information']['trusted_user|flags|disabled'], (int) $user_class -> returnFlags ($trusted_id) & (int) $_HOAM_setting ['user']['flags']['disabled']);
						$HTML .= '<br /></div>';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<fieldset id="general">';
						$HTML .= HOAM_formField ('user|accountname', $_HOAM_language ['field_names']['user']['accountname'], $_SESSION['user_information']['trusted_user|accountname'], $user_class -> returnAccountName ($trusted_id), $user_class -> returnColumnSize ("accountname"), $_HOAM_setting ['user']['minimum_accountname_length']);
						$HTML .= '<label for="user|password|show-change">' . $_HOAM_language ['field_names']['user']['password']['change'];
						$HTML .= '</label>';
						$HTML .= '<input ';
						if (isset ($_SESSION['error_class_item'])) {
							if ($_SESSION['error_class_item'] -> ShowCategory ("user|password|new") OR
							$_SESSION['error_class_item'] -> ShowCategory ("user|password|verify")) {
								$HTML .= 'checked ';
							}
						}
						$HTML .= 'type="checkbox" id="user|password|show-change" name="user|password|show-change" value="1" />';
						$HTML .= '<fieldset id="user|change-password"';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION['error_class_item'] -> ShowCategory ("user|password|new") OR
						$_SESSION['error_class_item'] -> ShowCategory ("user|password|verify"))) {
						} else {
							$HTML .= ' style="display: none;"';
						}
						$HTML .= '>';
						// Note! The field size for the password has been set to
						// just 32 chars, however the system is able to handle
						// passwords up to 255 chars long. This field limitation
						// is just for appearance.
						$HTML .= HOAM_formConfigPassword ('user|password|new', $_HOAM_language ['field_names']['user']['password']['new'], $_SESSION['user_information']['trusted_user|password|new'], '', 32, $_HOAM_setting ['user']['minimum_password_length']);
						$HTML .= HOAM_formConfigPassword ('user|password|verify', $_HOAM_language ['field_names']['user']['password']['verify'], $_SESSION['user_information']['trusted_user|password|verify'], '', 32);
						$HTML .= '</fieldset>';
						$HTML .= '<fieldset id="verification|hint">';
						$HTML .= '<label for="user|password|hint-name">' . $_HOAM_language ['field_names']['user']['password']['hint-name'];
						$HTML .= HOAM_formDisplayError ('user|password|hint-name');
						$HTML .= '</label>';
						$HTML .= '<select name="user|password|hint-name">';
						if (isset ($_SESSION['user_information']['trusted_user|password|hint-name'])) {
							$default_phrase = $_SESSION['user_information']['trusted_user|password|hint-name'];
						} else {
							$default_phrase = $user_class -> returnPasswordHintName ($trusted_id);
						}
						foreach ($_HOAM_language ['password_hint_phrases'] as $phrase) {
							$HTML .= "<option ";
							if ($phrase['value'] == $default_phrase) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= "value=\"" . $phrase['value'] . "\">" . $phrase['description'] . "</option>\n";
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formField ('user|password|hint', $_HOAM_language ['field_names']['user']['password']['hint'], $_SESSION['user_information']['trusted_user|password|hint'], $user_class -> returnPasswordHint ($trusted_id), $user_class -> returnColumnSize ("password_hint"), $_HOAM_setting ['user']['minimum_password_hint_length']);
						$HTML .= '</fieldset>';
						$HTML .= '<table style="background: transparent">';
						$HTML .= '<tbody class="nostripe">';
						$HTML .= '<tr><td>';
						$HTML .= HOAM_formSelectMultiple ('user|groups', $_HOAM_language ['field_names']['user']['groups'], 5);
						if (isset ($_SESSION['user_information']['trusted_user|groups'])) {
							$default_groups = $_SESSION['user_information']['trusted_user|groups'];
						} else {
							$member_list = $group_members_class -> listbyMember ($trusted_id);
							$default_groups = array ();
							foreach ($member_list as $member) {
								array_push ($default_groups, $group_members_class -> returnGroupId ($member));
							}
						}
						foreach ($default_groups as $group) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</td><td>';
						$HTML .= '<button type="button" id="user|group|add">&lt;&lt;</button><br />';
						$HTML .= '<button type="button" id="user|group|remove">&gt;&gt;</button>';
						$HTML .= '</td><td>';
						$HTML .= '<label for="user|groups_all">' . $_HOAM_language ['field_names']['user']['groups_all'];
						$HTML .= '</label>';
						$HTML .= '<select multiple="multiple" id="user|groups_list" size="5">';
						$group_list = $group_class -> listAllId ("name", "ASC");
						foreach ($group_list as $group) {
							if (!in_array ($group, $default_groups)) {
								// Only show groups that the current user has access to
								// If the user is root or a system adminstrator, skip
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) or 
								$_HOAM_setting ['user']['ids']['root'] == $_SESSION ['current_user']['id']) {
									$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
								} elseif ($group_members_class -> isMember ($group, $_SESSION['current_user']['id'])) {
									$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
								}
							}
						}
						$HTML .= '</select>';
						$HTML .= '</td></tr>';
						$HTML .= '</tbody>';
						$HTML .= '</table>';
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= '<fieldset id="personal">';
						$HTML .= HOAM_formField ('user|name|first', $_HOAM_language ['field_names']['user']['name']['first'], $_SESSION['user_information']['trusted_user|name|first'], $user_class -> returnFirstName ($trusted_id), $user_class -> returnColumnSize ("firstname"), $_HOAM_setting ['user']['minimum_firstname_length']);
						$HTML .= HOAM_formField ('user|name|last', $_HOAM_language ['field_names']['user']['name']['last'], $_SESSION['user_information']['trusted_user|name|last'], $user_class -> returnLastName ($trusted_id), $user_class -> returnColumnSize ("lastname"), $_HOAM_setting ['user']['minimum_lastname_length']);
						if ($_HOAM_setting ['user']['email_validation']) {
							$HTML .= '</div>';
							$HTML .= '<div class="article_note RHS">' . $_HOAM_language ['field_names']['user']['email_validation'] . '</div>';
							$HTML .= '<div class="mandatory">';
						}
						$HTML .= HOAM_formField ('user|email', $_HOAM_language ['field_names']['user']['email'], $_SESSION['user_information']['trusted_user|email'], $user_class -> returnEmail ($trusted_id), $user_class -> returnColumnSize ("email"));
						if ($_HOAM_setting ['user']['email_validation']) {
							$HTML .= '</div>';
							$HTML .= '<div class="optional">';
						}
						$HTML .= '</fieldset>';
						$HTML .= '<label for="user|resident">' . $_HOAM_language ['field_names']['user']['resident'];
						$HTML .= HOAM_formDisplayHelp ('user|resident');
						$HTML .= HOAM_formDisplayError ('user|resident');
						$homeowner_id = $user_class -> returnHomeownerId ($trusted_id);
						$lot_id = $homeowner_class -> returnLotId ($homeowner_id);
						// Does this user have a homeowner_id associated with them?
						if ($homeowner_id) {
							// Is this user a validated resident or homeowner in the neighborhood?
							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['resident'], $trusted_id) OR
								$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $trusted_id)) {
								$validated_resident = TRUE;
							} else {
								$validated_resident = FALSE;
								$HTML .= '<span class="warning">';
								// The user has a homeowner_id, but has not been added to either the resident or homeowner group yet.
								$HTML .= $_HOAM_language ['errors']['user']['not-validated-resident'];
								$HTML .= '</span>';
							}
						} else {
							$validated_resident = FALSE;
						}
						$HTML .= '</label>';
						$HTML .= '<input ';
						// If the user has been validated, then we want to show them the address information, but disable the input forms allowing them to change their address.
						if ($validated_resident OR
						(isset ($_SESSION['user_information']['trusted_user|resident']) AND $_SESSION['user_information']['trusted_user|resident']) OR
						$homeowner_id) {
							$HTML .= 'checked ';
						}
						if ($validated_resident) {
							$HTML .= 'disabled ';
						}
						$HTML .= 'type="checkbox" id="user|resident" name="user|resident" value="1" />';
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '<div class="mandatory" id="user|show-address"';
						if ($validated_resident OR
							(isset ($_SESSION['user_information']['trusted_user|resident']) AND $_SESSION['user_information']['trusted_user|resident']) OR
							$homeowner_id) {
						} else {
							$HTML .= 'style="display: none"';
						}
						$HTML .= '>';
						$HTML .= '<fieldset>';
						$HTML .= '<label for="user|address">' . $_HOAM_language ['field_names']['user']['address'];
						$HTML .= HOAM_formDisplayHelp ('user|address');
						$HTML .= HOAM_formDisplayError ('user|address');
						$HTML .= '</label>';
						$HTML .= '<select ';
						if ($validated_resident) {
							$HTML .= 'disabled ';
						}
						$HTML .= 'name="user|address">';
						if (isset ($_SESSION['user_information']['trusted_user|address'])) {
							$default_lot = $_SESSION['user_information']['trusted_user|address'];
						} elseif (isset ($lot_id) AND $lot_id) {
							$default_lot = $lot_id;
						} else {
							$default_lot = NULL;
						}
						$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
						foreach ($lot_list as $lot) {
							$HTML .= '<option ';
							if ($lot['id'] == $default_lot) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
							$HTML .= '</option>';
						}
						$HTML .= '</select>';
						if ($default_lot != NULL) {
							$HTML .= '&nbsp;&nbsp;' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($default_lot))));
							if ((int) $homeowner_class -> returnFlags ($homeowner_class -> returnCurrentOwner ($default_lot)) & (int) $_HOAM_setting ['homeowner']['flags']['resident']) {
							} else {
								$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['abbr']['lr']) . '">R</span> ';
							}
							
						}
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Edit user tab

						$HTML .= '<div class="tabbertab" id="user|customization" title="' . $_HOAM_language ['form_tabs']['user']['customization'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<fieldset id="advanced">';
						$HTML .= '<label for="user|messageboard|options">' . $_HOAM_language ['field_names']['user']['messageboard']['options'];
						$HTML .= '</label>';
						$HTML .= '<fieldset id="user|messageboard|options" class="advanced_options">';
						$HTML .= HOAM_formCheckbox ('user|messageboard|acronyms', $_HOAM_language ['field_names']['user']['messageboard']['acronyms'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['acronyms']), $user_class -> returnPreferenceAcronyms ($trusted_id));
						$HTML .= HOAM_formCheckbox ('user|messageboard|censor', $_HOAM_language ['field_names']['user']['messageboard']['censor'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['censor']), $user_class -> returnPreferenceCensor ($trusted_id));
						$HTML .= HOAM_formCheckbox ('user|messageboard|links', $_HOAM_language ['field_names']['user']['messageboard']['links'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['convert_links']), $user_class -> returnPreferenceLinks ($trusted_id));
						$HTML .= HOAM_formCheckbox ('user|messageboard|smileys', $_HOAM_language ['field_names']['user']['messageboard']['smileys'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['smileys']), $user_class -> returnPreferenceSmileys ($trusted_id));
						$HTML .= '</fieldset>';
						$HTML .= '<label for="user|display|options">' . $_HOAM_language ['field_names']['user']['display']['options'];
						$HTML .= '</label>';
						$HTML .= '<fieldset id="user|display|options" class="advanced_options">';
						$HTML .= HOAM_formCheckbox ('user|display|holidays', $_HOAM_language ['field_names']['user']['display']['holidays'], 1, isset ($_SESSION['user_information']['trusted_user|preference']) AND ($_SESSION['user_information']['trusted_user|preference'] & $_HOAM_setting ['user']['flags']['holidays']), $user_class -> returnPreferenceHolidays ($trusted_id));
						$HTML .= '</fieldset>';
						$HTML .= HOAM_formField ('user|edit_cols', $_HOAM_language ['field_names']['user']['edit_cols'], $_SESSION['user_information']['trusted_user|edit_cols'], $user_class -> returnPreferenceEditCol ($trusted_id), 5, 40);
						$HTML .= HOAM_formField ('user|edit_rows', $_HOAM_language ['field_names']['user']['edit_rows'], $_SESSION['user_information']['trusted_user|edit_rows'], $user_class -> returnPreferenceEditRow ($trusted_id), 5, 2);
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End optional settings tab

						$HTML .= '<div class="tabbertab" id="user_postings" title="' . $_HOAM_language ['form_tabs']['user']['postings'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<table>';
						$HTML .= '<colgroup>';
						$HTML .= '<col width="20%" />';
						$HTML .= '<col />';
						$HTML .= '<col width="15%" />';
						$HTML .= '</colgroup>';
						$HTML .= '<thead>';
						$HTML .= '<tr><th>' . $_HOAM_language ['field_names']['messageboard']['date'] . '</th><th>' . $_HOAM_language ['common']['subject'] . '</th><th>' . $_HOAM_language ['field_names']['messageboard']['replies'] . '</th></tr>';
						$HTML .= '</thead>';
						$HTML .= '<tbody id="user_messageboard_postings">';
						$messageboard_class = new HOAM_messageboard ('messageboard');
						$message_list = $messageboard_class -> listAuthor ($trusted_id);
						if (!empty ($message_list)) {
							foreach ($message_list as $message) {
								$HTML .= '<tr>';
								$HTML .= '<td style="text-align: center">' . HOAM_date ($_HOAM_country ['date']['format_long'], strtotime ($message['datecreated'])) . '</td>';
								$HTML .= '<td><a href="/apps/messageboard/view/' . $message['id'] . '/">' . $message['subject'] . '</a></td>';
								$HTML .= '<td style="text-align: center">' . $message['replies'] . '</td>';
								$HTML .= '</tr>';
							}
						} else {
							$HTML .= '<tr><td style="text-align: center" colspan="3">' . $_HOAM_language['errors']['messageboard']['no-messages-found'] . '</td></tr>';
						}
						$HTML .= '</tbody>';
						$HTML .= '</table>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End messageboard postings tab

						$HTML .= '<div class="tabbertab" id="user_groups" title="' . $_HOAM_language ['form_tabs']['user']['group_membership'] . '">';
						$HTML .= '<div class="optional">' . $_HOAM_language ['help_pages']['user']['group_membership'];
						$group_list = $group_class -> listAll (NULL, 'name ASC');
						$HTML .= '<table class="center">';
						$thead = '<thead>';
						$thead .= '<tr><th>' . $_HOAM_language ['field_names']['group']['name'] . '</th><th>' . $_HOAM_language ['field_names']['group']['member_count'] = 'Membership Count' . '</th></tr>';
						$thead .= '</thead>';
						$HTML .= $thead;
						$HTML .= preg_replace ('/thead/', 'tfoot', $thead);
						$HTML .= '<tbody>';
						foreach ($group_list as $group) {
							if ($group_members_class -> isMember ($group['id'], $trusted_id)) {
								$HTML .= '<tr>';
								$HTML .= '<td>' . $group['name'] . '</td>';
								$HTML .= '<td>' . $group_members_class -> returnCountMembers ($group['id']) . '</td>';
								$HTML .= '</tr>';
							}
						}
						$HTML .= '</table>';
						$HTML .= '</div>';					
						$HTML .= '</div>'; // End User Group Membership

						$HTML .= '<div class="tabbertab" id="user_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End User History

						$HTML .= '</div>'; // End tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /website/user/list/');
					exit ();
				}
			} else {
				$HTML = '<img src="/hoam/images/icons/user_edit.png" alt="' . $_HOAM_language ['field_names']['account']['edit'] . '" class="LHS" /><a href="/website/user/edit/">' . $_HOAM_language ['field_names']['user']['edit'] . '</a>';
			}
			break;
		case 'list' :
			$_HOAM_log -> add ('Loading /website/user/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['list'] . '</h1>';
				$messageboard_class = new HOAM_messageboard ('messageboard');
				$user_list = $user_class -> listAll (NULL, 'accountname ASC');
				$active = $count = count ($user_list);
				$thead = '<thead>';
				$thead .= '<tr>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['user']['accountname'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['user']['name'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['created'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['user']['last_login'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['messageboard']['posting_count'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['flags'] . '</th>';
				$thead .= '</tr>';
				$thead .= '</thead>';
				$tbody = '<tbody>';
				foreach ($user_list as $user) {
					$tbody .= '<tr>';
					$tbody .= '<td>' . html_encode ($user['accountname']) . '</td>';
					$tbody .= '<td>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$tbody .= '<small><a href="/website/user/edit/' . $user['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
					}
					$tbody .= ' &nbsp; ';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_delete'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$tbody .= '<small><a class="error" href="/website/user/delete/' . $user['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
					}
					$tbody .= '</td>';
					$tbody .= '<td>' . $user['firstname'] . ' ' . $user['lastname'] . '</td>';
					$tbody .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($user['datecreated'])) . '</td>';
					$tbody .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'] . ' ' . $_HOAM_country ['time']['format_short'], strtotime ($user['datelastlogin'])) . '</td>';
					$tbody .= '<td>' . $messageboard_class -> returnCountAuthor ($user['id']) . '</td>';
					$tbody .= '<td>';
					if ((int) $user['flags'] & (int) $_HOAM_setting ['user']['flags']['disabled']) {
						$tbody .= '<span class="caution cursor_help" title="' . $_HOAM_language ['field_names']['user']['flags']['disabled'] . '">D</span> ';
						$active--;
					}
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $user['id'])) {
						$tbody .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['user']['homeowner'] . '">H</span> ';
					}
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $user['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $user['id'])) {
						$tbody .= '<span class="cursor_help complete" title="' . $_HOAM_language ['field_names']['group']['ids']['officer'] . ' / ' . $_HOAM_language ['field_names']['group']['ids']['board'] . '">O</span> ';
					}
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $user['id'])) {
						$tbody .= '<span class="cursor_help warning" title="' . $_HOAM_language ['field_names']['group']['ids']['admin'] . '">S</span> ';
					}
					// Don't bother checking if the user hasn't requested
					// validation
					if ($user['homeowner_id']) {
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $user['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['resident'], $user['id'])) {
						} else {
							// The user has a homeowner_id, but has not been added to either the resident or homeowner group yet.
							$tbody .= '<span class="cursor_help less-opacity" title="' . $_HOAM_language ['field_names']['user']['flags']['validate'] . '">V</span> ';
						}
					}
					$tbody .= '</td>';
					$tbody .= '</tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= '<p>There are ' . $count . ' users (' . $active . ' active) in the system.</p>';
				$HTML .= '<table class="center">' . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody . '</table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['user'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['user'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/user.png" alt="' . $_HOAM_language ['field_names']['admin']['user'] . '" class="LHS" /><a href="/website/user/">' . $_HOAM_language ['field_names']['admin']['user'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_add'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/user_add.png\')"><a href="/website/user/add/">' . $_HOAM_language ['field_names']['user']['add'] . '</a></li>';
					}
//					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
//					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_edit'], $_SESSION['current_user']['id']) OR
//					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
//						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/user_edit.png\')"><a href="/website/user/edit/">' . $_HOAM_language ['field_names']['user']['edit'] . '</a></li>';
//					}
//					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
//					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_delete'], $_SESSION['current_user']['id']) OR
//					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
//						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/user_delete.png\')"><a href="/website/user/delete/">' . $_HOAM_language ['field_names']['user']['delete'] . '</a></li>';
//					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/user.png\')"><a href="/website/user/list/">' . $_HOAM_language ['field_names']['user']['list'] . '</a></li>';
					$HTML .= '</ul>';
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminWiki ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;
	
	$article_class = new HOAM_article ('articles');
	$group_class = new HOAM_group ('group_list');
	$group_members_class = new HOAM_group_members ('group_members');
	$user_class = new HOAM_user ('users');

	require_once (HOAM_PATH_FUNCTION . 'wiki.php');

	switch ($GLOBALS['global_url_array'][2]) {
		case 'add' :
			$_HOAM_log -> add ('Loading /website/wiki/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['wiki_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['wiki']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminWikiJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['wiki']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				// OK, we're checking to see that the article we're trying to
				// edit is the one that we have information on in the
				// article_information variable. If the referrer doesn't match
				// this current page, then we know we came from somewhere else
				// on the site, and thus the information is suspect. Also,
				// trusted_id will /only/ be set if a article was being
				// modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['article_information']['trusted_id']) AND ($GLOBALS['global_url_article_id'] != $_SESSION['article_information']['trusted_id']))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['article_information'])) {
						unset ($_SESSION['article_information']);
					}
				}

				if (isset ($_SESSION['article_information']['trusted_article|redirect']) AND is_true ($_SESSION['article_information']['trusted_article|redirect'])) {
					$redirect = TRUE;
				} else {
					$redirect = FALSE;
				}
				$HTML .= '<form id="article_form" action="/hoam/scripts/article/edit_script.php" method="post">';
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['article']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="article_information" title="' . $_HOAM_language ['form_tabs']['article']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formText ('article|title', $_HOAM_language ['field_names']['article']['title'], $_SESSION['article_information']['trusted_article|title'], '', $article_class -> returnColumnSize ('title'), 1);
				$HTML .= HOAM_formText ('article|urlname', $_HOAM_language ['field_names']['article']['urlname'], $_SESSION['article_information']['trusted_article|urlname'], '', $article_class -> returnColumnSize ('urlname'), 1);
				$HTML .= '</div>';    
				$HTML .= '<fieldset id="hide_if_redirect_1">';
				$HTML .= '<div class="mandatory">';
				$HTML .= '<table style="background: transparent">';
				$HTML .= '<tbody class="nostripe">';
				$HTML .= '<tr><td>';
				$HTML .= HOAM_formSelectMultiple ('article|groups', $_HOAM_language ['field_names']['article']['view_groups'], $_SESSION['current_user']['items_per_page']);
				if (isset ($_SESSION['article_information']['trusted_article|groups'])) {
					$default_groups = $_SESSION['article_information']['trusted_article|groups'];
                } else {
					$default_groups = unserialize ($_HOAM_setting ['wiki']['default_groups']);
                }
				if (!is_array ($default_groups)) {
					$default_groups = array ($_HOAM_setting ['group']['ids']['admin']);
				}
				foreach ($default_groups as $group) {
					$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</td><td>';
				$HTML .= '<button type="button" id="article|group|remove">&lt;&lt;</button><br />';
				$HTML .= '<button type="button" id="article|group|add">&gt;&gt;</button>';
				$HTML .= '</td><td>';
				$HTML .= HOAM_formSelectMultiple ('article|allgroups', $_HOAM_language ['field_names']['article']['all_groups'], $_SESSION['current_user']['items_per_page']);
				$group_list = $group_class -> listAllId ("name", "ASC");
				foreach ($group_list as $group) {
					if (!in_array ($group, $default_groups)) {
						// Only show groups that the current user has access to
						// If the user is root or a system adminstrator, skip
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$_HOAM_setting ['user']['ids']['root'] == $_SESSION ['current_user']['id']) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						} elseif ($group_members_class -> isMember ($group, $_SESSION['current_user']['id'])) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						}
					}
				}
				$HTML .= '</select>';
				$HTML .= '</td></tr>';
				$HTML .= '</tbody>';
				$HTML .= '</table>';
				$HTML .= '</div>';
				$HTML .= '</fieldset>'; // End of hide_if_redirect_1
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formField ('article|root', $_HOAM_language ['field_names']['article']['root'], $_SESSION['article_information']['trusted_article|root'], '', $article_class -> returnColumnSize ('root_id'), 1);
				$HTML .= '<fieldset id="hide_if_redirect_2">';
				$HTML .= HOAM_formTextarea ('article|article', $_HOAM_language ['field_names']['article']['article'], $_SESSION['article_information']['trusted_article|article'], '', $_SESSION['current_user']['edit']['row'], $article_class -> returnColumnSize ('article'), $_HOAM_setting ['article']['minimum_article_length']);
				$HTML .= '</fieldset>'; //End of hide_if_redirect_2
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Create New Article

				$HTML .= '<div class="tabbertab" id="advanced" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '<fieldset id="hide_if_redirect_3">';
				$HTML .= HOAM_formText ('article|keywords', $_HOAM_language ['field_names']['article']['keywords'], $_SESSION['article_information']['trusted_article|keywords'], '', $article_class -> returnColumnSize ('urlname'), 1);
				$HTML .= HOAM_formTextarea ('article|leadin', $_HOAM_language ['field_names']['article']['leadin'], $_SESSION['article_information']['trusted_article|leadin'], '', $_SESSION['current_user']['edit']['row'], $article_class -> returnColumnSize ('leadin'), 0);
				$HTML .= HOAM_formTextarea ('article|summary', $_HOAM_language ['field_names']['article']['summary'], $_SESSION['article_information']['trusted_article|summary'], '', $_SESSION['current_user']['edit']['row'], $article_class -> returnColumnSize ('summary'), 0);
				$HTML .= '</fieldset>'; // End of hide_if_redirect_3
				$HTML .= HOAM_formDate ('article|poststart', $_HOAM_language ['field_names']['article']['poststart'], $_SESSION['article_information']['trusted_article|poststart'], '');
				$HTML .= HOAM_formDate ('article|postend', $_HOAM_language ['field_names']['article']['postend'], $_SESSION['article_information']['trusted_article|postend'], '');

				$HTML .= '<label for="flags">' . $_HOAM_language ['field_names']['article']['flags']['article'];
				$HTML .= '</label>';
				$HTML .= '<fieldset id="flags" class="advanced_options">';
				$HTML .= '<fieldset id="hide_if_redirect_4">';
				$HTML .= HOAM_formCheckbox ('article|comments', $_HOAM_language ['field_names']['article']['flags']['comments'], 1, (int) $_SESSION['article_information']['trusted_article|flags'] & (int) $_HOAM_setting ['article']['flags']['comments'], 0);
				$HTML .= HOAM_formCheckbox ('article|draft', $_HOAM_language ['field_names']['article']['flags']['draft'], 1, (int) $_SESSION['article_information']['trusted_article|flags'] & (int) $_HOAM_setting ['article']['flags']['draft'], 0);
				$HTML .= '</fieldset>'; // End of hide_if_redirect_4 
				$HTML .= HOAM_formCheckbox ('article|redirect', $_HOAM_language ['field_names']['article']['flags']['redirect'], 1, (int) $_SESSION['article_information']['trusted_article|flags'] & (int) $_HOAM_setting ['article']['flags']['redirect'], 0);
				$HTML .= '<fieldset id="show_if_redirect">';
				$HTML .= HOAM_formText ('article|redirect_location', $_HOAM_language ['field_names']['article']['redirect_location'], $_SESSION['article_information']['trusted_article|redirect_location'], '', $article_class -> returnColumnSize ('keywords'), 1);
				$HTML .= '</fieldset>'; // End of show_if_redirect
				$HTML .= '</fieldset>'; // End of flags
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Advanced Options
				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}	
			break;
		case 'delete' :
			$_HOAM_log -> add ('Loading /website/wiki/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['wiki_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_id = $article_class -> superClean ($GLOBALS['global_url_array'][3], $article_class -> returnColumnSize ('id'));
					if ($article_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['wiki']['delete'])));									
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminWikiJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['wiki']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the article we're trying to
						// edit is the one that we have information on in the
						// article_information variable. If the referrer doesn't match
						// this current page, then we know we came from somewhere else
						// on the site, and thus the information is suspect. Also,
						// trusted_id will /only/ be set if a article was being
						// modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['article_information']['trusted_id']) AND ($GLOBALS['global_url_article_id'] != $_SESSION['article_information']['trusted_id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['article_information'])) {
								unset ($_SESSION['article_information']);
							}
						}

						if (isset ($_SESSION['article_information']['trusted_article|redirect']) AND is_true ($_SESSION['article_information']['trusted_article|redirect'])) {
							$redirect = TRUE;
						} else {
							$redirect = FALSE;
						}
						$HTML .= '<form id="article_form" action="/hoam/scripts/article/edit_script.php" method="post">';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['article']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="article_information" title="' . $_HOAM_language ['form_tabs']['article']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formTextDisabled ('article|title', $_HOAM_language ['field_names']['article']['title'], $article_class -> returnTitle ($trusted_id));
						$HTML .= HOAM_formTextDisabled ('article|urlname', $_HOAM_language ['field_names']['article']['urlname'], $article_class -> returnUrlname ($trusted_id));
						$HTML .= '</div>';    
						$HTML .= '<fieldset id="hide_if_redirect_1">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<table style="background: transparent">';
						$HTML .= '<tbody class="nostripe">';
						$HTML .= '<tr><td>';
						$HTML .= HOAM_formSelectMultipleDisabled ('article|groups', $_HOAM_language ['field_names']['article']['view_groups'], $_SESSION['current_user']['items_per_page']);
						if (isset ($_SESSION['article_information']['trusted_article|groups'])) {
							$default_groups = $_SESSION['article_information']['trusted_article|groups'];
						} else {
							$default_groups = unserialize ($article_class -> returnGroups ($trusted_id));
						}
						if (!is_array ($default_groups)) {
							$default_groups = array ($_HOAM_setting ['group']['ids']['admin']);
						}
						foreach ($default_groups as $group) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</td><td>';
						$HTML .= '<button type="button" id="article|group|remove">&lt;&lt;</button><br />';
						$HTML .= '<button type="button" id="article|group|add">&gt;&gt;</button>';
						$HTML .= '</td><td>';
						$HTML .= HOAM_formSelectMultipleDisabled ('article|allgroups', $_HOAM_language ['field_names']['article']['all_groups'], $_SESSION['current_user']['items_per_page']);
						$group_list = $group_class -> listAllId ("name", "ASC");
						foreach ($group_list as $group) {
							if (!in_array ($group, $default_groups)) {
								// Only show groups that the current user has access to
								// If the user is root or a system adminstrator, skip
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
									$_HOAM_setting ['user']['ids']['root'] == $_SESSION ['current_user']['id']) {
									$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
								} elseif ($group_members_class -> isMember ($group, $_SESSION['current_user']['id'])) {
									$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
								}
							}
						}
						$HTML .= '</select>';
						$HTML .= '</td></tr>';
						$HTML .= '</tbody>';
						$HTML .= '</table>';
						$HTML .= '</div>';
						$HTML .= '</fieldset>'; // End of hide_if_redirect_1
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formFieldReadonly ('article|id', $_HOAM_language ['field_names']['article']['id'], $trusted_id, $article_class -> returnColumnSize ('id'), 1);
						$HTML .= HOAM_formFieldDisabled ('article|root', $_HOAM_language ['field_names']['article']['root'], $article_class -> returnRootId ($trusted_id));
						$HTML .= '<fieldset id="hide_if_redirect_2">';
						$HTML .= HOAM_formTextareaDisabled ('article|article', $_HOAM_language ['field_names']['article']['article'], $article_class -> returnArticle ($trusted_id), $_SESSION['current_user']['edit']['row'], $article_class -> returnColumnSize ('article'), $_HOAM_setting ['article']['minimum_article_length']);
						$HTML .= '</fieldset>'; //End of hide_if_redirect_2
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Create New Article

						$HTML .= '<div class="tabbertab" id="advanced" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<fieldset id="hide_if_redirect_3">';
						$HTML .= HOAM_formTextDisabled ('article|keywords', $_HOAM_language ['field_names']['article']['keywords'], $article_class -> returnKeywords ($trusted_id));
						$HTML .= HOAM_formTextareaDisabled ('article|leadin', $_HOAM_language ['field_names']['article']['leadin'], $article_class -> returnLeadin ($trusted_id), $_SESSION['current_user']['edit']['row'], $article_class -> returnColumnSize ('leadin'), 0);
						$HTML .= HOAM_formTextareaDisabled ('article|summary', $_HOAM_language ['field_names']['article']['summary'], $article_class -> returnSummary ($trusted_id), $_SESSION['current_user']['edit']['row'], $article_class -> returnColumnSize ('summary'), 0);
						$HTML .= '</fieldset>'; // End of hide_if_redirect_3
						$HTML .= HOAM_formDateDisabled ('article|poststart', $_HOAM_language ['field_names']['article']['poststart'], $article_class -> returnDatePostStart ($trusted_id));
						$HTML .= HOAM_formDateDisabled ('article|postend', $_HOAM_language ['field_names']['article']['postend'], $article_class -> returnDatePostEnd ($trusted_id));	
						$HTML .= '<label for="flags">' . $_HOAM_language ['field_names']['article']['flags']['article'];
						$HTML .= '</label>';
						$HTML .= '<fieldset id="flags" class="advanced_options">';
						$HTML .= '<fieldset id="hide_if_redirect_4">';
						if (isset ($_SESSION['article_information']['trusted_article|flags'])) {
							$article_flags = $_SESSION['article_information']['trusted_article|flags'];
						} else {
							$article_flags = $article_class -> returnFlags ($trusted_id);
						}
						$HTML .= HOAM_formCheckboxDisabled ('article|comments', $_HOAM_language ['field_names']['article']['flags']['comments'], 1, (int) $article_flags & (int) $_HOAM_setting ['article']['flags']['comments']);
						$HTML .= HOAM_formCheckboxDisabled ('article|draft', $_HOAM_language ['field_names']['article']['flags']['draft'], 1, (int) $article_flags & (int) $_HOAM_setting ['article']['flags']['draft']);
						$HTML .= '</fieldset>'; // End of hide_if_redirect_4 
						$HTML .= HOAM_formCheckboxDisabled ('article|redirect', $_HOAM_language ['field_names']['article']['flags']['redirect'], 1, (int) $article_flags & (int) $_HOAM_setting ['article']['flags']['redirect']);
						$HTML .= '<fieldset id="show_if_redirect">';
						$HTML .= HOAM_formTextDisabled ('article|redirect_location', $_HOAM_language ['field_names']['article']['redirect_location'], $article_class -> returnRedirectLocation ($trusted_id));
						$HTML .= '</fieldset>'; // End of show_if_redirect
						$HTML .= '</fieldset>'; // End of flags
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Advanced Options

						$HTML .= '<div class="tabbertab" id="wiki_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End wiki history
						
						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/homeowner/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}	
			break;
		case 'edit' :
			$_HOAM_log -> add ('Loading /website/wiki/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['wiki_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_id = $article_class -> superClean ($GLOBALS['global_url_array'][3], $article_class -> returnColumnSize ('id'));
					if ($article_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['wiki']['edit'])));									
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminWikiJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['wiki']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the article we're trying to
						// edit is the one that we have information on in the
						// article_information variable. If the referrer doesn't match
						// this current page, then we know we came from somewhere else
						// on the site, and thus the information is suspect. Also,
						// trusted_id will /only/ be set if a article was being
						// modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['article_information']['trusted_id']) AND ($GLOBALS['global_url_article_id'] != $_SESSION['article_information']['trusted_id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['article_information'])) {
								unset ($_SESSION['article_information']);
							}
						}

						if (isset ($_SESSION['article_information']['trusted_article|redirect']) AND is_true ($_SESSION['article_information']['trusted_article|redirect'])) {
							$redirect = TRUE;
						} else {
							$redirect = FALSE;
						}
						$HTML .= '<form id="article_form" action="/hoam/scripts/article/edit_script.php" method="post">';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="article_information" title="' . $_HOAM_language ['form_tabs']['article']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formText ('article|title', $_HOAM_language ['field_names']['article']['title'], $_SESSION['article_information']['trusted_article|title'], $article_class -> returnTitle ($trusted_id), $article_class -> returnColumnSize ('title'), 1);
						$HTML .= HOAM_formText ('article|urlname', $_HOAM_language ['field_names']['article']['urlname'], $_SESSION['article_information']['trusted_article|urlname'], $article_class -> returnURLName ($trusted_id), $article_class -> returnColumnSize ('urlname'), 1);
						$HTML .= '</div>';    
						$HTML .= '<fieldset id="hide_if_redirect_1">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<table style="background: transparent">';
						$HTML .= '<tbody class="nostripe">';
						$HTML .= '<tr><td>';
						$HTML .= HOAM_formSelectMultiple ('article|groups', $_HOAM_language ['field_names']['article']['view_groups'], $_SESSION['current_user']['items_per_page']);
						if (isset ($_SESSION['article_information']['trusted_article|groups'])) {
							$default_groups = $_SESSION['article_information']['trusted_article|groups'];
						} else {
							$default_groups = unserialize ($article_class -> returnGroups ($trusted_id));
						}
						if (!is_array ($default_groups)) {
							$default_groups = array ($_HOAM_setting ['group']['ids']['admin']);
						}
						foreach ($default_groups as $group) {
							$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</td><td>';
						$HTML .= '<button type="button" id="article|group|remove">&lt;&lt;</button><br />';
						$HTML .= '<button type="button" id="article|group|add">&gt;&gt;</button>';
						$HTML .= '</td><td>';
						$HTML .= HOAM_formSelectMultiple ('article|allgroups', $_HOAM_language ['field_names']['article']['all_groups'], $_SESSION['current_user']['items_per_page']);
						$group_list = $group_class -> listAllId ("name", "ASC");
						foreach ($group_list as $group) {
							if (!in_array ($group, $default_groups)) {
								// Only show groups that the current user has access to
								// If the user is root or a system adminstrator, skip
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
									$_HOAM_setting ['user']['ids']['root'] == $_SESSION ['current_user']['id']) {
									$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
								} elseif ($group_members_class -> isMember ($group, $_SESSION['current_user']['id'])) {
									$HTML .= '<option value="' . $group . '">' . $group_class -> returnName ($group) . '</option>';
								}
							}
						}
						$HTML .= '</select>';
						$HTML .= '</td></tr>';
						$HTML .= '</tbody>';
						$HTML .= '</table>';
						$HTML .= '</div>';
						$HTML .= '</fieldset>'; // End of hide_if_redirect_1
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formFieldReadonly ('article|id', $_HOAM_language ['field_names']['article']['id'], $trusted_id, $article_class -> returnColumnSize ('id'), 1);
						$HTML .= HOAM_formField ('article|root', $_HOAM_language ['field_names']['article']['root'], $_SESSION['article_information']['trusted_article|root'], $article_class -> returnRootId ($trusted_id), $article_class -> returnColumnSize ('root_id'), 1);
						$HTML .= '<fieldset id="hide_if_redirect_2">';
						$HTML .= HOAM_formTextarea ('article|article', $_HOAM_language ['field_names']['article']['article'], $_SESSION['article_information']['trusted_article|article'], $article_class -> returnArticle ($trusted_id), $_SESSION['current_user']['edit']['row'], $article_class -> returnColumnSize ('article'), $_HOAM_setting ['article']['minimum_article_length']);
						$HTML .= '</fieldset>'; //End of hide_if_redirect_2
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Create New Article

						$HTML .= '<div class="tabbertab" id="advanced" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<fieldset id="hide_if_redirect_3">';
						$HTML .= HOAM_formText ('article|keywords', $_HOAM_language ['field_names']['article']['keywords'], $_SESSION['article_information']['trusted_article|keywords'], $article_class -> returnKeywords ($trusted_id), $article_class -> returnColumnSize ('urlname'), 1);
						$HTML .= HOAM_formTextarea ('article|leadin', $_HOAM_language ['field_names']['article']['leadin'], $_SESSION['article_information']['trusted_article|leadin'], $article_class -> returnLeadin ($trusted_id), $_SESSION['current_user']['edit']['row'], $article_class -> returnColumnSize ('leadin'), 0);
						$HTML .= HOAM_formTextarea ('article|summary', $_HOAM_language ['field_names']['article']['summary'], $_SESSION['article_information']['trusted_article|summary'], $article_class -> returnSummary ($trusted_id), $_SESSION['current_user']['edit']['row'], $article_class -> returnColumnSize ('summary'), 0);
						$HTML .= '</fieldset>'; // End of hide_if_redirect_3
						$HTML .= HOAM_formDate ('article|poststart', $_HOAM_language ['field_names']['article']['poststart'], $_SESSION['article_information']['trusted_article|poststart'], $article_class -> returnDatePostStart ($trusted_id));
						$HTML .= HOAM_formDate ('article|postend', $_HOAM_language ['field_names']['article']['postend'], $_SESSION['article_information']['trusted_article|postend'], $article_class -> returnDatePostEnd ($trusted_id));	
						$HTML .= '<label for="flags">' . $_HOAM_language ['field_names']['article']['flags']['article'];
						$HTML .= '</label>';
						$HTML .= '<fieldset id="flags" class="advanced_options">';
						$HTML .= '<fieldset id="hide_if_redirect_4">';
						if (isset ($_SESSION['article_information']['trusted_article|flags'])) {
							$article_flags = $_SESSION['article_information']['trusted_article|flags'];
						} else {
							$article_flags = $article_class -> returnFlags ($trusted_id);
						}
						$HTML .= HOAM_formCheckbox ('article|comments', $_HOAM_language ['field_names']['article']['flags']['comments'], 1, (int) $_SESSION['article_information']['trusted_article|flags'] & (int) $_HOAM_setting ['article']['flags']['comments'], (int) $article_flags & (int) $_HOAM_setting ['article']['flags']['comments']);
						$HTML .= HOAM_formCheckbox ('article|draft', $_HOAM_language ['field_names']['article']['flags']['draft'], 1, (int) $_SESSION['article_information']['trusted_article|flags'] & (int) $_HOAM_setting ['article']['flags']['draft'], (int) $article_flags & (int) $_HOAM_setting ['article']['flags']['draft']);
						$HTML .= '</fieldset>'; // End of hide_if_redirect_4 
						$HTML .= HOAM_formCheckbox ('article|redirect', $_HOAM_language ['field_names']['article']['flags']['redirect'], 1, (int) $_SESSION['article_information']['trusted_article|flags'] & (int) $_HOAM_setting ['article']['flags']['redirect'], (int) $article_flags & (int) $_HOAM_setting ['article']['flags']['redirect']);
						$HTML .= '<fieldset id="show_if_redirect">';
						$HTML .= HOAM_formText ('article|redirect_location', $_HOAM_language ['field_names']['article']['redirect_location'], $_SESSION['article_information']['trusted_article|redirect_location'], $article_class -> returnRedirectLocation ($trusted_id), $article_class -> returnColumnSize ('keywords'), 1);
						$HTML .= '</fieldset>'; // End of show_if_redirect
						$HTML .= '</fieldset>'; // End of flags
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Advanced Options
						
						$HTML .= '<div class="tabbertab" id="wiki_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End wiki history
						
						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/homeowner/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}	
			break;
		case 'list' :
			$_HOAM_log -> add ('Loading /website/wiki/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['wiki_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['wiki_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['wiki_edit'], $_SESSION['current_user']['id']) OR
				(($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['wiki']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['wiki']['list'] . '</h1>';
				$article_list = $article_class -> listAll ();
				$colgroup = '<colgroup>';
				$colgroup .= '<col width="10%" />';
				$colgroup .= '<col width="50%" />';
				$colgroup .= '<col width="10%" />';
				$colgroup .= '<col width="15%" />';
				$colgroup .= '<col width="15%" />';
				$colgroup .= '</colgroup>';
				$thead = '<thead><tr>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['date']['created'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['article']['title'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['created_by'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['date']['modified'] . '</th>';
				$thead .= '</tr></thead>';
				$tbody = '<tbody id="article_list">';
				if (is_array ($article_list)) {
					foreach ($article_list as $article) {
						$tbody .= '<tr>';
						$tbody .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($article ['datecreated'])) . '</td>';
						$tbody .= '<td>' . stripslashes ($article ['title']) . '</td>';
						$tbody .= '<td>';
						$tbody .= '<small><a href="' . HOAM_wikiURL ($article['id']) . '">' . $_HOAM_language ['action']['generic']['view'] . '</a></small>';
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['article_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/website/wiki/edit/' . $article['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['article_delete'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a class="error" href="/website/wiki/delete/' . $article['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
						}
						$tbody .= '</td>';
						$username = $user_class -> returnAccountName ($article['user_id']);
						if (!empty ($username)) {
							$tbody .= '<td>' . $user_class -> returnAccountName ($article['user_id']) . '</td>';
						} else {
							$tbody .= '<td class="caution">' . ucfirst ($_HOAM_language ['common']['unknown']) . '</td>';
						}
						if ($article['datecreated'] != $article['datemodified']) {
							$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'] . ' ' . $_HOAM_country['time']['format_long'], strtotime ($article['datemodified'])) . '</td>';
						} else {
							$tbody .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($article ['datemodified'])) . '</td>';
						}
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="5">' . $_HOAM_language['errors']['article']['none-found'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody . '</table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['wiki_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['wiki_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['wiki_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['wiki'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['wiki'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/table.png" alt="' . $_HOAM_language ['field_names']['admin']['wiki'] . '" class="LHS" /><a href="/website/wiki/">' . $_HOAM_language ['field_names']['admin']['wiki'] . '</a>';
					$HTML .= '<ul>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/table_add.png\')"><a href="/website/wiki/add/">' . $_HOAM_language ['field_names']['wiki']['add'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/table_multiple.png\')"><a href="/website/wiki/list/">' . $_HOAM_language ['field_names']['wiki']['list'] . '</a></li>';
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}	
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminAttachmentJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminAttachment.js"></script>';
}

function HOAM_adminCalendarJS () {
	echo '<script type="application/javascript" src="/3rdparty/mattkruse/CalendarPopup_combined_compact.js"></script>';
}

function HOAM_adminConfigJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminConfig.js"></script>';
}

function HOAM_adminDiagJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminDiag.js"></script>';
}

function HOAM_adminGroupJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminGroup.js"></script>';
}

function HOAM_adminNewsJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminNews.js"></script>';
}

function HOAM_adminUpgradeJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminUpgrade.js"></script>';
}

function HOAM_adminUserJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/user.js"></script>';
}

function HOAM_adminWikiJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminWiki.js"></script>';
}

if (isset ($_SESSION['current_user']['id'])) {
	if (isset ($GLOBALS['global_url_array'][1])) {
		switch ($GLOBALS['global_url_array'][1]) {
			case 'advertising' :
				$_HOAM_log -> add ('Loading /website/advertising/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminAdvertising (TRUE);
				break;
			case 'attachment' :
				$_HOAM_log -> add ('Loading /website/attachment/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminAttachment (TRUE);
				break;
			case 'config' :
				$_HOAM_log -> add ('Loading /website/config/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminConfig (TRUE);
				break;
			case 'diag' :
				$_HOAM_log -> add ('Loading /website/diag/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminDiag (TRUE);
				break;
			case 'export' :
				$_HOAM_log -> add ('Loading /website/export/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminExport (TRUE);
				break;
			case 'group' :
				$_HOAM_log -> add ('Loading /website/group/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminGroup (TRUE);
				break;
			case 'import' :
				$_HOAM_log -> add ('Loading /website/import/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminImport (TRUE);
				break;
			case 'log' :
				$_HOAM_log -> add ('Loading /website/log/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminLog (TRUE);
				break;
			case ('news') :
				$_HOAM_log -> add ('Loading /website/news/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminNews (TRUE);
				break;
			case 'plugin' :
				$_HOAM_log -> add ('Loading /website/plugin/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminPlugin (TRUE);
				break;
			case 'user' :
				$_HOAM_log -> add ('Loading /website/user/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminUser (TRUE);
				break;
			case 'wiki' :
				$_HOAM_log -> add ('Loading /website/wiki/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminWiki (TRUE);
				break;
			default:
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['site'])));
		echo '<h1>' . $_HOAM_language ['field_names']['admin']['site'] . '</h1>';
		if (version_compare (HOAM_VERSION, $_HOAM_setting ['hoam']['latest'], '<')) {
			printf ($_HOAM_language ['help_pages']['hoam']['new_version'], $_HOAM_setting['hoam']['latest']);
		}
		echo HOAM_adminWiki ();
		echo HOAM_adminUser ();
		echo HOAM_adminAttachment ();
		echo HOAM_adminNews ();
		echo HOAM_adminGroup ();
		echo HOAM_adminConfig ();
		echo HOAM_adminPlugin ();
		echo HOAM_adminAdvertising ();
		echo HOAM_adminExport ();
//		echo HOAM_adminLog ();
		echo HOAM_adminDiag ();
	}
} else {
	header ('Location: /error/403/');
	exit ();
}

?>
