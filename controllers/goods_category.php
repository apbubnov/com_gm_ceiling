<?php
class Gm_ceilingControllerGoods_category extends JControllerLegacy{
    public function getData(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id');
            $goodsCategoryModel = Gm_ceilingHelpersGm_ceiling::getModel('goods_category');
            $categories = $goodsCategoryModel->get($id);
            if(count($categories) >1){
                die("{\"categories\":".json_encode($categories,JSON_UNESCAPED_UNICODE)."}");
            }
            else{
                die(json_encode($categories,JSON_UNESCAPED_UNICODE));
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getInfo(){
        try {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id');
            $goodsCategoryModel = Gm_ceilingHelpersGm_ceiling::getModel('goods_category');
            $categories = $goodsCategoryModel->getData($id);
            if(count($categories) == 1){
                die(json_encode($categories,JSON_UNESCAPED_UNICODE));
            }
            else{
                die("{\"categories\":".json_encode($categories,JSON_UNESCAPED_UNICODE)."}");
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getParentCategories(){
        try{
            $goodsCategoryModel = Gm_ceilingHelpersGm_ceiling::getModel('goods_category');
            $categories = $goodsCategoryModel->getParentCategories();
            die("{\"categories\":".json_encode($categories,JSON_UNESCAPED_UNICODE)."}");
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getByParentId(){
        try{
            $jinput = JFactory::getApplication()->input;
            $parentId = $jinput->getInt('parent_id');
            $goodsCategoryModel = Gm_ceilingHelpersGm_ceiling::getModel('goods_category');
            $categories = $goodsCategoryModel->getByParentId($parentId);
            die("{\"categories\":".json_encode($categories,JSON_UNESCAPED_UNICODE)."}");
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function add(){
        try{
            $data = $_POST;
            if(!empty($data)){
                $name = $data['name'];
                $parentId = $data['parent_id'];
                $imageId = $data['image_id'];
                $viewType = $data['view_type'];
            }
            else{
                $jinput = JFactory::getApplication()->input;
                $name = $jinput->getString('name');
                $parentId =  $jinput->getString('parent_id');
                $imageId =  $jinput->getString('image_id');
                $viewType = $jinput->getString('view_type');
            }
            $goodsCategoryModel = Gm_ceilingHelpersGm_ceiling::getModel('goods_category');
            $categoryId = $goodsCategoryModel->create($name,$parentId,$imageId,$viewType);
            die(json_encode($categoryId));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delete(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id');
            $goodsCategoryModel = Gm_ceilingHelpersGm_ceiling::getModel('goods_category');
            $result = $goodsCategoryModel->delete($id);
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function update(){
        try{
            $data = $_POST;
            if(!empty($data)){
                $id = $data['id'];
                $name = $data['name'];
                $parentId = $data['parent_id'];
                $imageId = $data['image_id'];
                $viewType = $data['view_type'];
            }
            else{
                $jinput = JFactory::getApplication()->input;
                $id = $jinput->getInt('id');
                $name = $jinput->getString('name');
                $parentId =  $jinput->getString('parent_id');
                $imageId =  $jinput->getString('image_id');
                $viewType = $jinput->getString('view_type');
            }
            $goodsCategoryModel = Gm_ceilingHelpersGm_ceiling::getModel('goods_category');
            $categoryId = $goodsCategoryModel->update($id,$name,$parentId,$imageId,$viewType);
            die(json_encode($categoryId));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
