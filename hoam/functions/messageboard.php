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
 * Questions specific to HOAM should be directed to ARP Realty. Please see
 * the HOAM web site at http://hoam.arprs.com/
 *
 * Some portions of HOAM incorporate ideas and/or code from other sources, and
 * those portions are explicitly mentioned and attributed in the relevant
 * section of HOAM source code. Questions about that code should be directed to
 * the original authors.
 *
 */

function HOAM_insertSmileys ($string) {
	// Takes the given $string and replaces all known text smileys with a
	// graphic image
	// See The Unicode Standard, Version 6.0 Emoticons, Range: 1F600–1F64F
	$smileys = array (
	';)' => '/hoam/images/icons/emoticon_wink.png',
	':(' => '/hoam/images/icons/emoticon_unhappy.png',
	':o' => '/hoam/images/icons/emoticon_surprised.png',
//	'%)' => '/images/icons/smiley/crazy.gif',
	':)' => '/hoam/images/icons/emoticon_smile.png',
//	':|' => '/images/icons/smiley/straight.gif',
	';P' => '/hoam/images/icons/emoticon_tongue.png',
	'X|' => '/hoam/images/icons/emoticon_evilgrin.png',
	':D' => '/hoam/images/icons/emoticon_grin.png'
	);
	foreach ($smileys as $smiley => $image) {
		$smileycode = htmlspecialchars ($smiley);
		$pattern = '/(?<!\S)' . preg_quote ($smileycode, '/') . '((?!\S)|(?=<br \/\>))/';
		$replace = '<img src="' . $image . '" alt="' . $smiley . '" />';
		$string = preg_replace ($pattern, $replace, $string);	
	}
	return ($string);
}

function HOAM_insertLinks ($string) {
	// Don't get fancy and try to use "(ht|f)tp(s)?://" because you can fall
	// prey to hackers trying to use protocol resolution bypass passing just
	// "//www.google.com/" or something similar.
	$string = preg_replace ('`([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])`i', '$1<a href="$2">$2</a>', $string);
	$strring = mb_substr ($string, 1);
	
	return ($string);
}

function HOAM_messageboardDelete ($message_id, $tree_id) {
	// Delete a message that exists in the system.
	
	$messageboard_class = new HOAM_messageboard ('messageboard');
	
	if ($messageboard_class -> idExists ($message_id)) {
		// We need to change all of the replies to a message to reference the
		// current message root. We only have to change the immediate replies
		// because any lower branches will still have valid root references.
		$children_list = $messageboard_class -> listRootId ($message_id);
		$children_count = count ($children_list);
		$message_root_id = $messageboard_class -> returnRootId ($message_id);
		for ($i = 0; $i < $children_count; $i++) {
			$messageboard_class -> changeRootId ($children_list[$i], $message_root_id);
		}
		// OK, we've taken care of the children.
		// Now, decrement the reply count of the root article
		$messageboard_class -> changeReplies ($tree_id, $messageboard_class -> returnReplies ($tree_id) - 1);
		// Finally, remove the message itself.
		$messageboard_class -> deleteId ($message_id);
		return TRUE;
	} else {
		return FALSE;
	}
}

function HOAM_messageboardFilter ($message_id) {
	// Returns a string that contains HTML formatted output.
	global $_HOAM_language, $_HOAM_setting;
	
	$messageboard_class = new HOAM_messageboard ('messageboard');
	$user_class = new HOAM_user ('users');
	
	if ($messageboard_class -> idExists ($message_id)) {
		$HTML = $messageboard_class -> returnArticle ($message_id);
		$HTML = stripslashes (nl2br (str_replace ('\r\n', "\r\n", $HTML)));
		if (isset ($_SESSION['current_user']['id'])) {
			// Find out whether the current user prefers any options
			if ($user_class -> returnPreferenceAcronyms ($_SESSION['current_user']['id'])) {
				$HTML = HOAM_returnAcronym ($HTML);
			}
			if ($user_class -> returnPreferenceCensor ($_SESSION['current_user']['id'])) {
				$HTML = HOAM_censor ($HTML);
			}
			if ($user_class -> returnPreferenceLinks ($_SESSION['current_user']['id'])) {
				$HTML = HOAM_insertLinks ($HTML);
			}
			if ($user_class -> returnPreferenceSmileys ($_SESSION['current_user']['id'])) {
				$HTML = HOAM_insertSmileys ($HTML);
			}
		} else {
			// Assume guests want all options enabled.
			if (is_true ($_HOAM_setting ['user']['default']['acronyms'])) {
				$HTML = HOAM_returnAcronym ($HTML);
			}
			if (is_true ($_HOAM_setting ['user']['default']['censor'])) {
				$HTML = HOAM_censor ($HTML);
			}
			if (is_true ($_HOAM_setting ['user']['default']['convert_links'])) {
				$HTML = HOAM_insertLinks ($HTML);
			}
			if (is_true ($_HOAM_setting ['user']['default']['smileys'])) {
				$HTML = HOAM_InsertSmileys ($HTML);
			}
		}
	} else {
		$HTML = '<p>' . $_HOAM_language ['errors']['generic']['unknown-id'] . '</p>';
	}
	return $HTML;
}

function HOAM_messageboardList ($message_id, $recursion = TRUE) {
	// Returns a string that contains HTML formatted output.
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$messageboard_class = new HOAM_messageboard ('messageboard');
	
	if ($messageboard_class -> idExists ($message_id)) {
		// Are there any children of the $message_id given?
		$children = $messageboard_class -> listRootId ($message_id);
		if ($children != FALSE) {
			$children_count = count ($children);
			$HTML = '<ul style="list-style: none;">';
			for ($i = 0; $i < $children_count; $i++) {
				$HTML .= '<li>' . HOAM_messageboardView ($children[$i]);
				// Check whether we want to recurse, and then check for children
				if ($recursion && $messageboard_class -> returnReplies ($children[$i])) {
					// Children were found.
					$HTML .= HOAM_messageboardList ($children[$i]);
				}
				$HTML .= '</li>';
			}
			$HTML .= '</ul>';
			return $HTML;
		}
	} else {
		$HTML = '<p>' . $_HOAM_language ['errors']['generic']['unknown-id'] . '</p>';
		return $HTML;
	}
}

function HOAM_messageboardQuote ($message_id) {
	// Returns a string containing the given $message_id quoted like is seen
	// when replying to email
	global $_HOAM_language, $_HOAM_setting;
	
	$messageboard_class = new HOAM_messageboard ('messageboard');
	$user_class = new HOAM_user ('users');
	
	if ($messageboard_class -> idExists ($message_id)) {
		$string = (string) NULL;
		// get message
		$message_text = $messageboard_class -> returnArticle ($message_id);
		// find out how many lines current user is editing with
		if (!empty ($_SESSION['current_user']['id'])) {
			$columns = $user_class -> returnPreferenceEditCol ($_SESSION['current_user']['id']);
			if ($user_class -> returnPreferenceCensor ($_SESSION['current_user']['id'])) {
				$message_text = HOAM_censor ($message_text);
			}
		} else {
			$columns = $_HOAM_setting ['user']['default']['num_cols'];
			if (is_true ($_HOAM_setting ['user']['default']['censor'])) {
				$message_text = HOAM_censor ($message_text);
			}
		}
		$message_length = mb_strlen ($message_text);
		$current_position = 0;
		// while there is still text to quote,
		while ($current_position < $message_length) {
			// First, we need to get a $column length mb_substr beginning at the
			// current offset.
			$current_string = mb_substr ($message_text, $current_position, $columns - 2);
			// Then, we need to see whether there's a new line in the current
			// string
			$break_position = mb_strpos ($current_string, "\n");
			if ($break_position != FALSE) {
				// OK, this was a pain in the ass to fix. The whole damn
				// function was written in maybe a half-hour, but this part took
				// several hours to get right. Basically, we're looking for a
				// newline character in order to preserve the original
				// formatting. However, some lines could have both a \r and a
				// \n. In the end, we basically ignore the newline by grabbing
				// the text before it, and moving our pointer to after it.
				$currentline = mb_substr ($current_string, 0, $break_position - 1);
				$current_position += $break_position + 1;
				$string .= '> ' . $currentline . "\n";
			} else {
				// If we're not on the last line of the message, keep wrapping
				// the text.
				if (($current_position + $columns - 2) < $message_length) {
					// If there's not a break, then we need to find the last
					// space in that string.
					$space_position = mb_strrpos ($current_string, ' ');
					if ($space_position != FALSE) {
						// Finally, get a mb_substr of the $message_text starting
						// at current_position and going no further than the
						// last found space.
						$currentline = mb_substr ($current_string, 0, $space_position);
						$current_position += $space_position;
					} else {
						// But, we also need to check and make sure that
						// someone doesn't send us into an infinite loop by
						// using a string longer than $column.
						$currentline = $current_string;
						$current_position += $columns - 2;
					}
					// prepend '> ' to the beginning of each line
				} else {
					$currentline = $current_string;
					$current_position += $columns - 2;
				}
				$string .= '> ' . $currentline . "\n";
			}
		}
		return $string;
	} else {
		return FALSE;
	}
}

function HOAM_messageboardRoot ($message_id) {
	// Returns the top level message board that's being viewed.
	// In other words, don't go all the way to the root, just to the first item
	// in the current tree.
	
	$messageboard_class = new HOAM_messageboard ('messageboard');
	
	if ($messageboard_class -> idExists ($message_id)) {
		while ($message_id != '0') {
			$message_root_id = $message_id;
			$message_id = $messageboard_class -> returnRootId ($message_id);
		}
		return $message_root_id;
	} else {
		return FALSE;
	}
}

function HOAM_messageboardTopics ($board_id) {
	// Returns a string that contains HTML formatted output.
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$messageboard_class = new HOAM_messageboard ('messageboard');
	$user_class = new HOAM_user ('users');
	
	if ($messageboard_class -> idExists ($board_id)) {
		global $_HOAM_country;
		$HTML = '<tr><td>';
		$HTML .= '<a href="/apps/messageboard/view/' . $board_id . '/">' . $messageboard_class -> returnSubject ($board_id) . '</a>';
		$HTML .= '</td>';
		$HTML .= '<td class="center">';
		$HTML .= $messageboard_class -> returnReplies ($board_id);
		$HTML .= '</td>';
		$HTML .= '<td class="center">';
		$HTML .= $messageboard_class -> returnCountTopic ($board_id);
		$HTML .= '</td>';
		$HTML .= '<td class="center"><small>';
		$last_message = $messageboard_class -> listRecent (1, $board_id);
		if ($last_message != FALSE) {
			$last_message = $last_message[0];
			$HTML .= '<a href="/apps/messageboard/view/' . $last_message['id'] . '/">' . stripslashes ($last_message['subject']) . '</a><br />';
			$HTML .= $_HOAM_language ['field_names']['messageboard']['posted_by'] . ': ' . $user_class -> returnAccountName ($last_message ['user_id']) . ' ' . $_HOAM_language ['common']['on'] . ' ' . date ($_HOAM_country ['date']['format_long'], strtotime ($last_message ['datecreated']));
		} else {
			$HTML .= $_HOAM_language ['errors']['messageboard']['no-messages-found'];
		}
		$HTML .= '</small></td>';
		$HTML .= '</tr>';
	} else {
		$HTML = '<p>' . $_HOAM_language ['errors']['generic']['unknown-id'] . '</p>';
	}

	return $HTML;
}

function HOAM_messageboardView ($message_id, $parent_id = NULL) {
	// Returns a string that contains HTML formatted output.
	global $_HOAM_language, $_HOAM_setting;

	$group_members_class = new HOAM_group_members ('group_members');
	$messageboard_class = new HOAM_messageboard ('messageboard');
	$user_class = new HOAM_user ('users');
	
	if ($messageboard_class -> idExists ($message_id)) {
		global $_HOAM_country;
		// Get all the details for the message.
		$message_info = $messageboard_class -> listAll ($message_id);
		$message_info = $message_info [0];
		$HTML  = '<div class="messageboard">';
		$HTML .= '<h1>';
		if ((int) $message_info ['flags'] & $_HOAM_setting ['messageboard']['flags']['display_top']) {
			$HTML .= '<img src="/hoam/images/icons/star.png" height="16" width="16" alt="' . ucfirst ($_HOAM_language ['field_names']['messageboard']['sticky']) . '"/>';
			$HTML .= ucfirst ($_HOAM_language ['field_names']['messageboard']['sticky']) . ' : ';
		}
		$HTML .= stripslashes ($message_info ['subject']) . '</h1>';
		$HTML .= '<h2>' . $_HOAM_language ['field_names']['messageboard']['posted_by'] . ' ';
		if ($user_class -> returnAccountname ($message_info ['user_id']) != '') {
			$HTML .= $user_class -> returnAccountname ($message_info ['user_id']);
		} else {
			$HTML .= '<span class="error less-opacity">' . ucfirst ($_HOAM_language ['common']['unknown']) . '</span>'; 
		}
		$HTML .= ' ' . $_HOAM_language ['common']['on'] . ' ' . date ($_HOAM_country ['date']['format_long'] . ' @ ' . $_HOAM_country ['time']['format_long'], strtotime ($message_info ['datecreated'])) . '</h2>';
		// We copy the message text to a variable because we may perform various
		// actions on it before display.
		$HTML .= HOAM_messageboardFilter ($message_id);
		if ($message_info ['datemodified'] != $message_info ['datecreated']) {
			$HTML .= '<h4>' . ucfirst ($_HOAM_language ['common']['modified']) . ' ' . date ($_HOAM_country ['date']['format_long'] . ' @ ' . $_HOAM_country ['time']['format_long'], strtotime ($message_info ['datemodified']) ) . '</h4>';
		}
		$HTML .= '<div class="message_actions">';
		// If the current user is a member of the registered users group.
		if (isset ($_SESSION['current_user']['id']) AND
			($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['registered'], $_SESSION['current_user']['id'])) AND
			!$messageboard_class -> isBitSet ($message_id, $_HOAM_setting ['messageboard']['flags']['no_more_comments'])) {
			$HTML .= '<a href="/apps/messageboard/reply/' . $message_id . '/" title="' . $_HOAM_language ['field_names']['messageboard']['reply'] . '"><img src="/hoam/images/icons/comment_add.png" alt="[ ' . $_HOAM_language ['common']['reply'] . ' ]" /></a>';
		}
		// Doing a double-refer back to the parentage of the message.
		// There's probably a much cleaner and more correct way to do this,
		// but I'll worry about it later.
		$message_parent = $messageboard_class -> returnRootId ($message_id);
		// We will only be given a $parent_id when the user is viewing the root
		// of the message tree
		if ($parent_id != NULL) {
			$HTML .= '<a href="/apps/messageboard/" title="' . $_HOAM_language ['field_names']['messageboard']['parent'] . '"><img src="/hoam/images/icons/arrow_up.png" alt="[ ' . $_HOAM_language ['common']['parent'] . ' ]" /></a>';
		} else {
			$HTML .= '<a href="/apps/messageboard/view/' . $message_parent . '/" title="' . $_HOAM_language ['field_names']['messageboard']['parent'] . '"><img src="/hoam/images/icons/arrow_up.png" alt="[ ' . $_HOAM_language ['common']['parent'] . ' ]" /></a>';
		}
		// Is there a user logged in?
		if (!empty ($_SESSION['current_user']['id'])) {
			// Is the current user the author of this message?
			if ((($_SESSION['current_user']['id'] == $messageboard_class -> returnUserId ($message_id)) AND
				// Are we still below the time limit allowed for editing
				// previously posted messages? time_to_edit is the length of
				// time (in seconds) that the author of a message is allowed to
				// edit the message after its creation.
				((time () - strtotime ($message_info ['datecreated'])) <= $_HOAM_setting ['messageboard']['default']['time_to_edit'])) OR
				// OR, is the current user a member of the system administrators
				// group? Administrators are always allowed to edit posted
				// messages.
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id'])) {
				// Display the edit message icon.
				$HTML .= '<a href="/apps/messageboard/edit/' . $message_id . '/" title="' . $_HOAM_language ['field_names']['messageboard']['modify'] . '"><img src="/hoam/images/icons/comment_edit.png" alt="[ ' . $_HOAM_language ['common']['modify'] . ' ]" /></a>';
			}
			// Is the user a member of the system administrators group?
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) AND
				!((int) $message_info ['flags'] & (int) $_HOAM_setting ['messageboard']['flags']['do_not_delete'])) {
				// Display a link for deleting the message.
				$HTML .= '<a href="/apps/messageboard/delete/' . $message_id . '/" title="' . $_HOAM_language ['field_names']['messageboard']['delete'] . '"><img src="/hoam/images/icons/comment_delete.png" alt="[ ' . $_HOAM_language ['common']['delete'] . ' ]" /></a>';
			}
		}
		$HTML .= '</div>';
		$HTML .= '</div>';
	} else {
		$HTML = '<p>' . $_HOAM_language ['errors']['generic']['unknown-id'] . '</p>';
	}
	return $HTML;
}

?>
