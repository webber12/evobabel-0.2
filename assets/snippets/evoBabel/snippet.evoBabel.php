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
echo '<div id="eB_relations">' . $out . '</div>';
if (isset($params['show_panel']) && $params['show_panel'] == '1') {
    echo '<script type="text/javascript" src="' . MODX_BASE_URL . 'assets/snippets/evoBabel/script.js"></script>';
}
