<?php

include_once 'database_class.php';
	
class Payment extends Database {

	// This maps model properties to database
	protected static $tableSchemas = array(
			'ppId' => 'PP_ID',
			'payerId' => 'PAYER_ID',
			'payeeId' => 'PAYEE_ID',
			'paymentDate' => 'PAYMENT_DATE',
			'amount' => 'AMOUNT'
		)
	);

	protected function __construct () {
		parent::__construct();
	}

	protected $ppId;
	protected $payerId;
	protected $payeeId;
	protected $paymentDate;
	protected $amount;

	public function setProperties($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
		}
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