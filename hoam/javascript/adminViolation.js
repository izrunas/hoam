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

function HOAM_violationInit () {
	if ($('violation|add')) {
		Element.observe ($('violation|add'), 'click', function (event) {
			if ($('violation|categories').selectedIndex < 0) {
				Event.stop (event);
				confirm (HOAM_languageLookup ('errors|violation|category|none-selected'));
			}
		});
	}
	Element.observe ($('violation_form'), 'submit', function (event) {
		// Grab the various sections of the violation letter and submit.
		var letter_salutation = document.createElement ('input');
		letter_salutation.name = 'violation|letter|salutation';
		letter_salutation.setAttribute ('type', 'hidden');
		letter_salutation.value = $('violation|letter|salutation').innerHTML;
		var letter_preamble = document.createElement ('input');
		letter_preamble.name = 'violation|letter|preamble';
		letter_preamble.setAttribute ('type', 'hidden');
		letter_preamble.value = $('violation|letter|preamble').innerHTML;
		var letter_description = document.createElement ('input');
		letter_description.name = 'violation|letter|description';
		letter_description.setAttribute ('type', 'hidden');
		letter_description.value = $('violation|letter|description').innerHTML;
		var letter_specific = document.createElement ('input');
		letter_specific.name = 'violation|letter|specific';
		letter_specific.setAttribute ('type', 'hidden');
		letter_specific.value = $('violation|letter|specific').innerHTML;
		var letter_detail = document.createElement ('input');
		letter_detail.name = 'violation|letter|detail';
		letter_detail.setAttribute ('type', 'hidden');
		letter_detail.value = $('violation|letter|detail').innerHTML;
		var letter_closing = document.createElement ('input');
		letter_closing.name = 'violation|letter|closing';
		letter_closing.setAttribute ('type', 'hidden');
		letter_closing.value = $('violation|letter|closing').innerHTML;

		$('violation_form').appendChild (letter_salutation);
		$('violation_form').appendChild (letter_preamble);
		$('violation_form').appendChild (letter_description);
		$('violation_form').appendChild (letter_specific);
		$('violation_form').appendChild (letter_detail);
		$('violation_form').appendChild (letter_closing);
	});
	Element.observe ($('violation|address'), 'change', function () {
		HOAM_violationHistoryUpdate ();
		HOAM_violationQuickinfoUpdate ();
		HOAM_violationSalutationUpdate ();
		HOAM_violationSeverityUpdate ();
	});
	Element.observe ($('violation|categories'), 'change', function (event) {
		HOAM_violationCategoryUpdate (event);
		HOAM_violationSeverityUpdate (event);
	});
	if ($('violation|date|resolution')) {
		Element.observe ($('violation|date|resolution'), 'change', HOAM_verifyDate);
	}
	Element.observe ($('violation|date|resolveby'), 'change', function (event) {
		HOAM_verifyDate (event);
		HOAM_violationSeverityUpdate (event);
	});
	Element.observe ($('violation|date|violation'), 'change', function (event) {
		HOAM_verifyDate (event);
		HOAM_violationSeverityUpdate (event);
	});
	Element.observe ($('violation|severity|current'), 'change', HOAM_violationSeverityUpdate);
	Element.observe ($('violation|specific'), 'change', HOAM_violationSpecificUpdate);
	
	// Preload the violation information; however, don't do it if we're adding
	// a new violation.
	if ($F('violation|address') != '0') {
		HOAM_violationCategoryUpdate ();
		HOAM_violationQuickinfoUpdate ();
		HOAM_violationSalutationUpdate ();
		HOAM_violationSeverityUpdate ();
		HOAM_violationSpecificUpdate ();
		HOAM_violationHistoryUpdate ();
	}

	// Set up monitoring for violation history
	if ($('violation_history')) {
		Element.observe ($('violation_history').parentNode.getElementsByTagName ('li')[4], 'click', HOAM_violationHistory);
	}

}

function HOAM_violationCategoryUpdate () {
	// Display the violation category information
	var category = $F('violation|categories');
	var url = '/hoam/scripts/violation/xml_categories.php';
	var pars = 'category=' + category;

	// If we're loading the page initially, no categories will be selected.
	if (category) {
		var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
			// Clear out any old content
			$('violation|letter|description').innerHTML = '';
			$('violation|letter|detail').innerHTML = '';
			var new_description = '';
			var new_detail = '';
			var items = originalRequest.responseXML.getElementsByTagName ('item');
			for (count = 0; count < items.length; count++) {
				new_description += getElementTextNS ('', 'description', items[count], 0);
				new_detail += getElementTextNS ('', 'detail', items[count], 0);
			}
			$('violation|letter|description').innerHTML = new_description;
			$('violation|letter|detail').innerHTML = new_detail;
		}
		});
	}
}

function HOAM_violationHistory () {
	HOAM_tabLoading ($('violation_history'));
	var url = '/hoam/scripts/violation/violationHistory.php';
	var pars = 'id=' + $F('violation|id');
	var myAjax = new Ajax.Updater ($('violation_history'), url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		ZebraTable.stripe (($('violation_history').getElementsByTagName ('TBODY'))[0]);
	}
	});
}

function HOAM_violationHistoryUpdate () {
	var id = $F('violation|address');
	var url = '/hoam/scripts/violation/xml_violation_history.php';
	var pars = 'lot=' + id;

	var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		// put returned XML in the textarea
		var items = originalRequest.responseXML.getElementsByTagName ('violation');
		var history = '';
		var previous_history = '';
		for (count = 0; count < items.length; count++) {
			if (getElementTextNS ('', 'current_homeowner', items[count], 0) == 1) {
				history += getElementTextNS ('', 'tablerow', items[count], 0);
			} else {
				previous_history += getElementTextNS ('', 'tablerow', items[count], 0);
			}
		}
		if (history) {
			$('violation|history|homeowner').innerHTML = history;
			ZebraTable.stripe ($('violation|history|homeowner'));
			var lastResolveby = getElementTextNS ('', 'date_resolveby', items[0], 0);
			var today = new Date();    //Get today's date
			var today = today.getFullYear () + '-' + leadingZero (today.getMonth () + 1) + '-' + leadingZero (today.getDate ());
			if ((lastResolveby > today) && !$('violation|delete')) {
				alert (HOAM_languageLookup ('errors|violation|too-soon'));
			}
		} else {
			$('violation|history|homeowner').innerHTML = '<tr><td class="center" colspan="5">' + HOAM_languageLookup ('errors|violation|history_none') + '<td></tr>';
		}
		if (previous_history) {
			$('violation|history|previous').innerHTML = previous_history;
			ZebraTable.stripe ($('violation|history|previous'));
		} else {
			$('violation|history|previous').innerHTML = '<tr><td class="center" colspan="5">' + HOAM_languageLookup ('errors|violation|history_none') + '</td></tr>';
		}
	}
	});
}

function HOAM_violationQuickinfoUpdate () {
	var id = $F('violation|address');
	var url = '/hoam/scripts/violation/xml_violation_quickinfo.php';
	var pars = 'lot=' + id;
	
	var myAjax = new Ajax.Request (url, { method: 'get', parameters: pars, onComplete: function (originalRequest) {
		// put returned XML in the textarea
		var items = originalRequest.responseXML.getElementsByTagName ('ajax-response');
		$('violation|quickinfo').innerHTML = getElementTextNS ('', 'quickinfo', items[0], 0);
		// Don't display these warnings if we're deleting a violation.
		if (!$('violation|delete')) {
			if (getElementTextNS ('', 'exempt_grace', items[0], 0) == '1') {
				alert (HOAM_languageLookup ('errors|violation|homeowner-exempt'));
			} else {
				if (getElementTextNS ('', 'exempt_prior', items[0], 0) == '1') {
					alert (HOAM_languageLookup ('errors|violation|homeowner-exempt'));
				}
			}
		}
		if ($('violation|add')) {
			setSelectIndex ('violation|severity|current', getElementTextNS ('', 'recommended_severity', items[0], 0));
			HOAM_violationSeverityUpdate ();
		}
	}
	});
}

function HOAM_violationSalutationUpdate () {
	var id = $F('violation|address');
	var url = '/hoam/scripts/homeowner/xml_homeowner.php'
	var pars = 'lotid=' + id;

	var myAjax = new Ajax.Request (url, { method: 'get', parameters: pars, onComplete: function (originalRequest) {
		var items = originalRequest.responseXML.getElementsByTagName ('item');
		var salutation = getElementTextNS ("", 'mailing', items[0], 0);
		salutation = salutation + '<br /><br />';
		if (getElementTextNS ("", 'resident', items[0], 0) == '0') {
			// Should we send to the current resident?
			if (getElementTextNS ("", 'send_current_resident', items[0], 0) == '1') {
				salutation = salutation + 'CC: ' + HOAM_languageLookup ('field_names|letter|current_resident') + ' ' + HOAM_languageLookup ('common|of') + ' ' + getElementTextNS ("", 'address', items[0], 0);
				salutation = salutation + '<br /><br />';
			}
			salutation = salutation + 'RE: ' + getElementTextNS ("", 'address', items[0], 0);
			salutation = salutation + '<br /><br />';
		}
		salutation = salutation + HOAM_languageLookup ('field_names|letter|dear_homeowner') + ',<br />';
		$('violation|letter|salutation').innerHTML = salutation;
	}
	});
}

function HOAM_violationSeverityUpdate (event, async) {
	// The async option is only here for when the form is submitted.
	if (async == null) {
		async = true;
	}
	
	var id = $F('violation|severity|current');
	var url = '/hoam/scripts/violation/xml_severity.php'
	var pars = 'id=' + id;
	if (event && ((Event.element(event).getAttribute ('name') == 'violation|date|resolveby') || (Event.element(event).getAttribute ('type') == 'submit'))) { 
		pars = pars + '&resolveby=' + $F('violation|date|resolveby'); 
	} else {
		pars = pars + '&resolveby='; 
	}
	pars = pars + '&dateviolation=' + $F('violation|date|violation');
	pars = pars + '&address=' + $F('violation|address');
	pars = pars + '&category=' + $F('violation|categories');

	var myAjax = new Ajax.Request (url, { method: 'get', asynchronous: async, parameters: pars, onComplete: function (originalRequest) {
		var items = originalRequest.responseXML.getElementsByTagName ('item');
		$('violation|letter|preamble').innerHTML = getElementTextNS ('', 'preamble_updated', items[0], 0);
		$('violation|letter|closing').innerHTML = getElementTextNS ('', 'closing_updated', items[0], 0);
		// Long way around to update the resolveby date.
		var numdays = parseInt (getElementTextNS ('', 'numdays', items[0], 0));
		var today = new Date();    //Get today's date
		var resolveby = new Date (today.getFullYear (), today.getMonth (), today.getDate () - 0 + numdays);
		$('violation|date|resolveby').value = leadingZero (resolveby.getMonth () + 1) + '/' + leadingZero (resolveby.getDate ()) + '/' + resolveby.getFullYear ();
		if (getElementTextNS ('', 'assume_resolved', items[0], 0) == '1') {
			$('violation|assume_resolved').checked = true;
		} else {
			$('violation|assume_resolved').checked = false;
		}
	}
	});
}

function HOAM_violationSpecificUpdate () {
	// When loading the page initially, this will be blank.
	if ($F('violation|specific')) {
		$('violation|specific').value = HOAM_sentenceFormat ($F('violation|specific'));
		$('violation|letter|specific').innerHTML = '<p>' + HOAM_languageLookup ('common|specifically') + ': ' + $F('violation|specific') + '</p>';
	}
}
	
Event.observe (window, 'load', HOAM_violationInit);
