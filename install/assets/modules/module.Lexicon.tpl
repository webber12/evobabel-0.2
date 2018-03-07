//<?
/**
 * evoBabelLexicon
 * 
 * manage Lexicon
 * 
 * @author      webber (web-ber12@yandex.ru)
 * @category    module
 * @version     0.21
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @guid evobabels
 * @internal    @properties &lang_template_id=id шаблона языка;text;&rel_tv_id=id TV языковых связей;text;&currlang=язык по умолчанию;text;ru&show_panel=Показывать панель;text;1&publish=Публиковать (0 -нет, 1 - да);text;0&translate_lang=язык переводов;text;ru
 * @internal    @modx_category MultiLang
 * @internal    @installset base, sample
 */

$actions_path = MODX_BASE_URL . 'assets/snippets/evoBabel/lexicon/actions.php';
$lexicon_path = MODX_BASE_URL . 'assets/snippets/evoBabel/lexicon/';
$theme = $modx->config['manager_theme'];
//подгружаем язык
if (is_file (MODX_BASE_PATH . 'assets/snippets/evoBabel/lang/' . $translate_lang . '.php')) {
    include_once(MODX_BASE_PATH . 'assets/snippets/evoBabel/lang/' . $translate_lang . '.php');
} else {
    include(MODX_BASE_PATH . 'assets/snippets/evoBabel/lang/ru.php');
}


$sql="
CREATE TABLE IF NOT EXISTS " . $modx->getFullTableName('lexicon') . " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `ru` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
$q=$modx->db->query($sql);

if(isset($_POST['newlang'])&&$_POST['newlang'] != ''){
    $newlang = $modx->db->escape($_POST['newlang']);
    $q = $modx->db->query("ALTER TABLE " . $modx->getFullTableName('lexicon') . " ADD `" . $newlang . "` varchar(255)");
}
if(isset($_POST['del_lang']) && is_array($_POST['del_lang'])){
    $del_lang = $_POST['del_lang'];
    foreach ($del_lang as $k) {
        $q = $modx->db->query("ALTER TABLE " . $modx->getFullTableName('lexicon') . " DROP `" . $k . "`");
    }
}

//получаем названия колонок
$columns = '';
$lang = '';
$q = $modx->db->query("SELECT * FROM " . $modx->getFullTableName('lexicon') . " LIMIT 0,1");
$cols = $modx->db->getColumnNames($q);
for( $i = 0; $i < count( $cols ); $i++ ) { 
    if($cols[$i] != 'name') {
        if($cols[$i] == 'id') {
            $columns .= '<th field="' . $cols[$i] . '" width="50" editor="{}">' . $cols[$i] . '</th> ';
        }
        else{
            $columns .= '<th field="' . $cols[$i] . '" width="50" editor="{type:\'validatebox\',options:{}}">' . $cols[$i] . '</th> ';
            $langs .= '<div><input type="checkbox" name="del_lang[]" value="' . $cols[$i] . '"> ' . $cols[$i] . '</div>';
        }
    }
}


$output=<<<OUT
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>EVO-LEXICONS</title>
    <link rel="stylesheet" type="text/css" href="media/style/{$theme}/style.css" />
    <link rel="stylesheet" type="text/css" href="{$lexicon_path}jquery-easyui-1.5.3/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="{$lexicon_path}jquery-easyui-1.5.3/themes/icon.css">
    <link rel="stylesheet" type="text/css" href="{$lexicon_path}jquery-easyui-1.5.3/demo/demo.css">
    <style>
        body{padding-top:0;}
        h1{padding:0.8rem 0;}
        h1 .fa{color:#39515D;}
        .panel-header{background:#e9e9e9;}
        .panel-title{color:#333;text-transform:uppercase;height:auto;padding:5px;font-weight:400;font-size:14px;}
        .panel-header, .panel-body{border-color:#dddddd;}
        .datagrid-toolbar{padding-top:5px;padding-bottom:5px;background:#ffffff;}
        .datagrid-row{color: rgba(0, 0, 0, 0.9);}
        .datagrid-row-over{background-color: rgba(93, 109, 202, 0.16);}
        .datagrid-row-selected{background-color: rgba(93, 109, 202, 0.16);}
    </style>
    <script type="text/javascript" src="{$lexicon_path}jquery-easyui-1.5.3/jquery.min.js"></script>
    <script type="text/javascript" src="{$lexicon_path}jquery-easyui-1.5.3/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="{$lexicon_path}jquery-easyui-1.5.3/plugins/jquery.edatagrid.js"></script>
    <script type="text/javascript" src="{$lexicon_path}datagrid-filter/datagrid-filter.js"></script>
    <script type="text/javascript" src="{$lexicon_path}jquery-easyui-1.5.3/locale/easyui-lang-ru.js"></script>
</head>
<body>
<h1><i class="fa fa-pencil-square-o"></i>EVO-LEXICONS</h1>

<div class="table" style="width:100%;">
    <table id="dg" title="{$_eb_lang['translation_management']}" style="min-width:750px;width:auto;height:500px"
            toolbar="#toolbar" pagination="false" idField="id"
            rownumbers="true" fitColumns="true" singleSelect="true">
        <thead>
            <tr>
                <th field="name" width="50" editor="{type:'validatebox',options:{required:true}}">{$_eb_lang['param_name']}</th>
                {$columns}
            </tr>
        </thead>
    </table>
</div>
                
    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="javascript:$('#dg').edatagrid('addRow')"><i class="fa fa-plus-square" aria-hidden="true" style="color:#337ab7;"></i> {$_eb_lang['create']}</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="javascript:$('#dg').edatagrid('saveRow')"><i class="fa fa-floppy-o" aria-hidden="true" style="color:#5cb85c;"></i> {$_eb_lang['save']}</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="javascript:$('#dg').edatagrid('cancelRow')"><i class="fa fa-undo" aria-hidden="true"></i> {$_eb_lang['cancel']}</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="javascript:$('#dg').edatagrid('destroyRow')"><i class="fa fa-trash-o" aria-hidden="true" style="color:#e77755;"></i> {$_eb_lang['delete']}</a>
    </div>
    <script type="text/javascript">
        $(function(){
            var dg = $('#dg').edatagrid({
                url: '{$actions_path}?action=get',
                saveUrl: '{$actions_path}?action=save',
                updateUrl: '{$actions_path}?action=update',
                destroyUrl: '{$actions_path}?action=destroy'
            });

            dg.edatagrid('enableFilter');

        });
    </script>
    <div class="create" style="padding:10px 0;">
        <p style="text-transform:uppercase;"><b>{$_eb_lang['create_new_language']}</b></p>
        <form action="" method="post" id="lang_form">
            <div>
                <input type="text" name="newlang" value="" style="width:300px" placeholder="{$_eb_lang['new_language_name']}">
                <input type="submit" value="{$_eb_lang['create_new_language']}">
            </div>
        </form>
    </div>
    <hr>
    <p style="text-transform:uppercase;"><b>{$_eb_lang['available_languages']}</b></p>
    <form action="" method="post" id="del_form">
        <div>{$langs}<br><input type="submit" value="{$_eb_lang['delete_languages']}"></div>
    </form>

</body>
</html>

OUT;
echo $output;
