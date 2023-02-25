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

function HOAM_groupInit () {
	if ($('group_list')) {
		Element.observe ($("show|group"), 'change', HOAM_groupInfoLoad);
		// Populate the fields on page load.
		HOAM_groupInfoLoad ();
	}
	if ($('group_ismember')) {
		Element.observe ($("user|accountname"), 'change', HOAM_groupIsMemberLoad);
		Element.observe ($("group|name"), 'change', HOAM_groupIsMemberLoad);
		// Populate the fields on page load.
		HOAM_groupIsMemberLoad ();
	}
	// 'group|membership' is the membership editing form.
	if ($('group|membership')) {
		Element.observe ($('group|groups|add'), 'click', function () {
			HOAM_moveOption ($('group|membership'),  'group|groups_list', 'group|groups');
		});
		Element.observe ($('group|groups|remove'), 'click', function () {
			HOAM_moveOption ($('group|membership'),  'group|groups', 'group|groups_list');
		});
		Element.observe ($('group|users|add'), 'click', function () {
			HOAM_moveOption ($('group|membership'),  'group|users_list', 'group|users');
		});
		Element.observe ($('group|users|remove'), 'click', function () {
			HOAM_moveOption ($('group|membership'),  'group|users', 'group|users_list');
		});
		Element.observe ($('group|membership'), 'submit', function () {
			setSelectAll ('group|groups');
			setSelectAll ('group|users');
		});
	}
	
}

function HOAM_groupInfoLoad () {
	// Update the show_name, show_description, and show_flag_* checkboxes.
	var id = $F('show|group');
	var url = '/hoam/scripts/group/xml_group_info.php';
	var pars = 'id=' + id;

	var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: HOAM_groupInfoUpdate});
}

function HOAM_groupInfoUpdate (originalRequest) {
	// put returned XML in the textarea
	var items = originalRequest.responseXML.getElementsByTagName ("item");
	$('show|description').value = getElementTextNS ("", 'description', items[0], 0);
	$('show|member_count').value = getElementTextNS ("", 'member_count', items[0], 0);
}

function HOAM_groupIsMemberLoad () {
	// Update the show_name, show_description, and show_flag_* checkboxes.
	var uid = $F('user|accountname');
	var gid = $F('group|name');
	var url = '/hoam/scripts/group/groupUserIsMember.php';
	var pars = 'uid=' + uid + '&gid=' + gid;

	var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: HOAM_groupIsMemberUpdate});
}

function HOAM_groupIsMemberUpdate (originalRequest) {
	if (originalRequest.responseText == '1') {
		$('fillme').innerHTML = HOAM_languageLookup ('errors|group|is_member');
	} else {
		$('fillme').innerHTML = HOAM_languageLookup ('errors|group|is_not_member');
	}
}

Event.observe (window, 'load', HOAM_groupInit);
