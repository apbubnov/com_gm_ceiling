<?php
class Gm_ceilingModelGoods_category extends JModelList{
    public function get($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id as categoryID,category as name')
                ->from('`rgzbn_gm_stock_goods_categories`');
            if(!empty($id)){
                $query->where("id = $id");
            }
            $db->setQuery($query);
            if(!empty($id)){
                $result = $db->loadObject();
            }
            else{
                $result = $db->loadObjectList();
            }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*BAUNET получение категорий каталога*/
    public function getData($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id as categoryID,parent_id as parentID,name,image_id as imageID,viewType as viewType')
                ->from('`rgzbn_gm_ceiling_baunet_catalog_category`');
            if(!empty($id)){
                $query->where("id = $id");
            }
            $db->setQuery($query);
            if(!empty($id)){
                $result = $db->loadObject();
            }
            else{
                $result = $db->loadObjectList();
            }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getParentCategories(){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id as categoryID,parent_id as parentID,name,image_id as imageID,viewType as viewType')
                ->from('`rgzbn_gm_ceiling_baunet_catalog_category`')
                ->where("parent_id IS NULL OR parent_id = id");
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getByParentId($parentId){
        try{
            $result = [];
            if(!empty($parentId)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->select('id as categoryID,parent_id as parentID,name,image_id as imageID,viewType as viewType')
                    ->from('`rgzbn_gm_ceiling_baunet_catalog_category`')
                    ->where("parent_id = $parentId");
                $db->setQuery($query);
                $result = $db->loadObjectList();
            }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function create($name,$parentId,$imageID,$viewType){
        try{
            $result = null;
            if(!empty($name)) {
                $columns = '`name`';
                $values = "'$name'";
                if(!empty($parentId)){
                    $columns .= ',`parent_id`';
                    $values .= ",$parentId";
                }
                if(!empty($imageID)){
                    $columns .= ',`image_id`';
                    $values .= ",'$imageID'";
                }
                if(!empty($viewType) || $viewType == 0){
                    $columns .= ',`viewType`';
                    $values .= ",'$viewType'";
                }
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query
                    ->insert('`rgzbn_gm_ceiling_baunet_catalog_category`')
                    ->columns("$columns")
                    ->values("$values");
                $db->setQuery($query);
                $db->execute();
                $result = $db->insertId();
            }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delete($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('')
                ->from('`rgzbn_gm_ceiling_baunet_catalog_category`')
                ->where("id=$id");
            $db->setQuery($query);
            $db->execute();
            $result =  $db->getAffectedRows();

            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function update($id,$name,$parentId,$imageId,$viewType){
        try{
            $result = 0;
            if(!empty($id)){
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->update('`rgzbn_gm_ceiling_baunet_catalog_category`')
                    ->where("id = $id");
                if(!empty($name)){
                    $query->set("name = '$name'");
                }
                if(!empty($parentId)){
                    $query->set("parent_id = $parentId");
                }
                if(!empty($imageId)){
                    if($imageId == 'null'){
                        $imageId = '(NULL)';
                    }
                    $query->set("image_id = '$imageId'");
                }
                if(!empty($viewType) || $viewType == 0){
                    if($viewType == 'null'){
                        $viewType = '(NULL)';
                    }
                    $query->set("viewType = '$viewType'");
                }
                $db->setQuery($query);
                $db->execute();
                $result = $db->getAffectedRows();
            }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}