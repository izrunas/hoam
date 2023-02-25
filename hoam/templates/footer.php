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

?>

<div id="page_footer">

<div class="donotprint">
<?php
	if (defined (HOAM_INSTALLING) AND !HOAM_INSTALLING) {
		global $_HOAM_setting;
		$ad = HOAM_returnAdvertisement ($_HOAM_setting ['advertising']['flags']['position4']);
		if (!empty ($ad)) {
			echo $ad;
		}
		unset ($ad);
	}
?>
[http:|Home] | [http:legal/privacy/|Privacy Policy] | [http:help/tree/|Site Map] | [http:help/|Help]
</div>

<div class="copyright">Content is copyright &copy; {{CURRENTYEAR}} {{ORG_NAME}}. Powered by {{HOAM}}.</div>
<?php if (HOAM_DEBUG) printf ('<small>Page rendered in %.3f sec.</small>', HOAM_timerStop ()); ?>
</div>
