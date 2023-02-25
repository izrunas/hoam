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

function HOAM_userCreated ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	// Only show this to users logged NOT in
	if (!isset ($_SESSION['current_user']['id'])) {
		switch ($GLOBALS['global_url_array'][2]) {
			default:
				if (empty ($GLOBALS['global_url_array'][2])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['newuser']['registered'])));
					if ($h1) {
						$HTML = '<h1>' . $_HOAM_language ['field_names']['newuser']['registered'] . '</h1>';
						if ($_HOAM_setting ['user']['email_validation']) {
							$HTML .= $_HOAM_language ['help_pages']['newuser']['email_validation'];
						} else {
							$HTML .= $_HOAM_language ['help_pages']['newuser']['registered'];
						}
					}
				} else {
					header ('Location: /error/404/');
					exit ();
				}
				break;
		}
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userDisabled () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;
	
	$user_class = new HOAM_user ('users');
	
	// Only show this to users logged NOT in
	if (!isset ($_SESSION['current_user']['id'])) {
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['disabled'])));
		if (isset ($_SESSION['temp_username'])) {
			require_once (HOAM_PATH_FUNCTION . 'forms.php');

			$trusted_username = $_SESSION['temp_username'];
			$trusted_password = $_SESSION['temp_password'];
			$trusted_id = $user_class -> returnId ($trusted_username);

			$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['disabled'] . '</h1>';
			$HTML .= $_HOAM_language ['help_pages']['user']['disabled'];
			$HTML .= '<form id="verify_enable" action="/hoam/scripts/user/userVerifyAccountEnable.php" method="post">';
			$HTML .= '<div class="mandatory">';
			$HTML .= HOAM_formField ('answer', HOAM_userReturnSecurityQuestion ($user_class -> returnPasswordHintName ($trusted_id)), '', '', $user_class -> returnColumnSize ("password_hint"), $_HOAM_setting ['user']['minimum_password_hint_length']);
			$HTML .= '<input type="hidden" name="username" value="' . $trusted_username . '" />';
			$HTML .= '<input type="hidden" name="password" value="' . $trusted_password . '" />';
			$HTML .= '<button type="submit">' . $_HOAM_language ['form_buttons']['user']['validate'] . '</button>';
			$HTML .= '</div>';
		}
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userEnabled () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;
	
	// Only show this to users logged NOT in
	if (!isset ($_SESSION['current_user']['id'])) {
		switch ($GLOBALS['global_url_array'][2]) {
			default:
				if (empty ($GLOBALS['global_url_array'][2])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['enabled'])));
					if (isset ($_SESSION['temp_username'])) {
						$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['enabled'] . '</h1>';
						$HTML .= $_HOAM_language ['help_pages']['user']['enabled'];
					} else {
						header ('Location: /error/id_required/');
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
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userLogin ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_log, $_HOAM_queue_head, $_HOAM_setting;
	
	$article_class = new HOAM_article ('articles');
	$user_class = new HOAM_user ('users');
	
	// Only show this to users NOT logged in
	if (!isset ($_SESSION['current_user']['id'])) {
		switch ($GLOBALS['global_url_array'][2]) {
			default:
				if (empty ($GLOBALS['global_url_array'][2])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['form_buttons']['user']['login'])));
					if ($h1) {
						if (isset ($_COOKIE['PHPSESSID'])) {
							if ((isset ($_POST['username'])) AND isset ($_POST['password'])) {
								$trusted_username = $user_class -> superClean ($_POST['username'], $user_class -> returnColumnSize ('accountname'));
								$trusted_password = $user_class -> superClean ($_POST['password'], $user_class -> returnColumnSize ('password'));
							} elseif (isset ($_SESSION['temp_username'])) {
								$trusted_username = $_SESSION['temp_username'];
								$trusted_password = $_SESSION['temp_password'];
							}
							if ($user_class -> authenticateUser ($trusted_username, $trusted_password)) {
								// Grab the original account name as recorded
								// when the account was registered.
								$trusted_username = $user_class -> returnAccountName ($user_class -> returnId ($trusted_username));
								if ((int) $user_class -> returnFlags ($user_class -> returnId ($trusted_username)) & (int) $_HOAM_setting ['user']['flags']['disabled']) {
									$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['user']['disabled'], $trusted_username),
														HOAM_MESSAGE_NOTICE,
														array ('owner_id' => $user_class -> returnId ($trusted_username),
																'flags' => $_HOAM_setting ['log']['flags']['user']));
									$_SESSION['temp_username'] = $trusted_username;
									$_SESSION['temp_password'] = $trusted_password;
									header ('Location: /user/disabled/');
									exit ();
								} else {
									if (isset ($_SESSION['temp_username'])) {
										unset ($_SESSION['temp_username']);
										unset ($_SESSION['temp_password']);
									}
									session_regenerate_id ();
									$_SESSION['current_user']['id'] = $user_class -> returnId ($trusted_username);
									$_SESSION['current_user']['edit']['col'] = $user_class -> returnPreferenceEditCol ($_SESSION['current_user']['id']);
									$_SESSION['current_user']['edit']['row'] = $user_class -> returnPreferenceEditRow ($_SESSION['current_user']['id']);
									$_SESSION['current_user']['items_per_page'] = $user_class -> returnPreferenceItems ($_SESSION['current_user']['id']);
								
									// Update the last login date in the user's
									// profile
									$user_class -> changeLastLogin ($_SESSION['current_user']['id'], date('Y-m-d H:i:s'));
								
									// Need to add a few other things to this,
									// such as the list of groups that the user
									// is a member of
									$log_id = $_HOAM_log -> add (sprintf ($_HOAM_language ['log']['user']['logged_in'], $trusted_username),
																HOAM_MESSAGE_NOTICE,
																array ('owner_id' => $_SESSION['current_user']['id'],
																		'flags' => $_HOAM_setting ['log']['flags']['user']));
									// These variables should only exist if the
									// end user either just created an account
									// (and is now logging in for the first
									// time) OR if they _started_ creating a new
									// account, didn't hit cancel, and then
									// logged in with an existing account.
									if (isset ($_SESSION['user_information'])) {
										unset ($_SESSION['user_information']);
									}
									if (isset ($_SESSION['error_class_item'])) {
										unset ($_SESSION['error_class_item']);
									}
									// Check whether the user should be notified
									// about an old password, be redirected, or
									// just shown the main page.
									if ((strtotime ($user_class -> returnPasswordAge ($_SESSION['current_user']['id'])) + ($_HOAM_setting ['user']['days_password_age'] * 86400)) < time ()) {
										$log_id = $_HOAM_log -> add (sprintf ($_HOAM_language ['log']['user']['password']['age'], $trusted_username),
																	HOAM_MESSAGE_INFORMATIONAL,
																	array ('owner_id' => $_SESSION['current_user']['id'],
																			'flags' => $_HOAM_setting ['log']['flags']['user']));
										header ('Location: /user/password_age/');
									} elseif ((strtotime ($user_class -> returnPasswordAge ($_SESSION['current_user']['id'])) + ($_HOAM_setting ['user']['days_password_warn'] * 86400)) < time ()) {
										$log_id = $_HOAM_log -> add (sprintf ($_HOAM_language ['log']['user']['password']['warn'], $trusted_username),
																	HOAM_MESSAGE_INFORMATIONAL,
																	array ('owner_id' => $_SESSION['current_user']['id'],
																			'flags' => $_HOAM_setting ['log']['flags']['user']));
										header ('Location: /user/password_warn/');
									} elseif (isset ($_POST['redirect'])) {
										header ('Location: /wiki/' . $article_class -> superClean ($_POST['redirect'], $article_class -> returnColumnSize ('urlname')));
									} else {
										header ('Location: /');
									}
									exit ();
								}
							} else {
								if ($user_class -> accountExists ($trusted_username)) {
									$log_id = $_HOAM_log -> add (sprintf ($_HOAM_language ['errors']['user']['logon']['password_failure'], $trusted_username),
																HOAM_MESSAGE_WARNING,
																array ('owner_id' => $user_class -> returnId ($trusted_username),
																		'flags' => $_HOAM_setting ['log']['flags']['user']));
									// Bad password
									header ('Location: /error/password/');
									exit ();
								} else {
									$_HOAM_log -> add (sprintf ($_HOAM_language ['errors']['user']['logon']['unknown_user'], $trusted_username), HOAM_MESSAGE_WARNING);
									// unknown username
									header ('Location: /error/unknown_account/?accountname=' . $trusted_username);
									exit ();
								}
							}
						} else {
							// Cookies must be enabled
							header ('Location: /error/cookies/');
							exit ();
						}
						exit ();
					} else {
						$HTML = '<img src="/hoam/images/icons/user.png" alt="' . $_HOAM_language ['form_buttons']['user']['login'] . '" class="LHS" /><a href="/user/login/">' . $_HOAM_language ['form_buttons']['user']['login'] . '</a>';
					}
				} else {
					header ('Location: /error/404/');
					exit ();
				}
				break;
		}
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userLogout ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_log, $_HOAM_queue_head, $_HOAM_setting;

	$user_class = new HOAM_user ('users');

	// Only show this to users logged in
	if (isset ($_SESSION['current_user']['id'])) {
		switch ($GLOBALS['global_url_array'][2]) {
			default:
				if (empty ($GLOBALS['global_url_array'][2])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['form_buttons']['user']['logout'])));
					if ($h1) {
						$log_id = $_HOAM_log -> add (sprintf ($_HOAM_language ['log']['user']['logged_out'], $user_class -> returnAccountName ($_SESSION['current_user']['id'])),
													HOAM_MESSAGE_NOTICE,
													array ('owner_id' => $_SESSION['current_user']['id'],
															'flags' => $_HOAM_setting ['log']['flags']['user']));
						// Unset all of the session variables.
						$_SESSION = array ();
						
						// Delete the session cookie.
						if (ini_get ('session.use_cookies')) {
    						$params = session_get_cookie_params ();
    						setcookie (session_name (), '', time () - 42000,
        						$params['path'], $params['domain'],
        						$params['secure'], $params['httponly']);
						}
						
						session_destroy ();
						header ('Location: /');
						exit ();
					} else {
						$HTML = '<img src="/hoam/images/icons/user_go.png" alt="' . $_HOAM_language ['form_buttons']['user']['logout'] . '" class="LHS" /><a href="/user/logout/">' . $_HOAM_language ['form_buttons']['user']['logout'] . '</a><br />';
					}
				} else {
					header ('Location: /error/404/');
					exit ();
				}
				break;
		}
		return ($HTML);
	}
	header ('Location: /error/login/');
	exit ();
}

function HOAM_userNew ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	$lot_class = new HOAM_lot ('lots');
	$user_class = new HOAM_user ('users');

	// Only show this to users NOT logged in
	if (!isset ($_SESSION['current_user']['id'])) {
		if (empty ($GLOBALS['global_url_array'][2])) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['create'])));
			if ($h1) {
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_userJS');
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['create'] . '</h1>';
				$HTML .= '<form id="user_new" action="/hoam/scripts/user/edit_script.php" method="post">';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['user']['user-add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';
				$HTML .= '<div class="tabbertab" id="add_user" title="' . $_HOAM_language ['form_tabs']['user']['information'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= '<fieldset id="general">';
				$HTML .= HOAM_formField ('user|accountname', $_HOAM_language ['field_names']['user']['accountname'], $_SESSION['user_information']['trusted_user|accountname'], '', $user_class -> returnColumnSize ('accountname'), $_HOAM_setting ['user']['minimum_accountname_length']);
				$HTML .= HOAM_formConfigPassword ('user|password|new', $_HOAM_language ['field_names']['newuser']['password'], $_SESSION['user_information']['trusted_user|password|new'], '', $user_class -> returnColumnSize ('password'), $_HOAM_setting ['user']['minimum_password_length']);
				$HTML .= '<span id="user|password|strength"></span>';
				$HTML .= HOAM_formConfigPassword ('user|password|verify', $_HOAM_language ['field_names']['user']['password']['verify'], $_SESSION['user_information']['trusted_user|password|verify'], '', $user_class -> returnColumnSize ('password'));
				$HTML .= '</fieldset>';
				$HTML .= '<fieldset id="verification|hint">';
				$HTML .= HOAM_formSelect ('user|password|hint-name', $_HOAM_language ['field_names']['user']['password']['hint-name']);
				if (isset ($_SESSION["user_information"]["trusted_user|password|hint-name"])) {
					$default_phrase = $_SESSION["user_information"]["trusted_user|password|hint-name"];
				} else {
					$default_phrase = NULL;
				}
				foreach ($_HOAM_language ['password_hint_phrases'] as $phrase) {
					$HTML .= "<option ";
					if ($phrase["value"] == $default_phrase) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= "value=\"" . $phrase["value"] . "\">" . $phrase["description"] . "</option>\n";
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formField ('user|password|hint', $_HOAM_language ['field_names']['user']['password']['hint'], $_SESSION['user_information']['trusted_user|password|hint'], '', $user_class -> returnColumnSize ("password_hint"), $_HOAM_setting ['user']['minimum_password_hint_length']);
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formSelect ('newuser|heard-about', $_HOAM_language ['field_names']['newuser']['heard-about']);
				if (isset ($_SESSION["user_information"]["trusted_newuser|heard-about"])) {
					$default_option = $_SESSION["user_information"]["trusted_newuser|heard-about"];
				} else {
					$default_option = NULL;
				}
				$HTML .= '<option>' . $_HOAM_language ['common']['please-select'] . '</option>';
				foreach ($_HOAM_language ['heard_about_options'] as $option) {
					$HTML .= "<option ";
					if ($option["value"] == $default_option) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= "value=\"" . $option["value"] . "\">" . $option["description"] . "</option>\n";
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
				$HTML .= '<label for="user|resident">' . $_HOAM_language ['field_names']['user']['resident'];
				$HTML .= HOAM_formDisplayError ('user|resident');
				$HTML .= '</label>';
				$HTML .= '<input ';
				// If the user has been validated, then we want to show
				// them the address information, but disable the input
				// forms allowing them to change their address.
				if (isset ($_SESSION["user_information"]["trusted_user|resident"]) AND $_SESSION["user_information"]["trusted_user|resident"]) {
					$HTML .= 'checked="checked" ';
				}
				$HTML .= 'type="checkbox" id="user|resident" name="user|resident" value="1" />';
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory" id="user|show-address"';
				if (isset ($_SESSION["user_information"]["trusted_user|resident"]) AND $_SESSION["user_information"]["trusted_user|resident"]) {
				} else {
					$HTML .= ' style="display: none"';
				}
				$HTML .= '>';
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formSelect ('user|address', $_HOAM_language ['field_names']['user']['address']);
				if (isset ($_SESSION["user_information"]["trusted_user|address"])) {
					$default_lot = $_SESSION["user_information"]["trusted_user|address"];
				} else {
					$default_lot = NULL;
				}
				$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
				if (!empty ($lot_list)) {
					foreach ($lot_list as $lot) {
						$HTML .= '<option ';
						if ($lot['id'] == $default_lot) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
						$HTML .= '</option>';
					}
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
				$HTML .= HOAM_formCheckbox ('user|messageboard|acronyms', $_HOAM_language ['field_names']['user']['messageboard']['acronyms'], 1, (int) $_SESSION['user_information']['trusted_user|preference'] & (int) $_HOAM_setting ['user']['flags']['acronyms'], 1);
				$HTML .= HOAM_formCheckbox ('user|messageboard|links', $_HOAM_language ['field_names']['user']['messageboard']['links'], 1, (int) $_SESSION['user_information']['trusted_user|preference'] & (int) $_HOAM_setting ['user']['flags']['convert_links'], 1);
				$HTML .= HOAM_formCheckbox ('user|messageboard|smileys', $_HOAM_language ['field_names']['user']['messageboard']['smileys'], 1, (int) $_SESSION['user_information']['trusted_user|preference'] & (int) $_HOAM_setting ['user']['flags']['smileys'], 1);
				$HTML .= '</fieldset>';
				$HTML .= '<label for="user|display|options">' . $_HOAM_language ['field_names']['user']['display']['options'];
				$HTML .= '</label>';
				$HTML .= '<fieldset id="user|display|options" class="advanced_options">';
				$HTML .= HOAM_formCheckbox ('user|display|holidays', $_HOAM_language ['field_names']['user']['display']['holidays'], 1, (int) $_SESSION['user_information']['trusted_user|preference'] & (int) $_HOAM_setting ['user']['flags']['holidays'], 1);
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formField ('user|edit_cols', $_HOAM_language ['field_names']['user']['edit_cols'], $_SESSION['user_information']['trusted_user|edit_cols'], 80, 5, 40);
				$HTML .= HOAM_formField ('user|edit_rows', $_HOAM_language ['field_names']['user']['edit_rows'], $_SESSION['user_information']['trusted_user|edit_rows'], 10, 5, 2);
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End optional settings tab
				$HTML .= '</div>'; // End tabber
				$HTML .= '</form>';
			} else {
				$HTML = '<img src="/hoam/images/icons/user_add.png" alt="' . $_HOAM_language ['field_names']['user']['create'] . '" class="LHS" /><a href="/user/new/">' . $_HOAM_language ['field_names']['user']['create'] . '</a>';
			}
		} else {
		}
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userPasswordAge ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	$user_class = new HOAM_user ('users');

	// Only show this to users logged NOT in
	if (isset ($_SESSION['current_user']['id'])) {
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['password']['age'])));
		$HTML  = '<h1>' . $_HOAM_language ['field_names']['user']['password']['age'] . '</h1>';
		$days = (int) (time() - strtotime ($user_class -> returnPasswordAge ($_SESSION['current_user']['id']))) / 86400;
		$HTML .= sprintf ($_HOAM_language ['help_pages']['user']['password']['age'], $days);
		HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_userJS');
		require_once (HOAM_PATH_FUNCTION . 'forms.php');
		$HTML .= '<form id="user_modify" action="/hoam/scripts/user/userPasswordReset.php" method="post">';
		$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
		$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
		$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
		$HTML .= $_HOAM_language ['help_popups']['mandatory'];
		$HTML .= '<div class="tabber">';
		$HTML .= '<div class="tabbertab" id="modify_user" title="' . $_HOAM_language ['form_tabs']['user']['information'] . '">';
		$HTML .= '<div class="mandatory">';
		$HTML .= HOAM_formConfigPassword ('user|password|old', $_HOAM_language ['field_names']['user']['password']['old'], $_SESSION['user_information']['trusted_user|password|old'], '', $user_class -> returnColumnSize ('password'));
		$HTML .= HOAM_formConfigPassword ('user|password|new', $_HOAM_language ['field_names']['user']['password']['new'], $_SESSION['user_information']['trusted_user|password|new'], '', $user_class -> returnColumnSize ('password'), $_HOAM_setting ['user']['minimum_password_length']);
		$HTML .= '<span id="user|password|strength"></span>';
		$HTML .= HOAM_formConfigPassword ('user|password|verify', $_HOAM_language ['field_names']['user']['password']['verify'], $_SESSION['user_information']['trusted_user|password|verify'], '', $user_class -> returnColumnSize ('password'));
		$HTML .= '<fieldset id="verification|hint">';
		$HTML .= HOAM_formSelect ('user|password|hint-name', $_HOAM_language ['field_names']['user']['password']['hint-name']);
		if (isset ($_SESSION["user_information"]["trusted_user|password|hint-name"])) {
			$default_phrase = $_SESSION["user_information"]["trusted_user|password|hint-name"];
		} else {
			$default_phrase = $user_class -> returnPasswordHintName ($_SESSION['current_user']['id']);
		}
		foreach ($GLOBALS['_HOAM_language']['password_hint_phrases'] as $phrase) {
			$HTML .= '<option ';
			if ($phrase["value"] == $default_phrase) {
				$HTML .= 'selected="selected" ';
			}
			$HTML .= 'value="' . $phrase["value"] . '">' . $phrase["description"] . '</option>';
		}
		$HTML .= '</select>';
		$HTML .= HOAM_formField ('user|password|hint', $_HOAM_language ['field_names']['user']['password']['hint'], $_SESSION['user_information']['trusted_user|password|hint'], $user_class -> returnPasswordHint ($_SESSION['current_user']['id']), $user_class -> returnColumnSize ("password_hint"), $_HOAM_setting ['user']['minimum_password_hint_length']);
		$HTML .= '</fieldset>';
		$HTML .= '</div>';
		$HTML .= '</div>'; // End password change tab

		$HTML .= '</div>'; // End tabber
		$HTML .= '</form>';
		
		return ($HTML);
	} else {
		header ('Location: /error/login/');
		exit ();
	}
}

function HOAM_userPasswordForgot ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	$user_class = new HOAM_user ('users');

	// Only show this to users logged NOT in
	if (!isset ($_SESSION['current_user']['id'])) {
		if (empty ($GLOBALS['global_url_array'][2])) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['password']['forgot'])));
			if ($h1) {
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['password']['forgot'] . '</h1>';
				$HTML .= '<form id="forgot_password" action="/hoam/scripts/user/userPasswordForgot.php" method="post">';
				$HTML .= '<div class="tabber">';
				$HTML .= '<div class="tabbertab" id="password_forgot" title="' . $_HOAM_language ['form_tabs']['user']['password']['forgot'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= $_HOAM_language ['help_pages']['user']['password']['forgot'];
				$HTML .= HOAM_formField ('user|accountname', $_HOAM_language ['field_names']['user']['accountname'], '', '', $user_class -> returnColumnSize ('accountname'), $_HOAM_setting ['user']['minimum_accountname_length']);
				$HTML .= '<br /><br />';
				$HTML .= '<button id="submit">' . $_HOAM_language ['common']['submit'] . '</button>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Passsword forgot tab
				$HTML .= '</div>'; // End tabber
				$HTML .= '</form>';
			} else {
				$HTML = '<img src="/hoam/images/icons/user_red.png" alt="' . $_HOAM_language ['field_names']['user']['password']['forgot'] . '" class="LHS" /><a href="/user/password_forgot/">' . $_HOAM_language ['field_names']['user']['password']['forgot'] . '</a>';
			}
		} else {
			header ('Location: /error/404/');
			exit ();
		}
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userPasswordReset () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	// Only show this to users logged NOT in
	if (!isset ($_SESSION['current_user']['id'])) {
		switch ($GLOBALS['global_url_array'][1]) {
			default:
				if (!empty ($GLOBALS['global_url_array'][2])) {
					$user_class = new HOAM_user ('users');
					$validation_class = new HOAM_user_validation ('user_validation');
					require_once (HOAM_PATH_FUNCTION . 'forms.php');

					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['validate']['password'])));
					$trusted_hash = $validation_class -> superclean ($GLOBALS['global_url_array'][2], $validation_class -> returnColumnSize ('hash'));
					$trusted_id = $validation_class -> returnIdWithHash ($trusted_hash);
					if ($trusted_id) {
						$trusted_userid = $validation_class -> returnUserId ($trusted_id);
						$trusted_username = $user_class -> returnAccountName ($trusted_userid);
						$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['validate']['password'] . '</h1>';
						$HTML .= $_HOAM_language ['help_pages']['user']['password']['reset'];
						$HTML .= '<form id="verify_address" action="/hoam/scripts/user/userPasswordResetForgotten.php" method="post">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formFieldDisabled ('user|accountname', $_HOAM_language ['field_names']['user']['accountname'], $trusted_username, $user_class -> returnColumnSize ('accountname'));
						$HTML .= '<input type="hidden" name="user|accountname" value="' . $trusted_username . '" />';
						$HTML .= '<input type="hidden" name="user|hash" value="' . $trusted_hash . '" />';
						$HTML .= HOAM_formConfigPassword ('user|password|new', $_HOAM_language ['field_names']['user']['password']['new'], '', $_SESSION['user_information']['trusted_user|password|new'], $user_class -> returnColumnSize ('password'), $_HOAM_setting ['user']['minimum_password_length']);
						$HTML .= '<span id="user|password|strength"></span>';
						$HTML .= HOAM_formConfigPassword ('user|password|verify', $_HOAM_language ['field_names']['user']['password']['verify'], '', $_SESSION['user_information']['trusted_user|password|verify'], $user_class -> returnColumnSize ('password'));
						$HTML .= '<br /><br />';
						$HTML .= '<button type="submit">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '</div>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /error/id_required/');
					exit ();
				}
				break;
		}
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userPasswordResetQuestion () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	// Only show this to users logged NOT in
	if (!isset ($_SESSION['current_user']['id'])) {
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['password']['forgot'])));
		if (isset ($_SESSION['user_information']['trusted_user|accountname'])) {
			require_once (HOAM_PATH_FUNCTION . 'forms.php');

			$user_class = new HOAM_user ('users');
			
			$trusted_id = $user_class -> returnId ($_SESSION['user_information']['trusted_user|accountname']);

			$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['password']['forgot'] . '</h1>';
			$HTML .= $_HOAM_language ['help_pages']['user']['password']['question'];
			$HTML .= '<form id="verify_enable" action="/hoam/scripts/user/userPasswordResetQuestion.php" method="post">';
			$HTML .= '<input type="hidden" name="user|accountname" value="' . $_SESSION['user_information']['trusted_user|accountname'] . '" />';
			$HTML .= '<div class="mandatory">';
			$HTML .= HOAM_formField ('user|answer', HOAM_userReturnSecurityQuestion ($user_class -> returnPasswordHintName ($trusted_id)), '', $_SESSION['user_information']['trusted_user|answer'], $user_class -> returnColumnSize ("password_hint"), $_HOAM_setting ['user']['minimum_password_hint_length']);
			$HTML .= HOAM_formConfigPassword ('user|password|new', $_HOAM_language ['field_names']['user']['password']['new'], '', $_SESSION['user_information']['trusted_user|password|new'], $user_class -> returnColumnSize ('password'), $_HOAM_setting ['user']['minimum_password_length']);
			$HTML .= '<span id="user|password|strength"></span>';
			$HTML .= HOAM_formConfigPassword ('user|password|verify', $_HOAM_language ['field_names']['user']['password']['verify'], '', $_SESSION['user_information']['trusted_user|password|verify'], $user_class -> returnColumnSize ('password'));
			$HTML .= '<br /><br />';
			$HTML .= '<button type="submit">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
			$HTML .= '</div>';
		}
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userPasswordResetSent () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	// Only show this to users logged NOT in
	if (!isset ($_SESSION['current_user']['id'])) {
		switch ($GLOBALS['global_url_array'][2]) {
			default:
				if (empty ($GLOBALS['global_url_array'][2])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['password']['sent'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['password']['sent'] . '</h1>';
					$HTML .= $_HOAM_language ['help_pages']['user']['password']['sent'];
				} else {
					header ('Location: /error/404/');
					exit ();
				}
				break;
		}
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userPasswordWarn ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	$user_class = new HOAM_user ('users');

	// Only show this to users logged NOT in
	if (isset ($_SESSION['current_user']['id'])) {
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['password']['warn'])));
		$HTML  = '<h1>' . $_HOAM_language ['field_names']['user']['password']['warn'] . '</h1>';
		$days = (int) (time() - strtotime ($user_class -> returnPasswordAge ($_SESSION['current_user']['id']))) / 86400;
		$HTML .= sprintf ($_HOAM_language ['help_pages']['user']['password']['warn'], $days, $_HOAM_setting ['user']['days_password_age']);
		return ($HTML);
	} else {
		header ('Location: /error/login/');
		exit ();
	}
}

function HOAM_userSettings ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$user_class = new HOAM_user ('users');

	// Only show this to users logged in
	if (isset ($_SESSION['current_user']['id'])) {
		if (empty ($GLOBALS['global_url_array'][2])) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['account']['modify'])));
			if ($h1) {
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_userJS');
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['account']['modify'] . '</h1>';
				$HTML .= '<form id="user_modify" action="/hoam/scripts/user/edit_script.php" method="post">';
				$HTML .= '<input type="hidden" name="user|id" id="user|id" value="' . $_SESSION['current_user']['id'] . '" />';
				$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';
				$HTML .= '<div class="tabbertab" id="modify_user" title="' . $_HOAM_language ['form_tabs']['user']['information'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= '<fieldset id="general">';
				$HTML .= HOAM_formFieldDisabled ('user|accountname', $_HOAM_language ['field_names']['user']['accountname'], $user_class -> returnAccountName ($_SESSION['current_user']['id']), $user_class -> returnColumnSize ('accountname'));
				$HTML .= '<input type="hidden" name="user|accountname" value="' . $user_class -> returnAccountName ($_SESSION['current_user']['id']) . '" />';
				$HTML .= '<label for="user|password|show-change">' . $_HOAM_language ['field_names']['user']['password']['change'];
				$HTML .= '</label>';
				$HTML .= '<input ';
				if (isset ($_SESSION["error_class_item"])) {
					if ($_SESSION["error_class_item"] -> ShowCategory ("user|password|old") OR
						$_SESSION["error_class_item"] -> ShowCategory ("user|password|new") OR
						$_SESSION["error_class_item"] -> ShowCategory ("user|password|verify")) {
						$HTML .= 'checked="checked" ';
					}
				}
				$HTML .= 'type="checkbox" id="user|password|show-change" name="user|password|show-change" value="1" />';
				$HTML .= '<fieldset id="user|change-password"';
				if (isset ($_SESSION["error_class_item"]) AND ($_SESSION["error_class_item"] -> ShowCategory ("user|password|old") OR
					$_SESSION["error_class_item"] -> ShowCategory ("user|password|new") OR
					$_SESSION["error_class_item"] -> ShowCategory ("user|password|verify"))) {
				} else {
					$HTML .= ' style="display: none;"';
				}
				$HTML .= '>';
				// Note! The field size for the password has been set to
				// just 32 chars, however the system is able to handle
				// passwords up to 255 chars long. This field limitation
				// is just for appearance.
				$HTML .= HOAM_formConfigPassword ('user|password|old', $_HOAM_language ['field_names']['user']['password']['old'], $_SESSION['user_information']['trusted_user|password|old'], '', 32);
				$HTML .= HOAM_formConfigPassword ('user|password|new', $_HOAM_language ['field_names']['user']['password']['new'], $_SESSION['user_information']['trusted_user|password|new'], '', 32, $_HOAM_setting ['user']['minimum_password_length']);
				$HTML .= '<span id="user|password|strength"></span>';
				$HTML .= HOAM_formConfigPassword ('user|password|verify', $_HOAM_language ['field_names']['user']['password']['verify'], $_SESSION['user_information']['trusted_user|password|verify'], '', 32);
				$HTML .= '</fieldset>';
				$HTML .= '</fieldset>';
				$HTML .= '<fieldset id="verification|hint">';
				$HTML .= HOAM_formSelect ('user|password|hint-name', $_HOAM_language ['field_names']['user']['password']['hint-name']);
				if (isset ($_SESSION["user_information"]["trusted_user|password|hint-name"])) {
					$default_phrase = $_SESSION["user_information"]["trusted_user|password|hint-name"];
				} else {
					$default_phrase = $user_class -> returnPasswordHintName ($_SESSION['current_user']['id']);
				}
				foreach ($GLOBALS['_HOAM_language']['password_hint_phrases'] as $phrase) {
					$HTML .= '<option ';
					if ($phrase["value"] == $default_phrase) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $phrase["value"] . '">' . $phrase["description"] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formField ('user|password|hint', $_HOAM_language ['field_names']['user']['password']['hint'], $_SESSION['user_information']['trusted_user|password|hint'], $user_class -> returnPasswordHint ($_SESSION['current_user']['id']), $user_class -> returnColumnSize ("password_hint"), $_HOAM_setting ['user']['minimum_password_hint_length']);
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formField ('user|name|first', $_HOAM_language ['field_names']['user']['name']['first'], $_SESSION['user_information']['trusted_user|name|first'], $user_class -> returnFirstname ($_SESSION['current_user']['id']), $user_class -> returnColumnSize ("firstname"), $_HOAM_setting ['user']['minimum_firstname_length']);
				$HTML .= HOAM_formField ('user|name|last', $_HOAM_language ['field_names']['user']['name']['last'], $_SESSION['user_information']['trusted_user|name|last'], $user_class -> returnLastname ($_SESSION['current_user']['id']), $user_class -> returnColumnSize ("lastname"), $_HOAM_setting ['user']['minimum_lastname_length']);
				if ($_HOAM_setting ['user']['email_validation']) {
					$HTML .= '</div>';
					$HTML .= '<div class="article_note RHS">' . $_HOAM_language ['field_names']['user']['email_validation'] . '</div>';
					$HTML .= '<div class="mandatory">';
				}
				$HTML .= HOAM_formField ('user|email', $_HOAM_language ['field_names']['user']['email'], $_SESSION['user_information']['trusted_user|email'], $user_class -> returnEmail ($_SESSION['current_user']['id']), $user_class -> returnColumnSize ("email"));
				if ($_HOAM_setting ['user']['email_validation']) {
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
				}
				$HTML .= '<label for="user|resident">' . $_HOAM_language ['field_names']['user']['resident'];
				$HTML .= HOAM_formDisplayError ('user|resident');
				$homeowner_id = $user_class -> returnHomeownerId ($_SESSION['current_user']['id']);
				$lot_id = $homeowner_class -> returnLotId ($homeowner_id);
				// Does this user have a homeowner_id associated with them?
				if ($homeowner_id) {
					// Is this user a validated resident or homeowner in the neighborhood?
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['resident'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $_SESSION['current_user']['id'])) {
						$validated_resident = TRUE;
					} else {
						$HTML .= '<span class="warning">';
						// The user has a homeowner_id, but has not been added to either the resident or homeowner group yet.
						$HTML .= $_HOAM_language ['errors']['user']['not-validated-resident'];
						$HTML .= '</span>';
					}
				}
				$HTML .= '</label>';
				$HTML .= '<input ';
				// If the user has been validated, then we want to show them the address information, but disable the input forms allowing them to change their address.
				if ($validated_resident OR
					(isset ($_SESSION["user_information"]["trusted_user|resident"]) AND $_SESSION["user_information"]["trusted_user|resident"]) OR
					$homeowner_id) {
					$HTML .= 'checked="checked" ';
				}
				if ($validated_resident) { 
					$HTML .= 'disabled="disabled" ';
				}
				$HTML .= 'type="checkbox" id="user|resident" name="user|resident" value="1" />';
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory" id="user|show-address"';
				if ($validated_resident OR
					(isset ($_SESSION["user_information"]["trusted_user|resident"]) AND $_SESSION["user_information"]["trusted_user|resident"]) OR
					$homeowner_id) {
				} else {
					$HTML .= 'style="display: none"';
				}
				$HTML .= '>';
				$HTML .= '<fieldset>';
				$HTML .= '<label for="user|address">' . $_HOAM_language ['field_names']['user']['address'];
				$HTML .= HOAM_formDisplayError ('user|address');
				$HTML .= HOAM_formDisplayHelp ('user|address');
				$HTML .= '</label>';
				$HTML .= '<select ';
				if ($validated_resident) {
					$HTML .= 'disabled="disabled" ';
				}
				$HTML .= 'name="user|address">';
				if (isset ($_SESSION["user_information"]["trusted_user|address"])) {
					$default_lot = $_SESSION['user_information']['trusted_user|address'];
				} elseif (isset ($lot_id) AND $lot_id) {
					$default_lot = $lot_id;
				} else {
					$default_lot = NULL;
				}
				$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
				if (!empty ($lot_list)) {
					foreach ($lot_list as $lot) {
						$HTML .= '<option ';
						if ($lot['id'] == $default_lot) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
						$HTML .= '</option>';
					}
				}
				$HTML .= '</select>';
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End personal info tab

				$HTML .= '<div class="tabbertab" id="user|customization" title="' . $_HOAM_language ['form_tabs']['user']['customization'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '<fieldset id="advanced">';
				$HTML .= '<label for="user|messageboard|options">' . $_HOAM_language ['field_names']['user']['messageboard']['options'];
				$HTML .= '</label>';
				$HTML .= '<fieldset id="user|messageboard|options" class="advanced_options">';
				$HTML .= HOAM_formCheckbox ('user|messageboard|acronyms', $_HOAM_language ['field_names']['user']['messageboard']['acronyms'], 1, (int) $_SESSION['user_information']['trusted_user|preference'] & (int) $_HOAM_setting ['user']['flags']['acronyms'], $user_class -> returnPreferenceAcronyms ($_SESSION['current_user']['id']));
				$HTML .= HOAM_formCheckbox ('user|messageboard|links', $_HOAM_language ['field_names']['user']['messageboard']['links'], 1, (int) $_SESSION['user_information']['trusted_user|preference'] & (int) $_HOAM_setting ['user']['flags']['convert_links'], $user_class -> returnPreferenceLinks ($_SESSION['current_user']['id']));
				$HTML .= HOAM_formCheckbox ('user|messageboard|censor', $_HOAM_language ['field_names']['user']['messageboard']['censor'], 1, (int) $_SESSION['user_information']['trusted_user|preference'] & (int) $_HOAM_setting ['user']['flags']['censor'], $user_class -> returnPreferenceCensor ($_SESSION['current_user']['id']));
				$HTML .= HOAM_formCheckbox ('user|messageboard|smileys', $_HOAM_language ['field_names']['user']['messageboard']['smileys'], 1, (int) $_SESSION['user_information']['trusted_user|preference'] & (int) $_HOAM_setting ['user']['flags']['smileys'], $user_class -> returnPreferenceSmileys ($_SESSION['current_user']['id']));
				$HTML .= '</fieldset>';

				$HTML .= '<label for="user|display|options">' . $_HOAM_language ['field_names']['user']['display']['options'];
				$HTML .= '</label>';
				$HTML .= '<fieldset id="user|display|options" class="advanced_options">';
				$HTML .= HOAM_formCheckbox ('user|display|holidays', $_HOAM_language ['field_names']['user']['display']['holidays'], 1, (int) $_SESSION['user_information']['trusted_user|preference'] & (int) $_HOAM_setting ['users']['flags']['holidays'], $user_class -> returnPreferenceHolidays ($_SESSION['current_user']['id']));
/*				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
					$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
					$HTML .= HOAM_formCheckbox ('user|display|log', $_HOAM_language ['field_names']['user']['display']['log'], 1, (int) $_SESSION['user_information']['trusted_user|preference'] & (int) $_HOAM_setting ['users']['flags']['log'], $user_class -> isBitSet ($_SESSION['current_user']['id'], $_HOAM_setting ['user']['flags']['log'], 'preference'));
				}
*/
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formField ('user|edit_cols', $_HOAM_language ['field_names']['user']['edit_cols'], $_SESSION['user_information']['trusted_user|edit_cols'], $user_class -> returnPreferenceEditCol ($_SESSION['current_user']['id']), 5, 40);
				$HTML .= HOAM_formField ('user|edit_rows', $_HOAM_language ['field_names']['user']['edit_rows'], $_SESSION['user_information']['trusted_user|edit_rows'], $user_class -> returnPreferenceEditRow ($_SESSION['current_user']['id']), 5, 2);
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End User Customization tab

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
				$message_list = $messageboard_class -> listAuthor ($_SESSION['current_user']['id']);
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

				$HTML .= '</div>'; // End tabber
				$HTML .= '</form>';
			} else {
			$HTML = '<img src="/hoam/images/icons/user_edit.png" alt="' . $_HOAM_language ['field_names']['account']['modify'] . '" class="LHS" /><a href="/user/settings/">' . $_HOAM_language ['field_names']['account']['modify'] . '</a><br />';
			}
		} else {
			header ('Location: /error/404/');
			exit ();
		}
		return ($HTML);
	}
	header ('Location: /error/login/');
	exit ();
}

function HOAM_userValidate () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	// Only show this to users logged NOT in
	if (!isset ($_SESSION['current_user']['id'])) {
		switch ($GLOBALS['global_url_array'][1]) {
			default:
				if (!empty ($GLOBALS['global_url_array'][2])) {
					$user_class = new HOAM_user ('users');
					$validation_class = new HOAM_user_validation ('user_validation');
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
					
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['user']['validate']['email'])));
					$trusted_hash = $validation_class -> superclean ($GLOBALS['global_url_array'][2], $validation_class -> returnColumnSize ('hash'));
					$trusted_id = $validation_class -> returnIdWithHash ($trusted_hash);
					if ($trusted_id) {
						$trusted_userid = $validation_class -> returnUserId ($trusted_id);
						$trusted_username = $user_class -> returnAccountName ($trusted_userid);
						$HTML = '<h1>' . $_HOAM_language ['field_names']['user']['validate']['email'] . '</h1>';
						$HTML .= $_HOAM_language ['help_pages']['user']['email']['validate'];
						$HTML .= '<form id="verify_address" action="/hoam/scripts/user/userValidateEmailAddress.php" method="post">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formFieldDisabled ('user|accountname', $_HOAM_language ['field_names']['user']['accountname'], $trusted_username, $user_class -> returnColumnSize ('accountname'));
						$HTML .= '<input type="hidden" name="user|accountname" value="' . $trusted_username . '" />';
						$HTML .= '<input type="hidden" name="user|hash" value="' . $trusted_hash . '" />';
						$HTML .= '<label for="user|hint">' . HOAM_userReturnSecurityQuestion ($user_class -> returnPasswordHintName ($trusted_userid)) . '</label>';
						$HTML .= HOAM_formField ('user|hint', $_HOAM_language ['field_names']['user']['password']['hint'], '', '', $user_class -> returnColumnSize ("password_hint"), $_HOAM_setting ['user']['minimum_password_hint_length']);
						$HTML .= '<br /><br />';
						$HTML .= '<button type="submit">' . $_HOAM_language ['form_buttons']['user']['validate'] . '</button>';
						$HTML .= '</div>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /error/id_required/');
					exit ();
				}
				break;
		}
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userValidated () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;

	// Only show this to users logged NOT in
	if (!isset ($_SESSION['current_user']['id'])) {
		$HTML = '<h1>' . $_HOAM_language ['field_names']['account']['validated'] . '</h1>';
		$HTML .= $_HOAM_language ['help_pages']['user']['validated'];
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

function HOAM_userJS () {
	echo '<script type="text/javascript" src="/hoam/javascript/user.js"></script>';	
}

switch ($GLOBALS['global_url_array'][1]) {
	case ('created') :
		$_HOAM_log -> add ('Loading /user/created/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userCreated (TRUE);
		break;
	case ('disabled') :
		$_HOAM_log -> add ('Loading /user/disabled/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userDisabled ();
		break;
	case ('enabled') :
		$_HOAM_log -> add ('Loading /user/enabled/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userEnabled ();
		break;
	case ('login') :
		$_HOAM_log -> add ('Loading /user/login/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userLogin (TRUE);
		break;
	case ('logout') :
		$_HOAM_log -> add ('Loading /user/logout/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userLogout (TRUE);
		break;
	case ('new') :
		$_HOAM_log -> add ('Loading /user/new/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userNew (TRUE);
		break;
	case ('password_age') :
		$_HOAM_log -> add ('Loading /user/password_age/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userPasswordAge (TRUE);
		break;
	case ('password_forgot') :
		$_HOAM_log -> add ('Loading /user/password_forgot/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userPasswordForgot (TRUE);
		break;
	case ('password_reset') :
		$_HOAM_log -> add ('Loading /user/password_reset/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userPasswordReset ();
		break;
	case ('password_reset_question') :
		$_HOAM_log -> add ('Loading /user/password_reset_question/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userPasswordResetQuestion ();
		break;
	case ('password_reset_sent') :
		$_HOAM_log -> add ('Loading /user/password_reset_sent/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userPasswordResetSent ();
		break;
	case ('password_warn') :
		$_HOAM_log -> add ('Loading /user/password_warn/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userPasswordWarn (TRUE);
		break;
	case ('settings') :
		$_HOAM_log -> add ('Loading /user/settings/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userSettings (TRUE);
		break;
	case ('validate') :
		$_HOAM_log -> add ('Loading /user/validate/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userValidate ();
		break;
	case ('validated') :
		$_HOAM_log -> add ('Loading /user/validated/ page.', HOAM_MESSAGE_INFORMATIONAL);
		echo HOAM_userValidated ();
		break;
	default:
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $GLOBALS['_HOAM_language'] ['field_names']['admin']['user'])));
		echo '<h1>' . $GLOBALS['_HOAM_language'] ['field_names']['admin']['user'] . '</h1>';
		if (isset ($_SESSION['current_user']['id'])) {
			echo HOAM_userLogout ();
			echo HOAM_userSettings ();
		} else {
			echo $_HOAM_language ['help_pages']['user']['login'];
			echo HOAM_userNew ();
//			echo HOAM_userPasswordForgot ();
//			echo HOAM_userValidate ();
		}
		echo '</ul>';
		break;
}

?>
