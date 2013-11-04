<?php

include 'database_class.php';
	
class PaymentPart extends Database {

	// This maps model properties to database
	protected static $tableSchemas = array(
		'GAME' => array(
			'ID' => 'ID',
			'gsId' => 'GS_ID',
			'paymentSubpart' => 'PAYMENT_SUBPART',
			'amount' => 'AMOUNT'
		)
	);

	protected static $tableSequencer = 'PAYMENT_PART_SEQUENCE';

	protected function __construct () {
		parent::__construct();
	}

	protected $ID;
	protected $gsId;
	protected $paymentSubpart;
	protected $amount;

	public function setProperties($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function save() {
		if isset($this->ID) {
			$this->update();
		} else {
			$this->insert();
		}
	}

}

?>