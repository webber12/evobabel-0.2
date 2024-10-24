<?php
//@author webber (web-ber12@yandex.ru)

//значения по умолчанию на вкладке Свойства - &lang_template_id=id шаблона языка;text;11
if (!defined('MODX_BASE_PATH')) {die('What are you doing? Get out of here!');}

$out = '';

if (isset($_REQUEST['id']) && (int) $_REQUEST['id'] != 0) {
    if (!empty($params['rel_tv_id']) && !empty($params['lang_template_id'])) {

        include_once 'vendor/autoload.php';
        $controller = new EvoBabel\Controllers\EvoBabelController($modx, (int) $_REQUEST['id'], $params);

        /*****************создаем версии********************/
        if (isset($_GET['ebabel']) && (int) $_GET['ebabel'] != 0 && isset($_GET['parent']) && (int) $_GET['parent'] != 0) {
            $res = $controller->makeVersion();
            echo $res; //возвращаем js для переадресации на текущую страницу
        }
        /*********************** конец создания версий ****************/

        // получаем отформатированный список связей для вывода
        $out .= $controller->showRelations();
    } else {
        $out .= $_eb_lang['relation_tv_not_defined'];
    }
} else {
    $out .= '<span id="eb_relations_tv"></span>' . '<script>var eb_langs = {};</script>';
}
$out .= "<!-- evoBabel start -->
    <style>
        .eb_dropdown{position:relative;float:left;}
        .eb_dropdown label{text-transform:uppercase;padding-right:10px;margin:0;background:#337ab7!important;border-right-color:#337ab7!important;border-left-color:#337ab7!important;color:#ffffff!important;}
        .eb_dropdown label:hover{background:#285e8d!important;color:#ffffff;}
        .eb_dropdown label::after{font-family:'Font Awesome 5 Free';content:'\\f107';margin-left:5px;font-weight: 900;}
        .eb_dropdown input[type='checkbox']{display:none;}
        .eb_dropdown input#eb_checkbox:checked + .dropdown-menu {display: block;width:auto !important;}
        .eb_dropdown .eb_show{}
        .eb_dropdown .btn-block{text-transform:uppercase;}
    </style>";
$out .= '<script type="text/javascript" src="' . MODX_BASE_URL . 'assets/snippets/evoBabel/script.js?20241011"></script><!-- evoBabel end -->';
echo $out;
