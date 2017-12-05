<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');

}


// uncomment


dol_include_once('/importreglement/class/importreglement.class.php');

$PDOdb=new TPDOdb;

$o=new TImportReglement;
$o->init_db_by_vars($PDOdb);
