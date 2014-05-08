<?php
//@author webber (web-ber12@yandex.ru)

if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

class evoBabel{

public $modx;
public $id; //id текущего ресурса
public $content_table;
public $tvs_table;
public $rel_tv_id;
public $lang_template_id;
public $version_lang_id;
public $version_parent_id;
public $langs=array();
public $params=array();
public $topid;
public $iconfolder;
public $theme;

public function __construct($modx, $id, $params){
    $this->modx = $modx;
    $this->id = $id;
    $this->params = $params;
    $this->content_table = $this->modx->getFullTableName('site_content');
    $this->tvs_table = $modx->getFullTableName('site_tmplvar_contentvalues');
    $this->rel_tv_id = $params['rel_tv_id'];
    $this->lang_template_id = $params['lang_template_id'];
    $this->langs = $this->getAllSiteLangs($this->lang_template_id);
    $this->topid = $this->getCurLangId($this->id);
    $this->theme = $this->modx->config['manager_theme'];
    $this->iconfolder = "media/style/" . $this->theme . "/images/icons/";
}

//db functions
public function query($sql){
    return $this->modx->db->query($sql);
}

private function getRow($result){
    return $this->modx->db->getRow($result);
}

private function update($flds,$table,$where){
    return $this->modx->db->update($flds,$table,$where);
}

private function escape($a){
    return $this->modx->db->escape($a);
}

private function insert($flds,$table){
    return $this->modx->db->insert($flds,$table);
}


public function getValue($sql){	
    return $this->modx->db->getValue($this->query($sql));
}

private function getRecordCount($result){	
    return $this->modx->db->getRecordCount($result);
}
//end db functions

private function clearCache($type='full',$report=false){
    return $this->modx->clearCache($type, $report);
}
private function getTVName($tvid){
    return $this->getValue("SELECT name FROM " . $this->modx->getFullTableName('site_tmplvars') . " WHERE id=" . $tvid . " LIMIT 0, 1");
}

//оставляем в "списках через запятую" только цифры и удаляем лишние пробелы
private function checkNumberString($string){
    $string=trim($string);
    $tmp=explode(',',$string);
    $tmp2=array();
    if (is_array($tmp)) {
        foreach ($tmp as $k=>$v) {
            $v = (int)trim($v);
            if($v != 0){
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

//content functions
private function saveTV($contentid, $tvid,$tvval){
    //$isset = $this->getValue("SELECT value FROM " . $this->tvs_table . " WHERE contentid=" . $contentid . " AND tmplvarid=" . $tvid . " LIMIT 0, 1");
    $cnt = $this->modx->db->getRecordCount($this->modx->db->select('value', $this->tvs_table, "contentid=".$contentid." AND tmplvarid=".$tvid));
    if ($cnt) {
        $this->update(array('value'=>$tvval), $this->tvs_table, "contentid=" . $contentid . " AND tmplvarid=" . $tvid);
    } else {
        $this->insert(array('contentid'=>$contentid, 'tmplvarid'=>$tvid, 'value'=>$tvval), $this->tvs_table);
    }
}

private function copyTVs($oldid, $newid, $type='full'){ //or $type=ids tv comma separated
    $where='';
    if ($type != 'full') {
        $type = $this->checkNumberString($type);
        if ($type) {
            $where = ' AND tmplvarid IN(' . $type . ')';
        }
    }
    $sql = "SELECT * FROM " . $this->tvs_table . " WHERE contentid=" . $oldid . $where;
    $tvs = $this->query($sql);
    while ($row=$this->getRow($tvs)) {
        if ($row['tmplvarid'] != $this->rel_tv_id) {
            $this->saveTV($newid, $row['tmplvarid'], $this->escape($row['value']));
        }
    }
}

public function copyDoc($id, $newparent=false, $addzagol=false, $published=0){
    $new_id = false;
    $sql = "SELECT * FROM " . $this->content_table . " WHERE id=" . $id;
    $docrow = $this->getRow($this->query($sql));
    if (is_array($docrow)) {
        $tmp = array();
        foreach ($docrow as $k=>$v) {
            if ($k != 'id') {
                $tmp[$k] = $this->escape($v);
            }
        }
        if (!empty($tmp)) {
            $tmp['published'] = ($published != 0 ? 1 : 0);
            $tmp['parent'] = $newparent ? $newparent : $tmp['parent'];
            $tmp['pagetitle'] = $addzagol ? $tmp['pagetitle'] . ' (' . $addzagol . ')' : $tmp['pagetitle'];
            $new_id = $this->insert($tmp, $this->content_table);
            if ($new_id) {
                $isfolder = $this->update(array('isfolder'=>'1'), $this->content_table, 'isfolder=0 AND id=' . $tmp['parent']);
                $tvs = $this->copyTVs($id, $new_id);
                $this->clearCache();
            }
        }
    }
    return $new_id;
}

// end content functions

public function checkPage($id){//проверка существования страницы
    $result = $this->getValue("SELECT id FROM " . $this->content_table . " WHERE id={$id} LIMIT 0, 1");
    return $result;
}

public function checkActivePage($id){//проверка существования страницы и активности страницы
    $result = $this->getValue("SELECT id FROM " . $this->content_table . " WHERE id=" . $id . " AND deleted=0 AND published=1 LIMIT 0, 1");
    return $result;
}

public function getSiteLangs($lang_template_id){
    $q = $this->query("SELECT * FROM " . $this->content_table . " WHERE parent=0 AND template=" . $lang_template_id . " AND published=1 AND deleted=0 ORDER BY menuindex ASC");
    while ($row = $this->getRow($q)) {
        $langs[$row['id']]['lang'] = $row['pagetitle'];
        $langs[$row['id']]['name'] = $row['longtitle'];
        $langs[$row['id']]['home'] = $row['description'];
        $langs[$row['id']]['alias'] = $row['alias'];
    }
    return $langs;
}

public function getAllSiteLangs($lang_template_id){
    $q = $this->query("SELECT * FROM " . $this->content_table . " WHERE parent=0 AND template=" . $lang_template_id . " ORDER BY menuindex ASC");
    while($row = $this->getRow($q)){
		$langs[$row['id']]['lang'] = $row['pagetitle'];
        $langs[$row['id']]['name'] = $row['longtitle'];
        $langs[$row['id']]['home'] = $row['description'];
        $langs[$row['id']]['alias'] = $row['alias'];
    }
    return $langs;
}

public function _loadParent($id, $height){
    $parents = array();
    $q = $this->query("SELECT parent FROM " . $this->content_table." WHERE id=" . (int)$id);
    if ($this->getRecordCount($q) == 1) {
        $q = $this->getRow($q);
        $parents[$q['parent']] = $id;
        if($height>0 && $q['parent']>0){
            $data = $this->_loadParent($q['parent'], $height--);
            foreach ($data as $key=>$val) {
                $parents[$key] = $val;
            }
        }
    }
    return $parents;
}

public function getParentIds($id, $height = 10) {
    $parents = $this->_loadParent($id,$height);
    reset($parents);
    unset($parents[key($parents)]);
    return $parents;
}

public function getCurLangId($id){
    $res = $this->getParentIds($id);
    return $res[0];
}



public function getRelations($id){//получаем строку отношений для ресурса
    $res = $this->getValue("SELECT value FROM " . $this->tvs_table . " WHERE contentid=" . $id . " AND tmplvarid=" . $this->rel_tv_id . " LIMIT 0, 1");
    return $res;
}

public function getRelationsArray($relations){ //array ['lang_alias']=>['lang_page_id']
    $arr=array();
    if ($relations != '') {
        $arr1 = explode("||", $relations);
        foreach ($arr1 as $k=>$v) {
            if (isset($v) && $v != '') {
                $arr2 = explode(":", $v);
                $arr[$arr2[0]] = $arr2[1];
            }
        }
    }
    return $arr;
}

public function getFullRelationsArray($id, $langsArray){//полные отношения - недостающие заменяем на корневые языки
    if (!isset($langsArray[$id])) {
        $relations = $this->getRelations($id);
        $relationsArray = $this->getRelationsArray($relations);
        foreach ($langsArray as $k=>$v) {
            if (!isset($relationsArray[$v['alias']])) {
                $relationsArray[$v['alias']] = $k;
            }
        }
    } else {
        foreach ($langsArray as $k=>$v) {
            $relationsArray[$v['alias']] = $k;
        }
    }
    return $relationsArray;
}

public function makeVersion(){
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
            foreach ($this->langs as $k=>$v) {
                if (isset($rel_arr[$v['alias']]) && $this->checkPage($rel_arr[$v['alias']])) {//если страница старая
                    $new_rel .= $v['alias'] . ':' . $rel_arr[$v['alias']] . '||';
                } elseif ($k == $this->version_lang_id) {
                    $new_rel .= $v['alias'] . ':' . $new_id . '||';
                } else {

                }
            }
            $new_rel = substr($new_rel, 0, -2);
            $rel_arr2 = $this->getRelationsArray($new_rel);
            foreach ($rel_arr2 as $k=>$v) {
                $this->saveTV($v, $this->rel_tv_id, $new_rel);
            }
        }
        return '<script type="text/javascript">location.href="index.php?a=27&id=' . $this->id . '"</script>';
    }
}

public function showRelations(){
    $out = '';
    $rel_rows = '';

    //id родительского ресурса и его полные связи
    $parent_id = $this->getValue("SELECT parent FROM " . $this->content_table . " WHERE id={$this->id} LIMIT 0, 1");
    $parent_rels = $this->getFullRelationsArray($parent_id, $this->langs);

    //получаем связь текущей страницы
    $relation = $this->getRelations($this->id);

    //если связи есть, выводим их
    if($relation){
        $rels = $this->getRelationsArray($relation);
        foreach ($this->langs as $k=>$v){
            if ($k != $this->topid) {
                if (isset($rels[$v['alias']]) && $this->checkPage($rels[$v['alias']])) {
                    $rel_rows.='
                        <div class="eB_row" style="height:34px;">
                            <a href="index.php?a=27&id='.$rels[$v['alias']].'" class="primary">
                                <img alt="icons_save" src="'.$this->iconfolder.'save.png"/> '.$v['lang'].' -  перейти
                            </a>
                        </div>';
                } else {
                $rel_rows .= '
                    <div class="eB_row" style="height:34px;"> 
                        <a href="index.php?a=27&id='.$this->id.'&ebabel='.$k.'&parent='.$parent_rels[$v['alias']].'">
                            <img src="'.$this->iconfolder.'page_white_copy.png" alt="icons_resource_duplicate"/> '.$v['lang'].' - создать
                        </a>
                    </div>';
                }
            }
        }
        $rel_rows .= '<input type="hidden" name="tv'.$this->rel_tv_id.'" value="'.$relation.'">';
    } else {//если связей нет, то выводим ссылки на создание без проверок
        foreach ($this->langs as $k=>$v) {
            if ($k != $this->topid) {
                $rel_rows .= '<div class="eB_row" style="height:34px;">
                    <a href="index.php?a=27&id='.$this->id.'&ebabel='.$k.'&parent='.$parent_rels[$v['alias']].'">
                        <img src="'.$this->iconfolder.'page_white_copy.png" alt="icons_resource_duplicate"/> '.$v['lang'].' - создать
                    </a>';
                if ($parent_rels[$v['alias']] == $k && $k != $parent_id && !isset($this->langs[$parent_id])) {
                    $rel_rows .= '<b><font color=red>Внимание!</font></b> Рекомендуется создать сначала языковую версию <a href="index.php?a=27&id='.$parent_id.'"><img src="'.$this->iconfolder.'delete.png" alt="icons_delete_document"/> родителя</a>';
                }
                $rel_rows .= '</div>';
            }
        }
    }

    //общая "картина" для связей на выход
    $out.='<h3>Языковые версии</h3>
        <div class="eB_row eB_current" style="height:34px;">
            <img src="'.$this->iconfolder.'page_white_magnify.png" alt="icons_resource_duplicate"/> '.$this->langs[$this->topid]['lang'].' - Текущая версия
        </div> 
        <div class="actionButtons">'.$rel_rows.'</div>
    ';
    return $out;
}

public function synchTVs($synch_TV, $synch_template, $id){
    $synch_template = $this->checkNumberString($synch_template);
    $synch_TV = $this->checkNumberString($synch_TV);
    if ($synch_template && $synch_TV) {
        $q = $this->query("SELECT * FROM {$this->content_table} WHERE id={$id} AND template IN ({$synch_template}) LIMIT 0, 1");
        if ($this->getRecordCount($q) == 1) {
            $rels = $this->getRelations($id);
            $relations = $this->getRelationsArray($rels);
            $q = $this->query("SELECT tmplvarid,value FROM {$this->tvs_table} WHERE contentid={$id} AND tmplvarid IN ({$synch_TV})");
            while ($tvs = $this->getRow($q)) {
                foreach ($relations as $k=>$v) {
                    if($v != $id){
                        //$this->copyTVs($id, $v, $synch_TV);
                        $this->copyTVs($id, $v, $tvs['tmplvarid']);
                    }
                }
            }
        }
    }
    return true;
}

public function makeDelRelsArray($del_ids){//формируем массив для удаления связей перед очисткой корзины
    $del_array = array();
    $q = $this->query("SELECT contentid,value FROM " . $this->tvs_table . " WHERE contentid IN ({$del_ids}) AND tmplvarid={$this->rel_tv_id}");
    while ($row = $this->getRow($q)) {
        if ($row['value'] != '') {
            $rel_array = $this->getRelationsArray($row['value']);
            $del_array[$row['contentid']] = $rel_array;
        }
    }
    return $del_array;
}

public function updateDeletedRelations($del_array){//обновляем связи после окончательной очистки корзины
    foreach($del_array as $del_id=>$del_rels){
        if (is_array($del_rels)) {
            $newrel = '';
            $oldrel = '';
            $minrow = '';
            foreach ($del_rels as $k=>$v) {
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
                    $this->update(array('value'=>$newrel), $this->tvs_table, "`value`='".$oldrel."' AND tmplvarid=".$this->rel_tv_id);
                }
            }
            if (count($tmp) == 2) {//удаляем связь, если остался только один ресурс (сам к себе привязан)
                $this->query("DELETE FROM {$this->tvs_table} WHERE contentid={$tmp[1]} AND tmplvarid={$this->rel_tv_id}");
            }
        }
    }
}


}//end class
