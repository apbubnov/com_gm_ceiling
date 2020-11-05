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
}