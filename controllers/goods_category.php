<?php
class Gm_ceilingControllerGoods_category extends JControllerLegacy{
    public function getData(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id');
            $goodsCategoryModel = Gm_ceilingHelpersGm_ceiling::getModel('goods_category');
            die(json_encode($goodsCategoryModel->get($id)));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }
}
