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

function HOAM_letterInit () {
	Element.observe ($('letter|name'), 'change', HOAM_letterLoad);
	Element.observe ($('letter|address'), 'change', HOAM_letterLoad);
	Element.observe ($('letter|approver'), 'change', HOAM_letterLoad);
	Element.observe ($('letter|person'), 'change', HOAM_letterLoad);
	Element.observe ($('letter|specific'), 'change', HOAM_letterSpecific);
    Element.observe ($('letter|delivery'), 'change', HOAM_letterDeliveryUpdate);
    Element.observe ($('letter|submit'), 'click', function (event) {
    	// Kill the submit event. If everything passes, /then/ we'll submit.
		event.stop ();
		// Make sure a letter was selected.
		if ($F('letter|name') != '0') {
			// Were any delivery tracking options selected?
			if ($F('letter|delivery') != '0') {
				if ($F('letter|delivery_tracking') == '') {
					alert (HOAM_languageLookup ('errors|letter|delivery_tracking'));
					return false;
				} else {
//					if (HOAM_validateTracking()) {
						$('letter|delivery').disabled = false;
						var add_input = document.createElement ('input');
						add_input.type = 'hidden';
						add_input.name = 'add';
						add_input.value = 'add';	
						$('letter').appendChild (add_input);
						var letter_input = document.createElement ('input');
						letter_input.type = 'hidden';
						letter_input.name = 'letter|content';
						letter_input.value = $('letter|content').innerHTML;	
						$('letter').appendChild (letter_input);
						$('letter').submit();
/*					} else {
						alert (HOAM_languageLookup ('errors|letter|delivery_tracking'));
						return false;
					}
*/				}
			} else {
				// If a tracking option was not selected, then just submit. 
				var add_input = document.createElement ('input');
				add_input.type = 'hidden';
				add_input.name = 'add';
				add_input.value = 'add';	
				$('letter').appendChild (add_input);
				var letter_input = document.createElement ('input');
				letter_input.type = 'hidden';
				letter_input.name = 'letter|content';
				letter_input.value = $('letter|content').innerHTML;	
				$('letter').appendChild (letter_input);
				$('letter').submit();
			}
    	} else {
    		alert (HOAM_languageLookup ('errors|letter|not_selected'));
    	}
    });
   	Element.observe ($('letter|delivery_tracking'), 'change', function () {
   		HOAM_letterTracking ();
   		HOAM_validateTracking ();
   	});
}

function HOAM_letterDeliveryUpdate () {
	switch ($F('letter|delivery')) {
		// autodraft only exists when paying vendor invoices.
		case '0':
			$('letter|delivery_tracking').hide ();
			$('letter|delivery_tracking').previousSibling.hide ();
			break;
		case '1':
			$('letter|delivery_tracking').show ();
			$('letter|delivery_tracking').previousSibling.show ();
			break;
		case '2':
			$('letter|delivery_tracking').show ();
			$('letter|delivery_tracking').previousSibling.show ();
			break;
		case '3':
			$('letter|delivery_tracking').show ();
			$('letter|delivery_tracking').previousSibling.show ();
			break;
	}
}

function HOAM_letterLoad () {
	if (($F('letter|name')) != '0') {
		var url = '/hoam/scripts/letter/letterLoad.php';
		var pars = 'letter=' + $F('letter|name');
		pars = pars + '&address=' + $F('letter|address');
		pars = pars + '&approver=' + $F('letter|approver');
		pars = pars + '&person=' + $F('letter|person');
		var myAjax = new Ajax.Request (url, { method: 'get', parameters: pars, onComplete: HOAM_letterLoaded });
	}
}

function HOAM_letterLoaded (originalRequest) {
	var items = originalRequest.responseXML.getElementsByTagName ('item');
	var content = getElementTextNS ('', 'content', items[0], 0);
	$('letter|content').innerHTML = content;
	var require_certified = getElementTextNS ('', 'require_delivery_certified', items[0], 0);
	var require_confirmation = getElementTextNS ('', 'require_delivery_confirmation', items[0], 0);
	var suggest_certified = getElementTextNS ('', 'suggest_delivery_certified', items[0], 0);
	var suggest_confirmation = getElementTextNS ('', 'suggest_delivery_confirmation', items[0], 0);
	switch (true) {
		case (require_certified == '1'):
			$('letter|delivery|options').removeClassName ('optional');
			$('letter|delivery|options').addClassName ('mandatory');
			setSelectIndex ('letter|delivery', '3');
			$('letter|delivery').disabled = true;
			break;
		case (require_confirmation == '1'):
			$('letter|delivery|options').removeClassName ('optional');
			$('letter|delivery|options').addClassName ('mandatory');
			setSelectIndex ('letter|delivery', '1');
			$('letter|delivery').disabled = true;
			break;
		case (suggest_certified == '1'):
			$('letter|delivery|options').removeClassName ('mandatory');
			$('letter|delivery|options').addClassName ('optional');
			setSelectIndex ('letter|delivery', '3');
			$('letter|delivery').disabled = false;
			break;
		case (suggest_confirmation == '1'):
			$('letter|delivery|options').removeClassName ('mandatory');
			$('letter|delivery|options').addClassName ('optional');
			setSelectIndex ('letter|delivery', '1');
			$('letter|delivery').disabled = false;
			break;
		default:
			$('letter|delivery|options').removeClassName ('mandatory');
			$('letter|delivery|options').addClassName ('optional');
			setSelectIndex ('letter|delivery', '0');
			$('letter|delivery').disabled = false;
			break;
	}
	if (getElementTextNS ('', 'additional_fields', items[0], 0) != 'n/a') {
		if ($('additional_fields')) {
			$('additional_fields').remove();
		}
		var fields = originalRequest.responseXML.getElementsByTagName ('field');
		var new_fieldset = document.createElement ('FIELDSET');
		new_fieldset.id = 'additional_fields';
		// The simpler 'var i in fields' doesn't work, FF at least believes
		// there is more than the correct number of fields.
		for (var i = 0; i < fields.length; i++) {
			// Create the new input field
			var new_div = document.createElement ('DIV');
			if (getElementTextNS ('', 'required', fields[i], 0) == '1') {
				new_div.addClassName ('mandatory');
			} else {
				new_div.addClassName ('optional');
			}
			var new_field = document.createElement ('INPUT');
			var id = getElementTextNS ('', 'id', fields[i], 0);
			new_field.name = 'additional|' + id;
			Event.observe (new_field, 'change', function () {
				$(this.name.replace(/additional\|/, '')).innerHTML = this.value;
			});
			new_div.appendChild (new_field);
			
			// Create the label for the field
			var new_label = document.createElement ('LABEL');
			new_label.setAttribute ('for', new_field.name);
			new_label.innerHTML = getElementTextNS ('', 'description', fields[i], 0);
			new_div.insertBefore (new_label, new_field);

			new_fieldset.appendChild (new_div);
		}
		$('letter|add').appendChild (new_fieldset);
	} else {
		// Make sure we're not showing the additional fields if it's not
		// necessary on this letter.
		if ($('additional_fields')) {
			$('additional_fields').remove();
		}
	}

	HOAM_letterDeliveryUpdate ();
	HOAM_letterSpecific ();
	HOAM_letterSignature ();
}

function HOAM_letterSpecific () {
	// If the letter has a 'specific' field, and the user has entered
	// information, fill in the letter.
	var specific_elem = $('letter|content').getElementsByClassName ('letter-specific');
	if (specific_elem[0] != undefined) {
		$('letter|specific').disabled = false;
		if ($F('letter|specific') == HOAM_languageLookup ('common|na')) {
			$('letter|specific').value = '';
		}
		specific_elem[0].innerHTML = $F('letter|specific');
	} else {
		// Not all letters have the 'specific' field present. If that's the
		// case, don't enable the field for input.
		$('letter|specific').value = HOAM_languageLookup ('common|na');
		$('letter|specific').disabled = true;
	}
}

function HOAM_letterSignature () {
	// If the letter does not have an individual's name listed as the approver,
	// remove the information listed at the end under 'person' so it doesn't
	// show a tag under the Association's name.
	var person_elem = $('letter|content').getElementsByClassName ('letter-person');
	if (person_elem[0] != undefined) {
		if ($F('letter|approver') == '') {
			person_elem[0].innerHTML = '';
		}
	}
}

function HOAM_letterTracking () {
	// If the user entered 'tracking' information, add to the letter.
	var specific_elem = $('letter|content').getElementsByClassName ('letter-tracking');
	if (specific_elem[0] != undefined) {
		specific_elem[0].innerHTML = HOAM_languageLookup ('field_names|letter|delivery_tracking') + '<br />' + $F('letter|delivery_tracking');
	}
}

function HOAM_validateTracking () {
	// Validate that the tracking number is at least the correct format.
	if ($F('letter|delivery_tracking').match(/^E\D{1}\d{9}\D{2}$|^9\d{15,21}$/)) {
		HOAM_formErrorClear ('letter|delivery_tracking');
		return true;
	} else {
		HOAM_formErrorMessage ('letter|delivery_tracking', HOAM_languageLookup ('errors|letter|delivery_tracking'));
		return false;
	}
}

Event.observe (window, 'load', HOAM_letterInit);
