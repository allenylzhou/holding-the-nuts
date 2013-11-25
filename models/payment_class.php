<?php

include_once 'database_class.php';
	
class Payment extends Database {

	protected static $tableKey = array(
		'ppId' => array('type' => DataType::NUMBER, 'sequence' => 'PAYMENT_SEQUENCE')
	);
	
	// This maps model properties to database
	protected static $tableAttributes  = array(
		'PAYMENT' => array(
			'payerId' => array('type' => DataType::NUMBER),
			'payeeId' => array('type' => DataType::NUMBER),
			'paymentDate' => array('type' => DataType::DATE, 'default' => 0),
			'amount' => array('type' => DataType::NUMBER, 'default' => 0),
		)
	);

	public function __construct ($key = array(), $select = false) {
		parent::__construct();

		foreach ($key as $name => $value) {
			if (array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}

		if ($select) {
			$this->setAttributes($this->select());
		}
	}

	protected $ppId;
	protected $payerId;
	protected $payeeId;
	protected $paymentDate;
	protected $amount;

	public function setAttributes($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
		}
	}
	
	public static function getPaymentsTo($userId) {
		return Payment::_getPayments($userId, false, true);
	}
	
	public static function getPaymentsFrom($userId) {
		return Payment::_getPayments($userId, true, false);
	}
	
	public static function _getPayments($userId, $isFrom, $isTo){
		$results = array();
		if(!$isFrom && !$isTo){
			return $results;
		}
		$connection = static::start();

		$fromClause = '';
		if($isFrom){
			$fromClause = '(payer_id = :userId)';
		}
		
		$toClause = '';
		if($isTo){
			$toClause = '(payee_id = :userId)';
		}
		
		$or = '';
		if($isTo && $isFrom){
			$or = 'or';
		}
		
		$sqlString = "select PP_ID, u1.username as PAYER, u2.username AS PAYEE, PAYMENT_DATE, AMOUNT
						from payment, users u1, users u2
						where u1.user_id = payer_id
						and u2.user_id = payee_id
						and (" . $fromClause . $or . $toClause . ")";
						
		$sqlStatement = oci_parse($connection, $sqlString);
		oci_bind_by_name($sqlStatement, ':userId', $userId);

		if(oci_execute($sqlStatement)) {
			while ($row = oci_fetch_assoc($sqlStatement)) {
				$rowTemp = array();
				$rowTemp['FROM'] = $row['PAYER'];
				$rowTemp['TO'] = $row['PAYEE'];
				$rowTemp['DATE'] = $row['PAYMENT_DATE'];
				$rowTemp['AMOUNT'] = $row['AMOUNT'];
				$results[] = $rowTemp;
			}
		}
		static::end($connection);

		return $results;
	}

	public function save() {
		if (isset($this->ppId)) {
			$this->update();
		} else {
			$this->insert();
		}
	}

}

?>