<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( '\VentureCapital\HTMLCell' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Reporting/HTMLTableCell.php';
}
use VentureCapital\HTMLTableCell;

class HTMLTable {

	const TABLE_START = "<table id='~ID~' class='~CLASS~'>";
	const TABLE_END = "</table>";
	
	const TABLE_HEADER_START = "<thead>";
	const TABLE_HEADER_END = "</thead>";

	const TABLE_BODY_START = "<tbody>";
	const TABLE_BODY_END = "</tbody>";

	const TABLE_ROW_START = "<tr>";
	const TABLE_ROW_END = "</tr>";

	private $ID;
	private $caption;
	private $tableStart;
	private $tableEnd;
	private $tableBodyStart;
	private $tableBodyEnd;
    private $tableData;
	private $body;
    private $headerRow;
    private $cols;
	
	public function __construct($ID, $classes, $headerCells, $caption=null) {
		$this->ID = $ID;
		$this->tableStart = str_replace("~ID~", $ID, self::TABLE_START);
		if (!empty($classes)) {
			$classList = implode(" ", $classes);
			$this->tableStart = str_replace('~CLASS~', $classList, $this->tableStart );
		}
		else {
			$this->tableStart = str_replace('~CLASS~', "", $this->tableStart );
		}
		$this->caption = $caption;
		$this->tableData = array();
		$this->headerRow = self::TABLE_ROW_START;
		foreach ($headerCells as $header) {
			$headerCell = new HTMLTableCell($header->data, true, $header->classes, $header->colspan);
			$this->headerRow .= $headerCell->getHTML();
		}
		$this->headerRow .= self::TABLE_ROW_END;
		$this->cols = count ( $headerCells );
	}
	
    function addRow( $row ) {
		$row = self::TABLE_ROW_START . $row . self::TABLE_ROW_END;
        array_push($this->tableData, $row);
    }

    function addEmptyRowWithHeader( $header, $numExtraColumns ) {
		$row = self::TABLE_ROW_START . $header->data;
		for($i=0;$i<$numExtraColumns;$i++) {
			$row .= (new HTMLTableCell("", false, $header->classes, $header->colspan))->getHTML();
		}
		$row .= self::TABLE_ROW_END;
        array_push($this->tableData, $row);
    }

    function addEmptyRow(  $numColumns ) {
		$row = self::TABLE_ROW_START;
		for($i=0;$i<$numColumns;$i++) {
			$row .= (new HTMLTableCell("", false, "", ""))->getHTML();
		}
		$row .= self::TABLE_ROW_END;
        array_push($this->tableData, $row);
    }

    function output(){
		$html = $this->tableStart . self::TABLE_HEADER_START . $this->headerRow . self::TABLE_HEADER_END;
		if ($this->caption) {
			$html .= "<caption>" . $this->caption . "</caption>";
		}
		$html .= self::TABLE_BODY_START;
		
        foreach ( $this->tableData as $row ) {
             $html .= $row;
        }

		$html .= self::TABLE_BODY_END;
		$html .= self::TABLE_END;

		echo $html;
    }

}
	
?>