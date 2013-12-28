<?php
//@author webber (web-ber12@yandex.ru)

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

	define('MODX_API_MODE', true);
	include_once("../../../../index.php");
	$modx->db->connect();
	if (empty($modx->config)) {
		$modx->getSettings();
	}

	//работа с данными - удаление, сохранение, обновление
	if(isset($_GET['action'])){
		$action=$_GET['action'];
		switch ($action){
			case 'get':
				$q=$modx->db->query("SELECT * FROM ".$modx->getFullTableName('lexicon')." ORDER BY name ASC");
				$list = $modx->db->makeArray($q);
				$a=json_encode($list);
				echo $a;
			break;
			
			case 'save':
				foreach($_POST as $k=>$v){
					if($k!='isNewRecord'){
						$fields[$k]=$modx->db->escape($v);
					}
				}
				if($_POST['isNewRecord']){
					$modx->db->insert($fields,$modx->getFullTableName('lexicon'));
				}
			break;
			
			case 'update':
				foreach($_POST as $k=>$v){
					if($k=='id'){$id=(int)$v;}
					else{
						$fields[$k]=$modx->db->escape($v);
					}
				}
				if($id&&$id!=0){
					$modx->db->update($fields,$modx->getFullTableName('lexicon'),'id='.$id);
				}
			break;
			
			case 'destroy':
				$out='';
				foreach($_POST as $k=>$v){
					if($k=='id'){$id=(int)$v;}
				}
				if($id&&$id!=0){
					$modx->db->delete($modx->getFullTableName('lexicon'),'id='.$id);
				}
			break;
			
			default:
			break;
		}
	}	

exit;
}


?>