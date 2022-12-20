<?php
namespace EvoBabel\Controllers;

//@author webber (web-ber12@yandex.ru)

class EvoBabelController
{

    public $modx;
    public $id; //id текущего ресурса
    public $content_table;
    public $tvs_table;
    public $rel_tv_id;
    public $lang_template_id;
    public $version_lang_id;
    public $version_parent_id;
    public $langs = [];
    public $params = [];
    public $topid;
    public $iconfolder;
    public $theme;
    public $language = '';

    public function __construct($modx, $id, $params)
    {
        $this->modx = $modx;
        $this->id = $this->setId($id);
        $this->params = $params;
        $this->params['translate_lang'] = $params['translate_lang'] ?? 'ru';
        $this->loadLangFile($this->params['translate_lang']);
        $this->content_table = $this->modx->getFullTableName('site_content');
        $this->tvs_table = $this->modx->getFullTableName('site_tmplvar_contentvalues');
        $this->rel_tv_id = $params['rel_tv_id'];
        $this->lang_template_id = $params['lang_template_id'];
        $this->langs = $this->getAllSiteLangs($this->lang_template_id);
        $this->topid = $this->getCurLangId($this->id);
        $this->theme = $this->modx->config['manager_theme'];
        $this->iconfolder = "media/style/" . $this->theme . "/images/icons/";
    }

    private function escape($a)
    {
        return $this->modx->db->escape($a);
    }

    protected function clearCache($type = 'full', $report = false)
    {
        return $this->modx->clearCache($type, $report);
    }

    protected function getTVName($tvid)
    {
        return $this->modx->db->getValue("SELECT name FROM " . $this->modx->getFullTableName('site_tmplvars') . " WHERE id=" . $tvid . " LIMIT 0, 1");
    }

    //подключаем файл перевода
    protected function loadLangFile($file)
    {
        if (is_file(dirname(__FILE__) . '/../../lang/' . $file . '.php')) {
            include(dirname(__FILE__) . '/../../lang/' . $file . '.php');
        } else {
            include(dirname(__FILE__) . '/../../lang/ru.php');
        }
        $this->eb_lang = $_eb_lang;
    }

    //оставляем в "списках через запятую" только цифры и удаляем лишние пробелы
    protected function checkNumberString($string)
    {
        $tmp = array_map('trim', explode(',', trim($string)));
        $tmp2 = [];
        if (is_array($tmp)) {
            foreach ($tmp as $k => $v) {
                $v = (int)$v;
                if ($v != 0) {
                    $tmp2[$k] = $v;
                }
            }
            if (!empty($tmp2)) {
                return implode(',', $tmp2);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //************* content functions
    protected function saveTV($contentid, $tvid, $tvval)
    {
        $isset = $this->modx->db->getRecordCount($this->modx->db->query("SELECT value FROM " . $this->tvs_table . " WHERE contentid=" . $contentid . " AND tmplvarid=" . $tvid . " LIMIT 0, 1"));
        if ($isset) {
            $this->modx->db->update([ 'value' => $tvval ], $this->tvs_table, "contentid=" . $contentid . " AND tmplvarid=" . $tvid);
        } else {
            $this->modx->db->insert([ 'contentid' => $contentid, 'tmplvarid' => $tvid, 'value' => $tvval ], $this->tvs_table);
        }
    }

    //or $type=ids tv comma separated
    protected function copyTVs($oldid, $newid, $type = 'full')
    {
        $where = '';
        if ($type != 'full') {
            $type = $this->checkNumberString($type);
            if ($type) {
                $where = ' AND tmplvarid IN(' . $type . ')';
            }
        }
        $sql = "SELECT * FROM " . $this->tvs_table . " WHERE contentid=" . $oldid . $where;
        $tvs = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($tvs)) {
            if ($row['tmplvarid'] != $this->rel_tv_id) {
                $this->saveTV($newid, $row['tmplvarid'], $this->escape($row['value']));
            }
        }
    }

    public function setId($id)
    {
        $docid = !empty($id) ? $id : (!empty($this->modx->documentIdentifier) ? $this->modx->documentIdentifier : 0);
        return $docid;
    }

    public function copyDoc($id, $newparent = false, $addzagol = false, $published = 0)
    {
        $new_id = false;
        $sql = "SELECT * FROM " . $this->content_table . " WHERE id=" . $id;
        $docrow = $this->modx->db->getRow($this->modx->db->query($sql));
        if (is_array($docrow)) {
            $tmp = [];
            foreach ($docrow as $k => $v) {
                if ($k != 'id') {
                    $tmp[$k] = $this->escape($v);
                }
            }
            if (!empty($tmp)) {
                $tmp['published'] = ($published != 0 ? 1 : 0);
                $tmp['parent'] = $newparent ? $newparent : $tmp['parent'];
                $tmp['pagetitle'] = $addzagol ? $tmp['pagetitle'] . ' (' . $addzagol . ')' : $tmp['pagetitle'];
                $new_id = $this->modx->db->insert($tmp, $this->content_table);
                if ($new_id) {
                    $isfolder = $this->modx->db->update([ 'isfolder' => '1' ], $this->content_table, 'isfolder=0 AND id=' . $tmp['parent']);
                    $tvs = $this->copyTVs($id, $new_id);
                    $groups = $this->copyDocGroups($id, $new_id);
                    $this->modx->clearCache('full');
                }
            }
        }
        return $new_id;
    }

    //****************** end content functions


    //проверка существования страницы
    public function checkPage($id)
    {
        return $this->modx->db->getValue("SELECT id FROM " . $this->content_table . " WHERE id={$id} LIMIT 0, 1");
    }

    //проверка существования страницы и активности страницы
    public function checkActivePage($id)
    {
        return $this->modx->db->getValue("SELECT id FROM " . $this->content_table . " WHERE id=" . $id . " AND deleted=0 AND published=1 LIMIT 0, 1");
    }

    //получаем активные языки сайта (опубликованные и неудаленные)
    public function getSiteLangs($lang_template_id)
    {
        $langs = [];
        if (!empty($this->langs)) {
            foreach ($this->langs as $k => $v) {
                if ($v['row']['published'] == 1 && $v['row']['deleted'] == 0) {
                    $langs[$k]['name'] = $v['name'];
                    $langs[$k]['alias'] = $v['alias'];
                }
            }
        }
        return $langs;
    }

    //получаем все языки сайта, в том числе удаленные и неопубликованные
    public function getAllSiteLangs($lang_template_id)
    {
        $langs = [];
        $q = $this->modx->db->query("SELECT * FROM " . $this->content_table . " WHERE parent=0 AND template=" . $lang_template_id . " ORDER BY menuindex ASC");
        while ($row = $this->modx->db->getRow($q)) {
            $langs[$row['id']]['name'] = $row['longtitle'];
            $langs[$row['id']]['alias'] = $row['alias'];
            $langs[$row['id']]['row'] = $row;
        }
        return $langs;
    }


    public function getParentIds($id, $height = 10)
    {
        $tmp = $this->modx->getParentIds($id, $height);
        return !empty($tmp) ? array(end($tmp)) : array($id);
    }

    public function getCurLangId($id)
    {
        $res = $this->getParentIds($id);
        return $res[0] ?? '';
    }


    //получаем строку отношений для ресурса
    public function getRelations($id)
    {
        return $this->modx->db->getValue("SELECT value FROM " . $this->tvs_table . " WHERE contentid=" . $id . " AND tmplvarid=" . $this->rel_tv_id . " LIMIT 0,1");
    }

    //array ['lang_alias']=>['lang_page_id']
    public function getRelationsArray($relations)
    {
        $arr = [];
        if ($relations != '') {
            $arr1 = explode("||", $relations);
            foreach ($arr1 as $k => $v) {
                if (isset($v) && $v != '') {
                    $arr2 = explode(":", $v);
                    $arr[$arr2[0]] = $arr2[1];
                }
            }
        }
        return $arr;
    }

    //полные отношения - недостающие заменяем на корневые языки
    public function getFullRelations($id, $langsArray)
    {
        if (!isset($langsArray[$id])) {
            $relations = $this->getRelations($id);
            $relationsArray = $this->getRelationsArray($relations);
            foreach ($langsArray as $k => $v) {
                if (!isset($relationsArray[$v['alias']])) {
                    $relationsArray[$v['alias']] = $k;
                }
            }
        } else {
            foreach ($langsArray as $k => $v) {
                $relationsArray[$v['alias']] = $k;
            }
        }
        return $relationsArray;
    }

    public function makeVersion()
    {
        $this->version_lang_id = (int)$_GET['ebabel'];
        $this->version_parent_id = (int)$_GET['parent'];
        //копируем ресурс вместе со всеми ТВ
        $new_id = $this->copyDoc($this->id, $this->version_parent_id, $this->langs[$this->version_lang_id]['name'], (int)$this->params['publish']);
        if ($new_id) {//если ресурс скопирован, создаем новые связи
            //проверяем старые связи
            $curr_rel = $this->getRelations($this->id);
            if (!$curr_rel || $curr_rel == '') {//если связи не было, то просто создаем новую
                $new_rel = $this->langs[$this->topid]['alias'] . ':' . $this->id . '||' . $this->langs[$this->version_lang_id]['alias'] . ':' . $new_id;
                $this->saveTV($this->id, $this->rel_tv_id, $new_rel);
                $this->saveTV($new_id, $this->rel_tv_id, $new_rel);
            } else {//если связь есть, то обновляем ее везде
                $rel_arr = $this->getRelationsArray($curr_rel);
                $new_rel = '';
                foreach ($this->langs as $k => $v) {
                    if (isset($rel_arr[$v['alias']]) && $this->checkPage($rel_arr[$v['alias']])) {//если страница старая
                        $new_rel .= $v['alias'] . ':' . $rel_arr[$v['alias']] . '||';
                    } elseif ($k == $this->version_lang_id) {
                        $new_rel .= $v['alias'] . ':' . $new_id . '||';
                    } else {

                    }
                }
                $new_rel = substr($new_rel, 0, -2);
                $rel_arr2 = $this->getRelationsArray($new_rel);
                foreach ($rel_arr2 as $k => $v) {
                    $this->saveTV($v, $this->rel_tv_id, $new_rel);
                }
            }
            $evtOut = $this->modx->invokeEvent('OnDocDuplicate', [
                'id' => $this->id,
                'new_id' => $new_id,
                'source' => 'evobabel'
            ]);
            return '<script type="text/javascript">location.href="index.php?a=27&id=' . $this->id . '"</script>';
        }
    }

    public function showRelations()
    {
        $out = '';
        $rel_rows = '';

        //id родительского ресурса и его полные связи
        $parent_id = $this->modx->db->getValue("SELECT parent FROM " . $this->content_table . " WHERE id=" . $this->id. " LIMIT 0,1");
        $parent_rels = $this->getFullRelations($parent_id, $this->langs);

        //получаем связь текущей страницы
        $relation = $this->getRelations($this->id);

        //json-список языков для формирования селекта в админке
        $json = [];

        //если связи есть, выводим их
        if ($relation) {
            $rels = $this->getRelationsArray($relation);
            foreach ($this->langs as $k => $v) {
                if ($k != $this->topid) {
                    if (isset($rels[$v['alias']]) && $this->checkPage($rels[$v['alias']])) {
                        $json[$rels[$v['alias']]]['url'] = "index.php?a=27&id=" . $rels[$v['alias']];
                        $json[$rels[$v['alias']]]['text'] = $v['alias'] . ' -  ' . $this->eb_lang['jump_version'];
                    } else {
                        $json[$parent_rels[$v['alias']]]['url'] = "index.php?a=27&id=" . $this->id . "&ebabel=" . $k . "&parent=" . $parent_rels[$v['alias']];
                        $json[$parent_rels[$v['alias']]]['text'] = $v['alias'] . ' - ' . $this->eb_lang['create_version'];
                    }
                }
            }
            //$rel_rows .= '<input type="hidden" name="tv' . $this->rel_tv_id . '" value="' . $relation . '">';
        } else {//если связей нет, то выводим ссылки на создание без проверок
            foreach ($this->langs as $k => $v) {
                if ($k != $this->topid) {
                    if ($parent_rels[$v['alias']] == $k && $k != $parent_id && !isset($this->langs[$parent_id])) {
                        $json[$parent_id]['url'] = "index.php?a=27&id=" . $parent_id;
                        $json[$parent_id]['text'] = $v['alias'] . ' - ' . $this->eb_lang['no_parent'];
                    } else {
                        $json[$parent_rels[$v['alias']]]['url'] = "index.php?a=27&id=" . $this->id . "&ebabel=" . $k . "&parent=" . $parent_rels[$v['alias']];
                        $json[$parent_rels[$v['alias']]]['text'] = $v['alias'] . ' -  ' . $this->eb_lang['create_version'];
                    }
                }
            }
        }

        //возвращаем json-доступных языков и их url
        $json[0]['url'] = "#";
        $json[0]['text'] = $this->langs[$this->topid]['alias']/* . ' - ' . $this->eb_lang['current_version']*/
        ;
        return '<input type="hidden" id="eb_relations_tv" name="tv' . $this->rel_tv_id . '" value="' . $relation . '">' . '<script>var eb_langs = ' . json_encode($json) . '</script>';
    }

    public function synchTVs($id)
    {
        $synch_template = $this->checkNumberString($this->params['synch_template'] ?? '');
        $synch_TV = $this->checkNumberString($this->params['synch_TV'] ?? '');
        if (!empty($synch_template) && !empty($synch_TV)) {
            $q = $this->modx->db->query("SELECT * FROM {$this->content_table} WHERE id={$id} AND template IN ({$synch_template}) LIMIT 0, 1");
            if ($this->modx->db->getRecordCount($q) == 1) {
                $rels = $this->getRelations($id);
                $relations = $this->getRelationsArray($rels);
                $q = $this->modx->db->query("SELECT tmplvarid,value FROM {$this->tvs_table} WHERE contentid={$id} AND tmplvarid IN ({$synch_TV}) AND tmplvarid != {$this->rel_tv_id}");
                //собираем сюда все, что действительно обновилось (остались записи в базе)
                $synch = [];
                while ($tvs = $this->modx->db->getRow($q)) {
                    foreach ($relations as $k => $v) {
                        if ($v != $id) {
                            //$this->copyTVs($id, $v, $synch_TV);
                            $this->saveTV($v, $tvs['tmplvarid'], $tvs['value']);
                        }
                    }
                    $synch[] = $tvs['tmplvarid'];
                }
                //а теперь удаляем то, что удалилось из базы и в других "родственниках"-языках
                if (!empty($synch)) {
                    foreach ($relations as $k => $v) {
                        if ($v != $id) {
                            $sql = "DELETE FROM {$this->tvs_table} WHERE contentid={$v} AND tmplvarid IN ({$synch_TV}) AND tmplvarid != {$this->rel_tv_id} AND tmplvarid NOT IN (" . implode(',', $synch) . ")";
                            $del = $this->modx->db->query($sql);
                        }
                    }
                }
            }
        }
        return true;
    }

    //формируем массив для удаления связей перед очисткой корзины
    public function makeDelRelsArray($del_ids)
    {
        $del_array = [];
        $q = $this->modx->db->query("SELECT contentid,value FROM " . $this->tvs_table . " WHERE contentid IN ({$del_ids}) AND tmplvarid={$this->rel_tv_id}");
        while ($row = $this->modx->db->getRow($q)) {
            if ($row['value'] != '') {
                $rel_array = $this->getRelationsArray($row['value']);
                $del_array[$row['contentid']] = $rel_array;
            }
        }
        return $del_array;
    }

    //обновляем связи после окончательной очистки корзины
    public function updateDeletedRelations($del_array)
    {
        foreach ($del_array as $del_id => $del_rels) {
            if (is_array($del_rels)) {
                $newrel = '';
                $oldrel = '';
                $minrow = '';
                foreach ($del_rels as $k => $v) {
                    $oldrel .= $k . ':' . $v . '||';
                    if ($v != $del_id) {
                        $newrel .= $k . ':' . $v . '||';
                    } else {
                        $minrow = $k . ':' . $v;
                    }
                }
                $oldrel = substr($oldrel, 0, -2);
                $newrel = substr($newrel, 0, -2);
                $tmp = explode(":", $newrel);
                if ($oldrel != '') {
                    if ($newrel != $minrow) {
                        $this->modx->db->update([ 'value' => $newrel ], $this->tvs_table, "`value`='" . $oldrel . "' AND tmplvarid=" . $this->rel_tv_id);
                    }
                }
                if (count($tmp) == 2) {//удаляем связь, если остался только один ресурс (сам к себе привязан)
                    $this->modx->db->query("DELETE FROM {$this->tvs_table} WHERE contentid={$tmp[1]} AND tmplvarid={$this->rel_tv_id}");
                }
            }
        }
    }

    //копируем права доступа - группы, к которым принадлежит документ
    protected function copyDocGroups($old_id, $new_id)
    {
        $q = $this->modx->db->select("document_group", $this->modx->getFullTableName('document_groups'), "document=" . $old_id);
        $values = [];
        while ($row = $this->modx->db->getRow($q)) {
            $values[] = "(" . $row['document_group'] . ", " . $new_id . ")";
        }
        if (count($values)) {
            $sql = "INSERT INTO " . $this->modx->getFullTableName('document_groups') . " (document_group,document) VALUES " . implode(",", $values);
            $this->modx->db->query($sql);
        }
        return count($values);
    }


    protected function setLexicon($lexicon)
    {
        $q = $this->modx->db->query("SELECT * FROM " . $this->modx->getFullTableName('lexicon'));
        while ($row = $this->modx->db->getRow($q)) {
            $perevod[ $row['name'] ] = $row[$lexicon] ?? $row['name'];
        }
        $_SESSION['evoBabel_curLang'] = $lexicon;
        $_SESSION['perevod'] = $perevod;
    }


    //plugin events

    public function listenOnPageNotFound()
    {
        $root = explode('/', htmlentities($_GET['q']))[0];
        $curr = [];
        foreach($this->langs as $id => $lang) {
            if($lang['alias'] == $root) {
                $curr = [ 'id' => $id, 'alias' => $lang['alias'] ];
                $_SESSION['curr_lang_id'] = $id;
                $_SESSION['evoBabel_curLang'] = $lang['alias'];
            }
        }
        //если мы вне папок языков
        if(empty($curr)) {
            //смотрим с какого языка мы сюда попали
            if(!empty($_SESSION['curr_lang_id'])) {
                $curr = [ 'id' => $_SESSION['curr_lang_id'], 'alias' => $this->langs[ $_SESSION['curr_lang_id'] ]['alias'] ];
            } else {
                $curr = [ 'id' => $this->getCurLangId($this->modx->getConfig('site_start')), 'alias' => $this->params['currlang'] ];
            }
        }
        if(!empty($curr)) {
            $this->setLexicon($curr['alias']);
        }
        if(isset($_SESSION['perevod'])) {
            if(!empty($_SESSION['perevod']['Страница не найдена'])) {
                $docid = (int)$_SESSION['perevod']['Страница не найдена'];
            } else {
                if($this->getCurLangId($this->modx->getConfig('error_page')) == $curr['id']) {
                    //если мы в текущем контексте, то ничего не делаем
                    $docid = false;
                } else {
                    $docid = $curr['id'];
                }
            }
            if (!empty($docid)) {
                $this->modx->setConfig('error_page', $docid);
            }
        }
    }

    public function listenOnDocFormSave()
    {
        if ($this->params['mode'] == 'upd' && !empty($this->params['synch_template']) && !empty($this->params['synch_TV'])) {
            $this->synchTVs($this->params['id']);
        }
    }

    public function listenOnBeforeEmptyTrash()
    {
        if (!empty($this->params['ids'])) {
            $del_ids = implode(',', $this->params['ids']);
            $del_array = $this->makeDelRelsArray($del_ids);
            $_SESSION['del_array'] = $del_array;
        }
    }

    public function listenOnEmptyTrash()
    {
        $del_array = $_SESSION['del_array'] ?? [];
        if (!empty($del_array)) {
            $this->updateDeletedRelations($del_array);
        }
        unset($_SESSION['del_array']);
    }

    public function listenOnWebPageInit()
    {
        // в нужном месте прописываем [+activeLang+] (вывод текущего языка) и [+switchLang+] - вывод переключалки (списка) языков
        // параметры вызова
        // &activeLang - шаблон вывода текущего языка (отдельно)
        // &activeRow - шаблон вывода текущего языка в списке языков
        // &unactiveRow - шаблон вывода языков в списке (кроме текущего)
        // &langOuter - шаблон обертки для списка языков

        //шаблоны вывода по умолчанию
        $configFile = $this->params['config'] ?? '';
        if (!empty($configFile) && is_file(MODX_BASE_PATH . $configFile)) {
            include_once(MODX_BASE_PATH . $configFile);
        }
        //активный язык отдельно
        $activeLang = $activeLang ?? '<div id="curr_lang"><img src="assets/snippets/evoBabel/config/images/flag_[+alias+].png"> <a href="javascript:;">[+name+]</a></div>';
        //активный язык в списке
        $activeRow = $activeRow ?? '<div class="active"><img src="assets/snippets/evoBabel/config/images/flag_[+alias+].png"> &nbsp;<a href="[+url+]">[+name+]</a></div>';
        //неактивный язык списка
        $unactiveRow = $unactiveRow ?? '<div><img src="assets/snippets/evoBabel/config/images/langs/flag_[+alias+].png"> &nbsp;<a href="[+url+]">[+name+]</a></div>';
        //обертка списка языков
        $langOuter = $langOuter ?? '<div class="other_langs">[+wrapper+]</div>';

        //фикс для OnWebPageInit на несуществующей странице с несуществующим documentIdentifier
        if (!empty($this->modx->documentIdentifier)) {

            $out = '';
            $langs = [];
            $others = [];//массив других языков (кроме текущего)
            $this->id = $this->modx->documentIdentifier;
            $siteAllLangs = $this->langs;
            $siteLangs = $this->getSiteLangs($this->lang_template_id);
            $curr_lang_id = $this->getCurLangId($this->id);
            if (empty($curr_lang_id)) {//не смогли найти язык
                if (isset($_SESSION['curr_lang_id'])) {//есть предыдущий, берем его
                    $curr_lang_id = $_SESSION['curr_lang_id'];
                } else {//нет предыдущего, берем язык "домашней страницы"
                    $curr_lang_id = $this->getCurLangId($this->modx->config['site_start']);
                    $_SESSION['curr_lang_id'] = $curr_lang_id;
                }
            } else {
                $_SESSION['curr_lang_id'] = $curr_lang_id;
            }
            $relations = $this->getRelations($this->id);
            $relArray = $this->getRelationsArray($relations);

            //устанавливаем текущий язык
            $currLang = str_replace([ '[+alias+]', '[+name+]' ], [ $siteLangs[$curr_lang_id]['alias'], $siteLangs[$curr_lang_id]['name'] ], $activeLang);

            //устанавливаем список языков с учетом активного
            $langRows = '';

            foreach ($siteLangs as $k => $v) {
                $tpl = ($k != $curr_lang_id ? $unactiveRow : $activeRow);
                if (isset($relArray[$v['alias']]) && $this->checkActivePage($relArray[$v['alias']])) {//если есть связь и эта страница активна
                    $url = $relArray[$v['alias']];
                } else {//нет связи либо страница не активна -> проверяем родителя
                    $parent_id = $this->modx->db->getValue("SELECT parent FROM " .$this->content_table . " WHERE id=" . $this->id . " AND published=1 AND deleted=0 AND parent!=0 AND template!=" . $this->lang_template_id);
                    if (!$parent_id) {//если нет родителя, отправляем на главную страницу языка
                        $url = $k;
                    } else {//если родитель есть, проверяем его связи
                        $parent_relations = $this->getRelations($parent_id);
                        $relParentArray = $this->getRelationsArray($parent_relations);
                        if (isset($relParentArray[$v['alias']]) && $this->checkActivePage($relParentArray[$v['alias']])) {//у родителя активная связь
                            $url = $relParentArray[$v['alias']];
                        } else {//иначе -> на главную страницу языка
                            $url = $k;
                        }
                    }
                }
                $langRows .= str_replace([ '[+alias+]', '[+url+]', '[+name+]' ], [ $v['alias'], $this->modx->makeUrl($url), $v['name'] ], $tpl);
            }
            $langsList = str_replace([ '[+wrapper+]' ], [ $langRows ], $langOuter);

            // устанавливаем плейсхолдеры [+activeLang+] и [+switchLang+] для вывода активного языка и списка языков соответственно
            $this->modx->setPlaceholder("activeLang", $currLang);
            $this->modx->setPlaceholder("switchLang", $langsList);

            //получаем массив перевода для чанков в сессию
            $cur_lexicon = $siteAllLangs[$curr_lang_id]['alias'];
            if ($cur_lexicon == '') {
                $doc = $this->modx->getDocument($this->modx->documentIdentifier);
                $cur_lexicon = $doc['alias'];
            }
            $this->setLexicon($cur_lexicon);
        }
    }

    public function listenOnDocDuplicate()
    {
        if ($this->params['new_id'] && (empty($this->params['source']) || $this->params['source'] != 'evobabel')) {
            $q = $this->modx->db->query("DELETE FROM " . $this->tvs_table . " WHERE contentid=" . $this->params['new_id'] . " AND tmplvarid=" . $this->rel_tv_id);
        }
    }

}
