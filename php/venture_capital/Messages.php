<?php
namespace VentureCapital;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class Messages {
	const MESSAGES_EN_US = array( 'VC_MESSAGE_YEAR_TOTALS_ABBREV' => "Yr Totals",
															'VC_MESSAGE_SUBTOTAL_UNITS' => "subtotal UNITS",
															'VC_MESSAGE_TOTAL_HEADER' => "TOTAL",
															'VC_MESSAGE_TOTAL_SALES' => "Total Sales",
															'VC_MESSAGE_TOTAL_UNITS' => "Total Units",
															'VC_MESSAGE_UNITS' => "Units",
															'VC_MESSAGE_YEAR_OVER_YEAR' => "Year over year",
															'VC_MESSAGE_CHANNEL' => "Channel",
															'VC_MESSAGE_SALES_CHANNEL' => "Sales Channel",
															'VC_MESSAGE_CATEGORY' => "Category",
															'VC_MESSAGE_PRODUCT_CATEGORY' => "Product Category",
															'VC_MESSAGE_GROWTH_RATE' => "Growth Rate",
															'VC_MESSAGE_UNIT_COST' => "Unit Cost",
															'VC_MESSAGE_UNITS_SOLD' => "Units Sold",
															'VC_MESSAGE_PERCENT_OF_COST' => "% of Cost",
															'VC_MESSAGE_AVERAGE_MARGIN_BY_CHANNEL' => "Avg. Margin, by Channel",
															'VC_MESSAGE_BASELINE' => "Baseline",
															'VC_MESSAGE_INCOME_PROJECTION' => "Income Projection",
															'VC_MESSAGE_PERCENT_OF_SALES_ABBREV' => "% of Sales",
															'VC_MESSAGE_DIRECT_EXPENSES' => "Direct Expenses",
															'VC_MESSAGE_TOTAL_VARIABLE_EXPENSES' => "Total Variable Expenses",
															'VC_MESSAGE_TOTAL_FIXED_EXPENSES' => "Total Fixed Expenses",
															'VC_MESSAGE_GROSS_PROFIT' => "Gross Profit",
															'VC_MESSAGE_TOTAL_CONTRIBUTION_GROSS_PROFIT' => "Total Contribution (Gross Profit)",
															'VC_MESSAGE_NET_INCOME_BEFORE_TAXES' => "Net Income Before Tax",
															'VC_MESSAGE_FIXED_EXPENSES_PLUS_INTEREST' => "Fixed Expenses + Interest",
															'VC_MESSAGE_BREAK_EVEN_SALES' => "Break-Even Sales",
															'VC_MESSAGE_OVER_UNDER_FROM_BREAK_EVEN_SALES' => "Over (Under) from Break-Even Sales",
															'VC_MESSAGE_OVER_UNDER_FROM_BREAK_EVEN_UNITS' => "Over (Under) from Break-Even Units",
															'VC_MESSAGE_CONTRIBUTION_AVG_PER_UNIT' => "Contribution (Avg. $/Unit)",
															'VC_MESSAGE_BREAK_EVEN_UNITS' => "Break-Even (#Units)",
															'VC_MESSAGE_OPERATING_EXPENSES' => "Operating Expenses",
															'VC_MESSAGE_MANUFACTURING_EXPENSES' => "Manufacturing Expenses",
															'VC_MESSAGE_SELLING_EXPENSES' => "Selling Expenses",
															'VC_MESSAGE_FACILITY_EXPENSES' => "Facility Expenses",
															'VC_MESSAGE_COGS' => "COGS",
															'VC_MESSAGE_TOTAL_COGS' => "TOTAL COGS",
															'VC_MESSAGE_COGS_INGREDIENTS' => "Ingredients",
															'VC_MESSAGE_COGS_LABOUR' => "Labour",
															'VC_MESSAGE_COGS_PACKAGING' => "Packaging",
															'VC_MESSAGE_OPERATING_PROFIT' => "Operating Profit (EBITDA)",
															'VC_MESSAGE_INTEREST_EXPENSE' => "Interest Expense",
															'VC_MESSAGE_DEPRECIATION_EXPENSE' => "Depreciation Expense",
															'VC_MESSAGE_TAXES_PAYABLE' => "Taxes Payable",
															'VC_MESSAGE_PRE_TAX_INCOME' => "Pre-Tax Income",
															'VC_MESSAGE_NET_PROFIT_AFTER_TAXES' => "Net Profit after Taxes",
															'VC_MESSAGE_ACCUMULATED_CARRYFORWARD_CAPITAL_LOSSES' => "Accumulated Carryforward Capital Losses",
															'VC_MESSAGE_STAFF_AND_TRAVEL_EXPENSES' => 'Staff & Travel Expenses',
															'VC_MESSAGE_SALARY_AND_BENEFITS' => 'Salary and Benefits',
															'VC_MESSAGE_MARKETING_AND_PROMOTION' => 'Marketing & Promotion',
															'VC_MESSAGE_FOOD_SAFETY_AND_REGULATORY' => 'Food Safety & Regulatory',
															'VC_MESSAGE_ADMINISTRATIVE_EXPENSES' => 'Administrative Expenses',
															'VC_MESSAGE_CONTINGENCY_UNPLANNED_EXPENSES' => 'Contingency/Unplanned',
															'VC_MESSAGE_NET_INCOME_FROM_OPERATIONS' => 'Net Income from Operations',
															'VC_MESSAGE_DEPRECIATION_EXPENSE_LONG' => 'Add back depreciation expense (non-cash expense)',
															'VC_MESSAGE_CASH_ADDITIONS_LOANS_FUNDING' => 'Cash Max LOC / Working Capital Loan Balance Required, Loans & Funding',
															'VC_MESSAGE_CURRENT' => 'Current',
															'VC_MESSAGE_TOTAL_CASH_IN' => 'Total Cash In',
															'VC_MESSAGE_TOTAL_CASH_OUT' => 'Total Cash Out',
															'VC_MESSAGE_EQUITY_INVESTMENTS' => 'Equity Investments',
															'VC_MESSAGE_LONG_TERM_LOANS' => 'Long Term Loans',
															'VC_MESSAGE_SHORT_TERM_LOANS' => 'Short Term Loans',
															'VC_MESSAGE_CASH_IN' => 'Cash In',
															'VC_MESSAGE_CASH_OUT' => 'Cash Out',
															'VC_MESSAGE_CHANGE_IN_INVENTORY' => 'Change in Inventory',
															'VC_MESSAGE_CAPITAL_PURCHASES' => 'Capital Purchases',
															'VC_MESSAGE_PAYMENTS_DIVIDENDS' => 'Payments & Dividends',
															'VC_MESSAGE_CASH_FLOW_SUMMARY' => 'Cash Flow Summary',
															'VC_MESSAGE_CASH_OPENING_BALANCE' => 'Cash Opening Balance',
															'VC_MESSAGE_CHANGE_IN_CASH' => 'Change in Cash',
															'VC_MESSAGE_CASH_CLOSING_BALANCE' => 'Cash Closing Balance',
															'VC_MESSAGE_LOAN_PAYMENTS' => 'Loan Payments',
															'VC_MESSAGE_OWNERS_DRAW_AND_DIVIDENDS' => 'Owners\' Draw & Dividends',
															'VC_MESSAGE_CASH_FLOW_SUMMARY' => 'Cash Flow Summary',
															'VC_MESSAGE_CASH_CHANGE' => 'Change in Cash',
															'VC_MESSAGE_ASSETS' => 'Assets',
															'VC_MESSAGE_CASH_AND_OTHER_WORKING_CAPITAL' => 'Cash & Other Working Capital',
															'VC_MESSAGE_INVENTORY' => 'Inventory',
															'VC_MESSAGE_ACCUMULATED_DEPRECIATION' => 'Accumulated depreciation',
															'VC_MESSAGE_TOTAL_ASSETS' => 'Total Assets',
															'VC_MESSAGE_LIABILITIES' => 'Liabilities',
															'VC_MESSAGE_TOTAL_LIABILITIES' => 'Total Liabilities',
															'VC_MESSAGE_EQUITY' => 'Equity',
															'VC_MESSAGE_RETAINED_EARNINGS' => 'Retained Earnings',
															'VC_MESSAGE_SHAREHOLDER_AND_INVESTOR_EQUITY' => 'Shareholder & Investor Equity',
															'VC_MESSAGE_TOTAL_EQUITY' => 'Total Equity',
															'VC_MESSAGE_TOTAL_LIABILITIES_AND_EQUITY' => 'Total Liabilities & Equity',
															'VC_MESSAGE_MONTHLY_BENCHMARK' => 'Monthly Benchmark',
															// report names
															"VC_MESSAGE_FORECAST_TABLE_CAPTION_SALES_BY_CHANNEL_GROWTH" => "Sales Projections based on Sales Channel growth",
															"VC_MESSAGE_FORECAST_TABLE_CAPTION_SALES_BY_CATEGORY_GROWTH" => "Sales Projections based on Sales Category growth",
															"VC_MESSAGE_BREAK_EVEN_ANALYSIS_REPORT" => "Break Even Analysis",
															"VC_MESSAGE_CASH_FLOW_REPORT" => "Cash Flow",
															"VC_MESSAGE_BALANCE_SHEET_REPORT" => "Balance Sheet",
															"VC_MESSAGE_INCOME_PROJECTION_REPORT" => "Income Projection",
															"VC_MESSAGE_PRODUCTION_ECONOMICS_SALES_MARGIN_REPORT" => "Sales Margin By Product Category & Sales Channel",
															"VC_MESSAGE_PRODUCTION_ECONOMICS_UNIT_COST_ANALYSIS_REPORT" => "Production Unit Cost Analysis",
															"VC_MESSAGE_PRODUCTION_ECONOMICS_ANNUAL_UNIT_COST_REPORT" => "Annual Unit Cost",
															"VC_MESSAGE_PRODUCTION_ECONOMICS_PRODUCTION_AND_COGS_REPORT" => "Production and COGS Summary",
															"VC_MESSAGE_SALES_PROJECTIONS" => "Sales Projections",
															"VC_MESSAGE_PL_PROJECTION_REPORT" => "P&L Projection");
															
	const MESSAGES_FR_CA = array( 'VC_MESSAGE_YEAR_TOTALS_ABBREV' => "frYr Totals",
															'VC_MESSAGE_SUBTOTAL_UNITS' => "frsubtotal UNITS",
															'VC_MESSAGE_TOTAL_HEADER' => "TOTAL",
															'VC_MESSAGE_TOTAL_SALES' => "frTotal Sales",
															'VC_MESSAGE_TOTAL_UNITS' => "frTotal Units",
															'VC_MESSAGE_UNITS' => "frUnits",
															'VC_MESSAGE_YEAR_OVER_YEAR' => "frYear over year",
															'VC_MESSAGE_CHANNEL' => "frChannel",
															'VC_MESSAGE_SALES_CHANNEL' => "frSales Channel",
															'VC_MESSAGE_CATEGORY' => "frCategory",
															'VC_MESSAGE_PRODUCT_CATEGORY' => "frProduct Category",
															'VC_MESSAGE_GROWTH_RATE' => "frGrowth Rate",
															'VC_MESSAGE_UNIT_COST' => "frUnit Cost",
															'VC_MESSAGE_UNITS_SOLD' => "frUnits Sold",
															'VC_MESSAGE_PERCENT_OF_COST' => "fr% of Cost",
															'VC_MESSAGE_AVERAGE_MARGIN_BY_CHANNEL' => "frAvg. Margin, by Channel",
															'VC_MESSAGE_BASELINE' => "frBaseline",
															'VC_MESSAGE_INCOME_PROJECTION' => "frIncome Projection",
															'VC_MESSAGE_PERCENT_OF_SALES_ABBREV' => "fr% of Sales",
															'VC_MESSAGE_DIRECT_EXPENSES' => "frDirect Expenses",
															'VC_MESSAGE_TOTAL_VARIABLE_EXPENSES' => "frTotal Variable Expenses",
															'VC_MESSAGE_TOTAL_FIXED_EXPENSES' => "frTotal Fixed Expenses",
															'VC_MESSAGE_GROSS_PROFIT' => "frGross Profit",
															'VC_MESSAGE_TOTAL_CONTRIBUTION_GROSS_PROFIT' => "frTotal Contribution (Gross Profit)",
															'VC_MESSAGE_NET_INCOME_BEFORE_TAXES' => "frNet Income Before Tax",
															'VC_MESSAGE_FIXED_EXPENSES_PLUS_INTEREST' => "frFixed Expenses + Interest",
															'VC_MESSAGE_BREAK_EVEN_SALES' => "frBreak-Even Sales",
															'VC_MESSAGE_OVER_UNDER_FROM_BREAK_EVEN_SALES' => "frOver (Under) from Break-Even Sales",
															'VC_MESSAGE_OVER_UNDER_FROM_BREAK_EVEN_UNITS' => "frOver (Under) from Break-Even Units",
															'VC_MESSAGE_CONTRIBUTION_AVG_PER_UNIT' => "frContribution (Avg. $$/Unit)",
															'VC_MESSAGE_BREAK_EVEN_UNITS' => "frBreak-Even (#Units)",
															'VC_MESSAGE_OPERATING_EXPENSES' => "frOperating Expenses",
															'VC_MESSAGE_MANUFACTURING_EXPENSES' => "frManufacturing Expenses",
															'VC_MESSAGE_SELLING_EXPENSES' => "frSelling Expenses",
															'VC_MESSAGE_FACILITY_EXPENSES' => "frFacility Expenses",
															'VC_MESSAGE_COGS' => "frCOGS",
															'VC_MESSAGE_TOTAL_COGS' => "frTOTAL COGS",
															'VC_MESSAGE_COGS_INGREDIENTS' => "frIngredients",
															'VC_MESSAGE_COGS_LABOUR' => "frLabour",
															'VC_MESSAGE_COGS_PACKAGING' => "frPackaging",
															'VC_MESSAGE_OPERATING_PROFIT' => "frOperating Profit (EBITDA)",
															'VC_MESSAGE_INTEREST_EXPENSE' => "frInterest Expense",
															'VC_MESSAGE_DEPRECIATION_EXPENSE' => "frDepreciation Expense",
															'VC_MESSAGE_TAXES_PAYABLE' => "frTaxes Payable",
															'VC_MESSAGE_PRE_TAX_INCOME' => "frPre-Tax Income",
															'VC_MESSAGE_NET_PROFIT_AFTER_TAXES' => "frNet Profit after Taxes",
															'VC_MESSAGE_ACCUMULATED_CARRYFORWARD_CAPITAL_LOSSES' => "frAccumulated Carryforward Capital Losses",
															'VC_MESSAGE_STAFF_AND_TRAVEL_EXPENSES' => 'frStaff & Travel Expenses',
															'VC_MESSAGE_SALARY_AND_BENEFITS' => 'frSalary and Benefits',
															'VC_MESSAGE_MARKETING_AND_PROMOTION' => 'frMarketing & Promotion',
															'VC_MESSAGE_FOOD_SAFETY_AND_REGULATORY' => 'frFood Saftey & Regulatory',
															'VC_MESSAGE_ADMINISTRATIVE_EXPENSES' => 'frAdministrative Expenses',
															'VC_MESSAGE_CONTINGENCY_UNPLANNED_EXPENSES' => 'frContingency/Unplanned',
															'VC_MESSAGE_NET_INCOME_FROM_OPERATIONS' => 'frNet Income from Operations',
															'VC_MESSAGE_DEPRECIATION_EXPENSE_LONG' => 'frAdd back depreciation expense (non-cash expense)',
															'VC_MESSAGE_CASH_ADDITIONS_LOANS_FUNDING' => 'frCash Max LOC / Working Capital Loan Balance Required, Loans & Funding',
															'VC_MESSAGE_CURRENT' => 'frCurrent',
															'VC_MESSAGE_TOTAL_CASH_IN' => 'frTotal Cash In',
															'VC_MESSAGE_TOTAL_CASH_OUT' => 'frTotal Cash Out',
															'VC_MESSAGE_EQUITY_INVESTMENTS' => 'frEquity Investments',
															'VC_MESSAGE_LONG_TERM_LOANS' => 'frLong Term Loans',
															'VC_MESSAGE_SHORT_TERM_LOANS' => 'frShort Term Loans',
															'VC_MESSAGE_CASH_IN' => 'frCash In',
															'VC_MESSAGE_CASH_OUT' => 'frCash Out',
															'VC_MESSAGE_CHANGE_IN_INVENTORY' => 'frChange in Inventory',
															'VC_MESSAGE_CAPITAL_PURCHASES' => 'frCapital Purchases',
															'VC_MESSAGE_PAYMENTS_DIVIDENDS' => 'frPayments & Dividends',
															'VC_MESSAGE_CASH_FLOW_SUMMARY' => 'frCash Flow Summary',
															'VC_MESSAGE_CASH_OPENING_BALANCE' => 'frCash Opening Balance',
															'VC_MESSAGE_CHANGE_IN_CASH' => 'frChange in Cash',
															'VC_MESSAGE_CASH_CLOSING_BALANCE' => 'frCash Closing Balance',
															'VC_MESSAGE_LOAN_PAYMENTS' => 'frLoan Payments',
															'VC_MESSAGE_OWNERS_DRAW_AND_DIVIDENDS' => 'frOwners\' Draw & Dividends',
															'VC_MESSAGE_CASH_FLOW_SUMMARY' => 'frCash Flow Summary',
															'VC_MESSAGE_CASH_CHANGE' => 'frChange in Cash',
															'VC_MESSAGE_CASH_CLOSING_BALANCE' => 'frCash - Closing Balance',
															'VC_MESSAGE_ASSETS' => 'frAssets',
															'VC_MESSAGE_CASH_AND_OTHER_WORKING_CAPITAL' => 'frCash & Other Working Capital',
															'VC_MESSAGE_INVENTORY' => 'frInventory',
															'VC_MESSAGE_ACCUMULATED_DEPRECIATION' => 'frAccumulated depreciation',
															'VC_MESSAGE_TOTAL_ASSETS' => 'frTotal Assets',
															'VC_MESSAGE_LIABILITIES' => 'frLiabilities',
															'VC_MESSAGE_TOTAL_LIABILITIES' => 'frTotal Liabilities',
															'VC_MESSAGE_EQUITY' => 'frEquity',
															'VC_MESSAGE_RETAINED_EARNINGS' => 'frRetained Earnings',
															'VC_MESSAGE_SHAREHOLDER_AND_INVESTOR_EQUITY' => 'frShareholder & Investor Equity',
															'VC_MESSAGE_TOTAL_EQUITY' => 'frTotal Equity',
															'VC_MESSAGE_TOTAL_LIABILITIES_AND_EQUITY' => 'frTotal Liabilities & Equity',
															'VC_MESSAGE_MONTHLY_BENCHMARK' => 'frMonthly Benchmark',
															// report names
															"VC_MESSAGE_FORECAST_TABLE_CAPTION_SALES_BY_CHANNEL_GROWTH" => "frSales Projections based on Sales Channel growth",
															"VC_MESSAGE_FORECAST_TABLE_CAPTION_SALES_BY_CATEGORY_GROWTH" => "frSales Projections based on Sales Category growth",
															"VC_MESSAGE_BREAK_EVEN_ANALYSIS_REPORT" => "frBreak Even Analysis",
															"VC_MESSAGE_CASH_FLOW_REPORT" => "frCash Flow",
															"VC_MESSAGE_BALANCE_SHEET_REPORT" => "frBalance Sheet",
															"VC_MESSAGE_INCOME_PROJECTION_REPORT" => "frIncome Projection",
															"VC_MESSAGE_PRODUCTION_ECONOMICS_SALES_MARGIN_REPORT" => "frSales Margin By Product Category & Sales Channel",
															"VC_MESSAGE_PRODUCTION_ECONOMICS_UNIT_COST_ANALYSIS_REPORT" => "frProduction Unit Cost Analysis",
															"VC_MESSAGE_PRODUCTION_ECONOMICS_ANNUAL_UNIT_COST_REPORT" => "frAnnual Unit Cost",
															"VC_MESSAGE_PRODUCTION_ECONOMICS_PRODUCTION_AND_COGS_REPORT" => "frProduction and COGS Summary",
															"VC_MESSAGE_PL_PROJECTION_REPORT" => "frP&L Projection");
	
	public static function getMessage($token) {
		//$currentLocale = setlocale(LC_ALL, 0);
		//$region = Locale::getRegion($currentLocale);
		//$language = Locale.getPrimaryLanguage($currentLocale);
		//$region = "US";
		//$language = "en";
		$lang = htmlspecialchars($_GET["lang"]);
		switch ($language . '_' . $region) {
			case '':
			case 'en':
				return self::MESSAGES_EN_US["$token"];
			case 'fr':
				return self::MESSAGES_FR_CA["$token"];
			default:
				return self::MESSAGES_EN_US["$token"];
		}
	}
}
?>