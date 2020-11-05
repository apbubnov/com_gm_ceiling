<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

class Gm_ceilingModelGoods extends JModelList{
    public function get($id = null){
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`g`.`id`, 
                    `g`.`category_id`, 
                    `g`.`name`, 
                    `g`.`unit_id`, 
                    `g`.`price`')
                ->from('`#__gm_stock_goods` as `g`');
            if (!empty($id)) {
                $query->where("`g`.`id`= $id");
            }
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getByCategory($category_id){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`g`.`id`, 
                    `g`.`category_id`, 
                    `g`.`name`, 
                    `g`.`unit_id`, 
                    `g`.`price`')
                ->from('`#__gm_stock_goods` as `g`');
            if (!empty($id)) {
                $query->where("`g`.`category_id`= $category_id");
            }
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getUnits(){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id,unit')
                ->from('`rgzbn_gm_stock_units`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}