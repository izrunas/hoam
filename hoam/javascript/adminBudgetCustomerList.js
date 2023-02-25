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

function HOAM_budgetCustomerListInit () {
	$('showPrevious').observe ('click', HOAM_budgetCustomerListPreviousToggle);
	$('showZero').observe ('click', HOAM_budgetCustomerListZeroToggle);
	
	HOAM_budgetCustomerListPreviousToggle ();
	HOAM_budgetCustomerListZeroToggle ();
}

function HOAM_budgetCustomerListPreviousToggle () {
	tablerows = $('budget_customer_list_tbody').select ('tr.previous_owner');
	for (var i = 0; i < tablerows.length; i++) {
		if ($('showPrevious').checked) {
			tablerows[i].show ();
		} else {
			tablerows[i].hide ();
		}
	}
}

function HOAM_budgetCustomerListZeroToggle () {
	tablerows = $('budget_customer_list_tbody').getElementsByTagName ('TR');
	for (var i = 0; i < tablerows.length; i++) {
		amount = tablerows[i].select ('td.currency');
		if (amount[0].innerHTML == '0.00') {
			if ($('showZero').checked) {
				tablerows[i].show ();
			} else {
				tablerows[i].hide ();
			}
		}
	}
}

Event.observe (window, 'load', HOAM_budgetCustomerListInit);
