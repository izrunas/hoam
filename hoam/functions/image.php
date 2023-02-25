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

function HOAM_imageDisplay ($image, $alt = " ", $height = NULL, $width = NULL, $check_is_valid = TRUE) {
	// Returns a string that contains either a valid image tag or an error message
	if ($check_is_valid) {
		if (is_file (HOAM_PATH . $image)) {
			if (!exif_imagetype (HOAM_PATH . $image)) {
				$returnValue = "ERROR: Specified file is not a valid image.";
			}
		} else {
			$returnValue = '<small class="warning">ERROR: Specified Image Not Found.</small>';
		}
		if (isset ($returnValue)) {
		    return $returnValue;
		}
	}
	if ($height == NULL) {
		$size = getimagesize(HOAM_PATH . $image);
		$height = $size[1];
		$width = $size[0];
	}
	$returnValue  = "<div class=\"img-shadow\">\n";
	$returnValue .= "<img src=\"" . $image . "\" height=\"" . $height . "\" width=\"" . $width . "\" alt=\"" . $alt . "\" />\n";
	$returnValue .= "</div>\n";
	return $returnValue;
}

function HOAM_barchart ($value_list, $cx, $cy, $options = NULL) {	// Parse any options received, and use default values otherwise.
	if (!isset ($options['colors'])) {
		$options['colors'] = array ('red', 'orange', 'yellow', 'lime', 'green', 'aqua', 'blue', 'navy', 'indigo', 'violet', 'pink', 'silver', 'gray', 'black');
	}
	if (!isset ($options['divider_color'])) {
		$options['divider_color'] = 'black';
	}
	if (!isset ($options['fontsize'])) {
		$options['fontsize'] = 20;
	}
	if (!isset ($options['height'])) {
		$options['height'] = 400;
	}
	if (!isset ($options['margin'])) {
		$options['margin'] = 20;
	}
	if (!isset ($options['width'])) {
		$options['width'] = 1000;
	}

		header ('Content-type: image/svg+xml');
		$chartelem = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
		$chartelem .= '<svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" version="1.0" width="' . $options['width'] . '" height="' . $options['height'] . '">';

		$sum = 0;
		foreach ($value_list as $key => $val) {
			$sum += $val;
		}

		// Loop through the slices
		$max = count ($value_list);
		if ($max > 1) {
			$next_label = 0;
			for ($i = 0; $i < $max; $i++) {
				$vals = [key ($value_list), current ($value_list)];
				next ($value_list);
			}
		}
}

function HOAM_piechart ($value_list, $cx, $cy, $radius, $options = NULL) {
	// http://brankocollin.nl/sites/default/files/uploads/piechart.php.txt
	// Originally by Branko Collin in 2008 (highly modified afterward)
	// This code is hereby released into the public domain. In case this is not
	// legally possible: I, Branko Collin, hereby grant anyone the right to use
	// this work for any purpose, without any conditions, unless such conditions
	// are required by law. 
	
	// Arguments are an aray of values, the centre coordinates x and y, and 
	// the radius of the piechart.	

	// Parse any options received, and use default values otherwise.
	if (!isset ($options['colors'])) {
		$options['colors'] = array ('red', 'orange', 'yellow', 'lime', 'green', 'aqua', 'blue', 'navy', 'indigo', 'violet', 'pink', 'silver', 'gray', 'black');
	}
	if (!isset ($options['divider_color'])) {
		$options['divider_color'] = 'black';
	}
	if (!isset ($options['fontsize'])) {
		$options['fontsize'] = 20;
	}
	if (!isset ($options['height'])) {
		$options['height'] = 400;
	}
	if (!isset ($options['width'])) {
		$options['width'] = 1000;
	}

	if ($radius < 5) {
		$chartelem = $_HOAM_language ['errors']['image']['piechart_radius'];
	} else {
		header ('Content-type: image/svg+xml');
		$chartelem = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
		$chartelem .= '<svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" version="1.0" width="' . $options['width'] . '" height="' . $options['height'] . '">';

		$sum = 0;
		foreach ($value_list as $key => $val) {
			$sum += $val;
		}

		$deg = $sum / 360; // one degree
		$jung = $sum / 2; // necessary to test for arc type

		// Data for grid, circle, and slices
	
		$dx = $radius; // Starting point: 
		$dy = 0; // first slice starts in the East
		$oldangle = 0;
	
		// Loop through the slices
		$max = count ($value_list);
		if ($max > 1) {
			$next_label = 0;
			for ($i = 0; $i < $max; $i++) {
				$vals = [key ($value_list), current ($value_list)];
				next ($value_list);
				$angle = $oldangle + $vals[1] / $deg; // cumulative angle
				$x = cos (deg2rad ($angle)) * $radius; // x of arc's end point
				$y = sin (deg2rad ($angle)) * $radius; // y of arc's end point
	
				$color = $options['colors'][$i];

				if ($vals[1] > $jung) {
					// arc spans more than 180 degrees
					$laf = 1;
				} else {
					$laf = 0;
				}
	
				$ax = $cx + $x; // absolute $x
				$ay = $cy + $y; // absolute $y
				$adx = $cx + $dx; // absolute $dx
				$ady = $cy + $dy; // absolute $dy
				$chartelem .= "\n";
				$chartelem .= "<path d=\"M$cx,$cy "; // move cursor to center
				$chartelem .= "L$adx,$ady "; // draw line away away from cursor
				$chartelem .= "A$radius,$radius 0 $laf,1 $ax,$ay "; // draw arc
				$chartelem .= "z\" "; // z = close path
				$chartelem .= "fill=\"$color\" stroke=\"" . $options['divider_color'] . "\" stroke-width=\"2\" ";
				$chartelem .= "fill-opacity=\"0.6\" stroke-linejoin=\"round\" />";
				$dx = $x; // old end points become new starting point
				$dy = $y; // id.
				$oldangle = $angle;
	
				// Print description box

				$next_label += 20;
				$chartelem .= '<rect x="' . (($cx * 2) + 30) . '" y="' . $next_label . '" width="20" height="20" fill="' . $color . '" fill-opacity="0.6" stroke="gray" stroke-width="2" />';
				$chartelem .= '<text x="' . (($cx * 2) + 60) . '" y="' . ($next_label + 20) . '" font-family="Verdana" font-size="' . $options['fontsize'] . '" fill="black" >' . $vals[0] . '</text>';
				$next_label += 10;
			}
			$chartelem .= "\n</svg>\n";
		} else {
			$next_label = 0;
			$vals = [key ($value_list), current ($value_list)];
			next ($value_list);
			$angle = 359.999; // cumulative angle
			$x = cos (deg2rad ($angle)) * $radius; // x of arc's end point
			$y = sin (deg2rad ($angle)) * $radius; // y of arc's end point
	
			$color = $options['colors'][0];
			
			$ax = $cx + $x; // absolute $x
			$ay = $cy + $y; // absolute $y
			$adx = $cx + $dx; // absolute $dx
			$ady = $cy + $dy; // absolute $dy
			$chartelem .= "\n";
			$chartelem .= "<path d=\"M$cx,$cy "; // move cursor to center
			$chartelem .= "L$adx,$ady "; // draw line away away from cursor
			$chartelem .= "A$radius,$radius 0 1,1 $ax,$ay "; // draw arc
			$chartelem .= "z\" "; // z = close path
			$chartelem .= "fill=\"$color\" stroke=\"" . $options['divider_color'] . "\" stroke-width=\"2\" ";
			$chartelem .= "fill-opacity=\"0.6\" stroke-linejoin=\"round\" />";
			$dx = $x; // old end points become new starting point
			$dy = $y; // id.
			$oldangle = $angle;
	
			// Print description box

			$next_label += 20;
			$chartelem .= '<rect x="' . (($cx * 2) + 30) . '" y="' . $next_label . '" width="20" height="20" fill="' . $color . '" fill-opacity="0.6" stroke="gray" stroke-width="2" />';
			$chartelem .= '<text x="' . (($cx * 2) + 60) . '" y="' . ($next_label + 20) . '" font-family="Verdana" font-size="' . $options['fontsize'] . '" fill="black" >' . $vals[0] . '</text>';
			$next_label += 10;
			$chartelem .= "\n</svg>\n";
		}
	}
	return ($chartelem);
}

function HOAM_randomImage ($directory, $type = "GIF JPEG JPG PNG SWF", $bgcolor = "white") {
	// Return a random image from an array fetched by imagelist()
	// Strip off any ending '/' from the path given
	if ( (mb_strlen ($directory) - 1) == (mb_strrpos ($directory, "/"))) {
		$directory = mb_substr ($directory, 0, (mb_strlen ($directory) - 1));
	}
	// Create a temporary directory name, including the full path.
	// This is needed for the opendir() function in imagelist()
	if ( ($PAGE_document_color_background == "white") || ($PAGE_document_color_background == "#ffffff")) {
		$tempdir = $directory . "/white/";
	} elseif ( ($PAGE_document_color_background == "black") || ($PAGE_document_color_background == "#000000")) {
		$tempdir = $directory . "/black/";
	} else {
		// If the background color isn't white or black, just get a transparent image;
		$tempdir = $directory . "/trans/";
	}
	$imagelist = imagelist (HOAM_PATH . $tempdir);
	$imagecount = sizeof ($imagelist);
	// If the array is empty, it means no images have been found.
	if (!$imagecount) return FALSE;
	$image = $imagelist[rand (0, ($imagecount - 1))];
	return $tempdir . $image;
}

function HOAM_imageList ($directory) {
	// Read all files matching $type in the specified directory
	// and return an array containing the names
	// If the path given is not a directory, fail
	if (!is_dir ($directory)) return FALSE;
	$imagelist = array ();
	$handle = opendir ($directory);
	if ($handle) {
		while ( ($filename = readdir ($handle)) !== FALSE) {
			// We only want to look at files.
			if (is_file ($directory . $filename)) {
				if (exif_imagetype ($directory . $filename)) {
					array_push ($imagelist, $filename);
				}
			}
		}
		closedir ($handle);
	}
	sort ($imagelist);
	return $imagelist;
}

function HOAM_hex2int ($hex) {
	// @param    $hex string        6-digit hexadecimal color
	// @return    array            3 elements 'r', 'g', & 'b' = int color values
	// @desc Converts a 6 digit hexadecimal number into an array of
	// 3 integer values ('r'  => red value, 'g'  => green, 'b'  => blue)
	return array ( 'r' => hexdec(mb_substr($hex, 0, 2)), // 1st pair of digits
	'g' => hexdec(mb_substr($hex, 2, 2)), // 2nd pair
	'b' => hexdec(mb_substr($hex, 4, 2))  // 3rd pair
	);
}

function HOAM_is_image ($mime_type) {
	// Is the mime_type an image? This may miss certain other types of 'images'
	// such as flash, svg, bmp, or others that don't have official mime types
	// ( http://www.iana.org/assignments/media-types/ ) defined.
	return (mb_substr (mb_strtolower ($mime_type), 0, 6) == 'image/');
}

?>
