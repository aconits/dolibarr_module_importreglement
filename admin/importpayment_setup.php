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
 * 	\file		admin/importpayment.php
 * 	\ingroup	importpayment
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/importpayment.lib.php';
dol_include_once('/importpayment/class/importpayment.class.php');

// Translations
$langs->load("importpayment@importpayment");
$langs->load("admin");
$langs->load('bills');

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
	$code=$reg[1];
	$value=GETPOST($code);
	if ($code === 'IMPORTPAYMENT_TFIELD_ORDER') $value=serialize(GETPOST('TField'));
	
	if (dolibarr_set_const($db, $code, $value, 'chaine', 0, '', $conf->entity) > 0)
	{
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}
	
if (preg_match('/del_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_del_const($db, $code, 0) > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

/*
 * View
 */
$page_name = "ImportPaymentSetup";
$TJs = array('/importpayment/js/importpayment.js');
$TCss = array('/importpayment/css/importpayment.css');
llxHeader('', $langs->trans($page_name), '', '', 0, 0, $TJs, $TCss);

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = importpaymentAdminPrepareHead();
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("Module104741Name"),
    0,
    "importpayment@importpayment"
);

// Setup page goes here
$form=new Form($db);
$var=false;
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("IMPORTPAYMENT_ALLOW_OVERRIDE_CONF_ON_IMPORT").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'; // Keep form because ajax_constantonoff return single link with <a> if the js is disabled
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="set_IMPORTPAYMENT_ALLOW_OVERRIDE_CONF_ON_IMPORT">';
print ajax_constantonoff('IMPORTPAYMENT_ALLOW_OVERRIDE_CONF_ON_IMPORT');
print '</form>';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("IMPORTPAYMENT_DEFAULT_NB_INGORE").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="set_IMPORTPAYMENT_DEFAULT_NB_INGORE">';
print '<input type="text" name="IMPORTPAYMENT_DEFAULT_NB_INGORE" value="'.$conf->global->IMPORTPAYMENT_DEFAULT_NB_INGORE.'" size="5" />';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</form>';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("IMPORTPAYMENT_DEFAULT_DELIMITER").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="set_IMPORTPAYMENT_DEFAULT_DELIMITER">';
print '<input type="text" name="IMPORTPAYMENT_DEFAULT_DELIMITER" value="'.dol_escape_htmltag($conf->global->IMPORTPAYMENT_DEFAULT_DELIMITER).'" size="5" />';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</form>';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("IMPORTPAYMENT_DEFAULT_ENCLOSURE").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="set_IMPORTPAYMENT_DEFAULT_ENCLOSURE">';
print '<input type="text" name="IMPORTPAYMENT_DEFAULT_ENCLOSURE" value="'.dol_escape_htmltag($conf->global->IMPORTPAYMENT_DEFAULT_ENCLOSURE).'" size="5" />';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</form>';
print '</td></tr>';


print '</table>';

$TFieldPossible = TImportPayment::getTFieldPossible();
$TFieldRequired = TImportPayment::getTFieldRequired();
$TFieldOptional = TImportPayment::getTFieldOptional();

$TField = TImportPayment::getTFieldOrder();

print '<br />';
print '<div class="underbanner clearboth"></div>';
print '<div class="titre">'.$langs->trans("ColumnsOrder").'</div>';

if (!checkFieldsRequiredUse($TField, $TFieldRequired))
{
	print '<div class="error">'.$langs->trans('ImportPaymentErrorOneOrMoreFieldsRequiredNotUsed', implode(', ', $TFieldRequired)).'</div>';
}

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="action" value="set_IMPORTPAYMENT_TFIELD_ORDER">';

print '<fieldset>';
print '<legend>'.$langs->trans('ImportPaymentFieldsOrder').'</legend>';
print '<ul id="columns_order" class="inline sortable">';
foreach ($TField as $field)
{
	print '<li '.((!empty($TFieldRequired[$field])) ? 'class="fieldrequired" ' : '').'data-field="'.$field.'">';
	print '<input type="hidden" name="TField[]" value="'.$field.'" />';
	print '<span class="grip" style="background-image: url(\''.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/grip.png\'); background-repeat: no-repeat; background-position: center center;">&nbsp;</span>';
	print $TFieldPossible[$field];
	print img_picto($langs->trans('Delete'), 'delete', 'onclick="removeElementPI(this);" class="remove"');
	print '</li>';
}
print '</ul>';

print '<div class="center"><input class="button" name="bouton" value="'.$langs->trans('Save').'" type="submit"></div>';
print '</fieldset>';

print '</form>';


print '<fieldset>';
print '<legend>'.$langs->trans('ImportPaymentFieldsAvailable').'</legend>';
print '<ul id="columns_available" class="inline sortable">';
foreach ($TFieldPossible as $field => $label)
{
	print '<li '.((!empty($TFieldRequired[$field])) ? 'class="fieldrequired" ' : '').'data-field="'.$field.'">';
	print '<input type="hidden" name="TField[]" value="'.$field.'" />';
	print '<span class="grip" style="background-image: url(\''.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/grip.png\'); background-repeat: no-repeat; background-position: center center;">&nbsp;</span>';
	print $label;
	print img_picto($langs->trans('Delete'), 'delete', 'onclick="removeElementPI(this);" class="remove"');
	print '</li>';
}
print '</ul>';
print '</fieldset>';

print '<script type="text/javascript"> IMPORPAYMENT_TFIELD = '.json_encode($form->selectarray('TField[]', $TField, '', 0, 0, 0, '', 0, 0, 0, '', '', 0, '', 0, 1)).'; </script>';

llxFooter();

$db->close();