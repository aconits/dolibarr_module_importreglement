<?php

if (!class_exists('TObjetStd'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}


class TImportPayment extends TObjetStd
{

	public function __construct()
	{
		global $conf;
		
		$this->set_table(MAIN_DB_PREFIX.'importpayment');
		
		$this->add_champs('entity,fk_user_author,fk_c_paiement,fk_bank_account', array('type' => 'integer'));
		$this->add_champs('brut_file_content', array('type' => 'text'));
		
		$this->_init_vars();
		$this->start();
		
		$this->entity = $conf->entity;
	}
}
