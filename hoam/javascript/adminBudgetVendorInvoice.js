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

function HOAM_budgetVendorInvoiceInit () {
    Event.observe ($('budget|invoice|amount'), 'change', function () {
    	$('budget|invoice|amount').value = HOAM_returnCurrency ($F('budget|invoice|amount'), 1);
    	});
    Event.observe ($('budget|invoice|vendor'), 'change', HOAM_budgetVendorInvoiceCategoryUpdate);
    Event.observe ($('budget|payment|method'), 'change', HOAM_budgetVendorPaymentMethodUpdate);

    HOAM_budgetVendorInvoiceCategoryUpdate ();
    HOAM_budgetVendorPaymentMethodUpdate ();
}

function HOAM_budgetVendorInvoiceCategoryUpdate () {
	// Display the budget category information
	var vendor = $F('budget|invoice|vendor');
	var url = '/hoam/scripts/budget/xml_vendors.php';
	var pars = 'id=' + vendor;

	var myAjax = new Ajax.Request (url, {
		method: 'get',
		parameters: pars,
		onComplete: function (originalRequest) {
			var items = originalRequest.responseXML.getElementsByTagName ('item');
			for (count = 0; count < items.length; count++) {
				// Need to add a check in here so we don't override a different
				// selected category while fixing errors on the submit.
				var default_category = getElementTextNS ('', 'default_category', items[count], 0);
				setSelectIndex ('budget|invoice|category', default_category);
			}
		}
	});
	
}

function HOAM_budgetVendorPaymentMethodUpdate () {
	switch ($F('budget|payment|method')) {
		case 'autodraft':
			$('budget|payment|checknum').hide ();
			$('budget|payment|checknum').previousSibling.hide ();
			$('budget|payment|credit').hide ();
			$('budget|payment|credit').previousSibling.hide ();

			$('budget|payment|autodraft').show ();
			$('budget|payment|autodraft').previousSibling.show ();
			// Fill in default text
			$('budget|payment|autodraft').value = HOAM_languageLookup ('field_names|budget|payment|by_autodraft')
			break;
		case 'check':
			$('budget|payment|autodraft').hide ();
			$('budget|payment|autodraft').previousSibling.hide ();
			$('budget|payment|checknum').show ();
			$('budget|payment|checknum').previousSibling.show ();
			$('budget|payment|credit').hide ();
			$('budget|payment|credit').previousSibling.hide ();
			break;
		case 'credit':
			$('budget|payment|autodraft').hide ();
			$('budget|payment|autodraft').previousSibling.hide ();
			$('budget|payment|checknum').hide ();
			$('budget|payment|checknum').previousSibling.hide ();
			$('budget|payment|credit').show ();
			$('budget|payment|credit').previousSibling.show ();
			// Fill in default text
			$('budget|payment|credit').value = HOAM_languageLookup ('field_names|budget|payment|by_credit')
			break;
		default:
			$('budget|payment|autodraft').hide ();
			$('budget|payment|autodraft').previousSibling.hide ();
			$('budget|payment|credit').hide ();
			$('budget|payment|credit').previousSibling.hide ();

			$('budget|payment|checknum').show ();
			$('budget|payment|checknum').previousSibling.show ();
			break;
	}
}

Event.observe (window, 'load', HOAM_budgetVendorInvoiceInit, false);
