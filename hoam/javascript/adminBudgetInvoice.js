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

function HOAM_budgetInvoiceInit () {
	Element.observe ($('add'), 'click', HOAM_budgetInvoiceSubmit);

	Element.observe ($('budget|invoice|charge|add'), 'click', HOAM_budgetInvoiceChargeAdd);

	Event.observe ($('to_all_button'), 'click', function () {
		HOAM_moveOption ('new_invoice', 'budget|invoice|customer', 'budget|invoice|customer-all');
		});
	Event.observe ($('to_customer_button'), 'click', function () {
		HOAM_moveOption ('new_invoice', 'budget|invoice|customer-all', 'budget|invoice|customer');
		});
	// We want to cache this since it would be painful to load every time.
	budget_categories = HOAM_budgetInvoiceCategoryList ();
}

function HOAM_budgetInvoiceCategoryList () {
	// Display the budget category information
	var url = '/hoam/scripts/budget/xml_categories.php';
	var categories = [];

	var myAjax = new Ajax.Request (url, {method: 'get', parameters: url, onComplete: function (originalRequest) {
		// Clear out any old content
		var items = originalRequest.responseXML.getElementsByTagName ('item');
	    for (var i = 0; i < items.length; i++) {
	    	// yes, a 2-dim array would make everything cleaner, but I had
	    	// problems getting it to return.
	    	categories.push (getElementTextNS ('', 'name', items[i], 0));
	    	categories.push (getElementTextNS ('', 'child', items[i], 0));
	    }
	}
	});
	return categories;
}

function HOAM_budgetInvoiceChargeAdd () {
	// Clone the attachment code, so we don't have to kepe reloading it.
	var table = document.createElement ('TABLE');
	table.addClassName ('budget_charge');
	table.setAttribute ('width', '100%');
	var colgroup = document.createElement ('COLGROUP');
	var col = document.createElement ('COL');
	col.setAttribute ('width', '20%');
	colgroup.appendChild (col);
	var col = document.createElement ('COL');
	colgroup.appendChild (col);
	var col = document.createElement ('COL');
	col.setAttribute ('width', '20%');
	colgroup.appendChild (col);
	var col = document.createElement ('COL');
	col.setAttribute ('width', '10%');
	colgroup.appendChild (col);
	table.appendChild (colgroup);
	var tr = document.createElement ('TR');
	tr.addClassName ('alternate-row');
	var td = document.createElement ('TD');
	td.addClassName ('budget_datedue');
	td.addClassName ('less-opacity');
	td.innerHTML = HOAM_languageLookup ('field_names|budget|invoice|charge|due');
	Element.observe (td, 'click', HOAM_budgetInvoiceEditDateDue);
	tr.appendChild (td);
	var td = document.createElement ('TD');
	td.addClassName ('budget_memo');
	td.addClassName ('less-opacity');
	td.innerHTML = HOAM_languageLookup ('field_names|budget|invoice|charge|memo');
	Element.observe (td, 'click', HOAM_budgetInvoiceEditMemo);
	tr.appendChild (td);
	var td = document.createElement ('TD');
	td.addClassName ('budget_amount');
	td.addClassName ('less-opacity');
	td.innerHTML = HOAM_languageLookup ('field_names|budget|invoice|charge|amount');
	Element.observe (td, 'click', HOAM_budgetInvoiceEditAmount);
	tr.appendChild (td);
	var td = document.createElement ('TD');
	td.addClassName ('center');
	td.setAttribute ('rowspan', '2');
	var button = document.createElement ('BUTTON');
	button.setAttribute ('type', 'button');
	button.innerHTML = HOAM_languageLookup ('field_names|budget|invoice|charge|remove');
	Element.observe (button, 'click', function (event) {
		Event.element(event).parentNode.parentNode.parentNode.parentNode.removeChild(Event.element(event).parentNode.parentNode.parentNode);
	});
	td.appendChild (button);
	tr.appendChild (td);
	table.appendChild (tr);
	var tr = document.createElement ('TR');
	var td = document.createElement ('TD');
	td.addClassName ('budget_datelate');
	td.addClassName ('less-opacity');
	td.innerHTML = HOAM_languageLookup ('field_names|budget|invoice|charge|late');
	Element.observe (td, 'click', HOAM_budgetInvoiceEditDateLate);
	tr.appendChild (td);
	var td = document.createElement ('TD');
	td.addClassName ('budget_category');
	td.addClassName ('less-opacity');
	td.setAttribute ('colspan', '2');
	td.innerHTML = HOAM_languageLookup ('field_names|budget|invoice|charge|category');
	Element.observe (td, 'click', HOAM_budgetInvoiceEditCategory);
	tr.appendChild (td);
	table.appendChild (tr);
	$('budget|invoice|charge|add').parentNode.appendChild (table);
}

function HOAM_budgetInvoiceEditAmount (event) {
	var elem = Event.element(event);
	Event.stopObserving(Event.element(event));
	var field = document.createElement ('INPUT');
	field.addClassName ('text');
	if ((elem.innerHTML == HOAM_languageLookup ('field_names|budget|invoice|charge|amount')) || (elem.innerHTML == '')) {
	} else {
		field.value = elem.innerHTML;
	}
	elem.innerHTML = '';
	Element.observe (field, 'blur', function (e) {
		if (Event.element(e).value != HOAM_returnCurrency (0)) {
		} else {
			elem.addClassName ('less-opacity');
		}
		Event.element(e).parentNode.innerHTML = HOAM_returnCurrency (Event.element(e).value);
		Event.element(e).remove;
		Element.observe (elem, 'click', HOAM_budgetInvoiceEditAmount);
	});
	Element.observe (field, 'keydown', function (e) {
		if (e.shiftKey && e.keyCode == 9) {
  			//shift was down when tab was pressed
			e.stop();
			Event.element(e).parentNode.previousSibling.simulate('click');
		}
		if (e.keyCode == 9) {
			e.stop();
			Event.element(e).parentNode.parentNode.nextSibling.childNodes[0].simulate('click');
		}
	});
	elem.appendChild(field);
	elem.removeClassName ('less-opacity');
	field.focus();
	if (field.value == HOAM_returnCurrency (0)) {
		field.select();
	}
}

function HOAM_budgetInvoiceEditCategory (event) {
	var elem = Event.element(event);
	Event.stopObserving(Event.element(event));
	var old_category = elem.innerHTML;
	var field = document.createElement ('SELECT');
	field.addClassName ('text');
	field.id = 'category_select';
	for (var i = 0; i < budget_categories.length;) {
		if (budget_categories[i + 1] == '0') {
			var optgroup = document.createElement ('OPTGROUP');
			optgroup.label = budget_categories[i];
			// Skip to the next element
			i++; i++;
			while (budget_categories[i + 1] == '1') {
				var opt = document.createElement ('OPTION');
				opt.innerHTML = budget_categories[i];
				opt.value = budget_categories[i];
				optgroup.appendChild(opt);
				// Skip to the next element
				i++; i++;
			}
			field.appendChild(optgroup);
		}
	}
	elem.innerHTML = '';
	Element.observe (field, 'blur', function (e) {
		var i = getSelectIndex (Event.element(e));
		Event.element(e).parentNode.innerHTML = (Event.element(e).options[i].innerHTML);
		Event.element(e).remove;
		Element.observe (elem, 'click', HOAM_budgetInvoiceEditCategory);
	});
	Element.observe (field, 'keydown', function (e) {
			if (e.shiftKey && e.keyCode == 9) {
  			//shift was down when tab was pressed
			e.stop();
			Event.element(e).parentNode.previousSibling.simulate('click');
		}
	});
	elem.appendChild(field);
	elem.removeClassName ('less-opacity');
	setSelectIndex ('category_select', old_category);
	field.focus();
}

function HOAM_budgetInvoiceEditDateDue (event) {
	var elem = Event.element(event);
	Event.stopObserving(Event.element(event));
	var field = document.createElement ('INPUT');
	field.addClassName ('text');
	if ((elem.innerHTML == HOAM_languageLookup ('field_names|budget|invoice|charge|due')) || (elem.innerHTML == '')) {
		field.value = HOAM_returnDateCurrent ();
	} else {
		field.value = elem.innerHTML;
	}
	elem.innerHTML = '';
	Element.observe (field, 'blur', function (e) {
		Event.element(e).parentNode.innerHTML = Event.element(e).value;
		Event.element(e).remove;
		Element.observe (elem, 'click', HOAM_budgetInvoiceEditDateDue);
	});
	Element.observe (field, 'keydown', function (e) {
		if (e.keyCode == 9) {
			e.stop();
			Event.element(e).parentNode.nextSibling.simulate('click');
		}
	});
	elem.appendChild (field);
	elem.removeClassName ('less-opacity');
	field.focus();
}

function HOAM_budgetInvoiceEditDateLate (event) {
	var elem = Event.element(event);
	Event.stopObserving(Event.element(event));
	var field = document.createElement ('INPUT');
	field.addClassName ('text');
	if ((elem.innerHTML == HOAM_languageLookup ('field_names|budget|invoice|charge|late')) || (elem.innerHTML == '')) {
		field.value = HOAM_returnDateEndOfMonth ();
	} else {
		field.value = elem.innerHTML;
	}
	elem.innerHTML = '';
	Element.observe (field, 'blur', function (e) {
		Event.element(e).parentNode.innerHTML = Event.element(e).value;
		Event.element(e).remove;
		Element.observe (elem, 'click', HOAM_budgetInvoiceEditDateLate);
	});
	Element.observe (field, 'keydown', function (e) {
		if (e.shiftKey && e.keyCode == 9) {
  			//shift was down when tab was pressed
			e.stop();
			Event.element(e).parentNode.parentNode.previousSibling.childNodes[2].simulate('click');
		}
		if (e.keyCode == 9) {
			e.stop();
			Event.element(e).parentNode.nextSibling.simulate('click');
		}
	});
	elem.appendChild(field);
	elem.removeClassName ('less-opacity');
	field.focus();
}

function HOAM_budgetInvoiceEditMemo (event) {
	var elem = Event.element(event);
	Event.stopObserving(Event.element(event));
	var field = document.createElement ('INPUT');
	field.addClassName ('text');
	if ((elem.innerHTML == HOAM_languageLookup ('field_names|budget|invoice|charge|memo')) || (elem.innerHTML == '')) {
		field.value = '';
	} else {
		field.value = elem.innerHTML;
	}
	elem.innerHTML = '';
	Element.observe (field, 'blur', function (e) {
		if (Event.element(e).value != '') {
		} else {
			elem.addClassName ('less-opacity');
		}
		Event.element(e).parentNode.innerHTML = Event.element(e).value;
		Event.element(e).remove;
		Element.observe (elem, 'click', HOAM_budgetInvoiceEditMemo);
	});
	Element.observe (field, 'keydown', function (e) {
		if (e.shiftKey && e.keyCode == 9) {
  			//shift was down when tab was pressed
			e.stop();
			Event.element(e).parentNode.previousSibling.simulate('click');
		}
		if (e.keyCode == 9) {
			e.stop();
			Event.element(e).parentNode.nextSibling.simulate('click');
		}
	});
	elem.appendChild(field);
	elem.removeClassName ('less-opacity');
	field.focus();
}

function HOAM_budgetInvoiceSubmit (e) {
	e.stop();
	var charges = $('budget|invoice|charges').getElementsByTagName ('TABLE');
//	alert (charges.length);
//	alert ($('budget|invoice|customer').length);
//	alert ($('budget|invoice|customer')[0].value);
	for (i = 0; i < charges.length; i++) {
		var cells = charges[i].getElementsByTagName ('TD');
		var datedue = document.createElement ('INPUT');
		datedue.setAttribute ('type', 'hidden');
		datedue.setAttribute ('name', 'datedue' + i);
		datedue.value = cells[0].innerHTML;
		$('budget|invoice|charges').appendChild(datedue);
		var memo = document.createElement ('INPUT');
		memo.setAttribute ('type', 'hidden');
		memo.setAttribute ('name', 'memo' + i);
		memo.value = cells[1].innerHTML;
		$('budget|invoice|charges').appendChild(memo);
		var amount = document.createElement ('INPUT');
		amount.setAttribute ('type', 'hidden');
		amount.setAttribute ('name', 'amount' + i);
		amount.value = cells[2].innerHTML;
		$('budget|invoice|charges').appendChild(amount);
		var datelate = document.createElement ('INPUT');
		datelate.setAttribute ('type', 'hidden');
		datelate.setAttribute ('name', 'datelate' + i);
		datelate.value = cells[4].innerHTML;
		$('budget|invoice|charges').appendChild(datelate);
		var category = document.createElement ('INPUT');
		category.setAttribute ('type', 'hidden');
		category.setAttribute ('name', 'category' + i);
		category.value = cells[5].innerHTML;
		$('budget|invoice|charges').appendChild(category);
	}
	var count = document.createElement ('INPUT');
	count.setAttribute ('type', 'hidden');
	count.setAttribute ('name', 'charge_count');
	count.value = i;
	$('budget|invoice|charges').appendChild(count);
	var add = document.createElement ('input');
	add.type = 'hidden';
	add.name = 'add';
	add.value = 'add';	
	$('new_invoice').appendChild (add);
	setSelectAll ('budget|invoice|customer');
	$('new_invoice').submit();
}

var budget_categories = [];
Event.observe (window, 'load', HOAM_budgetInvoiceInit, false);
