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

function HOAM_appsMessageboard ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_log, $_HOAM_queue_head, $_HOAM_setting;

	$group_members_class = new HOAM_group_members ('group_members');
	$messageboard_class = new HOAM_messageboard ('messageboard');

	require_once (HOAM_PATH_FUNCTION . 'messageboard.php');
	if (empty ($GLOBALS['global_url_array'][2])) {
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['messageboard_view'], $_SESSION['current_user']['id']) OR
			$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['apps']['messageboard'])));
			if ($h1) {
				$HTML = '<h1>' . $_HOAM_language ['field_names']['apps']['messageboard'] . '</h1>';
				$HTML .= '<table>';
				$HTML .= '<colgroup><col width="40%" /><col width="10%" span="2" /><col width="40%" /></colgroup>';
				$HTML .= '<thead>';
				$HTML .= '<tr>';
				$HTML .= '<th>' . $_HOAM_language ['tables']['column']['messageboard']['board'] . '</th>';
				$HTML .= '<th>' . $_HOAM_language ['tables']['column']['messageboard']['total'] . '</th>';
				$HTML .= '<th>' . $_HOAM_language ['tables']['column']['messageboard']['topics'] . '</th>';
				$HTML .= '<th>' . $_HOAM_language ['tables']['column']['messageboard']['last'] . '</th>';
				$HTML .= '</tr>';
				$HTML .= '</thead>';
				$HTML .= '<tbody id="messageboard_list">';
				$board_list = $messageboard_class -> ListRootId ('0');
				if (is_array ($board_list)) {
					foreach ($board_list as $board) {
						$HTML .= HOAM_messageboardTopics ($board);
					}
				} else {
					$HTML .= '<tr><td colspan="4">' . $_HOAM_language ['errors']['messageboard']['no-boards-found'] . '</td></tr>';
				}
				$HTML .= '</tbody>';
				$HTML .= '</table>';
				$HTML .= '<br/>';
			} else {
				$HTML .= '<ul>';
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/date_magnify.png\')"><a href="/apps/messageboard/">' . $_HOAM_language ['field_names']['apps']['messageboard'] . '</a></li>';
				$HTML .= '</ul>';
			}
		} else {
			header ('Location: /error/401/');
			exit ();
		}
	} else {
		switch ($GLOBALS['global_url_array'][2]) {
			case 'delete' :
				$_HOAM_log -> add ('Loading /apps/messageboard/delete/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['messageboard_delete'], $_SESSION['current_user']['id']) OR
					$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
					if ($GLOBALS['global_url_array'][3]) {
						$trusted_id = $messageboard_class -> superClean ($GLOBALS['global_url_array'][3], $messageboard_class -> returnColumnSize ('id'));
						if ($messageboard_class -> idExists ($trusted_id)) {
							HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['messageboard']['delete'])));
							HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_messageboardJS');
						
							$HTML = '<h1>' . $_HOAM_language ['field_names']['messageboard']['delete'] . '</h1>';
							require_once (HOAM_PATH_FUNCTION . 'forms.php');
							$HTML .= '<form id="messageboard" action="/hoam/scripts/messageboard/edit_script.php" method="post">';
							$HTML .= '<input type="hidden" name="messageboard|id" id="messageboard|id" value="' . $trusted_id . '"></input>';
							$temp_id = $trusted_id;
							while ($temp_id != "0") {
								$message_tree_id = $temp_id;
								$temp_id = $messageboard_class -> returnRootId ($temp_id);
							}
							$HTML .= '<input type="hidden" name="messageboard|tree" value="' . $message_tree_id . '"></input>';
						
							$HTML .= '<fieldset>';
							$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['messageboard']['delete'] . '</button>';
							$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
							$HTML .= '</fieldset>';
							$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						
							$HTML .= '<div class="tabber">';
							$HTML .= '<div class="tabbertab" id="messageboard_post" title="' . $_HOAM_language ['form_tabs']['messageboard']['original'] . '">';
							$HTML .= '<div class="mandatory">';
							$HTML .= HOAM_formTextDisabled ('messageboard|subject', $_HOAM_language ['field_names']['messageboard']['subject'], $messageboard_class -> returnSubject ($trusted_id), $messageboard_class -> returnColumnSize ('subject'));
							$HTML .= HOAM_formTextareaDisabled ('messageboard|message', $_HOAM_language ['field_names']['messageboard']['message'], $messageboard_class -> returnArticle ($trusted_id), $_SESSION['current_user']['edit']['row'], $messageboard_class -> returnColumnSize ('article'));
							$HTML .= '</div>';
							$HTML .= '</div>'; // End New Message Tab

							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
								$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
								$HTML .= '<div class="tabbertab" id="message_options" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
								$HTML .= '<div class="optional">';
								$HTML .= HOAM_formCheckboxDisabled ('messageboard|flags|display_top', $_HOAM_language ['field_names']['messageboard']['flags']['display_top'], 1, $messageboard_class -> isBitSet ($trusted_id, $_HOAM_setting ['messageboard']['flags']['display_top']));
								$HTML .= '<br />';
								$HTML .= '<br />';
								$HTML .= HOAM_formCheckboxDisabled ('messageboard|flags|do_not_delete', $_HOAM_language ['field_names']['messageboard']['flags']['do_not_delete'], 1, $messageboard_class -> isBitSet ($trusted_id, $_HOAM_setting ['messageboard']['flags']['do_not_delete']));
								$HTML .= '<br />';
								$HTML .= '<br />';
								$HTML .= HOAM_formCheckboxDisabled ('messageboard|flags|no_more_comments', $_HOAM_language ['field_names']['messageboard']['flags']['no_more_comments'], 1, $messageboard_class -> isBitSet ($trusted_id, $_HOAM_setting ['messageboard']['flags']['no_more_comments']));
								$HTML .= '<br />';
								$HTML .= '<br />';
								$HTML .= '</div>';
								$HTML .= '</div>'; // End Message Options Tab

								$HTML .= '<div class="tabbertab" id="message_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
								$HTML .= '</div>'; // End Message Options Tab
							}
						
							$HTML .= '</div>'; // End Tabber
							$HTML .= '</form>';
						} else {
							header ('Location: /error/unknown_id/');
							exit ();
						}
					} else {
						header ('Location: /apps/messageboard/');
						exit ();
					}
				} else {
					header ('Location: /error/401/');
					exit ();
			}
				break;
			case 'edit' :
				$_HOAM_log -> add ('Loading /apps/messageboard/edit/ page.', HOAM_MESSAGE_DEBUG);
				// This is changed around slightly because we want to know if
				// the user is the original author of the message, and if so
				// we'll let them edit it.
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_id = $messageboard_class -> superClean ($GLOBALS['global_url_array'][3], $messageboard_class -> returnColumnSize ('id'));
					if ($messageboard_class -> idExists ($trusted_id)) {
						if (($messageboard_class -> returnUserId ($trusted_id) == $_SESSION['current_user']['id']) OR
							($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['messageboard_edit'], $_SESSION['current_user']['id']) OR
							$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id'])) {

							HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['messageboard']['edit'])));
							HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_messageboardJS');

							$HTML = '<h1>' . $_HOAM_language ['field_names']['messageboard']['edit'] . '</h1>';
							require_once (HOAM_PATH_FUNCTION . 'forms.php');
							$HTML .= '<form id="messageboard" action="/hoam/scripts/messageboard/edit_script.php" method="post">';
							$HTML .= '<input type="hidden" name="messageboard|id" id="messageboard|id" value="' . $trusted_id . '"></input>';
							$HTML .= '<input type="hidden" name="messageboard|root" value="' . $messageboard_class -> returnRootId ($trusted_id) . '"></input>';
							$temp_id = $trusted_id;
							while ($temp_id != "0") {
								$message_tree_id = $temp_id;
								$temp_id = $messageboard_class -> returnRootId ($temp_id);
							}
							$HTML .= '<input type="hidden" name="messageboard|tree" value="' . $message_tree_id . '"></input>';
					
							$HTML .= '<fieldset>';
							$HTML .= '<button type="submit" name="edit">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
							$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
							$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
							$HTML .= '</fieldset>';
							$HTML .= $_HOAM_language ['help_popups']['mandatory'];

							$HTML .= '<div class="tabber">';
							$HTML .= '<div class="tabbertab" id="messageboard_post" title="' . $_HOAM_language ['form_tabs']['messageboard']['original'] . '">';
							$HTML .= '<div class="mandatory">';
							$HTML .= HOAM_formText ('messageboard|subject', $_HOAM_language ['field_names']['messageboard']['subject'], $_SESSION['message_information']['trusted_messageboard|subject'], $messageboard_class -> returnSubject ($trusted_id), $messageboard_class -> returnColumnSize ('subject'));
							$HTML .= HOAM_formTextarea ('messageboard|message', $_HOAM_language ['field_names']['messageboard']['message'], $_SESSION['message_information']['trusted_messageboard|message'], $messageboard_class -> returnArticle ($trusted_id), $_SESSION['current_user']['edit']['row'], $messageboard_class -> returnColumnSize ('article'));
							$HTML .= '</div>';
							$HTML .= '</div>'; // End New Message Tab

							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
								$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
								$HTML .= '<div class="tabbertab" id="message_options" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
								$HTML .= '<div class="optional">';
								$HTML .= HOAM_formCheckbox ('messageboard|flags|display_top', $_HOAM_language ['field_names']['messageboard']['flags']['display_top'], 1, $_SESSION['message_information']['trusted_messageboard|flags|display_top'], $messageboard_class -> isBitSet ($trusted_id, $_HOAM_setting ['messageboard']['flags']['display_top']));
								$HTML .= '<br />';
								$HTML .= '<br />';
								$HTML .= HOAM_formCheckbox ('messageboard|flags|do_not_delete', $_HOAM_language ['field_names']['messageboard']['flags']['do_not_delete'], 1, $_SESSION['message_information']['trusted_messageboard|flags|do_not_delete'], $messageboard_class -> isBitSet ($trusted_id, $_HOAM_setting ['messageboard']['flags']['do_not_delete']));
								$HTML .= '<br />';
								$HTML .= '<br />';
								$HTML .= HOAM_formCheckbox ('messageboard|flags|no_more_comments', $_HOAM_language ['field_names']['messageboard']['flags']['no_more_comments'], 1, $_SESSION['message_information']['trusted_messageboard|flags|no_more_comments'], $messageboard_class -> isBitSet ($trusted_id, $_HOAM_setting ['messageboard']['flags']['no_more_comments']));
								$HTML .= '<br />';
								$HTML .= '<br />';
								$HTML .= '</div>';
								$HTML .= '</div>'; // End Message Options Tab
	
								$HTML .= '<div class="tabbertab" id="message_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
								$HTML .= '</div>'; // End Message Options Tab
							}
						
							$HTML .= '</div>'; // End Tabber
							$HTML .= '</form>';
						} else {
							header ('Location: /error/unknown_id/');
							exit ();
						}
					} else {
						header ('Location: /apps/messageboard/');
						exit ();
					}
				} else {
					header ('Location: /error/401/');
					exit ();
				}
				break;
			case 'post' :
				$_HOAM_log -> add ('Loading /apps/messageboard/post/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['messageboard_post'], $_SESSION['current_user']['id']) OR
					$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
					if ($GLOBALS['global_url_array'][3]) {
						$trusted_id = $messageboard_class -> superClean ($GLOBALS['global_url_array'][3], $messageboard_class -> returnColumnSize ('id'));
						if ($messageboard_class -> idExists ($trusted_id)) {
							HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['messageboard']['post'])));
							$HTML = '<h1>' . $_HOAM_language ['field_names']['messageboard']['post'] . '</h1>';
							require_once (HOAM_PATH_FUNCTION . 'forms.php');
							$HTML .= '<form id="messageboard" action="/hoam/scripts/messageboard/edit_script.php" method="post">';
							$HTML .= '<input type="hidden" name="messageboard|root" value="' . $trusted_id . '"></input>';
							$temp_id = $trusted_id;
							while ($temp_id != "0") {
								$message_tree_id = $temp_id;
								$temp_id = $messageboard_class -> returnRootId ($temp_id);
							}
							$HTML .= '<input type="hidden" name="messageboard|tree" value="' . $message_tree_id . '"></input>';
						
							$HTML .= '<fieldset>';
							$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['messageboard']['post'] . '</button>';
							$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
							$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
							$HTML .= '</fieldset>';
							$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						
							$HTML .= '<div class="tabber">';
							$HTML .= '<div class="tabbertab" id="messageboard_post" title="' . $_HOAM_language ['form_tabs']['messageboard']['post'] . '">';
							$HTML .= '<div class="mandatory">';
							$HTML .= HOAM_formText ('messageboard|subject', $_HOAM_language ['field_names']['messageboard']['subject'], $_SESSION['message_information']['trusted_messageboard|subject'], '', $messageboard_class -> returnColumnSize ('subject'));
							$HTML .= HOAM_formTextarea ('messageboard|message', $_HOAM_language ['field_names']['messageboard']['message'], $_SESSION['message_information']['trusted_messageboard|message'], '', $_SESSION['current_user']['edit']['row'], $messageboard_class -> returnColumnSize ('article'));
							$HTML .= '</div>';
							$HTML .= '</div>'; // End New Message Tab

							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
								$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
								$HTML .= '<div class="tabbertab" id="messageboard_options" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
								$HTML .= '<div class="optional">';
								$HTML .= HOAM_formCheckbox ('messageboard|flags|display_top', $_HOAM_language ['field_names']['messageboard']['flags']['display_top'], 1, $_SESSION['message_information']['trusted_messageboard|flags|display_top'], 0);
								$HTML .= '<br />';
								$HTML .= '<br />';
								$HTML .= HOAM_formCheckbox ('messageboard|flags|do_not_delete', $_HOAM_language ['field_names']['messageboard']['flags']['do_not_delete'], 1, $_SESSION['message_information']['trusted_messageboard|flags|do_not_delete'], 0);
								$HTML .= '<br />';
								$HTML .= '<br />';
								$HTML .= HOAM_formCheckbox ('messageboard|flags|no_more_comments', $_HOAM_language ['field_names']['messageboard']['flags']['no_more_comments'], 1, $_SESSION['message_information']['trusted_messageboard|flags|no_more_comments'], 0);
								$HTML .= '<br />';
								$HTML .= '<br />';
								$HTML .= '</div>';
								$HTML .= '</div>'; // End Message Options Tab
							}

							$HTML .= '</div>'; // End Tabber
							$HTML .= '</form>';
						} else {
							header ('Location: /error/unknown_id/');
							exit ();
						}
					} else {
						header ('Location: /apps/messageboard/');
						exit ();
					}
				} else {
					header ('Location: /error/401/');
					exit ();
				}
				break;
			case 'reply' :
				$_HOAM_log -> add ('Loading /apps/messageboard/reply/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['messageboard_post'], $_SESSION['current_user']['id']) OR
					$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
					if ($GLOBALS['global_url_array'][3]) {
						$trusted_id = $messageboard_class -> superClean ($GLOBALS['global_url_array'][3], $messageboard_class -> returnColumnSize ('id'));
						if ($messageboard_class -> idExists ($trusted_id)) {
							HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['messageboard']['reply'])));
							$HTML = '<h1>' . $_HOAM_language ['field_names']['messageboard']['reply'] . '</h1>';
							require_once (HOAM_PATH_FUNCTION . 'forms.php');
							$HTML .= '<form id="messageboard" action="/hoam/scripts/messageboard/edit_script.php" method="post">';
							$HTML .= '<input type="hidden" name="messageboard|root" value="' . $trusted_id . '"></input>';
							$temp_id = $trusted_id;
							while ($temp_id != "0") {
								$message_tree_id = $temp_id;
								$temp_id = $messageboard_class -> returnRootId ($temp_id);
							}
							$HTML .= '<input type="hidden" name="messageboard|tree" value="' . $message_tree_id . '"></input>';
						
							$HTML .= '<fieldset>';
							$HTML .= '<button type="submit" name="reply">' . $_HOAM_language ['form_buttons']['messageboard']['post'] . '</button>';
							$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
							$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
							$HTML .= '</fieldset>';
							$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						
							$HTML .= '<div class="tabber">';
							$HTML .= '<div class="tabbertab" id="messageboard_post" title="' . $_HOAM_language ['form_tabs']['messageboard']['post'] . '">';
							$HTML .= '<div class="mandatory">';
							$HTML .= HOAM_formText ('messageboard|subject', $_HOAM_language ['field_names']['messageboard']['subject'], $_SESSION['message_information']['trusted_messageboard|subject'], $_HOAM_language ['field_names']['messageboard']['re'] . ' ' . $messageboard_class -> returnSubject ($trusted_id), $messageboard_class -> returnColumnSize ('subject'));
							$HTML .= HOAM_formTextarea ('messageboard|message', $_HOAM_language ['field_names']['messageboard']['message'], $_SESSION['message_information']['trusted_messageboard|message'], '', $_SESSION['current_user']['edit']['row'], $messageboard_class -> returnColumnSize ('article'));
							$HTML .= '</div>';
							$HTML .= '</div>'; // End New Message Tab
							$HTML .= '<div class="tabbertab" id="messageboard_original" title="' . $_HOAM_language ['form_tabs']['messageboard']['original'] . '">';
							$HTML .= '<div class="optional">';
							$HTML .= HOAM_formTextareaReadonly ('messageboard|original', $_HOAM_language ['field_names']['messageboard']['original'], HOAM_messageboardQuote ($trusted_id), $_SESSION['current_user']['edit']['row'], $messageboard_class -> returnColumnSize ('article'));
							$HTML .= '</div>';
							$HTML .= '</div>'; // End Quoted Message
							$HTML .= '</div>'; // End Tabber
							$HTML .= '</form>';
						} else {
							header ('Location: /error/unknown_id/');
							exit ();
						}
					} else {
						header ('Location: /apps/messageboard/');
						exit ();
					}
				} else {
					header ('Location: /error/401/');
					exit ();
				}
				break;
			case 'view' :
				$_HOAM_log -> add ('Loading /apps/messageboard/view/ page.', HOAM_MESSAGE_DEBUG);
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_id = $messageboard_class -> superClean ($GLOBALS['global_url_array'][3], $messageboard_class -> returnColumnSize ('id'));
					$_HOAM_log -> add ('Viewing messageboard id: "' . $trusted_id . '".', HOAM_MESSAGE_DEBUG);
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['apps']['messageboard'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['apps']['messageboard'] . '</h1>';
					// Get a list of all messages with the same $root_id
					$message_root_id = $messageboard_class -> returnRootId ($trusted_id);
					// If the message's root_id is 0, then we know this is the next
					// message below the tree; IOW, this message represents a
					// different message 'board', and we want to display the text it
					// contains before displaying messages below it in the tree.
					if ($message_root_id === '0') {
						// Display the text of this message.
						$HTML .= HOAM_messageboardFilter ($trusted_id);
						// Get a list of messages below the current in the tree.
						$messages = $messageboard_class -> listRootId ($trusted_id);
					} else {
						// This isn't a 'board', so just display the message
						// specified normally. We assign the messsage_id to an
						// array, so we can share code
						$messages = array ($trusted_id);
					}
					// Are there any messages to display?
					if ($messages != FALSE) {
						// Yep, do something with them.
						$messages_count = count ($messages);
						for ($i = 0; $i < $messages_count; $i++) {
							if ($message_root_id === '0') {
								$HTML .= HOAM_messageboardView ($messages[$i], $message_root_id);
							} else {
								$HTML .= HOAM_messageboardView ($messages[$i]);
							}
							// List any descendants (ie, replies) to this current
							// message.
							$HTML .= HOAM_messageboardList ($messages[$i]);
						}
					} elseif ($messages == FALSE) {
						$HTML .= '<div style="padding: 1em; text-align: center">' . $_HOAM_language ['errors']['messageboard']['no-messages-found'] .'</div>';
					}
					// Show text about posting new messages.
					if (isset ($_SESSION['current_user']['id']) AND
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['messageboard_post'], $_SESSION['current_user']['id'])) {
						$HTML .= '<div style="padding: 1em; text-align: center">' . sprintf ($_HOAM_language ['errors']['messageboard']['post'], '/apps/messageboard/post/' . HOAM_messageboardRoot ($trusted_id) . '/') . '</div>';
					} elseif (isset ($_SESSION['current_user']['id']) AND
							!$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['registered'], $_SESSION['current_user']['id'])) {
						$HTML .= '<div style="padding: 1em; text-align: center"><span class="caution">' . $_HOAM_language ['field_names']['messageboard']['validation'] . '</span></div>';
					} else {
						$HTML .= '<div style="padding: 1em; text-align: center"><span class="caution">' . $_HOAM_language ['errors']['messageboard']['must_login'] . '</span></div>';
					}
				} else {
					header ('Location: /apps/messageboard/');
					exit ();
				}
				break;
			default:
				break;
		}
	}
	return ($HTML);
}

function HOAM_messageboardJS () {
	echo '<script type="text/javascript" src="/hoam/javascript/messageboard.js"></script>';
}

$group_members_class = new HOAM_group_members ('group_members');

switch ($GLOBALS['global_url_array'][1]) {
	case ('messageboard') :
		$_HOAM_log -> add ('Loading /apps/messageboard/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_appsMessageboard (TRUE);
		break;
	default:
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $GLOBALS['_HOAM_language'] ['field_names']['apps']['apps'])));
		echo '<h1>' . $GLOBALS['_HOAM_language'] ['field_names']['apps']['apps'] . '</h1>';
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['messageboard_view'], $_SESSION['current_user']['id']) OR
			$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
			echo HOAM_appsMessageboard ();
		}
		break;
}

?>
