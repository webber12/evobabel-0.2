//<?php
/**
 * alternate
 * 
 * MultiLang alternate links
 *
 * @author	    webber (web-ber12@yandex.ru)
 * @category 	snippet
 * @version 	0.21
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category MultiLang
 * @internal    @installset base, sample
 */
 
// используется для вывода ссылок на языковые версии аналогичных страниц
// https://support.google.com/webmasters/answer/189077?hl=ru
// https://support.google.com/webmasters/answer/189077?hl=en
// внести в модуль лексиконов строку hreflang для каждого языка (uk-ua, en-ua и т.п.)
// не забыть про необходимость заполнения там же строки Язык с алиасами языков
// вызывать в header в виде [[alternate? &use_default=`1` &default_lang=`ru` &tv=`[*relation*]`]] - чтобы указывать язык по умолчанию
// либо
// [[alternate? &tv=`[*relation*]`]] - если не требуется дефолтный язык

$out = '';
$curr_lang = $modx->runSnippet("lang", array("a" => "Язык"));
$curr_lang = !empty($curr_lang) ? $curr_lang : 'ru';
$default_lang = isset($default_lang) ? $default_lang : 'ru';
$use_default = isset($use_default) && $use_default == '1' ? true : false;

$langs = array();
if ($tv && !empty($tv)) {
    $tmp = explode("||", $tv);
    foreach($tmp as $k => $v) {
        $tmp2 = explode(":", $v);
        $langs[$tmp2[0]] = $tmp2[1];
    }
}
$default_link = '';
$alter_link = '';
if (!empty($langs)) {
    foreach ($langs as $lang => $docid) {
        if ($use_default && $default_lang == $lang) {
            $default_link .= '<link rel="alternate" hreflang="x-default" href="' . $modx->makeUrl($docid) . '" />';
        } else {
            $href_lang = $modx->runSnippet("lang", array("a" => "hreflang", "id" => $lang));
            $href_lang = !empty($href_lang) ? $href_lang : $lang;
            $alter_link .= '<link rel="alternate" href="' . $modx->makeUrl($docid, '', '', 'full') . '" hreflang="' . $href_lang . '" />';
        }
    }
}
$out .= $default_link . $alter_link;
return $out;
