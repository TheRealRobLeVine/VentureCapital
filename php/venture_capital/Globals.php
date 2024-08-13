<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class Globals {
	
	const NUMBER_FORMAT_TYPE_INTEGER = "TYPE_INTEGER";
	const NUMBER_FORMAT_TYPE_SALES_INTEGER = "TYPE_SALES_INTEGER";
	const NUMBER_FORMAT_TYPE_PRICE_FRACTION = "TYPE_PRICE_FRACTION";
	const NUMBER_FORMAT_TYPE_PRICE_FRACTION_3 = "TYPE_PRICE_FRACTION_3"; // three digits after the decimal point
	const NUMBER_FORMAT_TYPE_PRICE_DECIMAL_DIGITS = "TYPE_PRICE";
	const NUMBER_FORMAT_TYPE_PERCENT_INTEGER = "TYPE_PERCENT_INTEGER";
	const NUMBER_FORMAT_TYPE_PERCENT_DECIMAL = "TYPE_PERCENT_DECIMAL";
	
	public static function get_value_from_metas_by_id($metas, $field_id) {
		return isset($metas[$field_id]) ? $metas[$field_id] : null;
	}

	public static function get_value_from_metas_by_key($metas, $field_key) {
		return isset($metas[$field_key]) ? $metas[$field_key] : null;
	}
	
	public static function numberFormat($number, $formatType, $addSymbol=false) {
		// TODO - get the currency symbol from the locale (or whatever)
		switch ($formatType) {
			case self::NUMBER_FORMAT_TYPE_SALES_INTEGER:
				$formattedNumber = number_format($number);
/*				if (0 == $formattedNumber) {
					$formattedNumber = '-';
				}*/
				if ($formattedNumber < 0) {
					$formattedNumber = "(" . substr($formattedNumber, 1) . ")";
				}
				return (($addSymbol) ? "$" : "" ) . $formattedNumber;
			case self::NUMBER_FORMAT_TYPE_PRICE_FRACTION:
				$formattedNumber = number_format($number, 2);
				return (($addSymbol) ? "$" : "" ) . $formattedNumber;
			case self::NUMBER_FORMAT_TYPE_PRICE_FRACTION_3:
				$formattedNumber = number_format($number, 3);
				return (($addSymbol) ? "$" : "" ) . $formattedNumber;
			case self::NUMBER_FORMAT_TYPE_PERCENT_INTEGER:
				$formattedNumber = number_format($number);
				return $formattedNumber . (($addSymbol) ? "%" : "" );
			case self::NUMBER_FORMAT_TYPE_PERCENT_DECIMAL:
				$formattedNumber = number_format($number, 1);
				return $formattedNumber . (($addSymbol) ? "%" : "" );
			case self::NUMBER_FORMAT_TYPE_INTEGER:
				$formattedNumber = number_format($number);
				if ($formattedNumber < 0) {
					$formattedNumber = "(" . substr($formattedNumber, 1) . ")";
				}
				return $formattedNumber;
			default:
				return number_format($number);
		}
	}
}
?>