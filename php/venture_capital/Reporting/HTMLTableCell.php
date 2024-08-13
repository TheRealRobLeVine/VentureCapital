<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class HTMLTableCell {

	const BLANK_CELL =  "<td class='~CLASS~'>&nbsp;</td>";
	const CELL_START =  "<td class='~CLASS~' colspan='~COLSPAN~'>";
	const CELL_END = "</td>";
	const HEADER_CELL_START = "<th data-orderable='false' class='~CLASS~' colspan='~COLSPAN~'>";  // data-orderable turns off sorting & 2024-03-21 removed align='center' 
	const HEADER_CELL_END = "</th>";
	
	private $html;
	
	public function __construct($data, $isHeader=false, $classes=null, $colspan=null) {
		$cellStart = null;
		$classList = "cellAlignRight";
		if (!empty($classes)) {
			$classList = implode(" ", $classes);
		}
		$cellStart = str_replace('~CLASS~', $classList, ($isHeader) ? self::HEADER_CELL_START : self::CELL_START);
		
		if (!empty($colspan)) {
			$cellStart = str_replace('~COLSPAN~', $colspan, $cellStart);
		}
		else {
			$cellStart = str_replace("colspan='~COLSPAN~'", "", $cellStart);
		}
		$cellEnd = ($isHeader) ? self::HEADER_CELL_END : self::CELL_END;
		$this->html = $cellStart . $data . $cellEnd;
	}
	
	public function getHTML() {
		return $this->html;
	}
	
	public static function getBlankCell($classes=null) {  // datatables doesn't support colspan in the the table body
		$contents = self::BLANK_CELL;
		$classList = null;
		if (!empty($classes)) {
			$classList = implode(" ", $classes);
		}
		$contents = str_replace('~CLASS~', $classList, $contents);
		
		return $contents;
	}
	
	public static function alignCurrencyLeftAndValueRight($value) {
		return "<td><div style='float: left; text-align: left'>$</div><div style='float: right; text-align: right'>$value</div></td>";
	}
}
	
?>