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

global $_HOAM_language, $_HOAM_setting;

$group_members_class = new HOAM_group_members ('group_members');

// Should the entire toolbar be moved to a queue? That would make insertion of
// new plugins much easier.

$HTML = '<div id="page_toolbar">';
// Begin User Account Tool Box
$user_class = new HOAM_user ('users'); 
	
$HTML .= '<div class="toolbox">';
$HTML .= '<div>';
	
// If the user has not logged in, then the variable will not be set.
if (isset ($_SESSION['current_user']['id'])) {
	$HTML .= '<a href="/user/logout/">' . $_HOAM_language ['field_names']['user']['logout'] . '</a><br/><br/>';
	$HTML .= '<a href="/user/settings/">' . $_HOAM_language ['field_names']['user']['manage']['account'] .'</a>';
	if ($user_class -> returnHomeownerId ($_SESSION['current_user']['id'])) {
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['resident'], $_SESSION['current_user']['id'])) {
			$HTML .= '<br /><br />';
			$HTML .= '<a href="/residence/">' . $_HOAM_language ['field_names']['user']['manage']['residence'] . '</a>';
		}
		// Are users allowed to enter work requests
		if ($_HOAM_setting ['work_request']['allow_user_requests']) {
			$HTML .= '<br /><br />';
			$HTML .= '<a href="/residence/work_request/">' . $_HOAM_language ['field_names']['user']['manage']['requests'] . '</a>';
		}
	}
} else {
	$HTML .= '<form id="user_loginForm" action="/user/login/" method="post">';
	$HTML .= '<div>';
	$HTML .= '<strong>Log In:</strong><br /><br />';
	$HTML .= 'u: <input type="text" id="user_loginName" name="username" maxlength="32" size="11" tabindex="1" value="';
	// The "user_information" array will _only_ exist if the user just created a new account. It's left over from /scripts/user/edit.script.php
	if (isset ($_SESSION['user_information']['trusted_user|accountname']) AND !isset ($_SESSION['error_class_item'])) {
		$HTML .= $_SESSION['user_information']['trusted_user|accountname'];
	} else {
		$HTML .= 'user name';
	}
	$HTML .= '" /><br/>';
	// Note! The field size for the password has been set to just 32 chars,
	// however the system is able to handle passwords up to 255 chars long. This
	// field limitation is just for appearance.
	$HTML .= 'p: <input type="password" id="user_loginPassword" name="password" maxlength="32" size="11" tabindex="2" value="';
	if (isset ($_SESSION['user_information']['trusted_user|password|new']) AND !isset ($_SESSION['error_class_item'])) {
		$HTML .= $_SESSION['user_information']['trusted_user|password|new'];
	} else {
		$HTML .= 'password';
	}
	$HTML .= '" /><br />';
	$HTML .= '<button type="submit" name="Log In" value="Log In" tabindex="3">Log In</button>';
	$HTML .= '<br /><br />';
	$HTML .= '<a href="/user/new/"><strong>I am a New User</strong></a>';
	$HTML .= '</div>';
	$HTML .= '</form>';
}
$HTML .= '</div>';
	
$HTML .= '<br />';
$HTML .= '{{WEBSITE_USERS_ONLINE}}';

$HTML .= '</div>';
// End User Account Tool Box

// Begin Email Tool Box
// This needs to be moved to a plugin.

// Begin Calendar Tool Box
// This needs to be moved to a plugin.

// Begin Tasklist Tool Box
// This needs to be moved to the plugin. It also needs to be changed to insert
// itself into the toolbar queue rather than being called manually (eg, HOAM
// will crash if the plugin isn't installed right now).
$HTML .= HOAM_pluginTasklistMini ();
// End Tasklist Tool Box

// The below areas are always displayed regardless of logged in / out

// Begin Vote Tool Box
// This needs to be moved to a plugin.

$ad = HOAM_returnAdvertisement ($_HOAM_setting ['advertising']['flags']['position1']);
if (!empty ($ad)) {
	$HTML .= '<div class="toolbox">' . $ad . '<br /></div>';
}
unset ($ad);
// End Advertising Tool Box

// End class="toolbar" div
$HTML .= '</div>';

echo $HTML;
?>
