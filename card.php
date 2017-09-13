<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/importpayment/class/importpayment.class.php');
dol_include_once('/importpayment/lib/importpayment.lib.php');

if(empty($user->rights->facture->paiement) || empty($user->rights->importpayment->import)) accessforbidden();

$langs->load('importpayment@importpayment');
$langs->load('bills');
$action = GETPOST('action');

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
	case 'import':
		
			
		break;
	case 'confirm_import':
		
		header('Location: '.dol_buildpath('/importpayment/card.php', 1));
		exit;
		break;
	
	default:
		_fiche($object, $action);
		break;
}



/**
 * View
 */
function _fiche(&$object, $action)
{
	global $db,$langs,$conf,$user;
	
	$title=$langs->trans("ImportPayment");
	llxHeader('',$title);

	$head = importpayment_prepare_head($object);
	$picto = 'generic';
	dol_fiche_head($head, 'card', $langs->trans("ImportPayment"), 0, $picto);

	$formcore = new TFormCore;
	$formcore->Set_typeaff('edit');
	
	$form = new Form($db);

	$formquestion['text'] = '<textarea></textarea>';
	$formconfirm = getFormConfirm($form, $object, $action, $formquestion);
	if (!empty($formconfirm)) echo $formconfirm;

	$TBS=new TTemplateTBS();
	$TBS->TBS->protect=false;
	$TBS->TBS->noerr=true;

	echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_importpayment');

	$step = GETPOST('step', 'int');
	if (empty($step)) $step = 1;
	
	ob_start();
	$form->select_types_paiements('', 'fk_c_paiement');
	$selectPaymentMode = ob_get_clean();
	
	ob_start();
	$form->select_comptes('', 'fk_bank_account');
	$selectAccountToCredit = ob_get_clean();
	
	print $TBS->render('tpl/card.tpl.php'
		,array() // Block
		,array(
			'object'=>$object
			,'view' => array(
				'action' => 'save'
				,'step' => $step
				,'urlcard' => dol_buildpath('/importpayment/card.php', 1)
				,'showInputFile' => $formcore->fichier('', 'paymentfile', '', $conf->global->MAIN_UPLOAD_DOC)
				,'showInputPaymentDate' => $form->select_date('', 'p', 0, 0, 0, '', 1, 1, 1)
				,'showInputPaymentMode' => $selectPaymentMode
				,'showInputAccountToCredit' => $selectAccountToCredit
			)
			,'langs' => $langs
		)
	);

	echo $formcore->end_form();

	llxFooter();
	$db->close();
}
