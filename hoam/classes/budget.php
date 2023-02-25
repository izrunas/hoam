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

class HOAM_budget extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're
	// given. This class does not perform any cleansing of data!

	function changeAccountId ($id, $value) {
		return ($this -> changeString ($id, 'account_id', $value));
	}
	
	function changeAmount ($id, $value) {
		return ($this -> changeFloat ($id, 'amount', $value));
	}
	
	function changeCategoryId ($id, $value) {
		return ($this -> changeString ($id, 'category_id', $value));
	}
	
	function changeCheckNumber ($id, $value) {
		return ($this -> changeString ($id, 'checknum', $value));
	}
	
	function changeCustomerId ($id, $value) {
		return ($this -> changeString ($id, 'customer_id', $value));
	}
	
	function changeDateDue ($id, $value = NULL) {
		if ($value == NULL) {
			$value = date ("Y-m-d");
		}
		return ($this -> changeString ($id, 'datedue', $value));
	}
	
	function changeDateLate ($id, $value = NULL) {
		if ($value == NULL) {
			$value = date ("Y-m-d");
		}
		return ($this -> changeString ($id, 'datelate', $value));
	}
	
	function changeDatePosted ($id, $value = NULL) {
		if ($value == NULL) {
			$value = date ("Y-m-d");
		}
		return ($this -> changeString ($id, 'dateposted', $value));
	}
	
	function changeDescription ($id, $value) {
		return ($this -> changeString ($id, 'description', $value));
	}
	
	function changeInterestRate ($id, $value) {
		return ($this -> changeFloat ($id, 'interest_rate', $value));
	}
	
	function changeInvoiceID ($id, $value) {
		return ($this -> changeString ($id, 'invoice_id', $value));
	}
	
	function changeMemo ($id, $value) {
		return ($this -> changeString ($id, 'memo', $value));
	}

	function changeParentEntryId ($id, $value) {
		return ($this -> changeString ($id, 'parent_entry_id', $value));
	}
	
	function changePosted ($id, $value) {
		return ($this -> changeString ($id, 'dateposted', $value));
	}
	
	function changeVendorId ($id, $value) {
		return ($this -> changeString ($id, 'vendor_id', $value));
	}

	function listAllChargesForCustomer ($customer_id, $order = 'ORDER BY datecreated ASC, datedue ASC') {
		// Return an array containing the id of all charges, grouped by parent /
		// child
		$query = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id'
					$order";
		return ($this -> listSimpleQuery ($query));
	}

	function listAllPaymentsForCharge ($entry_id) {
		// Return an array containing the id of all payments
		global $_HOAM_setting;
		
		$payment_flag = $_HOAM_setting ['budget']['flags']['payment'];
		$query_payments = "SELECT id FROM $this->table
					WHERE parent_entry_id = '$entry_id' AND
					flags & $payment_flag
					ORDER BY dateposted ASC";
		return ($this -> listSimpleQuery ($query_payments));
	}

	function listAllPaymentsForCustomer ($customer_id, $date = NULL) {
		// Return an array containing the id of all payments
		global $_HOAM_country, $_HOAM_setting;
		
		$payment_flag = $_HOAM_setting ['budget']['flags']['payment'];
		$query_payments = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id' AND
					flags & $payment_flag";
		if ($date) {
			$query_payments .= " AND dateposted >= '$date'";
		}
		$query_payments .= " ORDER BY dateposted ASC";
		return ($this -> listSimpleQuery ($query_payments));
	}

	function listAvailableCreditsforCustomer ($customer_id) {
		// Return an array containing the id of all payments
		global $_HOAM_setting;
		$credit_flag = $_HOAM_setting ['budget']['flags']['credit'];
		$query_credits = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id' AND
					flags & $credit_flag AND
					dateposted IS NULL
					ORDER BY datecreated ASC";
		return ($this -> listSimpleQuery ($query_credits));
	}

	function listChargesForCategoryRange ($category_id, $first_date, $second_date = NULL) {
		// Added ROUND to sum because (for whatever reason) MySQL is returning
		// insignificant amounts (eg 3.5762786865234e-06) when the sum of all
		// amounts is actually 0.00.
		if ($second_date == NULL) {
			$second_date = date ('Y-m-d');
		}
		$query = "SELECT id FROM $this->table
					WHERE category_id = '$category_id' AND amount > 0					
					AND datedue >= '$first_date'
					AND datedue <= '$second_date'";
		return ($this -> listSimpleQuery ($query));
	}

	function listChargesOnInvoice ($invoice_id, $vendor = NULL) {
		$query = "SELECT id FROM $this->table
					WHERE invoice_id = '$invoice_id'";
		if ($vendor != NULL) {
			$query .= " AND vendor_id = '$vendor'";
		}
		$query .= " ORDER BY datedue ASC, memo ASC, amount DESC";
		$list = $this -> listSimpleQuery ($query);
		return ($list);
	}

	function listChildrenWithFlag ($parent_entry_id, $flag) {
		$query = "SELECT id FROM $this->table
					WHERE parent_entry_id = '$parent_entry_id'
					AND flags & $flag
					ORDER BY datedue ASC";
		$list = $this -> listSimpleQuery ($query);
		return ($list);
	}

	function listCurrentChargesForCustomer ($customer_id, $parent_entry_id = NULL) {
		// Return an array containing the id of all charges, grouped by parent /
		// child
		if ($parent_entry_id) {
			$parent_string = " parent_entry_id ='$parent_entry_id' ";
			$date_string = '';
		} else {
			$parent_string = '';
			$date_string = " dateposted IS NULL ";
		}
		$query = "SELECT id FROM $this->table
					WHERE $parent_string $date_string
					AND customer_id = '$customer_id'
					ORDER BY datedue ASC, parent_entry_id, category_id, flags";
		return ($this -> listSimpleQuery ($query));
	}
	
	function listChargesForCustomerDateRange ($customer_id, $start_date = '1970-01-01', $end_date = NULL) {
		// Return an array containing the id of all charges, grouped by parent /
		// child
		global $_HOAM_country;
		if ($end_date == NULL) {
			$end_date = date ($_HOAM_country ['date']['format_mysql']);
		}
		$query = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id' AND
					datedue >= '$start_date' AND
					datedue <= '$end_date'
					ORDER BY datedue ASC, parent_entry_id, memo ASC";
		return ($this -> listSimpleQuery ($query));
	}
	
	function listCurrentChargesForCustomerWithFlags ($customer_id, $flags) {
		// Return an array containing the id of all charges, grouped by flag(s)
		$query = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id'
					AND flags & $flags
					AND dateposted IS NULL
					ORDER BY datedue ASC, parent_entry_id, category_id, vendor_id";
		return ($this -> listSimpleQuery ($query));
	}

	function listCurrentChargesForCustomerWithoutFlags ($customer_id, $flags) {
		// Return an array containing the id of all charges, grouped by flag(s)
		$query = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id'
					AND ~flags & $flags
					AND dateposted IS NULL
					ORDER BY datedue ASC, parent_entry_id, category_id, vendor_id";
		return ($this -> listSimpleQuery ($query));
	}

	function listCustomers () {
		$query = "SELECT DISTINCT(b.customer_id), lots.street, lots.address, lots.building, lots.suite FROM $this->table AS b, lots, homeowners 
					WHERE lots.id=homeowners.lot_id AND b.customer_id = homeowners.id 
					ORDER BY lots.street ASC, lots.address ASC, lots.building ASC, lots.suite ASC";
		$list = $this -> listSimpleQuery ($query);
		return ($list);
	}
	
	function listCustomersWithBalanceDue ($order = NULL) {
		if ($order == NULL) {
			$order = "lots.street ASC, lots.address ASC, lots.building ASC, lots.suite ASC";
		}
		// I'm too damn tired (and too dumn) to figure out how to do this as a
		// single query.
		$query = "SELECT DISTINCT b.customer_id FROM $this->table AS b, homeowners
					WHERE b.customer_id = homeowners.id";
		$list = $this -> listSimpleQuery ($query);
		if (is_array ($list)) {
			$due_list = array ();
			foreach ($list as $customer) {
				if ($this -> returnTotalAmountDue ($customer) > 0) {
					array_push ($due_list, $customer);
				}
			}
			return ($due_list);
		} else {
			return ($list);
		}
		
		return ($list);
	}
	
	function listCustomersWithOpenCharges ($order = NULL) {
		if ($order == NULL) {
			$order = "(SELECT SUM(amount) FROM $this->table WHERE customer_id = b.customer_id) DESC, lots.street ASC, lots.address ASC, lots.building ASC, lots.suite ASC";
		}
		$query = "SELECT DISTINCT b.customer_id, lots.street, lots.address, lots.building, lots.suite FROM $this->table AS b, lots, homeowners 
					WHERE b.dateposted IS NULL AND lots.id = homeowners.lot_id AND b.customer_id = homeowners.id 
					ORDER BY $order";
		$list = $this -> listSimpleQuery ($query);
		return ($list);
	}

	function returnAccountBalance ($customer_id, $datecreated = NULL) {
		if (!$datecreated) {
			$datecreated = date ('Y-m-d H:i:s');
		}
		$query = "SELECT SUM(amount) FROM $this->table
					WHERE customer_id = '$customer_id' 
					AND datecreated <= '$datecreated'";
		return ($this -> itemQuery ($query));
	}

	function returnAccountId ($id) {
		return ($this -> returnItem ($id, 'account_id'));
	}

	function returnAmount ($id) {
		return ($this -> returnItem ($id, 'amount'));
	}

	function returnAmountDueDate ($customer_id, $date) {
		$total_amount_due += $this -> returnAmount ($budget_id);
		$query = "SELECT SUM(amount) FROM $this->table WHERE customer_id='$customer_id' AND (dateposted IS NULL OR dateposted <= '$date') OR (customer_id='$customer_id' AND datecreated <= '$date' AND amount > 0)";
		return ($this -> itemQuery ($query));
	}

	function returnAmountDueItem ($budget_id) {
		$total_amount_due += $this -> returnAmount ($budget_id);
		$query_children = "SELECT id FROM $this->table
							WHERE parent_entry_id='$budget_id'";
		$child_list = $this -> listSimpleQuery ($query_children);
		foreach ($child_list as $child) {
			$total_amount_due += $this -> returnAmountDueItem ($child);
		}
		return ($total_amount_due);
	}

	function returnAmountDueParent ($budget_id) {
		$query = "SELECT amount FROM $this->table
					WHERE id = '$budget_id'";
		$query1 = "SELECT parent_entry_id FROM $this->table WHERE id = '$budget_id'";
		$query_children = "SELECT id FROM $this->table
					WHERE parent_entry_id='$budget_id'";
		$child_list = $this -> listSimpleQuery ($query_children);
		foreach ($child_list as $child) {
			$total_amount_due += $this -> returnAmountDueParent ($child);
		}
		$total_amount_due += $this -> itemQuery ($query);
		return ($total_amount_due);
	}

	function returnAmountDueRange ($customer_id, $first_date, $second_date = NULL) {
		if ($second_date == NULL) {
			$second_date = date ('Y-m-d');
		}
		$query = "SELECT SUM(amount) from $this->table
					WHERE customer_id = '$customer_id'
					AND datedue >= '$first_date'
					AND datedue <= '$second_date'";
		return ($this -> itemQuery ($query));
	}

	function returnAuthor ($id) {
		return ($this -> returnItem ($id, 'user_id'));
	}

	function returnCategoryAmountDueRange ($category_id, $first_date, $second_date = NULL) {
		if ($second_date == NULL) {
			$second_date = date ('Y-m-d');
		}
		$query = "SELECT SUM(amount) from $this->table
					WHERE category_id = '$category_id'
					AND datedue >= '$first_date'
					AND datedue <= '$second_date'";
		return ($this -> itemQuery ($query));
	}

	function returnCategoryId ($id) {
		return ($this -> returnItem ($id, 'category_id'));
	}

	function returnCheckNumber ($id) {
		return ($this -> returnItem ($id, 'checknum'));
	}

	function returnCountAccountId ($id) {
		return ($this -> returnCountQuery ("WHERE account_id = '$id'"));
	}
	
	function returnCountCategoryId ($id) {
		return ($this -> returnCountQuery ("WHERE category_id = '$id'"));
	}

	function returnCountVendorId ($id) {
		return ($this -> returnCountQuery ("WHERE vendor_id = '$id'"));
	}

	function returnCustomerId ($id) {
		return ($this -> returnItem ($id, 'customer_id'));
	}

	function returnDateDue ($id) {
		return ($this -> returnItem ($id, 'datedue'));
	}

	function returnDateLate ($id) {
		return ($this -> returnItem ($id, 'datelate'));
	}

	function returnDatePosted ($id) {
		return ($this -> returnItem ($id, 'dateposted'));
	}

	function returnDescription ($id) {
		return ($this -> returnItem ($id, 'description'));
	}

	function returnInterestRate ($id) {
		return ($this -> returnItem ($id, 'interest_rate'));
	}

	function returnInvoiceChargeAmount ($invoice_id, $vendor_id = NULL) {
		global $_HOAM_setting;
		
		$query = "SELECT SUM(amount) FROM $this->table
					WHERE invoice_id = '$invoice_id'";
		if ($vendor_id != NULL) {
			$query .= " AND vendor_id='$vendor_id'";
		}
		$query .= " AND ~flags ^ " . (int) $_HOAM_setting ['budget']['flags']['payment']; 
		return ($this -> itemQuery ($query));
	}

	function returnInvoiceId ($id) {
		return ($this -> returnItem ($id, 'invoice_id'));
	}
	
	function returnInvoicePaymentAmount ($invoice_id) {
		global $_HOAM_setting;
		
		$query = "SELECT SUM(amount) FROM $this->table
					WHERE invoice_id = '$invoice_id'
					AND flags & " . (int) $_HOAM_setting ['budget']['flags']['payment']; 
		return ($this -> itemQuery ($query));
	}
	
	function returnLastInvoiceVendor ($vendor_id) {
		global $_HOAM_setting;
		
		$query = "SELECT invoice_id FROM $this->table
					WHERE vendor_id = '$vendor_id' ORDER BY datedue DESC LIMIT 1";
		return ($this -> itemQuery ($query));
	}

	function returnLastPayment ($customer_id) {
		global $_HOAM_setting;
		$query = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id' AND amount < 0 AND dateposted IS NOT NULL AND flags & " . $_HOAM_setting ['budget']['flags']['payment'] . " ORDER BY dateposted DESC LIMIT 1";
		return ($this -> itemQuery ($query));
	}

	function returnLastPaymentInvoice ($invoice_id) {
		global $_HOAM_setting;
		$query = "SELECT id FROM $this->table
					WHERE invoice_id = '$invoice_id'
					AND flags & " . $_HOAM_setting ['budget']['flags']['payment'] .
					" ORDER BY dateposted DESC LIMIT 1";
		return ($this -> itemQuery ($query));
	}

	function returnLastPaymentVendor ($vendor_id) {
		global $_HOAM_setting;
		$query = "SELECT id FROM $this->table
					WHERE vendor_id = '$vendor_id' AND amount < 0 AND dateposted IS NOT NULL AND flags & " . $_HOAM_setting ['budget']['flags']['payment'] . " ORDER BY dateposted DESC LIMIT 1";
		return ($this -> itemQuery ($query));
	}

	function returnMemo ($id) {
		return ($this -> returnItem ($id, 'memo'));
	}

	function returnNewestCharge ($customer_id) {
		$query = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id' AND amount > 0 AND dateposted IS NULL ORDER BY datedue DESC LIMIT 1";
		return ($this -> itemQuery ($query));
	}

	function returnOldestCharge ($customer_id) {
		$query = "SELECT id FROM $this->table
					WHERE customer_id = '$customer_id' AND amount > 0 AND dateposted IS NULL ORDER BY datedue ASC LIMIT 1";
		return ($this -> itemQuery ($query));
	}

	function returnParentEntryId ($id) {
		return ($this -> returnItem ($id, 'parent_entry_id'));
	}

	function returnPosted ($id) {
		return ($this -> returnItem ($id, 'dateposted'));
	}

	function returnTotalAmountDue ($customer_id) {
		$query = "SELECT SUM(amount) FROM $this->table
					WHERE customer_id = '$customer_id'";
		$amount = $this -> itemQuery ($query);
		return ($amount);
	}

	function returnVendorId ($id) {
		return ($this -> returnItem ($id, 'vendor_id'));
	}

}

?>
