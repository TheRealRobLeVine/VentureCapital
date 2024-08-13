<?php

namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

use FrmEntry;
use FrmField;
use FrmForm;
if ( ! class_exists( '\VentureCapital\Globals' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Globals.php';
}
if ( ! class_exists( '\VentureCapital\Messages' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Messages.php';
}
if ( ! class_exists( '\VentureCapital\Channel' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Channel.php';
}
if ( ! class_exists( '\VentureCapital\Category' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Category.php';
}
if ( ! class_exists( '\VentureCapital\PriceAndMargin' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/PriceAndMargin.php';
}
if ( ! class_exists( '\VentureCapital\SalesForecast' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/SalesForecast.php';
}
if ( ! class_exists( '\VentureCapital\BaselineSales' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/BaselineSales.php';
}
if ( ! class_exists( '\VentureCapital\Expenses' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/Expenses.php';
}
if ( ! class_exists( '\VentureCapital\InventoryAndCapitalPurchases' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/InventoryAndCapitalPurchases.php';
}
if ( ! class_exists( '\VentureCapital\LoansAndInvestments' ) ) {
	require_once ABSPATH . 'wp-content/themes/salient-child/php/venturecapital/LoansAndInvestments.php';
}
use VentureCapital\Globals;
use VentureCapital\Messages;
use VentureCapital\Channel;
use VentureCapital\Category;
use VentureCapital\PriceAndMargin;
use VentureCapital\BaselineSales;
use VentureCapital\SalesForecast;
use VentureCapital\InventoryAndCapitalPurchases;
use VentureCapital\Expenses;
use VentureCapital\LoansAndInvestments;

class Plan {

	private $ID;
	private $length;
	private $startYear;
	private $years;
	private $growBy; // Grow by Channel or Category
	private $categories;
	private $channels;
	private $pricesAndMargins;
	private $averageBaselineSales;
	private $inventoryAndCapitalPurchases;
	private $loansAndInvestments;
	private $channelBaselineTotals;
	private $categoryBaselineTotals;
	private $totalInterestPaidByYear;
	private $totalInterestPaid;
	private $longTermLoanContributionsByYear;
	private $shortTermLoanContributionsByYear;
	private $totalLoanContributions;
	private $investmentsByYear;
	private $totalInvestments;
	private $capitalPurchasesByYear;
	private $totalCapitalPurchases;
	
	private $totalSalesByCategoryAndYear; // array of totals per year (0=baseline), across each category
	private $totalSalesByChannelAndYear; // array of totals per year (0=baseline), across each channel

    private $totalSalesByChannel; // array total across all years for a single channel
	private $totalUnitsByChannel; // array total across all years for a single channel
    private $totalSalesByCategory; // array total across all years for a single category
	private $totalUnitsByCategory; // array total across all years for a single category
	
	private $forecast; // an array indexed by year and within that year there's a sales object for each channel/category combination

	private $operatingProfitByYear;
	private $totalOperatingProfit;
	private $pretaxIncomeByYear;
	private $totalPretaxIncome;
	private $totalNetProfitAfterTaxes;
	private $netProfitAfterTaxesByYear;
	private $totalTaxesPayable;
	private $taxesPayableByYear;
	private $carryoverLossByYear;
	
	public function __construct($ID, $startYear, $length, $userID) {
		global $wpdb;
		
		$this->ID = $ID;
		$this->length = $length;
		$this->startYear = $startYear;
		$this->years[0] = 0; // baseline year
		$iIndex = 1;
		for($i=$startYear;$i<($startYear+$length);$i++) {
			$this->years[$iIndex++] = $i;
		}
		
		// load channels
		$allChannelEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(Channel::VC_CHANNEL_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->channels = array();
		foreach($allChannelEntries as $channel) {
			$entry = FrmEntry::getOne($channel->id, true);
			$chan = new Channel($entry, $this, $userID);
			$this->channels[] = $chan;
		}

		// load categories
		$allCategoryEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(Category::VC_CATEGORY_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->categories = array();
		foreach($allCategoryEntries as $category) {
			$entry = FrmEntry::getOne($category->id, true);
			$cat = new Category($entry, $this, $userID);
			$this->categories[] = $cat;
		}

		// load prices and margins
		$allPriceAndMarginEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(PriceAndMargin::VC_PRICE_AND_MARGIN_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->pricesAndMargins = array();
		foreach($allPriceAndMarginEntries as $row) {
			$entry = FrmEntry::getOne($row->id, true);
			$this->pricesAndMargins[] = new PriceAndMargin($entry);
		}

		// load the "Growth By" (Either Sales Channel or Product Category) 
		$formID = FrmForm::get_id_by_key(SalesForecast::VC_SALES_FORECAST_FORM_KEY);
		$sql = "SELECT id FROM {$wpdb->prefix}frm_items WHERE form_id = $formID AND user_id = $userID";
		$entryID = $wpdb->get_var($sql);
		$salesForecastEntry = FrmEntry::getOne($entryID, true);
		$this->growBy = Globals::get_value_from_metas_by_key($salesForecastEntry->metas, SalesForecast::VC_GROW_BY_FIELD_KEY);
		
		// load baseline sales
		$averageBaselineEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(BaselineSales::VC_AVERAGE_BASELINE_CHILD_FORM_KEY), 'it.user_id' => $userID), '', '', false);
		$this->averageBaselineSales = array();
		foreach($averageBaselineEntries as $row) {
			$entry = FrmEntry::getOne($row->id, true);
			$this->averageBaselineSales[] = new BaselineSales($entry);
		}
		$this->forecast = new SalesForecast();
		self::_setBaselineGrowthRates(); // sets the growth for each of the years following the baseline
		self::_pushBaselineSalesIntoForecastAndGrow(); // grow from the baseline to the full length of the plan
		$this->forecast->setTotals($this);
		
		// Inventory & Capital Purchases
		$formID = FrmForm::get_id_by_key(InventoryAndCapitalPurchases::VC_INVENTORY_CAPITAL_PURCHASES_FORM_KEY);
		$sql = "SELECT id FROM {$wpdb->prefix}frm_items WHERE form_id = $formID AND user_id = $userID";
		$entryID = $wpdb->get_var($sql);
		$entry = FrmEntry::getOne($entryID, true);
		$this->inventoryAndCapitalPurchases = new InventoryAndCapitalPurchases($entry, $this->getYears(false), $userID);

		// Loans and Investments
		$formID = FrmForm::get_id_by_key(LoansAndInvestments::VC_LOANS_INVESTMENTS_FORM_KEY);
		$sql = "SELECT id FROM {$wpdb->prefix}frm_items WHERE form_id = $formID AND user_id = $userID";
		$entryID = $wpdb->get_var($sql);
		$entry = FrmEntry::getOne($entryID, true);
		$this->loansAndInvestments = new LoansAndInvestments($entry, $this->getYears(false), $userID);
		$this->_setLoanData();
		
		// Expenses
		$this->expenses = new Expenses($userID, $this);
		
		$this->_setOperatingProfit();
		$this->_setPretaxIncome();
		$this->_setCarryoverLoss();
		$this->_setTaxesPayable();
		$this->_setNetProfitAfterTaxes();
		$this->_setInvestments();
		$this->_setInventoryAndCapitalPurchases();
	}

	public function getID() {
		return $this->ID;
	}

	public function getLength() {
		return $this->length;
	}
	
	public function getStartYear() {
		return $this->startYear;
	}
	
	public function getYears($includeBaseline=true) {
		if ($includeBaseline) {
			return $this->years;
		}
		$years = $this->years;
		unset($years[0]);
		return $years;
	}
	
	public function getGrowBy() {
		return $this->growBy;
	}
	
	public function isGrowByChannel() {
		return ($this->growBy == Messages::getMessage('VC_MESSAGE_SALES_CHANNEL'));
	}

	public function getChannels() {
		return $this->channels;
	}
	
	public function getCategories() {
		return $this->categories;
	}

	public function getPricesAndMargins() {
		return $this->pricesAndMargins;
	}
	
	public function getPriceAndMarginByCategoryAndChannel($categoryID, $channelID) {
		foreach($this->pricesAndMargins as $priceAndMargin) {
			if ($categoryID == $priceAndMargin->getCategoryID() && $channelID == $priceAndMargin->getChannelID()) {
				return $priceAndMargin;
			}
		}
	}
	
	public function getAverageBaselineSales() {
		return $this->averageBaselineSales;
	}
	
	public function getAverageBaselineSalesByChannelAndCategory($channelID, $categoryID) {
		foreach ($this->averageBaselineSales as $sales) {
			if ($sales->getChannelID() == $channelID && $sales->getCategoryID() == $categoryID) {
				return $sales;
			}
		}
		return null;
	}
	
	public function getInventoryAndCapitalPurchases() {
		return $this->inventoryAndCapitalPurchases;
	}

	/*
	 * Function: setBaselineTotals
	 *		Sets the baseline totals across channels or categories
	 *     Used for reporting
	 *
	 *      Verified 2024-01-07
	 */
	private function setBaselineTotals() {
		$baselineTotals = array();

		$baselineSales = $this->getAverageBaselineSales();

		// By Channel
		foreach($this->getChannels() as $channel) {
			$channelID = $channel->getID();
			$baselineTotalsByChannel = new BaselineTotals($channel->getID());
			foreach($baselineSales as $sales) {
					if ($sales->getChannelID() == $channelID) {
						$unitPrice = $sales->getUnitPrice();
						$baselineTotalsByChannel->addToTotalUnits($sales->getUnits());
						$baselineTotalsByChannel->addToTotalSales($sales->getUnits()*$unitPrice);
					}
			}
			$baselineTotals[$channelID] = $baselineTotalsByChannel;
		}
		$this->channelBaselineTotals = $baselineTotals;
		
		// By category
		$baselineTotals = array();
		foreach($this->getCategories() as $category) {
			$catID = $category->getID();
			$baselineTotalsByCategory = new BaselineTotals($category->getID());
			foreach($baselineSales as $sales) {
					if ($sales->getCategoryID() == $catID) {
						$unitPrice = $sales->getUnitPrice();
						$baselineTotalsByCategory->addToTotalUnits($sales->getUnits());
						$baselineTotalsByCategory->addToTotalSales($sales->getUnits()*$unitPrice);
					}
			}
			$baselineTotals[$catID] = $baselineTotalsByCategory;
		}
		$this->categoryBaselineTotals = $baselineTotals;
	}
	
	/*
	 * Function: _setBaselineGrowthRates
	 *		Sets the growth rate for each category/channel combination
	 *     Used for reporting
	 */
	private function _setBaselineGrowthRates() {
		$baselineSales = $this->averageBaselineSales;
		foreach($baselineSales as $singleBaselineSales) {
			$catID = $singleBaselineSales->getCategoryID();
			$chanID = $singleBaselineSales->getChannelID();
			if ($this->isGrowByChannel()){
				foreach($this->getChannels() as $channel) {
					if ($channel->getID() == $chanID) {
						$growthRates = $channel->getGrowthRates();
						break;
					}
				}
			}
			else {
				foreach($this->getCategories() as $cat) {
					if ($cat->getID() == $catID) {
						$growthRates = $cat->getGrowthRates();
						break;
					}
				}
			}
			$singleBaselineSales->setGrowthRates($growthRates);
		}
	}
	
	/*
	 * Function: _setTotalSalesByCategory
	 *		For each category, calculates the total across all years
	 *     Used for reporting
	 * 2024-01-08
	 */
	private function _setTotalSalesByCategory() {
		foreach($this->categories as $category) {
			$catID = $category->getID();
			$totalUnits = 0;
			$totalSales = 0;
			foreach($this->getYears() as $year) {
				$salesObj = $category->getSalesBySingleYear($year);
				$totalUnits += $salesObj->getUnits();
				$totalSales += $salesObj->getSales();
			}
			$this->totalSalesByCategory[$catID] = $totalSales;
			$this->totalUnitsByCategory[$catID] = $totalUnits;
		}
	}
	
	/*
	 * Function: _setTotalSalesByChannel
	 *		For each category, calculates the total across all years
	 *     Used for reporting
	 * 2024-01-08
	 */
	private function _setTotalSalesByChannel() {
		foreach($this->channel as $channel) {
			$chanID = $channel->getID();
			$totalUnits = 0;
			$totalSales = 0;
			foreach($this->getYears() as $year) {
				$salesObj = $channel->getSalesBySingleYear($year);
				$totalUnits += $salesObj->getUnits();
				$totalSales += $salesObj->getSales();
			}
			$this->totalSalesByChannel[$chanID] = $totalSales;
			$this->totalUnitsByChannel[$chanID] = $totalUnits;
		}
	}

	/*
	 * Function: _pushBaselineSalesIntoForecastAndGrow
	 *		Pushes the baseline sales into the 0th year elements for each category/channel and grow out the numbers into each successive year
	 *     Used for reporting
	 * 2024-01-08
	 */
	private function _pushBaselineSalesIntoForecastAndGrow() {
		foreach($this->getYears() as $year) {
			if ($year == 0) {
				$this->forecast->setSalesByYear($this->averageBaselineSales, $year);
			}
			else {
				$yearCount = $year - $this->getStartYear() + 1;
				$grownSales = array(); // will hold the grown baseline sales
				foreach($this->averageBaselineSales as $baselineSales) {
					$salesObj = $baselineSales;
					$sales = $salesObj->getSales();
					$units = $salesObj->getUnits();
					$unitPrice = $salesObj->getUnitPrice();
					$newSalesObj = new Sales($salesObj->getChannelID(), $salesObj->getCategoryID(), 0, 0, 0);
					$growthRate = $baselineSales->getGrowthRateByYear($this->getStartYear());
					for($i=1;$i<=$yearCount;$i++) {
						$units *= (1+($growthRate/100));
						$sales *= (1+($growthRate/100));
						if (($i+1)<=$yearCount) {
							$growthRate = $baselineSales->getGrowthRateByYear($this->getStartYear()+$i);
						}
					}
					$newSalesObj->setUnits($units);
					$newSalesObj->setUnitPrice($unitPrice);
					$newSalesObj->setSales($sales);
					$grownSales[] = $newSalesObj;
				}
				$this->forecast->setSalesByYear($grownSales, $year);
			}
		}
	}
	
	public function getSalesByChannelAndCategory($channelID, $categoryID) {
		foreach($this->sales as $sales) {
			if ($channelID == $sales->getChannelID() && $categoryID == $sales->getCategoryID()) {
				return $sales;
			}
		}
	}
	
	public function getChannelByID($ID) {
		foreach($this->channels as $channel) {
			if ($ID == $channel->getID()) {
				return $channel;
			}
		}
	}
	
	public function getCategoryByID($ID) {
		foreach($this->categories as $category) {
			if ($ID == $category->getID()) {
				return $category;
			}
		}
	}

	public function getChannelBaselineTotals() {
		return $this->channelBaselineTotals;
	}

	public function getCategoryBaselineTotals() {
		return $this->categoryBaselineTotals;
	}

	public function getExpenses() {
		return $this->expenses;
	}
	
	public function getForecast() {
		return $this->forecast;
	}
	
	private function _setLoanData() {
		$this->totalInterestPaid = 0;
		$this->totalLoanContributions = 0;
		
		foreach($this->getYears(false) as $year) {
			$this->interestPaidByYear[$year] = 0;
			$this->longTermLoanContributionsByYear[$year] = 0;
			$this->shortTermLoanContributionsByYear[$year] = 0;
			//$this->yearEndClosingOrBalanceByYear[$year] = 0;
			foreach($this->loansAndInvestments->getLongTermLoans() as $ltLoan) {
				
				$this->interestPaidByYear[$year] += $ltLoan->getInterestPaidByYear($year);
				$this->totalInterestPaid += $this->interestPaidByYear[$year];
				$this->longTermLoanContributionsByYear[$year] += $ltLoan->getContributionsByYear($year);
				$this->totalLoanContributions +=  $ltLoan->getContributionsByYear($year);
				//$this->yearEndClosingOrBalanceByYear[$year] = $ltLoan->getYearEndClosingOrBalanceByYear($year);
			}
			foreach($this->loansAndInvestments->getShortTermLoans() as $stLoan) {
				$this->interestPaidByYear[$year] += $stLoan->getInterestPaidByYear($year);
				$this->totalInterestPaid += $this->interestPaidByYear[$year];
				$this->shortTermLoanContributionsByYear[$year] += $stLoan->getContributionsByYear($year);
				$this->totalLoanContributions +=  $stLoan->getContributionsByYear($year);
				//$this->yearEndClosingOrBalanceByYear[$year] = $stLoan->getYearEndClosingOrBalanceByYear($year);
			}
		}
	}
	
	public function getTotalInterestPaid() {
		return $this->totalInterestPaid;
	}

	public function getInterestPaidByYear($year) {
		return $this->interestPaidByYear[$year];
	}
	
	public function getLoansAndInvestments() {
		return $this->loansAndInvestments;
	}
	
	public function getShortTermLoanContributionsByYear($year) {
		return $this->shortTermLoanContributionsByYear[$year];
	}
	
	public function getLongTermLoanContributionsByYear($year) {
		return $this->longTermLoanContributionsByYear[$year];
	}
	
	public function getTotalLoanContributions() {
		return $this->totalLoanContributions;
	}
	
/*	public function getYearEndClosingOrBalanceByYear($year) {
		return $this->yearEndClosingOrBalanceByYear[$year];
	}*/
	
	private function _setOperatingProfit() {
		$forecast = $this->getForecast();
		$expenses = $this->getExpenses();
		$this->totalOperatingProfit = 0;
		foreach($this->getYears(false) as $year) {
			$this->operatingProfitByYear[$year] = $forecast->getTotalSalesPerYear($year) - $expenses->getVariableExpensesByYear($year) - $expenses->getFixedExpensesByYear($year);
			$this->totalOperatingProfit += $this->operatingProfitByYear[$year];
		}
	}
	
	public function getOperatingProfitByYear($year) {
		return $this->operatingProfitByYear[$year];
	}
	
	public function getTotalOperatingProfit() {
		return $this->totalOperatingProfit;
	}

	private function _setPretaxIncome() {
		$forecast = $this->getForecast();
		$expenses = $this->getExpenses();
		$this->totalPretaxIncome = 0;
		foreach($this->getYears(false) as $year) {
			$this->pretaxIncomeByYear[$year] = $this->operatingProfitByYear[$year] - ($this->interestPaidByYear[$year] + $expenses->getDepreciationByYear($year));
			$this->totalPretaxIncome += $this->pretaxIncomeByYear[$year];
		}
	}
	
	public function getPretaxIncomeByYear($year) {
		return $this->pretaxIncomeByYear[$year];
	}
	
	public function getTotalPretaxIncome() {
		return $this->totalPretaxIncome;
	}

	private function _setNetProfitAfterTaxes() {
		$forecast = $this->getForecast();
		$expenses = $this->getExpenses();
		$this->totalNetProfitAfterTaxes = 0;
		foreach($this->getYears(false) as $year) {
			$this->netProfitAfterTaxesByYear[$year] = $this->operatingProfitByYear[$year] - $this->getTaxesPayableByYear($year);
			$this->totalNetProfitAfterTaxes += $this->netProfitAfterTaxesByYear[$year];
		}
	}
	
	public function getNetProfitAfterTaxesByYear($year) {
		return $this->netProfitAfterTaxesByYear[$year];
	}
	
	public function getTotalNetProfitAfterTaxes() {
		return $this->totalNetProfitAfterTaxes;
	}
	
	private function _setTaxesPayable() {
		$expenses = $this->getExpenses();
		$this->totalTaxesPayable = 0;
		foreach($this->getYears(false) as $year) {
			if ($this->carryoverLossByYear[$year] >= 0) {
				if ($this->pretaxIncomeByYear[$year] + $this->carryoverLossByYear[$year] < 0) {
					$this->taxesPayableByYear[$year]  = 0;
				}
				else {
					$this->taxesPayableByYear[$year]  = $this->pretaxIncomeByYear[$year] * ($expenses->getTaxRate()/100);
				}
			}
			else {
				$this->taxesPayableByYear[$year] = 0;
			}
			$this->totalTaxesPayable += $this->taxesPayableByYear[$year];
		}
	}
	
	public function getTaxesPayableByYear($year) {
		return $this->taxesPayableByYear[$year];
	}
	
	public function getTotalTaxesPayable() {
		return $this->totalTaxesPayable;
	}

	private function _setCarryoverLoss() {
		$firstYearLoss = $this->getExpenses()->getCarryoverLoss();
		$index = 0;
		foreach($this->getYears(false) as $year) {
			if ($index++ == 0) {
				$this->carryoverLossByYear[$year] = -$firstYearLoss + $this->pretaxIncomeByYear[$year];
			}
			else {
				$this->carryoverLossByYear[$year] = (($this->carryoverLossByYear[$year-1] + $this->pretaxIncomeByYear[$year]) > 0) ? 0 : $this->carryoverLossByYear[$year-1] + $this->pretaxIncomeByYear[$year];
			}
		}
	}
	
	public function getCarryoverLossByYear($year) {
		return $this->carryoverLossByYear[$year];
	}

	private function _setInvestments() {
		$this->totalInvestments = 0;
		foreach($this->getYears(false) as $year) {
			$this->investmentsByYear[$year] = 0;
			foreach($this->loansAndInvestments->getEquityAndInvestments() as $investment) {
				$this->investmentsByYear[$year] += $investment->getInvestmentContributionsByYear($year);
				$this->totalInvestments += $investment->getInvestmentContributionsByYear($year);
			}
		}
	}
	
	public function getInvestmentsByYear($year) {
		return $this->investmentsByYear[$year];
	}

	public function getTotalInvestments() {
		return $this->totalInvestments;
	}
	
	private function _setInventoryAndCapitalPurchases() {
		$this->totalCapitalPurchases = 0;
		foreach($this->getYears(false) as $year) {
			$this->capitalPurchasesByYear[$year] = 0;
			foreach($this->inventoryAndCapitalPurchases->getCapitalExpenditures() as $expenditure) {
				$this->capitalPurchasesByYear[$year] += $expenditure->getAmountByYear($year);
				$this->totalCapitalPurchases += $expenditure->getAmountByYear($year);
			}
		}
	}
	
	public function getTotalCapitalPurchases() {
		return $this->totalCapitalPurchases;
	}
	
	public function getCapitalPurchasesByYear($year) {
		return $this->capitalPurchasesByYear[$year];
	}
	
}

?>