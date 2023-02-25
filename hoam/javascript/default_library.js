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

/* global variables */

var AjaxGlobalHandlers = {
	onCreate: HOAM_serverCommunicationBegin,
	onException: function (transport, exception) {
		HOAM_serverCommunicationError ();
//		alert (transport.responseText);
		alert (exception);
	},
	onLoading: HOAM_serverCommunicationLoading,
	onComplete: HOAM_serverCommunicationEnd
	};
Ajax.Responders.register (AjaxGlobalHandlers);

var ZebraTable = {
	// Stolen from http://www.thewatchmakerproject.com/zebra.html
	// A few modifications, though...
	class_alternate: 'alternate-row',
	class_hover: '',
	stripe: function (el) {
		if (!$(el)) return;
		var rows = $(el).getElementsByTagName('tr');
		for (var i = 0, len = rows.length; i < len; i++) {
			// Added the .extend for f'ing IE
			Element.extend (rows[i]);
			if (i % 2 == 0) rows[i].addClassName (this.class_alternate);
			Event.observe (rows[i], 'mouseover', function() { ZebraTable.mouseover(this); }, false);
			Event.observe (rows[i], 'mouseout', function() { ZebraTable.mouseout(this); }, false);
		}
	},
	mouseover: function (row) {
		row.addClassName (this.class_hover);
	},
	mouseout: function (row) {
		row.removeClassName (this.class_hover);
	}
}

function HOAM_countryLookup (messagePath) {
	var languageString;
	var url = '/hoam/scripts/help/countryLookup.php';
	// var pars = 'string=' + messagePath.replace (/_/g, '/');
	pars = 'string=' + messagePath;
	// Note, this call is being done synchronously, which could potentially
	// cause problems. However, it's done this way because the function was
	// returning before the request completed.
	var myAjax = new Ajax.Request (url, { method: 'get', asynchronous: false, parameters: pars, onComplete: function (originalRequest) {
		languageString = originalRequest.responseText;
	}
	});
	return (languageString);
}

function HOAM_languageLookup (messagePath, param1, param2) {
	var languageString;
	var url = '/hoam/scripts/help/languageLookup.php';
	// var pars = 'string=' + messagePath.replace (/_/g, '/');
	pars = 'string=' + messagePath;
	pars = pars + '&param1=' + param1 + '&param2=' + param2;
	// Note, this call is being done synchronously, which could potentially
	// cause problems. However, it's done this way because the function was
	// returning before the request completed.
	var myAjax = new Ajax.Request (url, { method: 'get', asynchronous: false, parameters: pars, onComplete: function (originalRequest) {
		languageString = originalRequest.responseText;
	}
	});
	return (languageString);
}

function HOAM_languageLookupAsync (messagePath, param1, param2) {
	var languageString;
	var url = '/scripts/help/languageLookup.php';
	// var pars = 'string=' + messagePath.replace (/_/g, '/');
	pars = 'string=' + messagePath;
	pars = pars + '&param1=' + param1 + '&param2=' + param2;
	var myAjax = new Ajax.Request (url, { method: 'get', asynchronous: true, parameters: pars, onComplete: function (originalRequest) {
		alert (originalRequest.responseText);
		languageString = originalRequest.responseText;
	}
	});
	return (languageString);
}

function HOAM_moveOption (form, source, dest) {
	// Believe this was originally Matt Kruse's http://www.mattkruse.com/
	/* Moves selected OPTION elements from one SELECT control to another.
	*
	* form   - A reference to the containing form,
	*          or a string containing the name or id.
	* source - The name or id of the source SELECT element.
	* dest   - The name or id of the destination SELECT element.
	*/
	if (typeof form == 'string') {
		form = $(form);
	}
	if (form && 'object' == typeof form && this['Option']) {
		dest = $(dest);
		source = $(source);
		if (dest && source && 'object' == typeof dest && 'object' == typeof source) {
			var c, i = 0, j = 0, dO = dest.options, sO = source.options, t;
			while (i < sO.length) {
				c = sO[i++]; t = new Option (c.text, c.value);
				if (c.selected) {
					dO[dO.length] = t;
				} else {
					sO[j++] = t;
				}
			}
			sO.length = j;
		}
	}
}

function HOAM_passwordStrength (element, language_strings) {
	if (!language_strings) {
		var language_strings = new Array ();
		language_strings[0] = HOAM_languageLookup ('common|very_weak');
		language_strings[1] = HOAM_languageLookup ('common|weak');
		language_strings[2] = HOAM_languageLookup ('common|mediocre');
		language_strings[3] = HOAM_languageLookup ('common|fair');
		language_strings[4] = HOAM_languageLookup ('common|strong');
		language_strings[5] = HOAM_languageLookup ('common|very_strong');
	}
	// Based on the routine at http://www.geekwisdom.com/dyn/passwdmeter
	var score = 0
	passwd = $F(element);
	
	// Higher score for longer passwords (up to a point)
	if (passwd.length < 5) {
		score = (score + 3)
	} else if (passwd.length > 4 && passwd.length < 8) {
		score = (score + 6)
	} else if (passwd.length>7 && passwd.length<16) {
		score = (score + 12)
	} else if (passwd.length > 15) {
		score = (score + 18)
	}
	// Alphabetic
	if (passwd.match(/[a-z]/)) {
		score = (score + 1)
	}
	if (passwd.match(/[A-Z]/)) {
		score = (score + 5)
	}
	// Numeric
	if (passwd.match(/\d+/)) {
		score = (score + 5)
	}
	if (passwd.match(/(.*[0-9].*[0-9].*[0-9])/)) {
		score = (score + 5)
	}
	// 'Special' chars
	if (passwd.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)) {
		score = (score + 5)
	}
	// At least two special characters
	if (passwd.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)) {
		score = (score + 5)
	}
	// Upper and lower case
	if (passwd.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
		score = (score + 2)
	}
	// Letters and numbers
	if (passwd.match(/([a-zA-Z])/) && passwd.match(/([0-9])/)) {
		score = (score + 2)
	}
	// Letters, numbers, and special characters
	if (passwd.match(/([a-zA-Z0-9].*[!,@,#,$,%,^,&,*,?,_,~])|([!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/)) {
		score = (score + 2)
	}
	if (score < 16) {
		element.nextSibling.innerHTML = ' ' + HOAM_sentenceFormat (language_strings[0]);
		element.nextSibling.addClassName ('warning');
	} else if (score > 15 && score < 25) {
		element.nextSibling.innerHTML = ' ' + HOAM_sentenceFormat (language_strings[1]);
		element.nextSibling.addClassName ('warning');
		element.nextSibling.removeClassName ('caution');
	} else if (score > 24 && score < 35) {
		element.nextSibling.innerHTML = ' ' + HOAM_sentenceFormat (language_strings[2]);
		element.nextSibling.addClassName ('caution');
		element.nextSibling.removeClassName ('complete');
		element.nextSibling.removeClassName ('strong');
		element.nextSibling.removeClassName ('warning');
	} else if (score > 34 && score < 40) {
		element.nextSibling.innerHTML = ' ' + HOAM_sentenceFormat (language_strings[3]);
		element.nextSibling.addClassName ('complete');
		element.nextSibling.removeClassName ('caution');
		element.nextSibling.removeClassName ('warning');
	} else if (score > 39 && score < 45) {
		element.nextSibling.innerHTML = ' &#10003; ' + HOAM_sentenceFormat (language_strings[4]);
		element.nextSibling.addClassName ('complete');
		element.nextSibling.removeClassName ('caution');
		element.nextSibling.removeClassName ('warning');
	} else {
		element.nextSibling.innerHTML = ' &#10003; ' + HOAM_sentenceFormat (language_strings[5]);
		element.nextSibling.addClassName ('complete');
		element.nextSibling.removeClassName ('caution');
		element.nextSibling.removeClassName ('warning');
	}
}

function HOAM_returnCleanFloat (obj) {
	return parseFloat ((obj).replace (/,/, ''))
}

function HOAM_returnDateCurrent () {
	var d = new Date();
	var curdate = '';
	var month = d.getMonth();
	month++;
	var day = d.getDate();
	if (month < 10) {
		curdate = curdate + '0' + month + '/';
	} else {
		curdate = curdate + month + '/';
	}
	if (day < 10) {
		curdate = curdate + '0' + day + '/';
	} else {
		curdate = curdate + day + '/';
	}
	curdate = curdate + d.getFullYear();
	return curdate;
}

function HOAM_returnDateEndOfMonth () {
	var d = new Date();
	var m = new Date(d.getFullYear(), d.getMonth() + 1, 1);
	d = new Date (m - 1);
	var curdate = '';
	var month = d.getMonth();
	month++;
	var day = d.getDate();
	if (month < 10) {
		curdate = curdate + '0' + month + '/';
	} else {
		curdate = curdate + month + '/';
	}
	if (day < 10) {
		curdate = curdate + '0' + day + '/';
	} else {
		curdate = curdate + day + '/';
	}
	curdate = curdate + d.getFullYear();
	return curdate;
}

function HOAM_returnCurrency (amount, show_sign) {
	// From (with modifications) http://javascript.internet.com/forms/currency-format.html
	// If we didn't receive anything, then just exit; we want a blank response.
	amount = amount.toString().replace(/\$|\,/g, '');
	if (isNaN (amount)) {
		amount = "0";
	}
	sign = (amount == (amount = Math.abs(amount)));
	amount = Math.floor(amount * 100 + 0.50000000001);
	cents = amount % 100;
	amount = Math.floor(amount / 100).toString();
	if (cents < 10) {
		cents = "0" + cents;
	}
	for (var i = 0; i < Math.floor((amount.length - (1 + i)) / 3); i++) {
		amount = amount.substring(0, amount.length - (4 * i + 3)) + ',' + amount.substring(amount.length - (4 * i + 3));
	}
	if (show_sign) {
		return (((sign) ? '' : '-') + amount + '.' + cents);
	} else {
		return (amount + '.' + cents);
	}
}

function HOAM_sentenceFormat (string) {
	// Should be made smarter, just needed something quick.
	string = string.toLowerCase();
	string = string.charAt(0).toUpperCase() + string.slice(1);

	return (string);
}

function HOAM_serverCommunicationBegin () {
	// Display notice to end-user that communciation is occurring with the server
	// ie, activity is taking place
	var status = document.createElement ('DIV');
	if ($('logo')) {
		var header = $('logo');
	} else {
		var header = $('page_logo');
	}
	var first = header.firstChild;
	status.id = 'server-communication';
	status.className = 'server-communication blink';
	status.innerHTML = '&nbsp;Connecting &hellip;';
	header.insertBefore (status, first);
}

function HOAM_serverCommunicationEnd () {
	// Remove notice to end-user tha communication is occurring with the server
	// ie, all activity has ceased
	var status = $('server-communication');
	if ($('logo')) {
		var header = $('logo');
	} else {
		var header = $('page_logo');
	}
	header.removeChild (status);
}

function HOAM_serverCommunicationError () {
	// Update the notice to show communication is occurring
	var status = $('server-communication');
	status.innerHTML = '&nbsp;** Error **';
}

function HOAM_serverCommunicationLoading () {
	// Update the notice to show communication is occurring
	var status = $('server-communication');
	status.innerHTML = '&nbsp;Loading &hellip;';
}

function HOAM_tableStripe () {
	// Locate any tables on the page and stripe alternate rows
	// NOTE! Only touches tbody's, not tables themselves. This may change in
	//       the future if I have time.
	// NOTE! If you do _NOT_ want a table striped, add the class 'nostripe' to
	//       the tbody.
	tables = document.getElementsByTagName ('TBODY');
	for (var i = 0; i < tables.length; i++) {
		// Added the .extend for f'ing IE
		Element.extend (tables[i]);
		if (!tables[i].hasClassName ('nostripe')) {
			ZebraTable.stripe (tables[i]);
		}
	}
}

function HOAM_tabLoading (container) {
	if (container) {
		var old_contents = container.descendants ();
		for (var i = 0; i < old_contents.length; i++) {
			Element.remove (old_contents[i]);
		}
		var div = document.createElement ('DIV');
		div.className = 'center';
		var img = document.createElement ('IMG');
		img.setAttribute ('src', '/hoam/images/throbber.gif');
		img.setAttribute ('alt', 'Progress animation');
		var message = document.createElement ('DIV');
		message.className = 'center';
		message.innerHTML = 'Loading &hellip;';
		div.appendChild (img);
		div.appendChild (message);
		container.appendChild (div);
	}
}

function HOAM_userLoginCheck (event) {
	if (($F('user_loginName') == '') || ($F('user_loginName') == 'user name')) {
		alert (HOAM_languageLookup ('errors|user|accountname|required'));
		$('user_loginName').focus();
		Event.stop (event);
	}
	if (!$F('user_loginPassword')) {
		alert (HOAM_languageLookup ('errors|user|password|required'));
		$('user_loginPassword').focus();
		Event.stop (event);
	}
}

function HOAM_verifyDate (event) {
	if ((Event.element(event).value.length == 0)) {
		HOAM_formErrorClear (Event.element(event).getAttribute ('name'));
	} else {
		if ((Event.element(event).value.length > 0) && (Event.element(event).value.length < Event.element(event).getAttribute ('minlength'))) {
			HOAM_formErrorMessage (Event.element(event).getAttribute ('name'), HOAM_languageLookup ('errors|generic|invalid-date', Event.element(event).getAttribute ('minlength')));
		} else {
// I don't know why, but the string read in through the config lookup isn't
// matching correctly, even though it's the same regex.
//			if (Event.element(event).value.match (HOAM_countryLookup ('date|match'))) {
			if (Event.element(event).value.match (/^([01]\d)[\-\.\/]([0-3]\d)[\-\.\/](19\d{2}|20\d{2})$/)) {
				HOAM_formErrorClear (Event.element(event).getAttribute ('name'));
				Event.element(event).value = Event.element(event).value.replace (/^([01]\d)[\-\.\/]([0-3]\d)[\-\.\/](19\d{2}|20\d{2})$/, '$1/$2/$3');
			} else {
				HOAM_formErrorMessage (Event.element(event).getAttribute ('name'), HOAM_languageLookup ('errors|generic|invalid-date'));
			}
		}
	}
}

function HOAM_verifyPostalcode (event) {
	if (Event.element(event).value.length < Event.element(event).getAttribute ('minlength')) {
		HOAM_formErrorMessage (Event.element(event).getAttribute('name'), HOAM_languageLookup ('errors|generic|postalcode-too-short', Event.element(event).getAttribute ('minlength')));
	} else {
//		if (Event.element(event).value.match (HOAM_countryLookup ('postalcode|match-js'))) {
		if (Event.element(event).value.match (/^(?!0{5})(\d{5})(?!-?0{4})(-?\d{4})?$/)) {
			HOAM_formErrorClear (Event.element(event).getAttribute ('name'));
			// Would like to do some auto formatting, this isn't working
			// currently
			// Event.element(event).value = Event.element(event).value.replace (/^(\d{5})$|^(\d{5})\-?(\d{4})$/, '$1-$2');
		} else {
			HOAM_formErrorMessage (Event.element(event).getAttribute ('name'), HOAM_languageLookup ('errors|generic|invalid-postalcode'));
		}
	}
}

function HOAM_verifyTelephone (event) {
	if ((Event.element(event).value.length == 0)) {
		HOAM_formErrorClear (Event.element(event).getAttribute ('name'));
	} else {
		if ((Event.element(event).value.length > 0) && (Event.element(event).value.length < Event.element(event).getAttribute ('minlength'))) {
			HOAM_formErrorMessage (Event.element(event).getAttribute ('name'), HOAM_languageLookup ('errors|generic|telephone-too-short', Event.element(event).getAttribute ('minlength')));
		} else {
// I don't know why, but the string read in through the config lookup isn't
// matching correctly, even though it's the same regex.
//			if (Event.element(event).value.match (HOAM_countryLookup ('telephone|match'))) {
			if (Event.element(event).value.match (/^\(?([2-9]\d{2})[^0-9]?\s?(\d{3})[^0-9]?\s?(\d{4})$/)) {
				HOAM_formErrorClear (Event.element(event).getAttribute ('name'));
				Event.element(event).value = Event.element(event).value.replace (/^\(?([2-9]\d{2})[^0-9]?\s?(\d{3})[^0-9]?\s?(\d{4})$/, '($1) $2-$3');
			} else {
				HOAM_formErrorMessage (Event.element(event).getAttribute ('name'), HOAM_languageLookup ('errors|generic|invalid-telephone'));
			}
		}
	}
}

function appendToSelectList (select, value, content) {
	// add item to select element the less elegant, but compatible way.
	var option = document.createElement ("option");
	option.value = value;
	option.appendChild (content);
	select.appendChild (option);
}

function binarySearch (array, find, caseInsensitive, getSubstring, arrayCheckThisIndex) {
	if (!array || typeof (array) != "object" || typeof (find) == "undefined" || !array.length) {
		return null;
	}
	find = (!caseInsensitive) ? find : find.toLowerCase();
	var low = 0;
	var high = array.length - 1;
	var highOnTop = (array[0] > array[array.length - 1]) ? 1 : 0;
	while (low <= high) {
		var aTry = parseInt((low + high) / 2);
		var checkThis = (typeof (arrayCheckThisIndex) == "undefined") ? array[aTry] : array[aTry][arrayCheckThisIndex];
		checkThis = (!caseInsensitive) ? checkThis : checkThis.toLowerCase();
		checkThis = (!getSubstring) ? checkThis : checkThis.substring(0, find.length);
		if (!highOnTop) {
			if (checkThis < find) {
				low = aTry + 1;
				continue;
			}
			if (checkThis > find) {
				high = aTry - 1;
				continue;
			}
		} else {
			if (checkThis > find) {
				low = aTry + 1;
				continue;
			}
			if (checkThis < find) {
				high = aTry - 1;
				continue;
			}
		}
		return aTry;
	}
	return null;
}

function clearSelectList (id) {
	// empty select list content
	var select = document.getElementById (id);
	while (select.length > 0) {
		select.remove (0);
	}
}

function getElementTextNS (prefix, local, parentElem, index) {
	// Based on Apple's example at http://developer.apple.com/internet/webcontent/xmlhttpreq.html
	// retrieve text of an XML document element, including elements using namespaces
	var result = "";
	if (prefix) {
		// IE/Windows way of handling namespaces
		result = parentElem.getElementsByTagName(prefix + ":" + local)[index];
	} else {
		// the namespace versions of this method
		// (getElementsByTagNameNS()) operate
		// differently in Safari and Mozilla, but both
		// return value with just local name, provided
		// there aren't conflicts with non-namespace element
		// names
		result = parentElem.getElementsByTagName(local)[index];
	}
	if (result) {
		// get text, accounting for possible
		// whitespace (carriage return) text nodes
		if (result.childNodes.length > 1) {
			return result.childNodes[1].nodeValue;
		} else {
			if (result.childNodes.length == 1) {
				return result.firstChild.nodeValue;
			} else {
				return result.nodeValue;
			}
		}
	} else {
		return "n/a";
	}
}

function getSelectIndex (id, value) {
	// find out the select option index of the value we have
	var select = document.getElementById (id);
	// Make sure we have something to do...
	if (select.length > 0) {
		var i = 0;
		while (i < select.length) {
			if (select.childNodes[i].value == value) {
				return (i);
			}
			i++
		}
		// fail out and return the default value
		return -1;
	}
}

function include (filename) {
	// From http://forums.digitalpoint.com/showthread.php?t=146094
	//
	// Be aware of the limitation that this function will place the included
	// file at the end of the hard-coded head. This means that any code
	// dependent on the incuded file must be listed after the file
	// just included (typically meaning it must be included also).
	var head = document.getElementsByTagName ('head')[0];
	
	script = document.createElement ('script');
	script.src = filename;
	script.type = 'text/javascript';
	
	head.appendChild (script)
}

function leadingZero (number) {
	// Shamelessly stolen from http://www.quirksmode.org/js/date.html
	if (number < 10) {
		number = "0" + number;
	}
	return number;
}

function moreFields (source_elem, dest_elem) {
	// Stolen from the excellent http://www.quirksmode.org/ site
	// Modified, of course
	if (!source_elem) source_elem = $('readroot');
	if (!dest_elem) dest_elem = $('writeroot');
	// We're using a non-standard attribute to store a temporary counter of how many 'new' fields we've added
	var counter = parseInt (source_elem.parentNode.getAttribute ('ref')) + 1;
	if (isNaN (counter)) {
		counter = 1;
	}
	var newFields = source_elem.cloneNode (true);
	newFields.id = '';
	newFields.style.display = 'block';
	var newField = newFields.childNodes;
	for (var i = 0; i < newField.length; i++) {
		var theName = newField[i].name
		if (theName)
		newField[i].name = theName + counter;
	}
	// Save the counter.
	source_elem.parentNode.setAttribute ('ref', counter);
	dest_elem.parentNode.insertBefore (newFields, dest_elem);
}

function removeAllChildren (id) {
	i = 0;
	var object = document.getElementById (id);
	while (i < object.childNodes.length) {
		object.removeChild (object.childNodes[0]);
	}
}

function removeLastChild (id) {
	// Remove the last child (if present)
	var object = document.getElementById (id).lastChild;
	if (object) {
		document.getElementById (id).removeChild (object);
	}
}

function replaceTextNode (id, text) {
	var element = document.getElementById (id);
	element.value = text;
}

function getSelectIndex (elem) {
	// Make sure we have something to do...
	for (var i = 0; i < elem.length; i++) {
		if (elem.options[i].selected) {
			return i;
		}
	}
	return false;
}

function setSelectIndex (id, value) {
	// find out the select option index of the value we have
	var select = document.getElementById (id);
	// Make sure we have something to do...
	for (var i = 0; i < select.length; i++) {
		if (select[i].value == value) {
			select.selectedIndex = i;
			return i;
		}
	}
}

function setTextNode (id, text) {
	var node = document.createTextNode (text);
	document.getElementById (id).appendChild (node);
}

function sleep (time) {
	// wait (time) millisecs
	var then, now;
	then = new Date ().getTime ();
	now = then;
	while ((now - then) < time) {
		now = new Date ().getTime ();
	}
}

function setSelect (select, property) {
	var found = false;
	for (var i = 0; i < select.options.length; i++) {
		if (select.options[i][property].toUpperCase().indexOf(field.value.toUpperCase()) == 0) {
			found = true;
			break;
		}
	}
	if (found) {
		select.selectedIndex = i;
	} else {
		select.selectedIndex = -1;
	}
}

function setSelectAll (select) {
	var select = document.getElementById (select);
	for (var i = 0; i < select.options.length; i++) {
		select.options[i].selected = true;
	}
}

/**
 * sprintf() for JavaScript v.0.4
 *
 * Copyright (c) 2007 Alexandru Marasteanu <http://alexei.417.ro/>
 * Thanks to David Baird (unit test and patch).
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

function str_repeat(i, m) { for (var o = []; m > 0; o[--m] = i); return(o.join('')); }

function sprintf () {
  var i = 0, a, f = arguments[i++], o = [], m, p, c, x;
  while (f) {
    if (m = /^[^\x25]+/.exec(f)) o.push(m[0]);
    else if (m = /^\x25{2}/.exec(f)) o.push('%');
    else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f)) {
      if (((a = arguments[m[1] || i++]) == null) || (a == undefined)) throw("Too few arguments.");
      if (/[^s]/.test(m[7]) && (typeof(a) != 'number'))
        throw("Expecting number but found " + typeof(a));
      switch (m[7]) {
        case 'b': a = a.toString(2); break;
        case 'c': a = String.fromCharCode(a); break;
        case 'd': a = parseInt(a); break;
        case 'e': a = m[6] ? a.toExponential(m[6]) : a.toExponential(); break;
        case 'f': a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a); break;
        case 'o': a = a.toString(8); break;
        case 's': a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a); break;
        case 'u': a = Math.abs(a); break;
        case 'x': a = a.toString(16); break;
        case 'X': a = a.toString(16).toUpperCase(); break;
      }
      a = (/[def]/.test(m[7]) && m[2] && a > 0 ? '+' + a : a);
      c = m[3] ? m[3] == '0' ? '0' : m[3].charAt(1) : ' ';
      x = m[5] - String(a).length;
      p = m[5] ? str_repeat(c, x) : '';
      o.push(m[4] ? a + p : p + a);
    }
    else throw ("Huh ?!");
    f = f.substring(m[0].length);
  }
  return o.join('');
}
