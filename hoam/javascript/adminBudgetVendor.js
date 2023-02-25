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

function HOAM_adminBudgetVendorInit () {
	if ($('delete_button')) {
		Element.observe ($('delete_button'), 'click', function (event) {
			Event.stop (event);
			HOAM_adminBudgetVendorDelete ();
			});
	} else {
		$j('#budget\\|vendor\\|telephone\\|fax').on ('change', HOAM_verifyTelephone);
		$j('#budget\\|vendor\\|telephone\\|work').on ('change', HOAM_verifyTelephone);
		// Currently the postalcode is optional.
		if ($j('#budget\\|vendor\\|postalcode').length) {
			$j('#budget\\|vendor\\|postalcode').on ('change', HOAM_verifyPostalcode);
		}
	}
}

function HOAM_adminBudgetVendorDelete () {
	var owner = $F('vendor|id');
	var url = '/hoam/scripts/attachment/xml_attachment_exist.php';
	var pars = 'owner=' + owner;
	var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		var attachments = originalRequest.responseXML.getElementsByTagName ('attachment');
		if (attachments.length > 0) {
			if (confirm (attachments.length + HOAM_languageLookup ('field_names|budget|vendor|delete_attachments'))) {
				var delete_input = document.createElement ('input');
				delete_input.type = 'hidden';
				delete_input.name = 'delete';
				delete_input.value = 'delete';	
				$('vendor_delete').appendChild (delete_input);
			} else {
				var cancel_input = document.createElement ('input');
				cancel_input.type = 'hidden';
				cancel_input.name = 'cancel';
				cancel_input.value = 'cancel';	
				$('vendor_delete').appendChild (cancel_input);
			}
		} else {
			var delete_input = document.createElement ('input');
			delete_input.type = 'hidden';
			delete_input.name = 'delete';
			delete_input.value = 'delete';	
			$('vendor_delete').appendChild (delete_input);
		}
		$('vendor_delete').submit();
	}});
}

$j(document).ready (HOAM_adminBudgetVendorInit, false);