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

function HOAM_lotInit () {
	if ($('lot|street-other_enable')) {
		// Only in effect when adding or modifying a lot.
		Element.observe ($('lot|street-other_enable'), 'change', HOAM_lotStreetOtherToggle);
		HOAM_lotStreetOtherToggle ();
		if (HOAM_attachmentAdd) {
			var front_counter = 0;
			var rear_counter = 0;
			var counter = 0;
			Element.observe ($('lot|view|front'), 'click', function () {
				HOAM_attachmentAdd (front_counter++, $('lot|view|front'));
			});
			Element.observe ($('lot|view|rear'), 'click', function () {
				HOAM_attachmentAdd (rear_counter++, $('lot|view|rear'));
			});
		}
		Element.observe ($('lot|list_existing'), 'change', HOAM_lotListExistingUpdate);
		HOAM_lotListExistingUpdate ();
	} else {
		// Only in effect after adding a new lot
		if ($('lot_added')) {
			if (confirm (HOAM_languageLookup ('field_names|lot|added'))) {
				window.location = 'http://' + location.host + '/admin/homeowner/add/' + $('lot_added').getAttribute ('data') + '/';
				return true;
			}
		}
	}
}

function HOAM_lotStreetOtherToggle () {
	if ($('lot|street-other_enable').checked) {
		$('lot|street').disable ();
		$('lot|street-other').enable ();
	} else {
		$('lot|street').enable ();
		$('lot|street-other').disable ();
	}
}

function HOAM_lotListExistingUpdate () {
	// Update the show_description and show_detail textareas.
	var url = '/hoam/scripts/lot/xml_lot_list_existing.php';
	var pars = 'street=' + $F('lot|list_existing');
	
	var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: HOAM_lotListExistingUpdateFields});
}

function HOAM_lotListExistingUpdateFields (originalRequest) {
	// put returned XML in the textarea
	var items = originalRequest.responseXML.getElementsByTagName ('list');
	var lot_list = '';
	for (count = 0; count < items.length; count++) {
		lot_list += getElementTextNS ('', 'tablerow', items[count], 0);
	}
	$('existing_lot_list').innerHTML = lot_list;
	ZebraTable.stripe ($('existing_lot_list'));
}

Event.observe (window, 'load', HOAM_lotInit);
