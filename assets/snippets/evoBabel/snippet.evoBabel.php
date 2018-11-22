<?php
//@author webber (web-ber12@yandex.ru)

//значения по умолчанию на вкладке Свойства - &lang_template_id=id шаблона языка;text;11
if (!defined('MODX_BASE_PATH')) {die('What are you doing? Get out of here!');}

$out = '';

if (isset($_REQUEST['id']) && (int)$_REQUEST['id'] != 0) {
    if (isset($params['rel_tv_id']) && isset($params['lang_template_id'])) {	
        include_once('evoBabel.class.php');
        $eB = new evoBabel($modx, (int)$_REQUEST['id'], $params);

        /*****************создаем версии********************/
        if (isset($_GET['ebabel']) && (int)$_GET['ebabel'] != 0 && isset($_GET['parent']) && (int)$_GET['parent'] != 0) {
            $res = $eB->makeVersion();
            echo $res;//возвращаем js для переадресации на текущую страницу
        }
        /*********************** конец создания версий ****************/


        // получаем отформатированный список связей для вывода
        $out .= $eB->showRelations();
    } else {
        $out .= $_eb_lang['relation_tv_not_defined'];
    }
}
$out .= "<!-- evoBabel start--><style>#eb_seletor{background-color:#dfdfdf;cursor:pointer;color:#464a4c;}.darkness #eb_seletor{background-color:#202329;color:#bbbbbb;}</style>";
$out .= '<script type="text/javascript" src="' . MODX_BASE_URL . 'assets/snippets/evoBabel/script.js"></script><!-- evoBabel end-->';
echo $out;
