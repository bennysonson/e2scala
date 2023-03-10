<?php
/**
 * Functions for parameter handling
 * extensions-leaflet-map
 */
// Direktzugriff auf diese Datei verhindern:
defined( 'ABSPATH' ) or die();

//Interpretiere !parameter und parameter als false und true
function leafext_clear_params($atts) {
	if (is_array($atts)) {
		for ($i = 0; $i < count($atts); $i++) {
			if (isset($atts[$i])) {
				if ( strpos($atts[$i],"!") === false ) {
					$atts[$atts[$i]] = true;
				} else {
					$atts[substr($atts[$i],1)] = false;
				}
				unset($atts[$i]);
			}
		}
	}
	return($atts);
}

//shortcode_atts gibt nur Kleinbuchstaben zurueck, Javascript braucht aber gross und klein
//Parameter: array mit keys wie es sein soll, array mit keys in klein von shortcode_atts
function leafext_case ($params,$array) {
	foreach ($params as $param) {
		if (strtolower($param) != $param) {
			if (isset($array[strtolower($param)])) {
				$array[$param] = $array[strtolower($param)];
				unset($array[strtolower($param)]);
			}
		}
	}
	return $array;
}

//Suche bestimmten Wert in array im admin interface
function leafext_array_find($needle, $haystack) {
	foreach ($haystack as $item) {
		if ($item[0] == $needle) {
			return $item;
			break;
		}
	}
}
//Suche bestimmten Wert in array im admin interface
function leafext_array_find2($needle, $haystack) {
	foreach ($haystack as $item) {
		if ($item['param'] == $needle) {
			return $item;
			break;
		}
	}
}

//Trage php array keys und values in javascript script ein.
function leafext_java_params ($params) {
	///var_dump($params); wp_die();
	$text = "";
	foreach ($params as $k => $v) {
		//var_dump($v,gettype($v));
		$text = $text. "$k: ";
		switch (gettype($v)) {
			case "string":
				switch ($v) {
					case "false":
					case "0": $value = "false"; break;
					case "true":
					case "1": $value = "true"; break;
					default:
						if (is_numeric($v)) {
							$value = $v;
						} else {
							$value = '"'.$v.'"';
						}
					break;
				}
				break;
			case "boolean":
				$value = $v ? "true" : "false"; break;
			case "integer":
				switch ($v) {
					//case 0: $value = "false"; break;
					//case 1: $value = "true"; break;
					default: $value = $v; break;
				}
				break;
			default: var_dump($k, $v, gettype($v)); wp_die("Type");
		}
		$text = $text.$value;
		$text = $text.",\n";
	}
	//var_dump($text); wp_die();
	return $text;
}

/**
 * This function replaces the keys of an associate array by those supplied in the keys array
 *
 * @param $array target associative array in which the keys are intended to be replaced
 * @param $keys associate array where search key => replace by key, for replacing respective keys
 * @return  array with replaced keys
 * from https://www.php.net/manual/de/function.array-replace.php
*/
function leafext_array_replace_keys($array, $keys) {
	foreach ($keys as $search => $replace) {
		if ( isset($array[$search])) {
			$array[$replace] = $array[$search];
			unset($array[$search]);
		}
	}
	return $array;
}
