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

// Please note, using preg_quote on the currency strings won't work because the
// decimal (.) also gets escaped.

/* The following also exist in the wiki class itself:

	// Process any attachments listed.
	$attachment_sub = '/\[\[attach:(.*?)\]\]/i';

	// Process any attachments listed.
	$wiki_tree_sub = '/\[\[tree:(.*?)\]\]/i';

	// Process any attachments listed.
	$wiki_url_sub = '/\[\[url:(.*?)\]\]/i';

	// Process any multicolor text
	$color_sub = "/%%%(.*?)%%%/sim";
*/

require_once (HOAM_PATH_FUNCTION . 'wiki.php');

$_HOAM_wiki_var = array (
	'/\{\{CURRENTDAY\}\}/i' => date ('j'),
	'/\{\{CURRENTDAY2\}\}/i' => date ('d'),// With leading zeros
	'/\{\{CURRENTDAY3\}\}/i' => date ('jS'),
	'/\{\{CURRENTDAYNAME\}\}/i' => date ('l'),
	'/\{\{CURRENTDOW\}\}/i' => date ('w'),
	'/\{\{CURRENTMONTH\}\}/i' => date ('n'),
	'/\{\{CURRENTMONTH2\}\}/i' => date ('m'),// With leading zeros
	'/\{\{CURRENTMONTHABBR\}\}/i' => date ('M'),
	'/\{\{CURRENTMONTHNAME\}\}/i' => date ('F'),
	'/\{\{CURRENTTIME\}\}/i' => date ('H:i'),
	'/\{\{CURRENTHOUR\}\}/i' => date ('H'),
	'/\{\{CURRENTMINUTE\}\}/i' => date ('i'),
	'/\{\{CURRENTWEEK\}\}/i' => date ('W'),
    '/\{\{CURRENTYEAR\}\}/i' => date ('Y'),
	'/\{\{CURRENTTIMESTAMP\}\}/i' => date ('c'),
	'/\{\{DATE\}\}/i' => date ($_HOAM_country ['date']['format_long']),
	'/\{\{HOAM\}\}/i' => '<span class="product_name"><a href="' . HOAM_URL . '" title="Link to HOAM Product Page">HOAM <img class="www" src="/hoam/images/external.png" alt="Link to External Site" /></a></span>',
	'/\{\{HOAM_DOC\}\}/i' => '<a href="' . HOAM_URL . 'documentation/" title="Link to HOAM Documentation">HOAM Documentation<img class="www" src="/hoam/images/external.png" alt="Link to External Site" /></a>',
	'/\{\{ORG_NAME\}\}/i' => $_HOAM_setting ['organization']['name'],
	'/\{\{WEBSITE_BLURB\}\}/i' => $_HOAM_setting ['website']['blurb'],
	'/\{\{WEBSITE_DOMAIN\}\}/i' => $_HOAM_setting ['website']['hostname'],
   	'/\{\{WEBSITE_URL\}\}/i' => $_HOAM_setting ['website']['url'],
	'/\{\{WEBSITE_USERS_ONLINE\}\}/i' => HOAM_wikiUsersOnline (),
);

if (defined ('HOAM_INSTALLING') AND !HOAM_INSTALLING) {
	require_once (HOAM_PATH_FUNCTION . 'lot.php');
	require_once (HOAM_PATH_FUNCTION . 'news.php');
	
	$installed_vars = array (
		'/\{\{BUDGET_ASSESSMENT_AMOUNT\}\}/i' => '\\' . HOAM_returnCurrency ($_HOAM_setting ['budget']['assessment']['amount']),
		'/\{\{BUDGET_ASSESSMENT_FREQUENCY\}\}/i' => ucfirst ($_HOAM_setting ['budget']['assessment']['frequency']),
		'/\{\{BUDGET_FEE_COLLECTION\}\}/i' => '\\' . HOAM_returnCurrency ($_HOAM_setting ['budget']['fee']['collection']),
		'/\{\{BUDGET_FEE_LATE\}\}/i' => '\\' . HOAM_returnCurrency ($_HOAM_setting ['budget']['fee']['late']),
		'/\{\{BUDGET_FEE_RETURNED\}\}/i' => '\\' . HOAM_returnCurrency ($_HOAM_setting ['budget']['fee']['returned']),
		'/\{\{BUDGET_INTEREST_RATE\}\}/i' => $_HOAM_setting ['budget']['interest_rate'] . '%',
		'/\{\{END_REPORT\}\}/i' => '<div style="padding-top: 4em; page-break-inside: avoid; text-align: center">- - - &nbsp; &nbsp; ' . $_HOAM_language ['field_names']['reports']['end'] . ' &nbsp; &nbsp; - - -</div>',
		'/\{\{LETTER_FOOTER\}\}/i' => $_HOAM_setting ['organization']['letter']['footer'],
		'/\{\{LETTER_HEAD\}\}/i' => $_HOAM_setting ['organization']['letter']['head'],
		'/\{\{LETTER_NOPRINT\}\}/i' => '<style type="text/css">@media print { #page_title, #page_footer { display: none; }}</style>',
		'/\{\{LETTER_SIGNATURE\}\}/i' => $_HOAM_setting ['organization']['letter']['signature'],
		'/\{\{NEWS_RECENT\}\}/i' => HOAM_newsRecent (),
		'/\{\{ORG_UNITS\}\}/i' => HOAM_lotReturnCountAll (),
		'/\{\{ORG_ADDRESS\}\}/i' => HOAM_organizationReturnMailingAddress (),
		'/\{\{ORG_CITY\}\}/i' => $_HOAM_setting ['organization']['physical_address']['city'],
		'/\{\{ORG_COUNTY\}\}/i' => $_HOAM_setting ['organization']['physical_address']['county'],
		'/\{\{ORG_DOC_REFINANCE\}\}/i' => '\\' . HOAM_returnCurrency ($_HOAM_setting ['organization']['fee']['refinance']),
		'/\{\{ORG_DOC_RESALE\}\}/i' => '\\' . HOAM_returnCurrency ($_HOAM_setting ['organization']['fee']['resale']),
		'/\{\{ORG_DOC_EXPEDITE\}\}/i' => '\\' . HOAM_returnCurrency ($_HOAM_setting ['organization']['fee']['expedite']),
		'/\{\{ORG_EMAIL_BOARD\}\}/i' => $_HOAM_setting ['organization']['email']['board'],
		'/\{\{ORG_EMAIL_OFFICERS\}\}/i' => $_HOAM_setting ['organization']['email']['officers'],
		'/\{\{ORG_EMAIL_MANAGEMENT\}\}/i' => $_HOAM_setting ['organization']['email']['management'],
		'/\{\{ORG_PROPERTY_NAME\}\}/i' => $_HOAM_setting ['organization']['property_name'],
		'/\{\{ORG_PHONE\}\}/i' => preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_HOAM_setting ['organization']['telephone'], 1),
		'/\{\{ORG_PHONE_FAX\}\}/i' => preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_HOAM_setting ['organization']['fax'], 1),
		'/\{\{ORG_STATE\}\}/i' => HOAM_organizationReturnStateName (),
		'/\{\{PAYPAL_EMAIL\}\}/i' => $_HOAM_setting ['budget']['paypal']['email'],
		'/\{\{PAYPAL_SURCHARGE_AMOUNT\}\}/i' => $_HOAM_setting ['budget']['paypal']['surcharge']['amount'],
		'/\{\{PAYPAL_SURCHARGE_PERCENT\}\}/i' => $_HOAM_setting ['budget']['paypal']['surcharge']['percent'],
	);
	
	$_HOAM_wiki_var = array_merge ($_HOAM_wiki_var, $installed_vars);
}

?>