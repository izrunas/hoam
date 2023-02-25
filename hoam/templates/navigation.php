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

$HTML  = '<div id="page_navigation">';
$HTML .= '<ul>';

$sections = array ();
$sections [0]['groups'] = array ($_HOAM_setting ['group']['ids']['everyone']);
$sections [0]['name'] = 'Home';
$sections [0]['url'] = '/';
$sections [1]['groups'] = array ($_HOAM_setting ['group']['ids']['everyone']);
$sections [1]['name'] = 'Contact Us';
$sections [1]['url'] = '/contact/';
$sections [2]['groups'] = array ($_HOAM_setting ['group']['ids']['everyone']);
$sections [2]['name'] = 'Messageboards';
$sections [2]['url'] = '/apps/messageboard/';
$sections [3]['groups'] = array ($_HOAM_setting ['group']['ids']['everyone']);
$sections [3]['name'] = 'Community Information';
$sections [3]['url'] = '/community/';
$sections [4]['groups'] = array ($_HOAM_setting ['group']['ids']['admin'], $_HOAM_setting ['group']['ids']['board'], $_HOAM_setting ['group']['ids']['officer']);
$sections [4]['name'] = 'HOA Administration';
$sections [4]['url'] = '/admin/';
$sections [5]['groups'] = array ($_HOAM_setting ['group']['ids']['admin'], $_HOAM_setting ['group']['ids']['board'], $_HOAM_setting ['group']['ids']['officer']);
$sections [5]['name'] = 'Website Administration';
$sections [5]['url'] = '/website/';
$sections [6]['groups'] = array ($_HOAM_setting ['group']['ids']['everyone']);
$sections [6]['name'] = 'Help';
$sections [6]['url'] = '/help/';

// Display the sections in the navbar
foreach ($sections as $section) {
	foreach ($section['groups'] as $group) {
		if ($group_members_class -> isMember ($group, $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
			// Yes, the user is a valid member. Display the link and break
			// out of the loop.
			$HTML .= '<li><a href="' . $section['url'] . '" title="' . $section['name'] . '">' . $section['name'] . '</a></li>';
			break;
		}
	}
}

$HTML .= '</ul>';
$HTML .= '</div>';

echo $HTML;

?>
