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
	public $error='';
	public $TError=array();
	
	public function __construct()
	{
		global $conf;
		
		parent::__construct();
		
		$this->set_table(MAIN_DB_PREFIX.'importpayment');
		
		$this->add_champs('entity,fk_user_author,fk_c_paiement,fk_bank_account', array('type' => 'integer'));
		$this->add_champs('brut_file_content', array('type' => 'text'));
		
		$this->_init_vars();
		$this->start();
		
		$this->entity = $conf->entity;
	}
	
	public function parseFile($filename, $nb_ignore=0, $delimiter=';', $enclosure='"')
	{
		$handle = fopen($filename, 'r');
		if (!$handle)
		{
			$this->error = 'ErrorImportPaymentCanNotOpenFile';
			$this->TError[] = $this->error;
			return array();
		}
		
		// TODO ligne(s) d'entête à ignorer ?
		if ($nb_ignore > 0)
		{
			while ($nb_ignore--) fgets($handle);
		}
		
		$TData = array();
		while ($line = fgets($handle))
		{
			$TData[] = str_getcsv($this->force_utf8($line), $delimiter, $enclosure);
		}
		
		return $TData;
	}
	
	public static function getSelectValues()
	{
		global $langs;
		
		$TValue = array(
			'ref_facture' => $langs->transnoentities('Invoice')
			//,'fk_soc' => $langs->transnoentities('Company') // TODO à ignorer si un fk_soc existe en param global
			//,'datep' => $langs->transnoentities('PaymentDate') // TODO à ignorer si une date de paiement existe en param global
			,'num_paiement' => $langs->transnoentities('Numero').' <em>('.$langs->transnoentities("ChequeOrTransferNumber").')</em>'
			,'chqemetteur' => $langs->transnoentities('CheckTransmitter').' <em>('.$langs->transnoentities("ChequeMaker").')</em>'
			,'chqbank' => $langs->transnoentities('Bank').' <em>('.$langs->transnoentities("ChequeBank").')</em>'
			,'comment' => $langs->transnoentities('Comments') // TODO à décliner en comment_line_1, comment_line_2, ...
		);
		
		return $TValue;
	}


	/**
	 * Check for UTF-8 compatibility
	 *
	 * Regex from Martin Dürst
	 * @source http://www.w3.org/International/questions/qa-forms-utf-8.en.php
	 * @param string $str String to check
	 * @return boolean
	 */
	function is_utf8($str)
	{
		return preg_match("/^(
			 [\x09\x0A\x0D\x20-\x7E]            # ASCII
		   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
		   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
		   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
		   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
		  )*$/x", $str
		);
	}

	/**
	 * Try to convert a string to UTF-8.
	 *
	 * @author Thomas Scholz <http://toscho.de>
	 * @param string $str String to encode
	 * @param string $inputEnc Maybe the source encoding. 
	 *               Set to NULL if you are not sure. iconv() will fail then.
	 * @return string
	 */
	function force_utf8($str, $inputEnc = 'WINDOWS-1252')
	{
		if ($this->is_utf8($str)) // Nothing to do.
			return $str;

		if (strtoupper($inputEnc) === 'ISO-8859-1')
			return utf8_encode($str);

		if (function_exists('mb_convert_encoding'))
			return mb_convert_encoding($str, 'UTF-8', $inputEnc);

		if (function_exists('iconv'))
			return iconv($inputEnc, 'UTF-8', $str);

		// You could also just return the original string.
		trigger_error(
			'Cannot convert string to UTF-8 in file '
			.__FILE__.', line '.__LINE__.'!', E_USER_ERROR
		);
	}

}
