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

function HOAM_budgetPaymentInit () {
	// Preload the Paypal info
	HOAM_budgetPaypalInfo ();
	
	Event.observe ($('budget|payment|amount'), 'focus', function () {
		if ($F('budget|payment|amount') == HOAM_returnCurrency (0)) {
			$('budget|payment|amount').value = '';
		}
	});
    Event.observe ($('budget|payment|amount'), 'change', function () {
    	$('budget|payment|amount').value = HOAM_returnCurrency ($F('budget|payment|amount'), 0);
		HOAM_budgetPaymentAmountCheck ();
    	HOAM_budgetPaymentMemoUpdate ();
    });
    Event.observe ($('budget|payment|checknum'), 'change', HOAM_budgetPaymentMemoUpdate);
    // Does not exist when paying vendor invoices.
    if ($('budget|payment|datereceived')) {
    	Event.observe ($('budget|payment|datereceived'), 'change', HOAM_budgetPaymentMemoUpdate);
    }
    Event.observe ($('budget|payment|method'), 'change', HOAM_budgetPaymentMethodUpdate);
    // Does not exist when paying vendor invoices.
    if ($('budget|payment|paypalid')) {
    	Event.observe ($('budget|payment|paypalid'), 'change', HOAM_budgetPaymentMemoUpdate);
    }
    if ($('budget|payment|writeoff')) {
    	Event.observe ($('budget|payment|writeoff'), 'change', HOAM_budgetPaymentMethodUpdate);
    }
	
    HOAM_budgetPaymentMethodUpdate ();
    if ($('budget|payment|customer|show-all')) {
    	Event.observe ($('budget|payment|customer|show-all'), 'click', HOAM_budgetPaymentCustomerUpdate);
    	HOAM_budgetPaymentCustomerUpdate ();
    }
}

function HOAM_budgetPaymentAmountCheck () {
	// Check the payment amount vs the owed amount
	var amount_due = $('budget|payment|customer')[$('budget|payment|customer').selectedIndex].getAttribute ('amount_due');

	if (parseFloat ($F('budget|payment|amount')) > parseFloat (amount_due * 1.5)) {
		HOAM_formErrorMessage ('budget|payment|amount', HOAM_languageLookup ('errors|budget|checknum_swapped'));
		var div = document.createElement ("DIV");
		var cb = document.createElement ("INPUT");
		var label = document.createElement ("LABEL");
		div.id = 'budget|payment|amount|ignore';
		cb.name = 'budget|payment|amount|ignore';
		cb.type = 'checkbox';
		cb.value = 1;
		label.textContent = HOAM_languageLookup ('errors|generic|ignore');
		div.appendChild (cb);
		div.appendChild (label);
		$('new_payment').insertBefore (div, $('budget|payment|amount').nextSibling);
	} else if (amount_due != $F('budget|payment|amount')) {
		if ($('budget|payment|amount|ignore')) {
			document.removeChild ($('budget|payment|amount|ignore'));
		}
		HOAM_formErrorMessage ('budget|payment|amount', HOAM_languageLookup ('errors|budget|amount_different'));
	} else {
		if ($('budget|payment|amount|ignore')) {
			document.removeChild ($('budget|payment|amount|ignore'));
		}
		HOAM_formErrorClear ('budget|payment|amount');
	}
}

function HOAM_budgetPaymentCustomerUpdate () {
	// Show or hide customers with zero balance or a credit
	var options = $('budget|payment|customer').getElementsByTagName('option');
	if ($('budget|payment|customer|show-all').checked) {
		for (var i = 0; i < options.length; i++) {
			if (options[i].getAttribute ('field') == 'hide') {
				options[i].show();
			}
		}
	} else {
		for (var i = 0; i < options.length; i++) {
			if (options[i].getAttribute ('field') == 'hide') {
				options[i].hide();
			}
		}		
	}
}

function HOAM_budgetPaymentMemoUpdate () {
	if ($F('budget|payment|amount')) {
		switch ($F('budget|payment|method')) {
			// autodraft only exists when paying vendor invoices.
			case 'autodraft':
			case 'credit':
			case 'check':
				var amount = 'Total $' + HOAM_returnCurrency ($F('budget|payment|amount'));
				break;
			case 'paypal':
				// The system lets you charge either a fixed amount or a
				// percentage of the amount paid to cover PayPal fees. Normally
				// just one or the other is used. However, we'll subtract both
				// just in case; if the amount is 0, then zero is subtracted.
				// Likewise, if percent is zero, we just divide by 1.
				var amount = $F('budget|payment|amount').toString().replace(/\$|\,/g, '');
				var amount = amount - HOAM_budgetPaypalSurchargeAmount;
				var amount = amount / ((100 + HOAM_budgetPaypalSurchargePercent) / 100);
				var amount = amount.toFixed(2);
				var amount = 'Total $' + HOAM_returnCurrency (amount) + ' ($' + HOAM_returnCurrency ($F('budget|payment|amount')) + ' minus PayPal fees)';
				break;
			case 'writeoff':
			default:
				var amount = '';
				break;
		}
	}
	switch ($F('budget|payment|method')) {
		case 'autodraft':
			// autodraft only exists when paying vendor invoices.
			var check = '';
			var memo = HOAM_languageLookup ('field_names|budget|payment|by_autodraft');
			break;
		case 'check':
			var check = 'Check #' + $F('budget|payment|checknum');
		    // Does not exist when paying vendor invoices.
			if ($('budget|payment|datereceived')) {
				var memo = 'Payment Received ' + $F('budget|payment|datereceived');
			} else {
			    // Only exists when paying vendor invoices.
				var memo = HOAM_languageLookup ('field_names|budget|payment|by_check') + ' ' + $F('budget|invoice|datepaid');
			}
			break;
		case 'credit':
			// credit only exists when paying vendor invoices.
			var check = '';
			var memo = HOAM_languageLookup ('field_names|budget|payment|by_credit');
			break;
		case 'paypal':
			var check = 'PayPal Transaction ID #' + $F('budget|payment|paypalid');
			var memo = 'Payment Received ' + $F('budget|payment|datereceived');
			break;
		case 'writeoff':
			var check = HOAM_languageLookup ('field_names|budget|writeoff|reason|writeoff');
			var memo = '';
			break;
		default:
			var check = '';
			var memo = '';
			break;
	}
	if (amount && memo) {
		memo = memo + ' (' + amount;
		// No check number when autodraft.
		if (check) {
			memo = memo + ' : ' + check;
		}
		memo = memo +  ')';
	} else {
		memo = check + HOAM_languageLookup ('field_names|budget|writeoff|reason|' + $F('budget|payment|writeoff'));
	}
	$('budget|payment|memo').value = memo;
}

function HOAM_budgetPaymentMethodUpdate () {
	switch ($F('budget|payment|method')) {
		case 'autodraft':
			// autodraft only exists when paying vendor invoices.
			$('budget|payment|checknum').hide ();
			$('budget|payment|checknum').previousSibling.hide ();
			break;
		case 'check':
		    // Does not exist when paying vendor invoices.
			if ($('budget|payment|paypalid')) {
				$('budget|payment|paypalid').hide ();
				$('budget|payment|paypalid').previousSibling.hide ();
				$('budget|payment|writeoff').hide ();
				$('budget|payment|writeoff').previousSibling.hide ();
			}
			$('budget|payment|checknum').show ();
			$('budget|payment|checknum').previousSibling.show ();
			break;
		case 'credit':
			// credit only exists when paying vendor invoices.
			$('budget|payment|checknum').hide ();
			$('budget|payment|checknum').previousSibling.hide ();
			break;
		case 'paypal':
		    // Does not exist when paying vendor invoices.
			if ($('budget|payment|paypalid')) {
				$('budget|payment|paypalid').show ();
				$('budget|payment|paypalid').previousSibling.show ();
				$('budget|payment|writeoff').hide ();
				$('budget|payment|writeoff').previousSibling.hide ();
			}
			$('budget|payment|checknum').hide ();
			$('budget|payment|checknum').previousSibling.hide ();
			break;
		case 'writeoff':
		    // Does not exist when paying vendor invoices.
			if ($('budget|payment|paypalid')) {
				$('budget|payment|paypalid').hide ();
				$('budget|payment|paypalid').previousSibling.hide ();
				$('budget|payment|writeoff').show ();
				$('budget|payment|writeoff').previousSibling.show ();
			}
			$('budget|payment|checknum').hide ();
			$('budget|payment|checknum').previousSibling.hide ();
			break;
		default:
		    // Does not exist when paying vendor invoices.
			if ($('budget|payment|paypalid')) {
				$('budget|payment|paypalid').hide ();
				$('budget|payment|paypalid').previousSibling.hide ();
				$('budget|payment|writeoff').hide ();
				$('budget|payment|writeoff').previousSibling.hide ();
			}
			$('budget|payment|checknum').show ();
			$('budget|payment|checknum').previousSibling.show ();
			break;
	}
	HOAM_budgetPaymentMemoUpdate ();
}

function HOAM_budgetPaypalInfo () {
	var url = '/hoam/scripts/budget/xml_paypal.php';
	var pars = '';
	
	var myAjax = new Ajax.Request (url, { method: 'get', parameters: pars, onComplete: function (originalRequest) {
		// put returned XML in the textarea
		var items = originalRequest.responseXML.getElementsByTagName ('ajax-response');
		HOAM_budgetPaypalEmail = getElementTextNS ('', 'email', items[0], 0);
		HOAM_budgetPaypalEnable = getElementTextNS ('', 'enable', items[0], 0);
		HOAM_budgetPaypalSurchargeAmount = parseInt (getElementTextNS ('', 'surcharge_amount', items[0], 0));
		HOAM_budgetPaypalSurchargePercent = parseInt (getElementTextNS ('', 'surcharge_percent', items[0], 0));
	}
	});
}

// Define these globally
var HOAM_budgetPaypalEmail, HOAM_budgetPaypalEnable, HOAM_budgetPaypalSurchargeAmount, HOAM_budgetPaypalSurchargePercent;

Event.observe (window, 'load', HOAM_budgetPaymentInit);
