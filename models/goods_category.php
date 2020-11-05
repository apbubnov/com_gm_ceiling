<?php
class Gm_ceilingModelGoods_category extends JModelList{
    public function get($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id,category')
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
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }
}