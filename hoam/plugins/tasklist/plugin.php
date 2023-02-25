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

// The name of the plugin that will be displayed on the administration page.
$plugin ['name'] = 'Tasklist';
// Priority ranges from 0-10, with 0 being the most important, 10 the least.
$plugin ['priority'] = '5';
// Does the plugin have a user-interface (eg, does a typical end-user need to
// interact with the plugin, or does it only affect the back-end of the system)
$plugin ['user'] = 1;
// What path should be used to access this plugin when administering, providing
// user access, etc.?
$plugin ['path'] = 'tasklist';
// Define functions for required actions
$plugin ['config'] = 'HOAM_pluginTasklistConfig';
$plugin ['disable'] = 'HOAM_pluginTasklistDisable';
$plugin ['enable'] = 'HOAM_pluginTasklistEnable';
$plugin ['init'] = 'HOAM_pluginTasklistInit';
$plugin ['install'] = 'HOAM_pluginTasklistInstall';
$plugin ['remove'] = 'HOAM_pluginTasklistRemove';

function HOAM_pluginTasklistInit () {
	global $_HOAM_language, $_HOAM_setting, $_HOAM_queue_head;
	// Usually the init code will simply insert itself into the necessary
	// queues and possibly load configuration information.
	//
	// Eventually the language files could be dumped into separate files and
	// loaded as needed (like is done at startup).
	$_HOAM_language ['errors']['plugin']['tasklist']['none'] = 'You have no tasks due.';
	$_HOAM_language ['errors']['plugin']['tasklist']['text_too_short'] = 'The Task text must be at least %d characters.';
	$_HOAM_language ['errors']['plugin']['tasklist']['unknown_id'] = 'The task ID provided is unknown.';
	$_HOAM_language ['errors']['plugin']['tasklist']['unsaved'] = 'There are unsaved changes to one of your tasks. Please press OK to Save these changes, or Cancel to ignore.';
	$_HOAM_language ['errors']['plugin']['tasklist']['text_too_small'] = 'The task must be at least %d characters.';
	$_HOAM_language ['errors']['plugin']['tasklist']['unsaved'] = 'The changes you made have not been saved. Select OK to continue or Cancel to go back.';
	
	$_HOAM_language ['field_names']['plugin']['tasklist']['config'] = 'Configure Tasklist Plugin';
	$_HOAM_language ['field_names']['plugin']['tasklist']['disable'] = 'Disable Tasklist Plugin';
	$_HOAM_language ['field_names']['plugin']['tasklist']['enable'] = 'Enable Tasklist Plugin';
	$_HOAM_language ['field_names']['plugin']['tasklist']['enabled'] = 'Enable Tasklist Plugin';
	$_HOAM_language ['field_names']['plugin']['tasklist']['install'] = 'Install Tasklist Plugin';
	$_HOAM_language ['field_names']['plugin']['tasklist']['minimum_text_length'] = 'Minimum Task Description Length';
	$_HOAM_language ['field_names']['plugin']['tasklist']['remove'] = 'Remove Tasklist Plugin';
	$_HOAM_language ['field_names']['plugin']['tasklist']['status']['assigned'] = 'Assigned';
	$_HOAM_language ['field_names']['plugin']['tasklist']['status']['cancelled'] = 'Cancelled';
	$_HOAM_language ['field_names']['plugin']['tasklist']['status']['complete'] = 'Complete';
	$_HOAM_language ['field_names']['plugin']['tasklist']['status']['on_hold'] = 'On Hold';
	$_HOAM_language ['field_names']['plugin']['tasklist']['status']['in_progress'] = 'In Progress';
	$_HOAM_language ['field_names']['plugin']['tasklist']['status']['new'] = 'New';
	$_HOAM_language ['field_names']['plugin']['tasklist']['status']['planning'] = 'Planning';
	$_HOAM_language ['field_names']['plugin']['tasklist']['status']['rejected'] = 'Rejected';

	$_HOAM_language ['help_popups']['plugin']['tasklist']['enabled'] = '<h3>Enabled</h3><p>This affects whether the plugin is enabled or not; it may still be configured and any changes made are saved.</p>';

	if ($_HOAM_setting ['plugin']['tasklist']['enabled']) {
		HOAM_queueInsertAfter ($GLOBALS['_HOAM_queue_head'], 'headCSS', 'HOAM_pluginTasklistCSS');
		HOAM_queueInsertAfter ($GLOBALS['_HOAM_queue_head'], 'headScripts', 'HOAM_pluginTasklistJS');
	}
}

function HOAM_pluginTasklistConfig () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;
	require_once (HOAM_PATH_FUNCTION . 'forms.php');
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['plugin']['tasklist']['config'])));
	$HTML = '<h1>' . $_HOAM_language ['field_names']['plugin']['tasklist']['config'] . '</h1>';
	$HTML .= '<form id="plugin_modify" action="/hoam/plugins/tasklist/configure.php" method="post">';
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
	$HTML .= '<div class="tabbertab" id="plugin_modify" title="' . $_HOAM_language ['form_tabs']['plugin']['information'] . '">';
	$HTML .= '<div class="mandatory">';
	$HTML .= '<fieldset>';
	$HTML .= HOAM_formCheckbox ('plugin|tasklist|enabled', $_HOAM_language ['field_names']['plugin']['tasklist']['enabled'], 1, $_SESSION['plugin_information']['trusted_plugin|tasklist|enabled'], $_HOAM_setting ['plugin']['tasklist']['enabled']);
	$HTML .= '</fieldset>';
	$HTML .= HOAM_formField ('plugin|tasklist|minimum_text_length', $_HOAM_language ['field_names']['plugin']['tasklist']['minimum_text_length'], $_SESSION['plugin_information']['trusted_plugin|tasklist|minimum_text_length'], $_HOAM_setting ['plugin']['tasklist']['minimum_text_length'], 5, 1);
	$HTML .= HOAM_formField ('plugin|tasklist|flag|status_new', $_HOAM_language ['field_names']['plugin']['tasklist']['status']['new'], $_SESSION['plugin_information']['trusted_plugin|tasklist|flag|status_new'], $_HOAM_setting ['plugin']['tasklist']['flag']['status_new'], 20, 1);
	$HTML .= HOAM_formField ('plugin|tasklist|flag|status_assigned', $_HOAM_language ['field_names']['plugin']['tasklist']['status']['assigned'], $_SESSION['plugin_information']['trusted_plugin|tasklist|flag|status_assigned'], $_HOAM_setting ['plugin']['tasklist']['flag']['status_assigned'], 20, 1);
	$HTML .= HOAM_formField ('plugin|tasklist|flag|status_planning', $_HOAM_language ['field_names']['plugin']['tasklist']['status']['planning'], $_SESSION['plugin_information']['trusted_plugin|tasklist|flag|status_planning'], $_HOAM_setting ['plugin']['tasklist']['flag']['status_planning'], 20, 1);
	$HTML .= HOAM_formField ('plugin|tasklist|flag|status_in_progress', $_HOAM_language ['field_names']['plugin']['tasklist']['status']['in_progress'], $_SESSION['plugin_information']['trusted_plugin|tasklist|flag|status_in_progress'], $_HOAM_setting ['plugin']['tasklist']['flag']['status_in_progress'], 20, 1);
	$HTML .= HOAM_formField ('plugin|tasklist|flag|status_on_hold', $_HOAM_language ['field_names']['plugin']['tasklist']['status']['on_hold'], $_SESSION['plugin_information']['trusted_plugin|tasklist|flag|status_on_hold'], $_HOAM_setting ['plugin']['tasklist']['flag']['status_on_hold'], 20, 1);
	$HTML .= HOAM_formField ('plugin|tasklist|flag|status_cancelled', $_HOAM_language ['field_names']['plugin']['tasklist']['status']['cancelled'], $_SESSION['plugin_information']['trusted_plugin|tasklist|flag|status_cancelled'], $_HOAM_setting ['plugin']['tasklist']['flag']['status_cancelled'], 20, 1);
	$HTML .= HOAM_formField ('plugin|tasklist|flag|status_rejected', $_HOAM_language ['field_names']['plugin']['tasklist']['status']['rejected'], $_SESSION['plugin_information']['trusted_plugin|tasklist|flag|status_postponed'], $_HOAM_setting ['plugin']['tasklist']['flag']['status_rejected'], 20, 1);
	$HTML .= HOAM_formField ('plugin|tasklist|flag|status_complete', $_HOAM_language ['field_names']['plugin']['tasklist']['status']['complete'], $_SESSION['plugin_information']['trusted_plugin|tasklist|flag|status_complete'], $_HOAM_setting ['plugin']['tasklist']['flag']['status_complete'], 20, 1);
	$HTML .= '</div>';			
	$HTML .= '</div>'; // End plugin configuration

	$HTML .= '</div>'; // End Tabber
	$HTML .= '</form>';
	
	return ($HTML);
}

function HOAM_pluginTasklistCSS () {
	global $_HOAM_setting;
	
	$group_members_class = new HOAM_group_members ('group_members');
	
	if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['tasklist_personal'], $_SESSION['current_user']['id'])) {
		echo '<link rel="stylesheet" type="text/css" href="/hoam/plugins/tasklist/tasklist.css" />';
	}
}

function HOAM_pluginTasklistDisable () {
	return (HOAM_pluginTasklistConfig);
}

function HOAM_pluginTasklistEnable () {
	return (HOAM_pluginTasklistConfig);
}

function HOAM_pluginTasklistInstall () {
	global $_HOAM_language, $_HOAM_queue_head;
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['plugin']['tasklist']['install'])));
	$HTML = '<h1>' . $_HOAM_language ['field_names']['plugin']['tasklist']['install'] . '</h1>';
	return ($HTML);
}

function HOAM_pluginTasklistJS () {
	global $_HOAM_setting;
	
	$group_members_class = new HOAM_group_members ('group_members');
	
	if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['tasklist_personal'], $_SESSION['current_user']['id'])) {
		echo '<script async="true" type="text/javascript" src="/hoam/plugins/tasklist/tasklist.js"></script>';
	}
}

function HOAM_pluginTasklistMini () {
	global $_HOAM_language, $_HOAM_setting;

	if ($_HOAM_setting ['plugin']['tasklist']['enabled']) {
		$group_members_class = new HOAM_group_members ('group_members');
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['tasklist_personal'], $_SESSION['current_user']['id'])) {
			// Tasklist
			require_once (HOAM_PATH_PLUGIN . 'tasklist/class.php');
			require_once (HOAM_PATH_PLUGIN . 'tasklist/functions.php');

			$tasklist_class = new tasklist ('tasks');
			$HTML = '<div class="toolbox mini-tasklist" id="mini-tasklist">';
			$HTML .= '<h1><a href="/apps/tasklist">Tasklist</a></h1>';

			// Get an array with at most 5 tasks
			$task_list = $tasklist_class -> listDue ($_SESSION['current_user']['id'], 5);

			// $tasks will be FALSE if none were found in the database.
			if (is_array ($task_list)) {
				$HTML .= '<ul>';
				foreach ($task_list as $task) {
					// The code to support visible truncating is available and
					// works, but the live-edit JS isn't smart enough to
					// properly handle it yet.
					// $HTML .= tasklist_returnTaskMiniHTML ($task, TRUE);
					$HTML .= tasklist_returnTaskMiniHTML ($task);
				}
				$HTML .= '</ul>';
				$total_tasks_due = $tasklist_class -> returnCountDueUser ($_SESSION['current_user']['id']);
				if ($total_tasks_due > count ($task_list)) {
					$HTML .= '<div class="center mini-tasklist-additional">[ Showing 5 of ' . $total_tasks_due . ' tasks due. ]</div>';
				}
			} else {
				$HTML .= $_HOAM_language ['errors']['plugin']['tasklist']['none'];
			}
			$HTML .= '</div>';
			return ($HTML);
		}
	}
}

function HOAM_pluginTasklistRemove () {
	global $_HOAM_language, $_HOAM_queue_head;
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['plugin']['tasklist']['remove'])));
	$HTML = '<h1>' . $_HOAM_language ['field_names']['plugin']['tasklist']['remove'] . '</h1>';
	return ($HTML);
}

?>