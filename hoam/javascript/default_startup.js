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

/* Optional: Temporarily hide the "tabber" class so it does not "flash"
   on the page as plain HTML. After tabber runs, the class is changed
   to "tabberlive" and it will appear. */

document.write ('<style type="text/css">.tabber{display:none;}<\/style>');

/****************************************************************
*
* Add any event handlers
*
*****************************************************************/

$j(window).ready (HOAM_tableStripe);
$j(window).ready (function () {
	if ($j('#user_loginForm').length) {
		$j('#user_loginForm').bind ('submit', HOAM_userLoginCheck, true);
		$j('#user_loginName').focus (function () {
			if ($j(this).length && $j(this).val () == "user name") {
				$j(this).val ('');
				$j('#user_loginPassword').val ('');
			} else {
				$j(this).select();
			}
		}
		);
		$j('#user_loginPassword').focus (function () {
			this.select();
		});
	}
});
