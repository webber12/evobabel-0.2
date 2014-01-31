//<?php
/**
 * lang
 * 
 * MultiLang output lexicon
 *
 * @author	    webber (web-ber12@yandex.ru)
 * @category 	snippet
 * @version 	0.2
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category MultiLang
 * @internal    @installset base, sample
 */


//использование в шаблонах чанках и т.п.
// [[lang? &a=`Главная страница`]] либо просто [%Главная страница%] если установлен плагин evoBabelPlaceholder
// использование в сниппетах 
// [[DocLister? &parents=`[[lang? &a=`Папка каталог`]]` ...другие параметры ..]]
// доступны плейсхолдеры вида [%Папка каталог%] - в шаблонах и чанках  если установлен плагин evoBabelPlaceholder

if(!is_scalar($a)) $a = null;
if(!is_scalar($currlang)) $currlang = null;
$out = (!empty($a) && isset($_SESSION['evoBabel_curLang'], $_SESSION['perevod'][$a]) && $_SESSION['evoBabel_curLang']==$currlang) ? $_SESSION['perevod'][$a] : null;
if(!empty($a) && !empty($currlang) && is_null($out)){
	$q = $modx->db->query("SELECT * FROM " . $modx->getFullTableName('lexicon')." WHERE name='".$modx->db->escape($a)."' LIMIT 1");
	$row = $modx->db->getRow($q);
	if(isset($row[$currlang])){
		$out = $row[$currlang];
		//evoBabel_curLang не сохраняем!
		if(empty($_SESSION['evoBabel_curLang']) || $currlang == $_SESSION['evoBabel_curLang']){
			$_SESSION['perevod'][$a] = $out;
		}
	}
}
return $out;