<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 8/10/2019
 * Time: 4:47 PM
 */

include_once(ACTION_FUNCTIONS_PATH);

class AuditItem {
    public $breakdownTable = "";
	public $totalIncomeForAudit = 0;
	public $totalProfitForAudit = 0;
	public $previousAuditID = 0;
	public $previousAuditDate = "";

	public function __construct( $breakdownTable, $totalIncomeForAudit, $totalProfitForAudit, $previousAuditID, $previousAuditDate ) {
		$this->breakdownTable = $breakdownTable;
		$this->totalIncomeForAudit = $totalIncomeForAudit;
		$this->totalProfitForAudit = $totalProfitForAudit;
		$this->previousAuditID = $previousAuditID;
		$this->previousAuditDate = $previousAuditDate;
	}

    /**
     * @return string
     */
    public function getBreakdownTable()
    {
        return $this->breakdownTable;
    }

    /**
     * @return int
     */
    public function getTotalIncomeForAudit()
    {
        return $this->totalIncomeForAudit;
    }

    /**
     * @return int
     */
    public function getTotalProfitForAudit()
    {
        return $this->totalProfitForAudit;
    }

    /**
     * @return int
     */
    public function getPreviousAuditID()
    {
        return $this->previousAuditID;
    }

    /**
     * @return string
     */
    public function getPreviousAuditDate()
    {
        return $this->previousAuditDate;
    }


}