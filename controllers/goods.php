<?php
defined('_JEXEC') or die;
class Gm_ceilingControllerGoods extends JControllerLegacy{
    public function getData(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id');
            $goodsModel = Gm_ceilingHelpersGm_ceiling::getModel('goods');
            die(json_encode($goodsModel->get($id)));
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
            die(json_encode($goodsModel->getByCategory($id)));
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
}