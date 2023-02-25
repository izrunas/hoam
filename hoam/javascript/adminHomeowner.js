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

function HOAM_homeownerInit () {
	Element.observe ($('homeowner|postalcode'), 'change', HOAM_verifyPostalcode);
	Element.observe ($('homeowner|purchasedate'), 'change', HOAM_verifyDate);
	Element.observe ($('homeowner|residence_mailing'), 'click', HOAM_homeownerToggleResidenceMailing);
	Element.observe ($('homeowner|saledate'), 'change', HOAM_verifyDate);
	Element.observe ($('homeowner|telephone|home'), 'change', HOAM_verifyTelephone);
	Element.observe ($('homeowner|telephone|fax'), 'change', HOAM_verifyTelephone);
	Element.observe ($('homeowner|telephone|mobile'), 'change', HOAM_verifyTelephone);
	Element.observe ($('homeowner|telephone|work'), 'change', HOAM_verifyTelephone);
	if ($('homeowner|add')) {
		Element.observe ($('homeowner|add'), 'click', function (event) {
			// Kill the submit event. If everything passes, /then/ we'll submit.
			event.stop();
			HOAM_homeownerCheckCurrent (event);
			});
	}
	if ($('homeowner|delete')) {
		Element.observe ($('homeowner|delete'), 'click', function (event) {
			Event.stop (event);
			HOAM_adminHomeownerDelete ();
			});
	}
	if ($('homeowner|flags|bankrupt')) {
		Element.observe ($('homeowner|flags|bankrupt'), 'change', HOAM_homeownerToggleBankrupt);
	}
	if ($('homeowner|assessment|payment_plan')) {
		Element.observe ($('homeowner|assessment|payment_plan'), 'change', HOAM_homeownerTogglePaymentPlan);
	}
	// Set up monitoring for homeowner history
	if ($('homeowner_history')) {
		Element.observe ($('homeowner_history').parentNode.getElementsByTagName ('li')[6], 'click', HOAM_homeownerHistory);
	}
	HOAM_homeownerAttachmentYear ();
	if ($('homeowner|assessment|payment_plan')) {
		// Doesn't exist when adding a new owner
		HOAM_homeownerTogglePaymentPlan ();
	}
}

function HOAM_homeownerAttachmentYear () {
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

function HOAM_homeownerCheckCurrent (event) {
	// Find out if there's a current homeowner
	var lot = $F('homeowner|address');
	var url = '/hoam/scripts/homeowner/xml_newhomeowner.php';
	var pars = 'lotid=' + lot;
	var current_exists = 0;
	var homeowner_sale = 0;
	var open_charges = 0;
	
	var myAjax = new Ajax.Request (url, {method: 'get', asynchronous: false, parameters: pars, onComplete: function (originalRequest) {
		var items = originalRequest.responseXML.getElementsByTagName ('item');
		current_exists = getElementTextNS ('', 'current_exists', items[0], 0);
		if (current_exists == 1) {
			open_charges = getElementTextNS ('', 'open_charges', items[0], 0);
			homeowner_sale = getElementTextNS ('', 'homeowner_sale', items[0], 0);
		}
	}});
	if (current_exists == 1) {
		if (confirm (HOAM_languageLookup ('errors|homeowner|current_exists') + '\n\n' + HOAM_languageLookup ('common|close-record') + ' ' + HOAM_languageLookup ('field_names|homeowner|set-purchasedate'))) {
			HOAM_homeownerCloseCurrent ();
		} else {
			alert (HOAM_languageLookup ('errors|generic|close-record'));
			return false;
		}
		if (open_charges == 1) {
			if (confirm (HOAM_languageLookup ('errors|homeowner|current_charges'))) {
				HOAM_homeownerCloseCharges ();
			} else {
				alert (HOAM_languageLookup ('errors|homeowner|current_transfer'));
			}
		}
		if (homeowner_sale == 1) {
			if (confirm (HOAM_languageLookup ('errors|homeowner|current_sale'))) {
				HOAM_homeownerCloseSale ();
			} else {
				alert (HOAM_languageLookup ('errors|generic|close_record'));
			}
		}
		// Whichever the result from the above, go ahead and try to submit.
		var add_input = document.createElement ('input');
		add_input.type = 'hidden';
		add_input.name = 'add';
		add_input.value = 'add';	
		$('homeowner_add').appendChild (add_input);
		$('homeowner_add').submit();
	} else {
		// If there's not a current homeowner, then go ahead and add.
		var add_input = document.createElement ('input');
		add_input.type = 'hidden';
		add_input.name = 'add';
		add_input.value = 'add';	
		$('homeowner_add').appendChild (add_input);
		$('homeowner_add').submit();
	}
}

function HOAM_homeownerCloseCharges () {
	var hidden_input = document.createElement ('input');
	hidden_input.type = 'hidden';
	hidden_input.name = 'homeowner|previous|charges';
	hidden_input.value = '1';	
	$('homeowner_add').appendChild (hidden_input);
	return true;
}

function HOAM_homeownerCloseCurrent () {
	var hidden_input = document.createElement ('input');
	hidden_input.type = 'hidden';
	hidden_input.name = 'homeowner|previous|close';
	hidden_input.value = '1';	
	$('homeowner_add').appendChild (hidden_input);
	return true;
}

function HOAM_homeownerCloseSale () {
	var hidden_input = document.createElement ('input');
	hidden_input.type = 'hidden';
	hidden_input.name = 'homeowner|previous|sale';
	hidden_input.value = '1';	
	$('homeowner_add').appendChild (hidden_input);
	return true;
}

function HOAM_adminHomeownerDelete () {
	var owner = $F('homeowner|id');
	var url = '/hoam/scripts/attachment/xml_attachment_exist.php';
	var pars = 'owner=' + owner;
	var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		var attachments = originalRequest.responseXML.getElementsByTagName ('attachment');
		if (attachments.length > 0) {
			if (confirm (attachments.length + HOAM_languageLookup ('field_names|homeowner|delete_attachments'))) {
				var delete_input = document.createElement ('input');
				delete_input.type = 'hidden';
				delete_input.name = 'delete';
				delete_input.value = 'delete';	
				$('homeowner_delete').appendChild (delete_input);
			} else {
				var cancel_input = document.createElement ('input');
				cancel_input.type = 'hidden';
				cancel_input.name = 'cancel';
				cancel_input.value = 'cancel';	
				$('homeowner_delete').appendChild (cancel_input);
			}
		} else {
			var delete_input = document.createElement ('input');
			delete_input.type = 'hidden';
			delete_input.name = 'delete';
			delete_input.value = 'delete';	
			$('homeowner_delete').appendChild (delete_input);
		}
		$('homeowner_delete').submit();
	}});
}

function HOAM_homeownerHistory () {
	HOAM_tabLoading ($('homeowner_history'));
	var url = '/hoam/scripts/homeowner/homeownerHistory.php';
	var pars = 'id=' + $F('homeowner|id');
	var myAjax = new Ajax.Updater ($('homeowner_history'), url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		ZebraTable.stripe (($('homeowner_history').getElementsByTagName ('TBODY'))[0]);
	}
	});
}

function HOAM_homeownerToggleBankrupt () {
	if ($('homeowner|flags|bankrupt').checked) {
		$('bankruptcy_info').show ();
	} else {
		$('bankruptcy_info').hide ();
	}
}

function HOAM_homeownerToggleResidenceMailing () {
	if ($('homeowner|residence_mailing').checked) {
		$('homeowner|mailing_address').hide ();
	} else {
		$('homeowner|mailing_address').show ();
		// Set address1 equal to the homeowner's name only when it's blank.
		if ($F('homeowner|address1') == '') {
			$('homeowner|address1').value = $F('homeowner|name');
		}
	}
}

function HOAM_homeownerTogglePaymentPlan () {
	if ($('homeowner|assessment|payment_plan').checked) {
		$('payment_plan_info').show ();
	} else {
		$('payment_plan_info').hide ();
	}
}

Event.observe (window, 'load', HOAM_homeownerInit);
