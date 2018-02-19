<?php

if (!class_exists('TObjetStd'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}


class TImportReglement extends TObjetStd
{
	public $error='';
	public $TError=array();

	public function __construct()
	{
		global $conf;

		parent::__construct();

		$this->set_table(MAIN_DB_PREFIX.'importreglement');

		$this->add_champs('entity,fk_user_author,fk_c_paiement,fk_bank_account', array('type' => 'integer'));
		$this->add_champs('datep', array('type' => 'date'));
		$this->add_champs('TFieldOrder,TDataCompressed', array('type' => 'text'));

		$this->_init_vars();
		$this->start();

		$this->entity = $conf->entity;
	}

	public function load(&$PDOdb,$id,$loadChild=true)
	{
		parent::load($PDOdb, $id, $loadChild);

		$this->TFieldOrder = unserialize($this->TFieldOrder);
		$this->TDataCompressed = unserialize(gzuncompress(base64_decode($this->TDataCompressed)));
	}

	public function parseFile($filename, $nb_ignore=0, $delimiter=';', $enclosure='"')
	{
		$handle = fopen($filename, 'r');
		if (!$handle)
		{
			$this->error = 'ErrorImportReglementCanNotOpenFile';
			$this->TError[] = $this->error;
			return array();
		}

		// ligne(s) d'entête à ignorer
		if ($nb_ignore > 0)
		{
			while ($nb_ignore--) fgets($handle);
		}

		// TODO à voir si je conserve la fonction fgets() plutôt que fgetcsv()
		$TData = array();
		while ($line = fgets($handle))
		{
			$TData[] = array_map('trim', str_getcsv($this->force_utf8($line), $delimiter, $enclosure));
		}

		return $TData;
	}

	public static function getTFieldPossible()
	{
		return array_merge(self::getTFieldRequired(), self::getTFieldOptional());
	}

	public static function getTFieldRequired()
	{
		global $langs;

		return array(
			'ref_facture' => $langs->transnoentities('InvoiceRef')
			,'total_ttc' => $langs->transnoentities('PaymentAmount')
		);
	}

	public static function getTFieldOptional()
	{
		global $langs;

		return array(
			'ignored' => $langs->transnoentities('ImportReglementIgnoredLine')
			,'num_paiement' => $langs->transnoentities('Numero').' <em>('.$langs->transnoentities("ChequeOrTransferNumber").')</em>'
			,'chqemetteur' => $langs->transnoentities('CheckTransmitter').' <em>('.$langs->transnoentities("ChequeMaker").')</em>'
			,'chqbank' => $langs->transnoentities('Bank').' <em>('.$langs->transnoentities("ChequeBank").')</em>'
			,'comment1' => $langs->transnoentities('Comment1')
			,'comment2' => $langs->transnoentities('Comment2')
			,'comment3' => $langs->transnoentities('Comment3')
			,'comment4' => $langs->transnoentities('Comment4')
			,'fk_soc' => $langs->transnoentities('Company') // TODO à ignorer si un fk_soc existe en param global
			,'datep' => $langs->transnoentities('PaymentDate')
		);
	}

	public static function getTFieldOrder($withLabel=false)
	{
		global $conf;

		$TRes = array();

		if (!empty($conf->global->IMPORTREGLEMENT_TFIELD_ORDER)) $TRes = unserialize($conf->global->IMPORTREGLEMENT_TFIELD_ORDER);
		else $TRes = self::getDefaultTFieldOrder();

		if ($withLabel)
		{
			$TLabel = self::getTFieldPossible();
			foreach ($TRes as $k => &$val)
			{
				$val = array('label' => $TLabel[$val], 'field' => $val);
			}
		}

		return $TRes;
	}

	public static function getDefaultTFieldOrder()
	{
		return array(
			'ref_facture'
			,'num_paiement'
			,'chqemetteur'
			,'chqbank'
			,'total_ttc'
			,'comment1'
		);
	}

	public static function getFormatedData(&$TFieldOrder, $TLineIndex, $TData)
	{
		global $db;
		require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';

		$TRes = array();

		foreach ($TLineIndex as $i)
		{
			foreach ($TFieldOrder as $field_index => $field_name)
			{
				$value = $TData[$i][$field_index];

				switch ($field_name) {
					case 'ref_facture':
						$facture = new Facture($db);
						if ($facture->fetch(null, $value) > 0)
						{
							$facture->val = $facture;
							$value = $facture;
						}
						else $value = '<span class="error">'.$value.'</span>';

						break;

					case 'total_ttc':
						$value = preg_replace('/[^0-9,.]/', '', $value);
						$value = price2num($value, 2);
						break;
				}

				$TRes[$i][] = $value;
			}
		}

		return $TRes;
	}

	public static function setPayments(&$TData, &$TFieldOrder, $datep, $fk_c_paiement, $fk_bank_account, $simulation=false, $closepaidinvoices=0, $avoid_already_paid=0, $do_not_import_double_payment=0)
	{
		global $db, $user, $langs;
		require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
		require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
		require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';

		$TError = array();
		$TOrderFieldName = array_flip($TFieldOrder);

		$db->begin();
		foreach ($TData as $key=>$Tab)
		{
			if (empty($Tab[$TOrderFieldName['ref_facture']]->id))
			{
				$TError[] = $langs->transnoentities('ImportReglementFactureNotFound', strip_tags($Tab[$TOrderFieldName['ref_facture']]));
				continue;
			} elseif ($avoid_already_paid && $Tab[$TOrderFieldName['ref_facture']]->getRemainToPay()<=0) {
				//Do not import already paid invoice
				unset($TData[$key]);
				continue;
			}

			if (empty($Tab[$TOrderFieldName['total_ttc']])) {
				continue;
				unset($TData[$key]);
			}

			// Creation of payment line
			$paiement = new Paiement($db);
			//Use date paiement from file if exists
			if (is_array($TOrderFieldName) && array_key_exists('datep', $TOrderFieldName) && !empty($Tab[$TOrderFieldName['datep']])) {
				$paiement->datepaye = strtotime($Tab[$TOrderFieldName['datep']]);
			} else {
				$paiement->datepaye = $datep;
			}

			$paiement->amounts = array($Tab[$TOrderFieldName['ref_facture']]->id => $Tab[$TOrderFieldName['total_ttc']]);   // Array with all payments dispatching with invoice id
			$paiement->paiementid = $fk_c_paiement; //dol_getIdFromCode($db,GETPOST('paiementcode'),'c_paiement');
			$paiement->num_paiement = $Tab[$TOrderFieldName['num_paiement']];
			$paiement->note = $Tab[$TOrderFieldName['comment1']]."\n";
			$paiement->note .= $Tab[$TOrderFieldName['comment2']]."\n";
			$paiement->note .= $Tab[$TOrderFieldName['comment3']]."\n";
			$paiement->note .= $Tab[$TOrderFieldName['comment4']]."\n";
			$paiement->note .= $langs->trans('ImportFromModuleImportReglement').'-'.dol_print_date($datep)."\n";

			$paiement->note = preg_replace('/^\n/m', '', $paiement->note);

			if (!empty($do_not_import_double_payment)) {
				$result=self::IsPaymentAlreadyExists($paiement);
			} else {
				$result=0;
			}

			//No payment found on the same date same amount same invoice
			if ($result===0) {
				$paiement_id = $paiement->create($user, $closepaidinvoices);
				if ($paiement_id < 0)
		        {
		            $TError[] = $paiement->error;
		        }
				else
				{
					$label='(CustomerInvoicePayment)';
					if ($Tab['ref_facture']->type == Facture::TYPE_CREDIT_NOTE) $label='(CustomerInvoicePaymentBack)';  // Refund of a credit note
					$result=$paiement->addPaymentToBank($user, 'payment', $label, $fk_bank_account, $Tab[$TOrderFieldName['chqemetteur']], $Tab[$TOrderFieldName['chqbank']]);
					if ($result < 0)
					{
						$TError[] = $paiement->error;
					}
				}
			} else {
				unset($TData[$key]);
			}
		}

		if ($simulation) $db->rollback();
		else $db->commit();

		return $TError;
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
			.__FILE__.', line '.__LINE__.'!', E_USER_WARNING
		);
	}

	public static function IsPaymentAlreadyExists(Paiement $payment){

		global $db;

		if (is_array($payment->amounts && count ($payment->amounts)>0)) {
			foreach($payment->amounts as $facid=>$amount) {
				$sql = 'SELECT p.rowid FROM '.MAIN_DB_PREFIX.$payment->tablename. ' as p ';
				$sql .= ' INNER JOIN '. MAIN_DB_PREFIX.'paiement_facture as pf';
				$sql .= ' ON pf.fk_paiement=p.rowid';
				$sql .= ' WHERE p.datep=\''.$this->db->idate($payment->datepaye).'\'';
				$sql .= ' AND pf.fk_facture='.$facid;
				$sql .= ' AND pf.amount=\''.$amount.'\'';

				dol_syslog(get_class($this).'::'.__METHOD__,LOG_DEBUG);
				$resql = $db->query($sql);
				if ($resql) {
					$obj=$db->fetch_object($resql);
					if (!empty($obj->rowid)) {
						return $obj->rowid;
					}
				} else {
					$this->TError[] = $db->lasterror;
				}
			}
			if (!empty($this->TError)) {
				return -1;
			}
		}

		return 0;

	}

}
