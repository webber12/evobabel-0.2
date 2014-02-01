//<?
/**
 * evoBabelLexicon
 * 
 * manage Lexicon
 * 
 * @author	    webber (web-ber12@yandex.ru)
 * @category	module
 * @version 	0.2
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@guid evobabels
 * @internal    @properties &lang_template_id=id шаблона языка;text; &rel_tv_id=id TV языковых связей;text; &currlang=язык по умолчанию;text;ru  &show_panel=Показывать панель;text;1
 * @internal	@modx_category MultiLang
 * @internal    @installset base, sample
 */

$actions_path=MODX_BASE_URL.'assets/snippets/evoBabel/lexicon/actions.php';
$lexicon_path=MODX_BASE_URL.'assets/snippets/evoBabel/lexicon/';
$theme=$modx->config['manager_theme'];


$sql="
CREATE TABLE IF NOT EXISTS ".$modx->getFullTableName('lexicon')." (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `ru` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
$q=$modx->db->query($sql);

if(isset($_POST['newlang'])&&$_POST['newlang']!=''){
	$newlang=$modx->db->escape($_POST['newlang']);
	$q=$modx->db->query("ALTER TABLE ".$modx->getFullTableName('lexicon')." ADD `".$newlang."` varchar(255)");
}
if(isset($_POST['del_lang'])&&is_array($_POST['del_lang'])){
	$del_lang=$_POST['del_lang'];
	foreach($del_lang as $k){
		$q=$modx->db->query("ALTER TABLE ".$modx->getFullTableName('lexicon')." DROP `".$k."`");
	}
}

//получаем названия колонок
$columns='';
$lang='';
$q=$modx->db->query("SELECT * FROM ".$modx->getFullTableName('lexicon')." LIMIT 0,1");
$cols = $modx->db->getColumnNames($q);
for( $i = 0; $i < count( $cols ); $i++ ) { 
	if($cols[$i]!='name'){
		if($cols[$i]=='id'){
			$columns .= '<th field="'.$cols[$i].'" width="50" editor="{}">'.$cols[$i].'</th> ';
		}
		else{
			$columns .= '<th field="'.$cols[$i].'" width="50" editor="{type:\'validatebox\',options:{}}">'.$cols[$i].'</th> ';
			$langs.='<div><input type="checkbox" name="del_lang[]" value="'.$cols[$i].'"> '.$cols[$i].'</div>';
		}
	}
}


$output=<<<OUT
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>MODx EVO Lexicons</title>
	<link rel="stylesheet" type="text/css" href="media/style/{$theme}/style.css" />
	<link rel="stylesheet" type="text/css" href="{$lexicon_path}jquery-easyui-1.3.4/themes/default/easyui.css">
	<link rel="stylesheet" type="text/css" href="{$lexicon_path}jquery-easyui-1.3.4/themes/icon.css">
	<link rel="stylesheet" type="text/css" href="{$lexicon_path}jquery-easyui-1.3.4/demo/demo.css">
	<script type="text/javascript" src="{$lexicon_path}jquery-easyui-1.3.4/jquery.min.js"></script>
	<script type="text/javascript" src="{$lexicon_path}jquery-easyui-1.3.4/jquery.easyui.min.js"></script>
	<script type="text/javascript" src="{$lexicon_path}jquery-easyui-1.3.4/plugins/jquery.edatagrid.js"></script>
	<script type="text/javascript" src="{$lexicon_path}datagrid-filter/datagrid-filter.js"></script>
	<script type="text/javascript" src="{$lexicon_path}jquery-easyui-1.3.4/locale/easyui-lang-ru.js"></script>
</head>
<body>

<div class="create" style="padding:10px 0;">
	<form action="" method="post" id="lang_form">
		<div>имя языка (совпадает с alias папки языка) <input type="text" name="newlang" value=""> <input type="submit" value="Создать новый язык"></div>
	</form>
</div>
	<div class="table" style="width:100%;">
    <table id="dg" title="Управление языковыми переводами" style="width:750px;height:500px"
            toolbar="#toolbar" pagination="false" idField="id"
            rownumbers="true" fitColumns="true" singleSelect="true">
        <thead>
            <tr>
                <th field="name" width="50" editor="{type:'validatebox',options:{required:true}}">Имя параметра</th>
				{$columns}
            </tr>
        </thead>
    </table>
</div>
				
    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="javascript:$('#dg').edatagrid('addRow')">Создать</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="javascript:$('#dg').edatagrid('destroyRow')">Удалить</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="javascript:$('#dg').edatagrid('saveRow')">Сохранить</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#dg').edatagrid('cancelRow')">Отменить</a>
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
	<p>&nbsp;</p>
	<p><b>Доступные языки</b></p>
	<form action="" method="post" id="del_form">
		<div>{$langs}<input type="submit" value="Удалить языки"></div>
	</form>

</body>
</html>

OUT;
echo $output;