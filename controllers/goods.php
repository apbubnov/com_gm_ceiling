<?php
defined('_JEXEC') or die;
class Gm_ceilingControllerGoods extends JControllerLegacy{
    public function getData(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id');
            $goodsModel = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $goods = $goodsModel->getInfo($id);
            if(count($goods) >1){
                $result = str_replace('\"','"',json_encode($goods,JSON_UNESCAPED_UNICODE));
                $result = str_replace('"{', '{', $result);
                $result = str_replace('}"', '}', $result);
                die("{\"goods\":" . $result ."}");
            }
            else{
                $result = str_replace('\"','"',json_encode($goods,JSON_UNESCAPED_UNICODE));
                $result = str_replace('"{', '{', $result);
                $result = str_replace('}"', '}', $result);
                die($result);
            }

        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getByCategory(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('category_id');
            $goodsModel = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $result = str_replace('\"','"',json_encode($goodsModel->getByCategory($id,0),JSON_UNESCAPED_UNICODE));
            $result = str_replace('"{', '{', $result);
            $result = str_replace('}"', '}', $result);
            die("{\"goods\":" .$result."}");
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getByMainCategory(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('category_id');
            $goodsModel = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $result = str_replace('\"','"',json_encode($goodsModel->getByCategory($id,1),JSON_UNESCAPED_UNICODE));
            $result = str_replace('"{', '{', $result);
            $result = str_replace('}"', '}', $result);
            die("{\"goods\":" .$result."}");
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getUnits(){
        try{
            $goodsModel = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            die(json_encode($goodsModel->getUnits()));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getGoodsInCategoriesByFilter(){
        try{
            $jinput = JFactory::getApplication()->input;
            $search = $jinput->getString('search');
            $filter = '';
            $goodsModel = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            if(!empty($search)){
                $filter = "c.category LIKE '%$search%' OR g.name LIKE '%$search%' OR g.price LIKE '%$search%'";
            }
            $result = $goodsModel->getGoodsInCategoriesByFilter($filter);
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getGoodsForStore(){
        try{
            $jinput = JFactory::getApplication()->input;
            $search = $jinput->getString('search');
            $filter = '';
            $goodsModel = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            if(!empty($search)){
                $filter = "c.category LIKE '%$search%' OR g.name LIKE '%$search%' OR g.price LIKE '%$search%'";
            }
            $result = $goodsModel->getGoodsInCategoriesForStore($filter);
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function changeCategory(){
        try{
            $jinput = JFactory::getApplication()->input;
            $category = $jinput->getInt('category');
            $goodsId = $jinput->getInt('id');
            $goodsModel = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            die(json_encode($goodsModel->changeCategory($goodsId,$category)));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getActionsOnGoodsInfo(){
        try{
            $jinput = JFactory::getApplication()->input;
            $goodsId = $jinput->getInt('goods_id');
            $dateTo = $jinput->get('date',date('Y-m-d H:i:s'),'STRING');
            $modelGoods = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $actionsInfo = $modelGoods->getActionsOnGoodsInfo($goodsId,$dateTo);
            die(json_encode($actionsInfo)) ;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addImages(){
        try{
            $jinput = JFactory::getApplication()->input;
            $hrefs = $jinput->get('hrefs',[],'ARRAY');
            $modelGoods = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $modelGoods->addImages($hrefs);
            die(json_encode(true));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addGoodsAvailibilityType(){
        try{
            $jinput = JFactory::getApplication()->input;
            $title = $jinput->getString('title');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $model->addAvailabilityType($title);
            die(json_encode(true));

        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getGoodsAvailibilityTypes(){
        try{
            $model = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $types = $model->getGoodsAvailabilityTypes();
            die(json_encode($types));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function add(){
        try{
            $data = $_POST;
            if(empty($data)){
                $jinput = JFactory::getApplication()->input;
                $category = $jinput->getInt('categoryID');
                $goodsName = $jinput->getString('name');
                $goodsUnit = $jinput->getInt('unit');
                $goodsMultiplicity = $jinput->getString('multiplicity');
                $goodsPrice = $jinput->getString('price');
                $goodsInfo = $jinput->getString('info');
            }
            else{
                $category = $data['categoryID'];
                $goodsName = $data['name'];
                $goodsUnit = $data['unit'];
                $goodsMultiplicity = $data['multiplicity'];
                $goodsPrice = $data['price'];
                $goodsInfo = $data['info'];
            }
            $model = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $goodsId = $model->addGoods($category,$goodsName,$goodsUnit,$goodsMultiplicity,$goodsPrice,$goodsInfo);
            die(json_encode($goodsId));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function updateInfo(){
        try{
            $data = $_POST;
            if(empty($data)) {
                $jinput = JFactory::getApplication()->input;
                $id = $jinput->getInt('id');
                $info = $jinput->getString('info');
            }
            else{
                $id = $data['id'];
                $info = $data['info'];
            }
            $model = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $result = $model->updateInfo($id,$info);
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function update(){
        try{
            $data = $_POST;
            if(empty($data)){
                $jinput = JFactory::getApplication()->input;
                $id = $jinput->getInt('id');
                $category = $jinput->getInt('category');
                $goodsName = $jinput->getString('name');
                $goodsUnit = $jinput->getInt('unit');
                $goodsMultiplicity = $jinput->getString('multiplicity');
                $goodsPrice = $jinput->getString('price');
                $goodsInfo = $jinput->getString('info');
            }
            else{
                $id = $data['id'];
                $category = $data['category'];
                $goodsName = $data['name'];
                $goodsUnit = $data['unit'];
                $goodsMultiplicity = $data['multiplicity'];
                $goodsPrice = $data['price'];
                $goodsInfo = $data['info'];
            }
            $model = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $result = $model->update($id,$category,$goodsName,$goodsUnit,$goodsMultiplicity,$goodsPrice,$goodsInfo);
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function delete(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $result = $model->delete($id);
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getGoodsJobsMap(){
        try{
            $jinput = JFactory::getApplication()->input;
            $ids = $jinput->getString('ids');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            $result = $model->getGoodsJobsMap($ids);
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}