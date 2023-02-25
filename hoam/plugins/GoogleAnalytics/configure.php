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

session_start ();

$group_members_class = new HOAM_group_members ('group_members');

// Is the current user allowed to add / edit articles?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['plugin_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['plugin_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['plugin_edit'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['cancel']) OR isset ($_POST['modify'])) {
		// If this is the second (or greater) time through the script (ie, there
		// were errors that had to be fixed before committing the changes) get
		// rid of the old cached information the user entered, we're going to
		// populate it with information just received from the form posting.
		if (isset ($_SESSION['plugin_information'])) {
			unset ($_SESSION['plugin_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the action, send them back to the main page
			header ('Location: /website/');
		} elseif (isset ($_POST['remove'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['plugin_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
				// OK, there's going to need to be code here for removing the
				// plugin configuration options, and then deleting the plugin
				// files themselves. This needs to be different from the
				// disable code.
				if ($result) {
					// Send the user back to the website administration page.
					header ('Location: /website/');
				} else {
					// Error Removing Item
					header ('Location: /error/remove/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/401/');
			}
		} else {
			$_SESSION['plugin_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			$_SESSION['plugin_information']['trusted_plugin|ga|enabled'] = (int) $_POST['plugin|ga|enabled'];
			$_SESSION['plugin_information']['trusted_plugin|ga|code'] = (string) $group_members_class -> superClean ($_POST['plugin|ga|code'], 12);
			if (preg_replace ($_HOAM_language ['plugin']['ga']['match'], $_HOAM_language ['plugin']['ga']['format'], $_SESSION ['plugin_information']['trusted_plugin|ga|code'], 1)) {
			} else {
				if ($_SESSION['plugin_information']['trusted_plugin|ga|enabled']) {
					// The code is not in the correct format.
					$_SESSION ['error_class_item'] -> addError ('plugin|ga|code', $_HOAM_language ['field_names']['plugin']['ga']['code-format']);
				} else {
					// We can ignore any formatting issues since the plugin is
					// disabled. Don't display an error.
				}
			}
			
// echo print_r ($_POST); print_r ($_SESSION['plugin_information']); $_SESSION['error_class_item'] -> errorCount; print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				header ('location: /website/plugin/config/ga/');
			} else {
				global $_HOAM_config;
				$_HOAM_config -> setValue ('plugin/ga/enabled', $_SESSION['plugin_information']['trusted_plugin|ga|enabled']);
				$_HOAM_config -> setValue ('plugin/ga/code', preg_replace ($_HOAM_language ['plugin']['ga']['match'], $_HOAM_language ['plugin']['ga']['format'], $_SESSION ['plugin_information']['trusted_plugin|ga|code'], 1));

				/*************************************************************************/

				// Clear the error_class_item variable since everything worked ok.
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}
				// Clear the plugin_information variable since everything worked ok.
				if (isset ($_SESSION['plugin_information'])) {
					unset ($_SESSION['plugin_information']);
				}

				header ('Location: /website/');
			}
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

?>
