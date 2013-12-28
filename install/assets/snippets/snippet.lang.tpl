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


$perevod=$_SESSION['perevod'];
return $perevod[$a];
