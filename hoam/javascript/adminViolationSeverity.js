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

function HOAM_violationSeverityInit () {
	Element.observe ($('violation|severity|show|name'), 'change', HOAM_violationSeverityShowExisting);
	HOAM_violationSeverityShowExisting ();
    Event.observe ($('violation|severity|fine_per_day'), 'change', function () {
	   	$('violation|severity|fine_per_day').value = HOAM_returnCurrency ($F('violation|severity|fine_per_day'), 1);
    });
    Event.observe ($('violation|severity|fine_per_notice'), 'change', function () {
	   	$('violation|severity|fine_per_notice').value = HOAM_returnCurrency ($F('violation|severity|fine_per_notice'), 1);
    });
    Event.observe ($('violation|severity|fine_interest'), 'change', function () {
	   	$('violation|severity|fine_interest').value = HOAM_returnCurrency ($F('violation|severity|fine_interest'), 1);
    });
}

function HOAM_violationSeverityShowExisting () {
	// Update the show|preamble, show|closing and other areas
	var id = $F('violation|severity|show|name');
	var url = '/hoam/scripts/violation/xml_severity.php';
	var pars = 'id=' + id;

	var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		// put returned XML in the textarea
		var items = originalRequest.responseXML.getElementsByTagName ("item");
		$('violation|severity|show|closing').value = getElementTextNS ("", 'closing', items[0], 0);
		$('violation|severity|show|escalate').value = getElementTextNS ("", 'escalate', items[0], 0);
		// TODO. Decide whether to leave the interest rate formatted as
		// currency or switching to another format.
		$('violation|severity|show|fine_interest').value = HOAM_returnCurrency (getElementTextNS ("", 'fine_interest', items[0], 0));
		$('violation|severity|show|fine_per_day').value = HOAM_returnCurrency (getElementTextNS ("", 'fine_per_day', items[0], 0));
		$('violation|severity|show|fine_per_notice').value = HOAM_returnCurrency (getElementTextNS ("", 'fine_per_notice', items[0], 0));
		$('violation|severity|show|numdays').value = getElementTextNS ("", 'numdays', items[0], 0);
		$('violation|severity|show|preamble').value = getElementTextNS ("", 'preamble', items[0], 0);
	}	
	});
}

Event.observe (window, 'load', HOAM_violationSeverityInit);
