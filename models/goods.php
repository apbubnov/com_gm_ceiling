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

    function getGoodsInCategoriesByFilter($filter){
        try{
            $db = $this->getDbo();

            $sessionQuery = 'SET SESSION group_concat_max_len  = 1048576';
            $db->setQuery($sessionQuery);
            $db->execute();

            $query = $db->getQuery(true);
            $query
                ->select('c.id,c.category')
                ->select('CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"id":"\',g.id,\'","name":"\',g.name,\'","price":"\',g.price,\'"}\') SEPARATOR \',\'),\']\') AS goods')
                ->from('`rgzbn_gm_stock_goods_categories` AS  c')
                ->leftJoin('`rgzbn_gm_stock_goods` AS g ON g.category_id = c.id')
                ->group('`c`.`id`')
                ->order('`c`.`id`,`g`.`id`');
            if(!empty($filter)){
                $query->where($filter);
            }
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    
    function getGoodsInCategoriesForStore($filter){
        try{
            $dealerId = JFactory::getUser()->dealer_id;
            $db = $this->getDbo();

            $sessionQuery = 'SET SESSION group_concat_max_len  = 1048576';
            $db->setQuery($sessionQuery);
            $db->execute();

            $query = $db->getQuery(true);
            $query
                ->select('c.id,c.category')
                ->select('CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"id":"\',g.id,\'","name":"\',g.name,\'","unit":"\',u.unit,\'","mult":"\',g.multiplicity,
\'","price":"\',( ROUND(
                    CASE
                      WHEN `gdp`.`operation_id` = 1 THEN
                        `gdp`.`value`
                      WHEN `gdp`.`operation_id` = 2 THEN
                        `g`.`price` + `gdp`.`value`
                      WHEN `gdp`.`operation_id` = 3 THEN
                        `g`.`price` - `gdp`.`value`
                      WHEN `gdp`.`operation_id` = 4 THEN
                        `g`.`price` + `gdp`.`value` / 100 * `g`.`price`
                      WHEN `gdp`.`operation_id` = 5 THEN
                        `g`.`price` - `gdp`.`value` / 100 * `g`.`price`
                      ELSE
                        `g`.`price`
                    END, 2
                  )
		),\'"}\') SEPARATOR \',\'),\']\') AS goods')
                ->from('`rgzbn_gm_stock_goods_categories` AS  c')
                ->leftJoin('`rgzbn_gm_stock_goods` AS g ON g.category_id = c.id')
                ->leftJoin('`rgzbn_gm_stock_units` AS `u` ON `u`.`id` = g.unit_id')
                ->leftJoin("`rgzbn_gm_ceiling_goods_dealer_price` AS gdp ON gdp.goods_id = g.id AND gdp.dealer_id = $dealerId")
                ->group('`c`.`id`')
                ->order('`c`.`id`,`g`.`id`');
            if(!empty($filter)){
                $query->where($filter);
            }
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function changeCategory($goodsId,$category){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('rgzbn_gm_stock_goods')
                ->set("category_id = $category")
                ->where("id = $goodsId");
            $db->setQuery($query);
            $db->execute();
            return $db->getAffectedRows();
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}