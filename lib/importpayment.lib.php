<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/importpayment.lib.php
 *	\ingroup	importpayment
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function importpaymentAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("importpayment@importpayment");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/importpayment/admin/importpayment_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/importpayment/admin/importpayment_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@importpayment:/importpayment/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@importpayment:/importpayment/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'importpayment');

    return $head;
}

/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	TImportPayment	$object		Object company shown
 * @return 	array				Array of tabs
 */
function importpayment_prepare_head(TImportPayment $object)
{
    global $db, $langs, $conf, $user;
    $h = 0;
    $head = array();
    $head[$h][0] = dol_buildpath('/importpayment/card.php', 1);
    $head[$h][1] = $langs->trans("ImportPaymentCard");
    $head[$h][2] = 'card';
    $h++;
	
	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@importpayment:/importpayment/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@importpayment:/importpayment/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'importpayment');
	
	return $head;
}

function getFormConfirmImportPayment(&$form, &$object, $action, $formquestion)
{
    global $langs,$conf,$user;

    $formconfirm = '';

    if ($action == 'import' && !empty($user->rights->facture->paiement) && !empty($user->rights->importpayment->import))
    {
        $text = $langs->trans('ConfirmValidateImportPayment', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'], $langs->trans('ConfirmImportPayment'), $text, 'confirm_import', $formquestion, 0, 1);
    }

    return $formconfirm;
}


function checkFieldsRequiredUse(&$TField, &$TFieldRequired)
{
	
	foreach ($TFieldRequired as $k => $v)
	{
		if (!in_array($k, $TField)) return false;
	}
	
	return true;
}