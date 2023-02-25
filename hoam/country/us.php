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

$_HOAM_country = array ();

$_HOAM_country ['code'] = 'us';
// There are known to be issues with some systems; for example, on Debian-based
// systems, 'en_US' is not a valid locale - you need 'en_US.UTF-8' or
// 'en_US.ISO-8559-1'.
$_HOAM_country ['locale'] = 'en_US';

$_HOAM_country ['currency']['symbol'] = '$';
/* currency pre-post: 0=preceding, 1=post
 */
$_HOAM_country ['currency']['pre-post'] = '0';

/* date and time formats use standard PHP date strings
 */
$_HOAM_country ['date']['format_compare'] = 'Y/m/d';
$_HOAM_country ['date']['format_endofmonth'] = 'm/t/Y';
$_HOAM_country ['date']['format_long'] = 'l, F jS, Y';
$_HOAM_country ['date']['format_mysql'] = 'Y-m-d';
$_HOAM_country ['date']['format_reports'] = 'm/d/Y';
$_HOAM_country ['date']['format_short'] = 'n/j/Y';
$_HOAM_country ['date']['match'] = '/^\(?([2-9]\d{2})[^0-9]?\s?(\d{3})[^0-9]?\s?(\d{4})$/';

$_HOAM_country ['postalcode']['format'] = '$1-$2';
$_HOAM_country ['postalcode']['length'] = '9';
$_HOAM_country ['postalcode']['minlength'] = '5';
$_HOAM_country ['postalcode']['match'] = '/^(\d{5})(\d{4})$/';
$_HOAM_country ['postalcode']['match-js'] = '/^(\d{5})$|^(\d{5})\-?(\d{4})$/';
// The postalcode matching is used as shown below:
// echo preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $_HOAM_setting ['organization']['mailing_address']['postalcode'], 1);

$_HOAM_country ['month'][0]['abbr'] = 'Jan';
$_HOAM_country ['month'][0]['name'] = 'January';
$_HOAM_country ['month'][1]['abbr'] = 'Feb';
$_HOAM_country ['month'][1]['name'] = 'February';
$_HOAM_country ['month'][2]['abbr'] = 'Mar';
$_HOAM_country ['month'][2]['name'] = 'March';
$_HOAM_country ['month'][3]['abbr'] = 'Apr';
$_HOAM_country ['month'][3]['name'] = 'April';
$_HOAM_country ['month'][4]['abbr'] = 'May';
$_HOAM_country ['month'][4]['name'] = 'May';
$_HOAM_country ['month'][5]['abbr'] = 'Jun';
$_HOAM_country ['month'][5]['name'] = 'June';
$_HOAM_country ['month'][6]['abbr'] = 'Jul';
$_HOAM_country ['month'][6]['name'] = 'July';
$_HOAM_country ['month'][7]['abbr'] = 'Aug';
$_HOAM_country ['month'][7]['name'] = 'August';
$_HOAM_country ['month'][8]['abbr'] = 'Sep';
$_HOAM_country ['month'][8]['name'] = 'September';
$_HOAM_country ['month'][9]['abbr'] = 'Oct';
$_HOAM_country ['month'][9]['name'] = 'October';
$_HOAM_country ['month'][10]['abbr'] = 'Nov';
$_HOAM_country ['month'][10]['name'] = 'November';
$_HOAM_country ['month'][11]['abbr'] = 'Dec';
$_HOAM_country ['month'][11]['name'] = 'December';

$_HOAM_country ['state'][0]["value"] = "AL";
$_HOAM_country ['state'][0]["description"] = "Alabama";
$_HOAM_country ['state'][1]["value"] = "AK";
$_HOAM_country ['state'][1]["description"] = "Alaska";
$_HOAM_country ['state'][2]["value"] = "AZ";
$_HOAM_country ['state'][2]["description"] = "Arizona";
$_HOAM_country ['state'][3]["value"] = "AR";
$_HOAM_country ['state'][3]["description"] = "Arkansas";
$_HOAM_country ['state'][4]["value"] = "CA";
$_HOAM_country ['state'][4]["description"] = "California";
$_HOAM_country ['state'][5]["value"] = "CO";
$_HOAM_country ['state'][5]["description"] = "Colorado";
$_HOAM_country ['state'][6]["value"] = "CT";
$_HOAM_country ['state'][6]["description"] = "Connecticut";
$_HOAM_country ['state'][7]["value"] = "DE";
$_HOAM_country ['state'][7]["description"] = "Delaware";
$_HOAM_country ['state'][8]["value"] = "DC";
$_HOAM_country ['state'][8]["description"] = "District of Columbia";
$_HOAM_country ['state'][9]["value"] = "FL";
$_HOAM_country ['state'][9]["description"] = "Florida";
$_HOAM_country ['state'][10]["value"] = "GA";
$_HOAM_country ['state'][10]["description"] = "Georgia";
$_HOAM_country ['state'][11]["value"] = "HI";
$_HOAM_country ['state'][11]["description"] = "Hawaii";
$_HOAM_country ['state'][12]["value"] = "ID";
$_HOAM_country ['state'][12]["description"] = "Idaho";
$_HOAM_country ['state'][13]["value"] = "IL";
$_HOAM_country ['state'][13]["description"] = "Illinois";
$_HOAM_country ['state'][14]["value"] = "IA";
$_HOAM_country ['state'][14]["description"] = "Iowa";
$_HOAM_country ['state'][15]["value"] = "KS";
$_HOAM_country ['state'][15]["description"] = "Kansas";
$_HOAM_country ['state'][16]["value"] = "KY";
$_HOAM_country ['state'][16]["description"] = "Kentucky";
$_HOAM_country ['state'][17]["value"] = "LA";
$_HOAM_country ['state'][17]["description"] = "Louisiana";
$_HOAM_country ['state'][18]["value"] = "ME";
$_HOAM_country ['state'][18]["description"] = "Maine";
$_HOAM_country ['state'][19]["value"] = "MD";
$_HOAM_country ['state'][19]["description"] = "Maryland";
$_HOAM_country ['state'][20]["value"] = "MA";
$_HOAM_country ['state'][20]["description"] = "Massachusetts";
$_HOAM_country ['state'][21]["value"] = "MI";
$_HOAM_country ['state'][21]["description"] = "Michigan";
$_HOAM_country ['state'][22]["value"] = "MN";
$_HOAM_country ['state'][22]["description"] = "Minnesota";
$_HOAM_country ['state'][23]["value"] = "MS";
$_HOAM_country ['state'][23]["description"] = "Mississippi";
$_HOAM_country ['state'][24]["value"] = "MO";
$_HOAM_country ['state'][24]["description"] = "Missouri";
$_HOAM_country ['state'][25]["value"] = "MT";
$_HOAM_country ['state'][25]["description"] = "Montana";
$_HOAM_country ['state'][26]["value"] = "NE";
$_HOAM_country ['state'][26]["description"] = "Nebraska";
$_HOAM_country ['state'][27]["value"] = "NV";
$_HOAM_country ['state'][27]["description"] = "Nevada";
$_HOAM_country ['state'][28]["value"] = "NH";
$_HOAM_country ['state'][28]["description"] = "New Hampshire";
$_HOAM_country ['state'][29]["value"] = "NJ";
$_HOAM_country ['state'][29]["description"] = "New Jersey";
$_HOAM_country ['state'][30]["value"] = "NM";
$_HOAM_country ['state'][30]["description"] = "New Mexico";
$_HOAM_country ['state'][31]["value"] = "NY";
$_HOAM_country ['state'][31]["description"] = "New York";
$_HOAM_country ['state'][32]["value"] = "NC";
$_HOAM_country ['state'][32]["description"] = "North Carolina";
$_HOAM_country ['state'][33]["value"] = "ND";
$_HOAM_country ['state'][33]["description"] = "North Dakota";
$_HOAM_country ['state'][34]["value"] = "OH";
$_HOAM_country ['state'][34]["description"] = "Ohio";
$_HOAM_country ['state'][35]["value"] = "OK";
$_HOAM_country ['state'][35]["description"] = "Oklahoma";
$_HOAM_country ['state'][36]["value"] = "OR";
$_HOAM_country ['state'][36]["description"] = "Oregon";
$_HOAM_country ['state'][37]["value"] = "PA";
$_HOAM_country ['state'][37]["description"] = "Pennsylvania";
$_HOAM_country ['state'][38]["value"] = "RI";
$_HOAM_country ['state'][38]["description"] = "Rhode Island";
$_HOAM_country ['state'][39]["value"] = "SC";
$_HOAM_country ['state'][39]["description"] = "South Carolina";
$_HOAM_country ['state'][40]["value"] = "SD";
$_HOAM_country ['state'][40]["description"] = "South Dakota";
$_HOAM_country ['state'][41]["value"] = "TN";
$_HOAM_country ['state'][41]["description"] = "Tennessee";
$_HOAM_country ['state'][42]["value"] = "TX";
$_HOAM_country ['state'][42]["description"] = "Texas";
$_HOAM_country ['state'][43]["value"] = "UT";
$_HOAM_country ['state'][43]["description"] = "Utah";
$_HOAM_country ['state'][44]["value"] = "VT";
$_HOAM_country ['state'][44]["description"] = "Vermont";
$_HOAM_country ['state'][45]["value"] = "VA";
$_HOAM_country ['state'][45]["description"] = "Virginia";
$_HOAM_country ['state'][46]["value"] = "WA";
$_HOAM_country ['state'][46]["description"] = "Washington";
$_HOAM_country ['state'][47]["value"] = "WV";
$_HOAM_country ['state'][47]["description"] = "West Virginia";
$_HOAM_country ['state'][48]["value"] = "WI";
$_HOAM_country ['state'][48]["description"] = "Wisconsin";
$_HOAM_country ['state'][49]["value"] = "WY";
$_HOAM_country ['state'][49]["description"] = "Wyoming";

$_HOAM_country ['telephone']['format'] = '($1) $2-$3';
$_HOAM_country ['telephone']['length'] = '10';
$_HOAM_country ['telephone']['match'] = '/^([2-9]\d{2})(\d{3})(\d{4})$/';
$_HOAM_country ['telephone']['prefix'] = '01';
// The telephone matching is used as shown below:
// echo preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_HOAM_setting ['organization']['telephone'], 1);

/* date and time formats use standard PHP date strings
 */
$_HOAM_country ['time']['format_long'] = 'g:i:s A';
$_HOAM_country ['time']['format_mysql'] = 'H:i:s';
$_HOAM_country ['time']['format_short'] = 'H:i';

$_HOAM_country ['vendor']['federal_id']['format'] = '$1-$2';
$_HOAM_country ['vendor']['federal_id']['length'] = '9';
$_HOAM_country ['vendor']['federal_id']['minlength'] = '9';
$_HOAM_country ['vendor']['federal_id']['match'] = '/^(\d{2})(\d{7})$/';
$_HOAM_country ['vendor']['state_id']['format'] = '$1';
$_HOAM_country ['vendor']['state_id']['length'] = '11';
$_HOAM_country ['vendor']['state_id']['minlength'] = '11';
$_HOAM_country ['vendor']['state_id']['match'] = '/^(\d{11})$/';

?>
