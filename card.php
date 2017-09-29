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
		$fk_bank_account = GETPOST('fk_bank_account');
		
		$nb_ignore = GETPOST('nb_ignore');
		if (empty($nb_ignore) && $nb_ignore !== 0) $nb_ignore = $conf->global->IMPORTPAYMENT_DEFAULT_NB_INGORE;
		$delimiter = GETPOST('delimiter');
		if (empty($delimiter)) $delimiter = $conf->global->IMPORTPAYMENT_DEFAULT_DELIMITER;
		$enclosure = GETPOST('enclosure');
		if (empty($enclosure)) $enclosure = $conf->global->IMPORTPAYMENT_DEFAULT_ENCLOSURE;
		
		$file = $_FILES['paymentfile'];
		
		$TData = $object->parseFile($file['tmp_name'], $nb_ignore, $delimiter, $enclosure);
		
		// TODO if error or required field empty then goto step1
		
		_step2($object, $TData, $datep, $fk_c_paiement, $fk_bank_account, $nb_ignore, $delimiter, $enclosure);
		exit;
		break;
	case 'confirm_import':
		
		header('Location: '.dol_buildpath('/importpayment/card.php', 1));
		exit;
		break;
	
	default:
		_step1($object, $action, $step);
		break;
}



/**
 * View
 */
function _step1(&$object)
{
	global $db,$langs,$conf,$user;
	
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
		,array('TData'=>array()) // Block
		,array(
			'object'=>$object
			,'view' => array(
				'action' => 'gotostep2'
				,'step' => 1
				,'urlcard' => dol_buildpath('/importpayment/card.php', 1)
				,'showInputFile' => $formcore->fichier('', 'paymentfile', '', $conf->global->MAIN_UPLOAD_DOC)
				,'showNbIgnore' => (!empty($conf->global->IMPORTPAYMENT_ALLOW_OVERRIDE_CONF_ON_IMPORT)) ? $formcore->number('', 'nb_ignore', (int) $conf->global->IMPORTPAYMENT_DEFAULT_NB_INGORE, 5) : $conf->global->IMPORTPAYMENT_DEFAULT_NB_INGORE
				,'showInputPaymentDate' => $form->select_date($datep, 'p', 0, 0, 0, '', 1, 1, 1)
				,'showDelimiter' => (!empty($conf->global->IMPORTPAYMENT_ALLOW_OVERRIDE_CONF_ON_IMPORT)) ? $formcore->texte('', 'delimiter', $conf->global->IMPORTPAYMENT_DEFAULT_DELIMITER, 5) : $conf->global->IMPORTPAYMENT_DEFAULT_DELIMITER
				,'showInputPaymentMode' => $selectPaymentMode
				,'showEnclosure' => (!empty($conf->global->IMPORTPAYMENT_ALLOW_OVERRIDE_CONF_ON_IMPORT)) ? $formcore->texte('', 'enclosure', $conf->global->IMPORTPAYMENT_DEFAULT_ENCLOSURE, 5) : $conf->global->IMPORTPAYMENT_DEFAULT_ENCLOSURE
				,'showInputAccountToCredit' => $selectAccountToCredit
			)
			,'langs' => $langs
		)
	);

	echo $formcore->end_form();
	
	_footer();
}

function _step2(&$object, &$TData, $datep, $fk_c_paiement, $fk_bank_account, $nb_ignore, $delimiter, $enclosure)
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
	
	print $TBS->render('tpl/card.tpl.php'
		,array(
			'TData' => $TData
			,'TFieldOrder' => TImportPayment::getTFieldOrder(true)
		) // Block
		,array(
			'object'=>$object
			,'view' => array(
				'action' => 'gotostep3'
				,'step' => 2
				,'urlcard' => dol_buildpath('/importpayment/card.php', 1)
				,'colspan' => !empty($TData[0]) ? count($TData[0])+1 : 1
				,'showInputFile' => ''
				,'showNbIgnore' => $nb_ignore
				,'showInputPaymentDate' => dol_print_date($datep, 'day')
				,'showDelimiter' => $delimiter
				,'showInputPaymentMode' => $form->cache_types_paiements[$fk_c_paiement]['label']
				,'showEnclosure' => $enclosure
				,'showInputAccountToCredit' => $account->label
			)
			,'langs' => $langs
			,'conf' => $conf
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
