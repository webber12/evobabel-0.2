<?php
//@author webber (web-ber12@yandex.ru)

//значения по умолчанию на вкладке Свойства - &lang_template_id=id шаблона языка;text;11
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

$out='';

if(isset($_GET['id'])&&(int)$_GET['id']!=0){
	if(isset($params['rel_tv_id'])&&isset($params['lang_template_id'])){	
		include_once('evoBabel.class.php');
		$eB=new evoBabel($modx,(int)$_GET['id'],$params);

		/*****************создаем версии********************/
		if(isset($_GET['ebabel'])&&(int)$_GET['ebabel']!=0&&isset($_GET['parent'])&&(int)$_GET['parent']!=0){
			$res=$eB->makeVersion();
			echo $res;//возвращаем js для переадресации на текущую страницу
		}
		/*********************** конец создания версий ****************/

	
		// получаем отформатированный список связей для вывода
		$out.=$eB->showRelations();
	}
	else{
		$out.='Не задан id TV для хранения языковых связей либо id шаблона языка в настройках сниппета';
	}
}
echo $out;
?>