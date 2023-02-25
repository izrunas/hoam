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
 * Questions specific to HOAM should be directed to ARP Realty. Please see the
 * HOAM web site at http://hoam.arprs.com/
 *
 * Some portions of HOAM incorporate ideas and/or code from other sources, and
 * those portions are explicitly mentioned and attributed in the relevant
 * section of HOAM source code. Questions about that code should be directed to
 * the original authors.
 *
 */

function HOAM_adminHomeownerSaleInit () {
	if ($('sale|delete')) {
		Element.observe ($('sale|delete'), 'click', function (event) {
			Event.stop (event);
			HOAM_adminHomeownerSaleDelete ();
			});
	}
	HOAM_adminHomeownerSaleAttachmentYear ();
}

function HOAM_adminHomeownerSaleAttachmentYear () {
	var span_fields = $('page_content').getElementsByTagName ('SPAN');
	for (var i = 0; i < span_fields.length; i++) {
		field_type = span_fields[i].getAttribute ('field');
	    // Look for specific fields and apply validation where applicable
		switch (field_type) {
			case 'year':
				span_fields[i].style.cursor = 'pointer';
				Element.observe (span_fields[i], 'click', function (event) {
					if (Event.element(event).nextSibling.visible()) {
						Event.element(event).innerHTML = '&#x0229E; ' + Event.element(event).innerHTML.substr(2);
						Event.element(event).nextSibling.hide();
					} else {
						Event.element(event).innerHTML = '&#x0229F; ' + Event.element(event).innerHTML.substr(2);
						Event.element(event).nextSibling.show();
					}
				});
				break;
			default:
				// No default action.
				break;
		}
	}
}

function HOAM_adminHomeownerSaleDelete () {
	var owner = $F('homeowner|sale|id');
	var url = '/hoam/scripts/attachment/xml_attachment_exist.php';
	var pars = 'owner=' + owner;
	var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		var attachments = originalRequest.responseXML.getElementsByTagName ('attachment');
		if (attachments.length > 0) {
			if (confirm (attachments.length + HOAM_languageLookup ('field_names|homeowner|sale|delete_attachments'))) {
				var delete_input = document.createElement ('input');
				delete_input.type = 'hidden';
				delete_input.name = 'delete';
				delete_input.value = 'delete';	
				$('delete_sale').appendChild (delete_input);
			} else {
				var cancel_input = document.createElement ('input');
				cancel_input.type = 'hidden';
				cancel_input.name = 'cancel';
				cancel_input.value = 'cancel';	
				$('delete_sale').appendChild (cancel_input);
			}
		} else {
			var delete_input = document.createElement ('input');
			delete_input.type = 'hidden';
			delete_input.name = 'delete';
			delete_input.value = 'delete';	
			$('delete_sale').appendChild (delete_input);
		}
		$('delete_sale').submit();
	}});
}

Event.observe (window, 'load', HOAM_adminHomeownerSaleInit);
