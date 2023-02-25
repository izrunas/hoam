<?php
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

class HOAM_budget_invoice extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!

	function changeCustomerId ($id, $value) {
		return ($this -> changeString ($id, 'customer_id', $value));
	}

	function changeInvoiceDate ($id, $value) {
		return ($this -> changeString ($id, 'dateinvoice', $value));
	}
	
	function changeNumber ($id, $value) {
		return ($this -> changeString ($id, 'number', $value));
	}

	function changeVendorId ($id, $value) {
		return ($this -> changeString ($id, 'vendor_id', $value));
	}

	function listInvoicesForCustomer ($customer_id, $current = FALSE) {
		// return an array containing the id of all invoices
		global $_HOAM_setting;
		
		if ($current) {
			$query = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id'
					ORDER BY dateinvoice DESC, datecreated DESC";
		} else {
			$query = "SELECT DISTINCT(budget_invoice.id), budget_invoice.dateinvoice, budget_invoice.datecreated FROM budget, budget_invoice
					WHERE budget_invoice.id = budget.invoice_id
					AND budget.dateposted IS NULL
					AND ~budget.flags & " . $_HOAM_setting ['budget']['flags']['payment'] . "
					AND budget.customer_id='$customer_id'
					ORDER BY budget_invoice.dateinvoice ASC, budget_invoice.datecreated ASC";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}

	function listInvoicesForVendor ($vendor_id) {
		// return an array containing the id of all invoices
		$query = "SELECT id FROM $this->table
					WHERE vendor_id = '$vendor_id' ORDER BY dateinvoice DESC, datecreated DESC";
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}

	function returnDateDue ($invoice_id) {
		global $HOAM_setting;
		$query = "SELECT datedue FROM budget WHERE invoice_id = '$invoice_id' ORDER BY datedue ASC LIMIT 1";
		// Execute the query
		$amount = $this -> itemQuery ($query);
		return ($amount);
	}

	function returnAverageInvoice ($vendor_id) {
		// return the average invoice amount for the vendor
		$query = "SELECT AVG(amount) FROM budget WHERE vendor_id = '$vendor_id' AND amount > 0";
		return ($this -> itemQuery ($query));
	}
	
	function returnCustomerId ($id) {
		return ($this -> returnItem ($id, 'customer_id'));
	}

	function returnInvoiceDate ($id) {
		return ($this -> returnItem ($id, 'dateinvoice'));
	}

	function returnLastInvoiceCustomer ($customer_id) {
		$query = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id' ORDER BY datecreated DESC LIMIT 1";
		return ($this -> itemQuery ($query));
	}

	function returnLastInvoiceVendor ($vendor_id) {
		$query = "SELECT id FROM $this->table
					WHERE vendor_id = '$vendor_id' ORDER BY datecreated DESC LIMIT 1";
		return ($this -> itemQuery ($query));
	}

	function returnNumber ($id) {
		return ($this -> returnItem ($id, 'number'));
	}

	function returnTotalAmount ($invoice_id) {
		global $_HOAM_setting;
		
		$query = "SELECT SUM(amount) FROM budget
					WHERE invoice_id = '$invoice_id' AND
					~flags & " . $_HOAM_setting ['budget']['flags']['payment'];
		// Execute the query
		$amount = $this -> itemQuery ($query);
		return ($amount);
	}

	function returnTotalAmountDue ($invoice_id) {
		$query = "SELECT SUM(amount) FROM budget
					WHERE invoice_id = '$invoice_id'";
		// Execute the query
		$amount = $this -> itemQuery ($query);
		return ($amount);
	}

	function returnVendorId ($id) {
		return ($this -> returnItem ($id, 'vendor_id'));
	}
}

?>
