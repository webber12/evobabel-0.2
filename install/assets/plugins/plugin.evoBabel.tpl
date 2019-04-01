/**
 * evoBabel
 *
 * plugin for work evoBabel
 *
 * @author	    webber (web-ber12@yandex.ru)
 * @category	plugin
 * @version	    0.3
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @events OnPageNotFound,OnDocFormSave,OnBeforeEmptyTrash,OnEmptyTrash,OnWebPageInit,OnDocDuplicate
 * @internal    @properties &synch_TV=ids TV для синхронизации;text;&synch_template=ids шаблонов для синхронизации;text;&config=Файл шаблонов;text;assets/snippets/evoBabel/config/config.php
 * @internal    @installset MultiLang
 * @internal	@modx_category MultiLang
 */

if (!defined('MODX_BASE_PATH')) {
    die ('What are you doing? Get out of here!');
}

define('CFG', $config);

$e = & $modx->event;

$content_table = $modx->getFullTableName('site_content');
$tvs_table = $modx->getFullTableName('site_tmplvar_contentvalues');

if (isset($params['rel_tv_id']) && isset($params['lang_template_id'])) {
    include_once MODX_BASE_PATH . 'assets/snippets/evoBabel/evoBabel.class.php';
    $eB=new evoBabel($modx, 0, $params);

	function evoBabelInit($cid, $eB) {
		global $modx;
           // в нужном месте прописываем [+activeLang+] (вывод текущего языка) и [+switchLang+] - вывод переключалки (списка) языков
           // параметры вызова
           // &activeLang - шаблон вывода текущего языка (отдельно)
           // &activeRow - шаблон вывода текущего языка в списке языков
           // &unactiveRow - шаблон вывода языков в списке (кроме текущего)
           // &langOuter - шаблон обертки для списка языков

           //шаблоны вывода по умолчанию
           if(!empty(CFG) && file_exists(MODX_BASE_PATH . CFG)){
           	include_once (MODX_BASE_PATH . CFG);
           }
           //активный язык отдельно
           $activeLang = isset($activeLang) ? $activeLang : '<div id="curr_lang"><img src="assets/snippets/evoBabel/config/images/lang_[+alias+].png"> <a href="javascript:;">[+name+]</a></div>';
           //активный язык в списке
           $activeRow = isset($activeRow) ? $activeRow : '<div class="active"><img src="assets/snippets/evoBabel/config/images/lang_[+alias+].png"> &nbsp;<a href="[+url+]">[+name+]</a></div>';
           //неактивный язык списка
           $unactiveRow = isset($unactiveRow) ? $unactiveRow : '<div><img src="assets/snippets/evoBabel/config/images/langs/lang_[+alias+].png"> &nbsp;<a href="[+url+]">[+name+]</a></div>';
           //обертка списка языков
           $langOuter = isset($langOuter) ? $langOuter : '<div class="other_langs">[+wrapper+]</div>';

           $out = '';
           $langs = array();
           $others = array();//массив других языков (кроме текущего)
           $eB->id = $cid;
           $siteAllLangs = $eB->langs;
           $siteLangs = $eB->getSiteLangs($eB->lang_template_id);
           /*$siteAllLangs = $eB->getAllSiteLangs($eB->lang_template_id);*/
           //если находимся в корневой папке языка, отправляем на главную страницу этого языка (при условии, что она задана и отличается от текущей)
           if (isset($siteLangs[$eB->id]) && $siteLangs[$eB->id]['home'] != '' && (int)$siteLangs[$eB->id]['home'] != 0 && $siteLangs[$eB->id]['home'] != $eB->id) {
               $modx->sendRedirect($modx->makeUrl((int)$siteLangs[$eB->id]['home']));
           }
           $curr_lang_id = $eB->getCurLangId($eB->id);
           if (empty($curr_lang_id)) {//не смогли найти язык
               if (isset($_SESSION['curr_lang_id'])) {//есть предыдущий, берем его
                   $curr_lang_id = $_SESSION['curr_lang_id'];
               } else {//нет предыдущего, берем язык "домашней страницы"
                   $curr_lang_id = $eB->getCurLangId($modx->config['site_start']);
                   $_SESSION['curr_lang_id'] = $curr_lang_id;
               }
           } else {
               $_SESSION['curr_lang_id'] = $curr_lang_id;
           }
           $relations = $eB->getRelations($eB->id);
           $relArray = $eB->getRelationsArray($relations);

           //устанавливаем текущий язык
           $currLang = str_replace(array('[+alias+]', '[+name+]', '[+lang+]'), array($siteLangs[$curr_lang_id]['alias'], $siteLangs[$curr_lang_id]['name'], $siteLangs[$curr_lang_id]['lang']), $activeLang);

           //устанавливаем список языков с учетом активного
           $langRows = '';

           foreach ($siteLangs as $k=>$v) {
               $tpl = ($k != $curr_lang_id ? $unactiveRow : $activeRow);
               if (isset($relArray[$v['alias']]) && $eB->checkActivePage($relArray[$v['alias']])) {//если есть связь и эта страница активна
                   $url = $relArray[$v['alias']];
               } else {//нет связи либо страница не активна -> проверяем родителя
                   $parent_id = $modx->db->getValue($modx->db->query("SELECT parent FROM {$eB->content_table} WHERE id={$eB->id} AND published=1 AND deleted=0 AND parent!=0 AND template!=$eB->lang_template_id"));
                   if (!$parent_id) {//если нет родителя, отправляем на главную страницу языка
                       $url = ((int)$v['home'] != 0 ? (int)$v['home'] : $k);
                   } else {//если родитель есть, проверяем его связи
                       $parent_relations = $eB->getRelations($parent_id);
                       $relParentArray = $eB->getRelationsArray($parent_relations);
                       if (isset($relParentArray[$v['alias']]) && $eB->checkActivePage($relParentArray[$v['alias']])) {//у родителя активная связь
                           $url = $relParentArray[$v['alias']];
                       } else {//иначе -> на главную страницу языка
                           $url = ((int)$v['home'] != 0 ? (int)$v['home'] : $k);
                       }
                   }
               }
               $langRows .= str_replace(array('[+alias+]', '[+url+]', '[+name+]', '[+lang+]'), array($v['alias'], $modx->makeUrl($url), $v['name'], $v['lang']), $tpl);
           }
           $langsList .= str_replace(array('[+wrapper+]'), array($langRows), $langOuter);

           // устанавливаем плейсхолдеры [+activeLang+] и [+switchLang+] для вывода активного языка и списка языков соответственно
           $modx->setPlaceholder("activeLang", $currLang);
           $modx->setPlaceholder("switchLang", $langsList);

           //получаем массив перевода для чанков в сессию
           $perevod = array();
           $cur_lexicon = $siteAllLangs[$curr_lang_id]['alias'];
           if($cur_lexicon == ''){
				$doc = $modx->getDocument($cid);
				$cur_lexicon = $doc['alias'];
			}
           $q = $modx->db->query("SELECT * FROM " . $modx->getFullTableName('lexicon'));
           while ($row = $modx->db->getRow($q)) {
               $perevod[$row['name']] = $row[$cur_lexicon];
           }
           $_SESSION['evoBabel_curLang'] = $cur_lexicon;
           $_SESSION['perevod'] = $perevod;

			// возвращаем 404 для указанных страниц в модуле evoBabelLexicon
			if ( $cid == $perevod['Страница не найдена'] ) {
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			}
	}

   switch ($e->name) {
       case 'OnPageNotFound'://переадресация на нужную страницу 404, указать ее в модуле лексикона
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			if($modx->config['friendly_urls'] != 1) break;
				// если значения в $_SESSION не установлены, то $docid берём из настроек
				if (isset($_SESSION['perevod'])) {
					$docid = (int)$_SESSION['perevod']['Страница не найдена'];
					if ($docid == 0) {
						$docid = (int)$_SESSION['perevod']['Главная страница'];
						if ($docid == 0) {
							$docid = (int)$_SESSION['perevod']['Корневая папка'];
						}
					}
				}
				if ($docid == 0) {
					//$modx->sendRedirect($modx->makeUrl($docid), 0, 'REDIRECT_HEADER', 'HTTP/1.0 404 Not Found');exit();
					$docid = !empty($modx->config['error_page']) ?  $modx->config['error_page'] : $modx->config['site_start'];
				}
				evoBabelInit($docid, $eB);
				$modx->sendForward($docid);
				exit();
           break ;
       case 'OnDocFormSave'://синхронизация выбранных TV на выбранном шаблоне
           if ($e->params['mode'] == 'upd' && (isset($synch_template) && $synch_template != '') && (isset($synch_TV) && $synch_TV != '')) {
               $eB->synchTVs($synch_TV, $synch_template, $e->params['id']);
           }
           break;
       case 'OnBeforeEmptyTrash': //собираем связи окончательно удаляемых ресурсов, чтобы потом скорректировать их связанные версии
           if (isset($ids) && is_array($ids)) {
               $del_ids = implode(',', $ids);
               $del_array = $eB->makeDelRelsArray($del_ids);
               $_SESSION['del_array'] = $del_array;
           }
           break;
       case 'OnEmptyTrash': //корректируем связи языковых версий с учетом окончательного удаления ресурсов
           $del_array = $_SESSION['del_array'];
           if (!empty($del_array)) {
               $eB->updateDeletedRelations($del_array);
           }
           break;
       case 'OnWebPageInit':
				$cid = $modx->documentIdentifier;
				evoBabelInit($cid, $eB);
           break;
       case 'OnDocDuplicate' :
           if ($e->params['new_id']) {
               $q = $modx->db->query("DELETE FROM " . $eB->tvs_table . " WHERE contentid={$e->params['new_id']} AND tmplvarid={$eB->rel_tv_id}");
           }
           break;
       default:
           return ;
   }
}
