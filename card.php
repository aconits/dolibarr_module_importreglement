<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';

dol_include_once('/importpayment/class/importpayment.class.php');
dol_include_once('/importpayment/lib/importpayment.lib.php');

dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/compta/paiement/class/paiement.class.php');

if(empty($user->rights->facture->paiement) || empty($user->rights->importpayment->import)) accessforbidden();

$langs->load('importpayment@importpayment');
$langs->load('bills');
$langs->load('errors');

$action = GETPOST('action');
$step = GETPOST('step', 'int');

if (empty($step)) $step = 1;
	
$object = new TImportPayment;

$hookmanager->initHooks(array('importpaymentcard', 'globalcard'));

/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');


$error = 0;
switch ($action) {
	case 'gotostep2':
		$datep = dol_mktime(12, 0, 0, GETPOST('pmonth'), GETPOST('pday'), GETPOST('pyear'));
		$fk_c_paiement = GETPOST('fk_c_paiement', 'int');
		$fk_bank_account = GETPOST('fk_bank_account', 'int');
		
		if (empty($datep)) { $error++; setEventMessage($langs->trans('ImportPaymentErrorDatePaymentEmpty'), 'errors'); }
		if ($fk_c_paiement <= 0) { $error++; setEventMessage($langs->trans('ImportPaymentErrorPaymentTypeEmpty'), 'errors'); }
		if ($fk_bank_account <= 0) { $error++; setEventMessage($langs->trans('ImportPaymentErrorBankAccountEmpty'), 'errors'); }
		
		$nb_ignore = GETPOST('nb_ignore', 'int');
		if (empty($nb_ignore) && strcmp($nb_ignore, 0) !== 0) $nb_ignore = $conf->global->IMPORTPAYMENT_DEFAULT_NB_INGORE;
		$delimiter = GETPOST('delimiter');
		if (empty($delimiter)) $delimiter = $conf->global->IMPORTPAYMENT_DEFAULT_DELIMITER;
		$enclosure = GETPOST('enclosure');
		if (empty($enclosure)) $enclosure = $conf->global->IMPORTPAYMENT_DEFAULT_ENCLOSURE;

		$file = $_FILES['paymentfile'];
		if ($file['error'] > 0)
		{
			// @see http://php.net/manual/fr/features.file-upload.errors.php
			$error++;
			setEventMessage($langs->trans('ImportPaymentFileError', $file['error']), 'errors');
		}
		
		if (empty($error))
		{
			$TData = $object->parseFile($file['tmp_name'], $nb_ignore, $delimiter, $enclosure);
			_step2($object, $TData, $datep, $fk_c_paiement, $fk_bank_account, $nb_ignore, $delimiter, $enclosure, $file['name'], GETPOST('closepaidinvoices', 'int'));
		}
		else
		{
			_step1($object);
		}
		
		break;
		
	case 'gotostep3':
		$datep = GETPOST('datep', 'int');
		$fk_c_paiement = GETPOST('fk_c_paiement', 'int');
		$fk_bank_account = GETPOST('fk_bank_account', 'int');
		$nb_ignore = GETPOST('nb_ignore', 'int');
		$delimiter = GETPOST('delimiter');
		$enclosure = GETPOST('enclosure');
		$filename = GETPOST('filename');
		$closepaidinvoices= GETPOST('closepaidinvoices', 'int');
		
		$TFieldOrder = GETPOST('TField', 'array');
		if (empty($TFieldOrder)) $TFieldOrder = TImportPayment::getTFieldOrder();
		
		// TODO remove static calls by standard methods
		$TData = TImportPayment::getFormatedData($TFieldOrder, GETPOST('TLineIndex', 'array'), GETPOST('TData', 'array'));
		if (!empty($TData))
		{
			$TError = TImportPayment::setPayments($TData, $TFieldOrder, $datep, $fk_c_paiement, $fk_bank_account, true, $closepaidinvoices);
		}
		
		if (empty($error) && empty($TError))
		{
			_step3($object, $TData, $datep, $fk_c_paiement, $fk_bank_account, $nb_ignore, $delimiter, $enclosure, $filename, $closepaidinvoices);
		}
		else
		{
			if (empty($TData)) setEventMessage($langs->trans('ImportPaymentEmptyData'), 'warnings');
			_step2($object, unserialize(gzuncompress(base64_decode(GETPOST('TDataCompressed')))), $datep, $fk_c_paiement, $fk_bank_account, $nb_ignore, $delimiter, $enclosure, $filename, $closepaidinvoices, $TError);
		}
		
		break;
	
	case 'confirm_import':
		$datep = GETPOST('datep');
		$fk_c_paiement = GETPOST('fk_c_paiement');
		$fk_bank_account = GETPOST('fk_bank_account');

		$TFieldOrder = GETPOST('TField', 'array');
		if (empty($TFieldOrder)) $TFieldOrder = TImportPayment::getTFieldOrder();
		
		$TData = GETPOST('TData', 'array');
		$TData = TImportPayment::getFormatedData($TFieldOrder, array_keys($TData), $TData);
		
		$TError = TImportPayment::setPayments($TData, $TFieldOrder, $datep, $fk_c_paiement, $fk_bank_account, false, GETPOST('closepaidinvoices', 'int'));

		if (empty($TError))
		{
			setEventMessages($langs->trans('ImportPaymentSuccess'));
			
			$object->entity = $conf->entity;
			$object->datep = $datep;
			$object->fk_user_author = $user->id;
			$object->fk_c_paiement = $fk_c_paiement;
			$object->fk_bank_account = $fk_bank_account;
			$object->TFieldOrder = serialize($TFieldOrder);
			$object->TDataCompressed = base64_encode(gzcompress(serialize($TData)));
				
			$PDOdb = new TPDOdb;
			$object->save($PDOdb);
		}
		else setEventMessages(null, $TError, 'errors');
		
		header('Location: '.dol_buildpath('/importpayment/card.php', 1));
		exit;
		break;
	
	default:
		_step1($object);
		break;
}



/**
 * View
 */
function _step1(&$object)
{
	global $db,$langs,$conf;
	
	_header($object);
	
	$formcore = new TFormCore;
	$formcore->Set_typeaff('edit');
	
	$form = new Form($db);
	
	$TBS=new TTemplateTBS();
	$TBS->TBS->protect=false;
	$TBS->TBS->noerr=true;

	echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_importpayment', 'POST', true);
	
	$datep = dol_mktime(12, 0, 0, GETPOST('pmonth'), GETPOST('pday'), GETPOST('pyear'));
	
	ob_start();
	$form->select_types_paiements(GETPOST('fk_c_paiement'), 'fk_c_paiement');
	$selectPaymentMode = ob_get_clean();
	
	ob_start();
	$form->select_comptes(GETPOST('fk_bank_account'), 'fk_bank_account');
	$selectAccountToCredit = ob_get_clean();

	print $TBS->render('tpl/card.tpl.php'
		,array(
			'TData'=>array()
			,'TError' => array()
		) // Block
		,array(
			'object'=>$object
			,'view' => array(
				'action' => 'gotostep2'
				,'step' => 1
				,'urlcard' => dol_buildpath('/importpayment/card.php', 1)
				,'showInputFile' => $formcore->fichier('', 'paymentfile', '', $conf->global->MAIN_UPLOAD_DOC)
				,'showNbIgnore' => (!empty($conf->global->IMPORTPAYMENT_ALLOW_OVERRIDE_CONF_ON_IMPORT)) ? $formcore->number('', 'nb_ignore', (GETPOST('nb_ignore', 'int') !== '' ? GETPOST('nb_ignore', 'int') : (int) $conf->global->IMPORTPAYMENT_DEFAULT_NB_INGORE), 5) : $conf->global->IMPORTPAYMENT_DEFAULT_NB_INGORE
				,'showInputPaymentDate' => $form->select_date($datep, 'p', 0, 0, 0, '', 1, 1, 1)
				,'showDelimiter' => (!empty($conf->global->IMPORTPAYMENT_ALLOW_OVERRIDE_CONF_ON_IMPORT)) ? $formcore->texte('', 'delimiter', (GETPOST('delimiter') !== '' ? GETPOST('delimiter') : $conf->global->IMPORTPAYMENT_DEFAULT_DELIMITER), 5) : $conf->global->IMPORTPAYMENT_DEFAULT_DELIMITER
				,'showInputPaymentMode' => $selectPaymentMode
				,'showEnclosure' => (!empty($conf->global->IMPORTPAYMENT_ALLOW_OVERRIDE_CONF_ON_IMPORT)) ? $formcore->texte('', 'enclosure', dol_escape_htmltag((GETPOST('enclosure') !== '' ? GETPOST('enclosure') : $conf->global->IMPORTPAYMENT_DEFAULT_ENCLOSURE)), 5) : dol_escape_htmltag($conf->global->IMPORTPAYMENT_DEFAULT_ENCLOSURE)
				,'showInputAccountToCredit' => $selectAccountToCredit
				,'showClosePaidInvoices' => $formcore->checkbox1('', 'closepaidinvoices', 1, (GETPOST('closepaidinvoices', 'int') == 1 ? true : false))
			)
			,'langs' => $langs
			,'TDataCompressed' => ''
		)
	);
 
	echo $formcore->end_form();
	
	_footer();
}

function _step2(&$object, &$TData, $datep, $fk_c_paiement, $fk_bank_account, $nb_ignore, $delimiter, $enclosure, $filename, $closepaidinvoices, $TError=array())
{
	global $db,$langs,$conf;
	
	_header($object);
	
	$TBS=new TTemplateTBS();
	$TBS->TBS->protect=false;
	$TBS->TBS->noerr=true;
	
	$form = new Form($db);
	$form->load_cache_types_paiements();
	
	$formcore = new TFormCore;
	$formcore->Set_typeaff('edit');
	
	$account = new Account($db);
	$account->fetch($fk_bank_account);
	
	echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_importpayment', 'POST', true);
	
	$TFieldOrder = TImportPayment::getTFieldOrder(true);
	print $TBS->render('tpl/card.tpl.php'
		,array(
			'TData' => $TData
			,'TFieldOrder' => $TFieldOrder
			,'TError' => array_unique($TError)
		) // Block
		,array(
			'object'=>$object
			,'view' => array(
				'action' => 'gotostep3'
				,'step' => 2
				,'colspan' => count($TFieldOrder)+1
				,'urlcard' => dol_buildpath('/importpayment/card.php', 1)
				,'showInputFile' => $filename.' '.$formcore->hidden('filename', $filename)
				,'showNbIgnore' => $nb_ignore.' '.$formcore->hidden('nb_ignore', $nb_ignore)
				,'showInputPaymentDate' => dol_print_date($datep, 'day').' '.$formcore->hidden('datep', $datep)
				,'showDelimiter' => $delimiter.' '.$formcore->hidden('delimiter', $delimiter)
				,'showInputPaymentMode' => $form->cache_types_paiements[$fk_c_paiement]['label'].' '.$formcore->hidden('fk_c_paiement', $fk_c_paiement)
				,'showEnclosure' => $enclosure.' '.$formcore->hidden('enclosure', dol_escape_htmltag($enclosure))
				,'showInputAccountToCredit' => $account->label.' '.$formcore->hidden('fk_bank_account', $fk_bank_account)
				,'showClosePaidInvoices' => yn((bool) $closepaidinvoices, 1, 2).$formcore->hidden('closepaidinvoices', $closepaidinvoices)
			)
			,'langs' => $langs
			,'conf' => $conf
			,'TDataCompressed' => base64_encode(gzcompress(serialize($TData)))
		)
	);
	
	echo $formcore->end_form();
	
	_footer();
}

function _step3(&$object, &$TData, $datep, $fk_c_paiement, $fk_bank_account, $nb_ignore, $delimiter, $enclosure, $filename, $closepaidinvoices)
{
	global $db,$langs,$conf;
	
	_header($object);
	
	$TBS=new TTemplateTBS();
	$TBS->TBS->protect=false;
	$TBS->TBS->noerr=true;
	
	$form = new Form($db);
	$form->load_cache_types_paiements();
	
	$formcore = new TFormCore;
	$formcore->Set_typeaff('edit');
	
	$account = new Account($db);
	$account->fetch($fk_bank_account);
	
	echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_importpayment', 'POST', true);
	
	$TFieldOrder = TImportPayment::getTFieldOrder(true);
	print $TBS->render('tpl/card.tpl.php'
		,array(
			'TData' => $TData
			,'TFieldOrder' => $TFieldOrder
			,'TError' => array()
		) // Block
		,array(
			'object'=>$object
			,'view' => array(
				'action' => 'confirm_import'
				,'step' => 3
				,'colspan' => count($TFieldOrder)
				,'urlcard' => dol_buildpath('/importpayment/card.php', 1)
				,'showInputFile' => $filename.' '.$formcore->hidden('filename', $filename)
				,'showNbIgnore' => $nb_ignore.' '.$formcore->hidden('nb_ignore', $nb_ignore)
				,'showInputPaymentDate' => dol_print_date($datep, 'day').' '.$formcore->hidden('datep', $datep)
				,'showDelimiter' => $delimiter.' '.$formcore->hidden('delimiter', $delimiter)
				,'showInputPaymentMode' => $form->cache_types_paiements[$fk_c_paiement]['label'].' '.$formcore->hidden('fk_c_paiement', $fk_c_paiement)
				,'showEnclosure' => $enclosure.' '.$formcore->hidden('enclosure', dol_escape_htmltag($enclosure))
				,'showInputAccountToCredit' => $account->label.' '.$formcore->hidden('fk_bank_account', $fk_bank_account)
				,'showClosePaidInvoices' => yn((bool) $closepaidinvoices, 1, 2).$formcore->hidden('closepaidinvoices', $closepaidinvoices)
			)
			,'langs' => $langs
			,'conf' => $conf
			,'TDataCompressed' => ''
		)
	);
	
	echo $formcore->end_form();
	
	_footer();
}

function _header(&$object)
{
	global $langs;
	
	$title=$langs->trans("ImportPayment");
	llxHeader('',$title);

	$head = importpayment_prepare_head($object);
	$picto = 'generic';
	dol_fiche_head($head, 'card', $langs->trans("ImportPayment"), 0, $picto);
}

function _footer()
{
	global $db;
	
	llxFooter();
	$db->close();
}

function getValue($FieldName, &$CurrVal, &$CurrPrm, &$TBS)
{
	global $langs;
	
	if (is_object($CurrVal) && get_class($CurrVal) === 'Facture')
	{
		$facture = $CurrVal;
		$CurrVal = $facture->getNomUrl(1);
		if (method_exists($facture, 'getRemainToPay'))
		{
			$remainToPay = $facture->getRemainToPay();
			$CurrVal.= '<br /><b class="'.($remainToPay <= 0 ? 'error' : 'ok').'">'.price($remainToPay, 0, $langs).'</b>';
		}
	}
	
	return $CurrVal;
}

function getSanitizedValue($FieldName, &$CurrVal, &$CurrPrm, &$TBS)
{
	if (is_object($CurrVal) && get_class($CurrVal) === 'Facture') $CurrVal = $CurrVal->ref;
	
	return $CurrVal;
}