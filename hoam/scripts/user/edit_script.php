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

require_once ('../../hoam-startup.php');

/*
 * Not only is this one of the more complicated scripts, but it's also
 * probably the most important one to not have any errors in (and to do
 * the most error checking in).
 *
 * Be wary of making changes, and make sure you test any changes made and
 * also regression test to make sure that nothing unexpected pops up
 * (like accidentally adding everyone to the administrators group,
 * screwing up people's passwords, etc.)
 */

session_start ();

if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
	require_once (HOAM_PATH_FUNCTION . 'forms.php');
	require_once (HOAM_PATH_FUNCTION . 'user.php');
	
	$group_class = new HOAM_group ('group_list');
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$user_class = new HOAM_user ('users');

	// If this is the second (or greater) time through the script (ie, there
	// were errors that had to be fixed before committing the changes) get rid
	// of the old cached information the user entered, we're going to populate
	// it with information just received from the form posting.
	if (isset ($_SESSION['user_information'])) {
		unset ($_SESSION['user_information']);
	}
	// Get rid of any old errors, and start over.
	if (isset ($_SESSION['error_class_item'])) {
		unset ($_SESSION['error_class_item']);
	}

	if (isset ($_POST['cancel'])) {
		if (isset ($_SESSION['current_user']['id']) AND
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_add'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_delete'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_edit'], $_SESSION['current_user']['id']) OR
			$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
			header ('Location: /website/user/');
			exit ();
		} else {
			header ('Location: /');
			exit ();
		}
	} elseif (isset ($_POST['delete'])) {
		if (isset ($_SESSION['current_user']['id']) AND
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_delete'], $_SESSION['current_user']['id']) OR
			$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {

			$user_id = (string) $user_class -> superClean ($_POST['user|id'], $user_class -> returnColumnSize ('id'));
			// Make sure we found the user
			if ($user_class -> IdExists ($user_id)) {
				$accountname = $user_class -> returnAccountName ($user_id);
				if ($user_class -> deleteId ($user_id)) {
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['user']['deleted'], $accountname),
										HOAM_MESSAGE_NOTICE,
										array ('owner_id' => $user_id,
												'flags' => $_HOAM_setting ['log']['flags']['user']));
					// Go through and delete all of the group_member entries for
					// this user also.
					$member_list = $group_members_class -> listbyMember ($member_id);
					foreach ($member_list as $member) {
						$group_members_class -> deleteId ($member);
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['group']['removed'], $accountname, $group_class -> returnName ($group_members_class -> returnGroupId ($member))),
											HOAM_MESSAGE_NOTICE,
											array ('owner_id' => $group_members_class -> returnGroupId ($member),
													'flags' => $_HOAM_setting ['log']['flags']['group']));
					}
					header ('Location: /website/user/');
				} else {
					// Error Removing Item
					header ('Location: /error/remove/');
				}
			} else {
				header ('Location: /error/unknown_id/');
			}
		} else {
			// User does not have the necessary permissions.
			header ('Location: /error/403/');
		}
	} else {
		// If we're still here, then we must be adding or modifying an existing
		// user. Start populating the user_information array, and check for
		// errors.
		$_SESSION['user_information'] = array ();
		$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		// The script performs different checks on the password depending on
		// whether the user is modifying an existing account or creating a new
		// account.
		if (isset ($_POST['user|id'])) {
			$_SESSION['user_information']['trusted_user|id'] = (string) $user_class -> superClean ($_POST['user|id'], $user_class -> returnColumnSize ('id'));
			if ($user_class -> idExists ($_SESSION['user_information']['trusted_user|id'])) {
			} else {
				$_SESSION['error_class_item'] -> addError ('user|accountname', $_HOAM_language ['errors']['generic']['invalid-id']);
			}
		}
		if (isset ($_POST['user|accountname'])) {
			$_SESSION['user_information']['trusted_user|accountname'] = (string) $user_class -> superClean ($_POST['user|accountname'], $user_class -> returnColumnSize ('accountname'));
			// Does the accountname contain any strange chars? (This check
			// currently breaks / ignores i8n concerns)
			$matches = array();
			preg_match_all ('/[a-zA-Z0-9\-\.@_]+/', $_SESSION['user_information']['trusted_user|accountname'], $matches);
			if ($_SESSION['user_information']['trusted_user|accountname'] == $matches[0][0]) {
			} else {
				$_SESSION['error_class_item'] -> addError ('user|accountname', $_HOAM_language ['errors']['newuser']['accountname']['invalid-chars']);
			}
			unset ($matches);
			// Make sure the account name isn't too short.
			if (mb_strlen ($_SESSION['user_information']['trusted_user|accountname']) >= $_HOAM_setting ['user']['minimum_accountname_length']) {
			} else {
				// The accountname is too short.
				$_SESSION ['error_class_item'] -> addError ('user|accountname', sprintf ($_HOAM_language ['errors']['newuser']['accountname']['too-short'], $_HOAM_setting ['user']['minimum_accountname_length']));
			}
			if (HOAM_userNameBanned ($_SESSION['user_information']['trusted_user|accountname'])) {
				// The accountname chosen has been banned. Check to see whether
				// the user (if logged in) already has a banned name (such as
				// the admin user changing his own password)
				if (isset ($_SESSION['current_user']['id']) AND HOAM_userNameBanned ($user_class -> returnAccountName ($_SESSION['current_user']['id']))) {
				} else { 
					$_SESSION ['error_class_item'] -> addError ('user|accountname', $_HOAM_language ['errors']['newuser']['accountname']['banned']);
				}
			}
			// Make sure the account name doesn't already exist in the system.
			if ($user_class -> accountExists ($_SESSION['user_information']['trusted_user|accountname'])) {
				// Added check for NULL, otherwise it was allowing duplicate
				// user names.
				if (($_SESSION['current_user']['id'] != NULL) AND ($_SESSION['current_user']['id'] == $_SESSION['user_information']['trusted_user|id'])) {
					// The user must have changed their account name.
					//
					// NOTE
					//
					// Currently, if a user changes their account name, and
					// they change the first two letters, it will cause their
					// password to not work because the salt will be different.
				} elseif ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
					// This must be an administrator modifying another account.
				} else {
					$_SESSION['error_class_item'] -> addError ('user|accountname', sprintf ($_HOAM_language ['errors']['newuser']['accountname']['in-use'], $_SESSION['user_information']['trusted_user|accountname']));
				}
			}

		}
		/*		if ($_SESSION['user_information']['trusted_user|id'] == ($user_class -> returnId ($_SESSION['user_information']['trusted_user|accountname']))) {
		} else {
		$_SESSION['error_class_item'] -> addError ('user|accountname', $_HOAM_language ['errors']['user']['accountname']['id-not-match']);
		}*/
		// There's a possibility that someone maliciously tries to change the
		// user id and accountname to make them match, thus trying to hack
		// someone else's account. This is checked below.
		if (isset ($_POST['modify'])) {
			// Even if the account account is being modified, if the user
			// didn't select to change their password, don't even check.
			if (isset ($_POST['user|password|show-change']) && ((int) $_POST['user|password|show-change'] === 1)) {
				// Verify that the old password is correct.
				if (isset ($_POST['user|password|old'])) {
					$_SESSION['user_information']['trusted_user|password|old'] = (string) $user_class -> superClean ($_POST['user|password|old'], $user_class -> returnColumnSize ('password'));
					if (!$user_class -> authenticateUser ($_SESSION['user_information']['trusted_user|accountname'], $_SESSION['user_information']['trusted_user|password|old'])) {
						// The password given was incorrect.
						$_SESSION['error_class_item'] -> addError ('user|password|old', $_HOAM_language ['errors']['user']['password']['incorrect']);
					}
				}
				// Check that the new password is different from the old
				// password. First, make sure the old password they entered was
				// correct.
				if (!$_SESSION['error_class_item'] -> ShowCategory ('user|password|old')) {
					if ($_POST['user|password|new'] == $_POST['user|password|old']) {
						$_SESSION['error_class_item'] -> addError ('user|password|new', $_HOAM_language ['errors']['user']['password']['same-as-old']);
					}
				}
				// Since we're modifying the account, the user has the option
				// of whether or not to change their password. If they don't
				// change, then the passwords will be blank. We'll check
				// whether this is the case when committing the changes.
				if (isset ($_POST['user|password|new'])) {
					$_SESSION['user_information']['trusted_user|password|new'] = (string) $user_class -> superClean ($_POST['user|password|new'], $user_class -> returnColumnSize ('password'));
					if ((mb_strlen ($_SESSION['user_information']['trusted_user|password|new']) > 0) && (mb_strlen ($_SESSION['user_information']['trusted_user|password|new']) < $_HOAM_setting ['user']['minimum_password_length'])) {
						$_SESSION['error_class_item'] -> addError ('user|password|new', sprintf ($_HOAM_language ['errors']['user']['password']['too-short'], $_HOAM_setting ['user']['minimum_password_length']));
					}
				}
				if (isset ($_POST['user|password|verify'])) {
					$_SESSION['user_information']['trusted_user|password|verify'] = (string) $user_class -> superClean ($_POST['user|password|verify'], $user_class -> returnColumnSize ('password'));
					if (strcmp ($_SESSION['user_information']['trusted_user|password|new'], $_SESSION['user_information']['trusted_user|password|verify'])) {
						$_SESSION['error_class_item'] -> addError ('user|password|verify', $_HOAM_language ['errors']['user']['password']['not-match']);
					}
				}
			}
		} else {
			if (isset ($_POST['user|password|new'])) {
				$_SESSION['user_information']['trusted_user|password|new'] = (string) $user_class -> superClean ($_POST['user|password|new'], $user_class -> returnColumnSize ('password'));
				if (mb_strlen ($_SESSION['user_information']['trusted_user|password|new']) < $_HOAM_setting ['user']['minimum_password_length']) {
					$_SESSION['error_class_item'] -> addError ('user|password|new', sprintf ($_HOAM_language ['errors']['user']['password']['too-short'], $_HOAM_setting ['user']['minimum_password_length']));
				}
			}
			if (isset ($_POST['user|password|verify'])) {
				$_SESSION['user_information']['trusted_user|password|verify'] = (string) $user_class -> superClean ($_POST['user|password|verify'], $user_class -> returnColumnSize ('password'));
				if (strcmp ($_SESSION['user_information']['trusted_user|password|new'], $_SESSION['user_information']['trusted_user|password|verify'])) {
					$_SESSION['error_class_item'] -> addError ('user|password|verify', $_HOAM_language ['errors']['user']['password']['not-match']);
				}
			}
		}
		if (isset ($_POST['user|name|first'])) {
			$_SESSION['user_information']['trusted_user|name|first'] = (string) $user_class -> superClean ($_POST['user|name|first'], $user_class -> returnColumnSize ('firstname'));
			// We don't require the user to provide a first name, so we only
			// check the length if it's provided.
			if ((mb_strlen ($_SESSION['user_information']['trusted_user|name|first']) > 0 ) && (mb_strlen ($_SESSION['user_information']['trusted_user|name|first']) < $_HOAM_setting ['user']['minimum_firstname_length'])) {
				$_SESSION['error_class_item'] -> addError ('user|name|first', sprintf ($_HOAM_language ['errors']['user']['name']['first']['too-short'], $_HOAM_setting ['user']['minimum_firstname_length']));
			}
		}
		if (isset ($_POST['user|name|last'])) {
			$_SESSION['user_information']['trusted_user|name|last'] = (string) $user_class -> superClean ($_POST['user|name|last'], $user_class -> returnColumnSize ('lastname'));
			// We don't require the user to provide a last name, so we only
			// check the length if it's provided.
			if ((mb_strlen ($_SESSION['user_information']['trusted_user|name|last']) > 0) && (mb_strlen ($_SESSION['user_information']['trusted_user|name|last']) < $_HOAM_setting ['user']['minimum_lastname_length'])) {
				$_SESSION['error_class_item'] -> addError ('user|name|last', sprintf ($_HOAM_language ['errors']['user']['name']['last']['too-short'], $_HOAM_setting ['user']['minimum_lastname_length']));
			}
		}
		if (isset ($_POST['user|password|hint-name'])) {
			$_SESSION['user_information']['trusted_user|password|hint-name'] = (string) $user_class -> superClean ($_POST['user|password|hint-name'], $user_class -> returnColumnSize ('password_hint_name'));
		}
		if (isset ($_POST['user|password|hint'])) {
			$_SESSION['user_information']['trusted_user|password|hint'] = (string) $user_class -> superClean ($_POST['user|password|hint'], $user_class -> returnColumnSize ('password_hint'));
			if (mb_strlen ($_SESSION['user_information']['trusted_user|password|hint']) < $_HOAM_setting ['user']['minimum_password_hint_length']) {
				$_SESSION['error_class_item'] -> addError ('user|password|hint', sprintf ($_HOAM_language ['errors']['user']['password']['hint']['too-short'], $_HOAM_setting ['user']['minimum_password_hint_length']));
			}

		}
		if (isset ($_POST['user|email'])) {
			$_SESSION['user_information']['trusted_user|email'] = (string) $user_class -> superClean ($_POST['user|email'], $user_class -> returnColumnSize ('email'));
			// If email validation is enabled, return an error if we haven't
			// been given a valid address.
			if (is_true ($_HOAM_setting ['user']['email_validation'])) {
				if (!filter_var ($_SESSION['user_information']['trusted_user|email'], FILTER_VALIDATE_EMAIL)) {
					$_SESSION['error_class_item'] -> addError ('user|email', $_HOAM_language ['errors']['user']['email']['invalid']);
				}
			// Even if validation isn't required, make sure any address provided
			// is correctly formatted.
			} elseif (mb_strlen ($_SESSION['user_information']['trusted_user|email']) > 0) {
				if (!filter_var ($_SESSION['user_information']['trusted_user|email'], FILTER_VALIDATE_EMAIL)) {
					$_SESSION['error_class_item'] -> addError ('user|email', $_HOAM_language ['errors']['user']['email']['invalid']);
				}
			}
		}

		if (isset ($_POST['user|flags|disabled'])) {
			$_SESSION['user_information']['trusted_user|flags|disabled'] = (int) $_POST['user|flags|disabled'];
		}
		
		if (isset ($_POST['newuser|heard-about'])) {
			$_SESSION['user_information']['trusted_newuser|heard-about'] = $user_class -> superClean ($_POST['newuser|heard-about'], 255);
		}

		if (isset ($_POST['user|resident'])) {
			$_SESSION['user_information']['trusted_user|resident'] = TRUE;
			if (isset ($_POST['user|address'])) {
				$_SESSION['user_information']['trusted_user|address'] = (int) $_POST['user|address'];
				if ($lot_class -> idExists ($_SESSION['user_information']['trusted_user|address'])) {
				} else {
					$_SESSION['error_class_item'] -> addError ('user|address', $_HOAM_language ['errors']['user']['address']['unknown']);
				}
			}
		}
		if (isset ($_POST['user|comments'])) {
			$_SESSION['user_information']['trusted_user|comments'] = (string) $user_class -> superClean ($_POST['user|comments'], $user_class -> returnColumnSize ('comments'));
		}

		// Continue with the setting of preferences, etc ... Start by going
		// back to the default and adding on.
		$_SESSION['user_information']['trusted_user|preference'] = 0;

		if (isset ($_POST['user|messageboard|acronyms'])) {
			$_SESSION['user_information']['trusted_user|preference'] += $_HOAM_setting ['user']['flags']['acronyms'];
		}
		if (isset ($_POST['user|messageboard|censor'])) {
			$_SESSION['user_information']['trusted_user|preference'] += $_HOAM_setting ['user']['flags']['censor'];
		}
		if (isset ($_POST['user|messageboard|links'])) {
			$_SESSION['user_information']['trusted_user|preference'] += $_HOAM_setting ['user']['flags']['convert_links'];
		}
		if (isset ($_POST['user|messageboard|smileys'])) {
			$_SESSION['user_information']['trusted_user|preference'] += $_HOAM_setting ['user']['flags']['smileys'];
		}
		if (isset ($_POST['user|display|holidays'])) {
			$_SESSION['user_information']['trusted_user|preference'] += $_HOAM_setting ['user']['flags']['holidays'];
		}
		if (isset ($_POST['user|display|log'])) {
			$_SESSION['user_information']['trusted_user|preference'] += $_HOAM_setting ['user']['flags']['log'];
		}

		if (isset ($_POST['user|edit_cols'])) {
			$_SESSION['user_information']['trusted_user|edit_cols'] = (int) $user_class -> superClean ($_POST['user|edit_cols'], $user_class -> returnColumnSize ('preference_edit_col'));
			HOAM_formValidateValue ('user|edit_cols', $_SESSION['user_information']['trusted_user|edit_cols'], $_SESSION['error_class_item'], 40, 120);
		}
		if (isset ($_POST['user|edit_rows'])) {
			$_SESSION['user_information']['trusted_user|edit_rows'] = (int) $user_class -> superClean ($_POST['user|edit_rows'], $user_class -> returnColumnSize ('preference_edit_row'));
			HOAM_formValidateValue ('user|edit_rows', $_SESSION['user_information']['trusted_user|edit_rows'], $_SESSION['error_class_item'], 2, 40);
		}
		
		/*
		if (isset ($_POST['preference_holiday2'])) {
		$_SESSION['user_information']['trusted_preference'] += PREFERENCE_MASK_HOLIDAY_COLOR;
		}
		if (isset ($_POST['preference_holiday3'])) {
		$_SESSION['user_information']['trusted_preference'] += PREFERENCE_MASK_HOLIDAY_RELIGIOUS;
		}
		if (isset ($_POST['holiday4'])) {
		$_SESSION['user_information']['trusted_preference'] += PREFERENCE_MASK_HOLIDAY_CULTURAL;
		}
		if (isset ($_POST['preference_handicap'])) {
		switch (TRUE) {
		case ((int) $_POST['handicap'] === 0) :
		break;
		case ((int) $_POST['handicap'] === 1) :
		$_SESSION['user_information']['trusted_preference'] += PREFERENCE_MASK_HANDICAP_COLOR_RED1;
		break;
		case ((int) $_POST['handicap'] === 2) :
		$_SESSION['user_information']['trusted_preference'] += PREFERENCE_MASK_HANDICAP_COLOR_RED2;
		break;
		case ((int) $_POST['handicap'] === 3) :
		$_SESSION['user_information']['trusted_preference'] += PREFERENCE_MASK_HANDICAP_COLOR_BLUE;
		break;
		case ((int) $_POST['handicap'] === 4) :
		$_SESSION['user_information']['trusted_preference'] += PREFERENCE_MASK_HANDICAP_MINIMAL;
		break;
		default:
		// There is no default action.
		break;
		}
		}
		*/
	}
	
// print_r ($_POST); print_r ($_SESSION['user_information']); $_SESSION['error_class_item'] -> errorCount; print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();	

	require_once (HOAM_PATH_PLUGIN . 'tasklist/class.php');
	$tasklist_class = new tasklist ('tasks');
	
	// If a normal user is running through the add, they'll never hit this, but
	// if an admin is adding a new user, or if an existing user is editing an
	// account, then we need to look for groups.
	if (isset ($_SESSION['current_user']['id'])) {
		if (isset ($_POST['user|groups'])) {
			$_SESSION['user_information']['trusted_user|groups'] = array ();
			// If groups is set, then an administrator must be modifying an
			// existing user.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['currnt_user']['id'])) {
				foreach ($_POST['user|groups'] as $group) {
					$trusted_group = $group_class -> superClean ($group, $group_class -> returnColumnSize ('id'));
					// First, find out if the group id is valid
					if ($group_class -> idExists ($trusted_group)) {
						// Now, we need to make sure that if the current user
						// is trying to modify / update the privileges of
						// another user, that they already have /at least/ the
						// same group membership being requested for the user
						// being edited.
						if ($group_members_class -> isMember ($trusted_group, $_SESSION['current_user']['id'])) {
							// This is a valid group, and the current user has
							// the appropriate group membership. Make sure the
							// group hasn't already been added to the list.
							if (!in_array ($trusted_group, $_SESSION['user_information']['trusted_user|groups'])) {
								array_push ($_SESSION['user_information']['trusted_user|groups'], $trusted_group);
							}
						} elseif ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$_HOAM_setting ['user']['ids']['root'] == $_SESSION['currnt_user']['id']) {
							if (!in_array ($trusted_group, $_SESSION['user_information']['trusted_user|groups'])) {
								array_push ($_SESSION['user_information']['trusted_user|groups'], $trusted_group);
							}
						} else {
							$_SESSION['error_class_item'] -> addError ('user|groups', $_HOAM_language ['errors']['group']['not-authorized']);
							// This should probably be a log entry, instead of
							// a task. Create a task entry for the system
							// administrator to review the current user's
							// account.
							$task_id = $tasklist_class -> add ($_HOAM_setting ['users']['ids']['root']);
							if ($task_id != FALSE) {
								$tasklist_class -> changeTask ($task_id, 'Validate: user "' . $user_class -> returnAccountName ($_SESSION['current_user']['id']) . '" attempted (but failed) to join <q>' . $_SESSION['user_information']['trusted_user|accountname'] . '</q> to the <span class="group_name">' . $group_class -> returnName ($trusted_group) . '</span> group while editing an end user. This is a security, not a system issue.');
								$tasklist_class -> changeDateDue ($task_id);
							}
						}
					} else {
						$_SESSION['error_class_item'] -> addError ('user|groups', $_HOAM_language ['errors']['generic']['invalid-id']);
					}
				}
			}
		}
	}

	// Are there any errors that the current user needs to fix before
	// committing the changes?
	if ($_SESSION['error_class_item'] -> errorCount > 0) {
		// If we have an id, send the user back to that.
		if (isset ($_POST['add'])) {
			if (isset ($_SESSION['current_user']['id'])) {
				header ('Location: /website/user/add/');
			} else {
				header ('Location: /user/new/');
			}
		} elseif (isset ($_POST['modify'])) {
			if ($_SESSION['current_user']['id'] == $_SESSION['user_information']['trusted_user|id']) {
				header ('Location: /user/settings/');
			} else {
				header ('Location: /website/user/edit/' . $_SESSION['user_information']['trusted_user|id'] . '/');
			}
		}
	} else {
		// Finally! We have good data and can start modifying the database.
		if (isset ($_POST['add']) ) {
			if (isset ($_SESSION['current_user']['id'])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_add'], $_SESSION['current_user']['id'])) {
					$user_id = $user_class -> add ($_SESSION['current_user']['id']);
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['user']['created'], $_SESSION['user_information']['trusted_user|accountname']),
										HOAM_MESSAGE_NOTICE,
										array ('owner_id' => $user_id,
												'flags' => $_HOAM_setting ['log']['flags']['user']));
					// Set the password age to today
					$user_class -> changePasswordAge ($user_id, date ($_HOAM_country ['date']['format_mysql']));
				} else {
					// User does not have the necessary permissions.
					header ('Location: /error/403/');
				}
			} else {
				$user_id = $user_class -> add ($_HOAM_setting ['user']['ids']['system']);
				$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['user']['modified'], $_SESSION['user_information']['trusted_user|accountname']),
									HOAM_MESSAGE_INFORMATIONAL,
									array ('owner_id' => $user_id,
											'flags' => $_HOAM_setting ['log']['flags']['user']));
			}
			// There is a /slight/ possibility that another end user could
			// have selected the same user name as we just did, and
			// completed their addition to the database before we did.
			// Hopefully, it never occurs, and also that this is the only
			// error we ever have from add().
			if ($user_id == FALSE) {
				// If there was a problem with the database, we should
				// already have died. However, let's doublecheck.
				header ('Location: /error/add/');
			} else {
				// OK, let's record where it is that the user says they
				// heard about the site.
				if (isset ($_SESSION['user_information']['trusted_newuser|heard-about'])) {
					$heard_tracker = new HOAM_tracker ('heard_tracker');
					$heard_tracker_id = $heard_tracker -> findText ($_SESSION['user_information']['trusted_newuser|heard-about']);

					// Do the keywords already exist in the database?
					if (empty ($heard_tracker_id)) {
						$heard_tracker_id = $heard_tracker -> add ($_SESSION['user_information']['trusted_newuser|heard-about']);
					} else {
						// We must have received something back when
						// checking to see if the text already existed
						$heard_tracker_id = $heard_tracker_id[0];
					}
					// Increment the link counter
					$heard_tracker -> changeDateModified ($heard_tracker_id);
					$heard_tracker -> changeCount ($heard_tracker_id, ($heard_tracker -> returnCount ($heard_tracker_id) + 1));

					// Clean up
					unset ($heard_tracker, $heard_tracker_id);
				}
			}
		} else {
			// If the current_user is not the user being modified, is the
			// current_user permitted to make modifications for other
			// users?
			if (isset ($_SESSION['current_user']['id'])) {
				if ($_SESSION['current_user']['id'] == $_SESSION['user_information']['trusted_user|id']) {
					$user_id = $_SESSION['current_user']['id'];
				} elseif ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					$user_id = $_SESSION['user_information']['trusted_user|id'];
				} else {
					// User does not have the necessary permissions.
					header ('Location: /error/403/');
				}
			} else {
				// We shouldn't ever get here, but just in case...
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		}

		// By now, we've already verified that all of our data is correct
		// and valid, therefore we can just update the db. Consider it a
		// poor-man's transaction :)
		if (isset ($_SESSION['user_information']['trusted_user|accountname'])) {
			$user_class -> changeAccountName ($user_id, $_SESSION['user_information']['trusted_user|accountname']);
		}
		// When a user modifies their account, unless they specifically
		// chose to, the password string will be blank. Double-check for
		// this condition, and don't change the password if it's blank.
		if (isset ($_SESSION['user_information']['trusted_user|password|new']) AND !empty ($_SESSION['user_information']['trusted_user|password|new'])) {
			$user_class -> changePassword ($user_id, $_SESSION['user_information']['trusted_user|password|new']);
			// Set the password age to today
			$user_class -> changePasswordAge ($user_id, date ($_HOAM_country ['date']['format_mysql']));
		}
		$user_class -> changeFirstname ($user_id, $_SESSION['user_information']['trusted_user|name|first']);
		$user_class -> changeLastname ($user_id, $_SESSION['user_information']['trusted_user|name|last']);
		$user_class -> changeEmail ($user_id, $_SESSION['user_information']['trusted_user|email']);
		if ($_SESSION['user_information']['trusted_user|flags|disabled']) {
			$user_class -> setBit ($user_id, $_HOAM_setting ['user']['flags']['disabled']);
		} else {
			$user_class -> clearBit ($user_id, $_HOAM_setting ['user']['flags']['disabled']);
		}
		if (isset ($_SESSION['user_information']['trusted_user|resident']) AND $_SESSION['user_information']['trusted_user|resident']) {
			if (isset ($_SESSION['user_information']['trusted_user|address'])) {
				// Has the homeowner already been verified as a resident or homeowner?
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $user_id) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['resident'], $user_id)) {
					// Do nothing, we don't want to change the homeowner id if this
					// user has already been validated. It might make user
					// administration a tiny bit cumbersome, but it keeps someone
					// from hacking their account to change the associated
					// homeowner account to view other peoples' records.
				} else {
					// Find out who is the current homeowner at that address
					$current_homeowner = $homeowner_class -> returnCurrentOwner ($_SESSION['user_information']['trusted_user|address']);
					$user_class -> changeHomeownerId ($user_id, $current_homeowner);
					// The below id needs to be changed to the globally configured
					// administrator id or group once all of that is working.
					$task_id = $tasklist_class -> add ($_HOAM_setting ['user']['ids']['system']);
					if ($task_id != FALSE) {
						$tasklist_class -> changeTask ($task_id, 'Validate: user "' . $_SESSION['user_information']['trusted_user|accountname'] . '" is requesting Homeowner and / or Resident group memebership for ' . $lot_class -> returnAddress ($_SESSION['user_information']['trusted_user|address']) . ' ' . $lot_class -> returnStreet ($_SESSION['user_information']['trusted_user|address'] . '.'));
						$tasklist_class -> changeDateDue ($task_id);
						$tasklist_class -> changeAssignedUser ($task_id, $_HOAM_setting ['user']['ids']['root']);
					}
				}
			}
		} else {
			// Has the homeowner already been verified as a resident or homeowner?
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $user_id) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['resident'], $user_id)) {
				// If the user is still validated as a homeowner/resident, then
				// don't mess with the account. Since the appropriate inputs
				// aren't submitted when editing users, this could
				// inadvertently cause their homeowner_id association to be
				// lost.
			} else {
				// Erase the current setting if the user is not a homeowner/resident.
				$user_class -> changeHomeownerId ($user_id, '');
			}
		}
		$user_class -> changePasswordHintName ($user_id, $_SESSION['user_information']['trusted_user|password|hint-name']);
		$user_class -> changePasswordHint ($user_id, $_SESSION['user_information']['trusted_user|password|hint']);
		$user_class -> changePreference ($user_id, $_SESSION['user_information']['trusted_user|preference']);
		$user_class -> changePreferenceEditCol ($user_id, $_SESSION['user_information']['trusted_user|edit_cols']);
		$user_class -> changePreferenceEditRow ($user_id, $_SESSION['user_information']['trusted_user|edit_rows']);
		if (isset ($_SESSION['user_information']['trusted_user|comments'])) {
			$user_class -> changeComments ($user_id, $_SESSION['user_information']['trusted_user|comments']);
		}
		// OK, this part gets a little hairy. We need to first remove the
		// user from any current group memberships, and then create new
		// entries in the database for them. Theoretically, this could be
		// all changed to act more like the way articles handle group
		// membership, but I like this way better. Maybe I'll change my
		// mind.
		
		// Make sure the user is a member of the Registered group. If
		// not a member, add it. But, don't do it if email validation is enabled
		// and the user has not yet done so. 
		if (is_true ($_HOAM_setting ['user']['email_validation'])) {
			if ((int) $_HOAM_setting ['user']['flags']['email_validated'] & (int) $user_class -> returnFlags ($user_id)) {
				if (!in_array ($_HOAM_setting ['group']['ids']['registered'], $_SESSION['user_information']['trusted_user|groups'])) {
					// Force the addition of the administrators group.
					array_push ($_SESSION['user_information']['trusted_user|groups'], $_HOAM_setting ['group']['ids']['registered']);
				}
			}
		} else {
			if (!in_array ($_HOAM_setting ['group']['ids']['registered'], $_SESSION['user_information']['trusted_user|groups'])) {
				// Force the addition of the administrators group.
				array_push ($_SESSION['user_information']['trusted_user|groups'], $_HOAM_setting ['group']['ids']['registered']);
			}
		}
		if (isset ($_SESSION['user_information']['trusted_user|groups'])) {
			// Get a list of any / all old groups.
			$old_group_list = $group_members_class -> listByMember ($user_id);
			// Delete any group entries the user had previously
			if (!empty ($old_group_list)) {
				foreach ($old_group_list as $old_group) {
					// We could use removeUserFromGroup (), but this is easier/
					// cleaner/faster since we're nuking everything
					$group_members_class -> deleteId ($old_group);
				}
			}
			// Create the new group memberships.
			foreach ($_SESSION['user_information']['trusted_user|groups'] as $group) {
				$id = $group_members_class -> addUserToGroup ($user_id, $group);
				if ($id) {
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['group']['added'], $_SESSION['user_information']['trusted_user|accountname'], $group_class -> returnName ($group)),
										HOAM_MESSAGE_NOTICE,
										array ('owner_id' => $group,
												'flags' => $_HOAM_setting ['log']['flags']['group']));
				} else {
					// There was a problem adding the new group, send the
					// user to the error page.
					header ('Location: /error/add/');
				}
			}
		}
		$user_class -> changeDateModified ($user_id);

		// Check to see whether the user has just created a new account. We
		// could / should also do a referrer check to see if we just came
		// from the /scripts/user/edit_script.php
		if (isset ($_POST['add'])) {
			// Clear error_class_item, but leave the user_information
			// intact, since we'll use that to complete the toolbar login
			// form.
			unset ($_SESSION['error_class_item']);
			
			if (isset ($_SESSION['current_user']['id'])) {
				header ('Location: /website/user/');
			} else {
				if (is_true ($_HOAM_setting ['user']['email_validation'])) {
					// For regular users, if configured, now send a validation
					// email. Generate a new link.
					$validation_class = new HOAM_user_validation ('user_validation');
					$validation_id = $validation_class -> add ($user_id);
					$validation_hash = md5 (mt_rand());
					$validation_class -> changeHash ($validation_id, $validation_hash);
				
					require_once (HOAM_PATH . '3rdparty/phpmailer/class.phpmailer.php');
					
					$wiki = new HOAM_wiki_parser ();
					$mail = new PHPMailer ();
							
					$mail -> IsSMTP (); // telling the class to use SMTP
					$mail -> SMTPDebug = HOAM_DEBUG; // Use the same setting as
												// HOAM
       					            	        // 1 = errors and messages
	        	        			            // 2 = messages only
					if ($_HOAM_setting ['email']['auth']) { // enable SMTP
															// authentication
						$mail -> SMTPAuth = TRUE;
						$mail -> Username = $wiki -> parse ($_HOAM_setting ['email']['username']);
						$mail -> Password = $_HOAM_setting ['email']['password'];
						// If we're authenticating, then we know we have a username.
						$mail -> SetFrom ($wiki -> parse ($_HOAM_setting ['email']['username']));
					} else {
						// Since we don't know that we have a username configured
						// (one would only be there if authentication was previously
						// configured and then disabled), use the address of the
						// management company.
						$mail -> SetFrom ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
					}
					$mail -> Host = $_HOAM_setting ['email']['server'];
					$mail -> Port = $_HOAM_setting ['email']['port'];
					$mail -> AddReplyTo ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
					$mail -> AddAddress ($_SESSION['user_information']['trusted_user|email']);
					$mail -> Subject = HOAM_returnWebsiteTitle () . ' :: ' . $_HOAM_language ['field_names']['newuser']['email_validation'];

					$body = sprintf ($_HOAM_language ['help_pages']['email']['validation'], $_SESSION['user_information']['trusted_user|accountname'], $validation_hash, $validation_hash);
					if ($_HOAM_setting ['email']['do_not_reply']) {
						$body .= $_HOAM_language ['help_pages']['generic']['do_not_reply'];
					}
					$mail -> MsgHTML ($wiki -> parse ($body));
					$mail -> AltBody = $_HOAM_language ['field_names']['email']['text_only'];

					if (!$mail -> Send ()) {
  						$_HOAM_log -> add ('PHPMailer Error: ' . $mail -> ErrorInfo, HOAM_MESSAGE_ERROR);
					} else {
						$_HOAM_log -> add ('User: email verification sent to "' . $_SESSION['user_information']['trusted_user|accountname'] . '".', HOAM_MESSAGE_INFORMATIONAL);
					}
				}
				header ('Location: /user/created/');
			}
		} else {
			// Clear the error_class_item and user_information variables
			// since everything worked ok.
			unset ($_SESSION['user_information']);
			unset ($_SESSION['error_class_item']);
			if ($_SESSION['current_user']['id'] == $user_id) {
				header ('Location: /');
			} else {
				header ('Location: /website/user/');
			}
		}
	}
} else {
	// We don't know what action (add / cancel / delete / modify) we're being
	// asked to perform
	header ('Location: /error/unknown_action/');
}

exit ();

?>
