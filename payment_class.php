<?php

include_once 'database_class.php';
	
class PaymentPart extends Database {

	// This maps model properties to database
	protected static $tableSchemas = array(
		'GAME' => array(
			'baId' => 'BA_ID',
			'gsId' => 'GS_ID',
			'paymentSubpart' => 'PAYMENT_SUBPART',
			'paymentDate' => 'PAYMENT_DATE',
			'amount' => 'AMOUNT'
		)
	);

	protected function __construct () {
		parent::__construct();
	}

	protected $baId;
	protected $gsId;
	protected $paymentSubpart;
	protected $paymentDate;
	protected $amount;

	public function setProperties($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function save() {
		if (isset($this->baId)) {
			$this->update();
		} else {
			$this->insert();
		}
	}

}

?>