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

            $imagesSubQuery = $db->getQuery(true);
            $imagesSubQuery
                ->select('goods_id,REPLACE(TO_BASE64(CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"link":"\',link,\'","is_main":"\',is_main,\'"}\') ORDER BY is_main DESC separator \',\' ),\']\')),\'\n\',\'\') AS images')
                ->from('`rgzbn_gm_stock_goods_images`')
                ->group('goods_id');


            $query = $db->getQuery(true);
            $query
                ->select('c.id,c.category')
                ->select('CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"id":"\',g.id,\'","name":"\',g.name,\'","price":"\',g.price,\'","images":"\',IFNULL(gi.images,\'\'),\'"}\') SEPARATOR \',\' ),\']\') AS goods')
                ->from('`rgzbn_gm_stock_goods_categories` AS  c')
                ->leftJoin('`rgzbn_gm_stock_goods` AS g ON g.category_id = c.id')
                ->leftJoin("($imagesSubQuery) as gi ON gi.goods_id = g.id")
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

            $imagesSubQuery = $db->getQuery(true);
            $imagesSubQuery
                ->select('goods_id,REPLACE(TO_BASE64(CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"link":"\',link,\'","is_main":"\',is_main,\'"}\') separator \',\'),\']\')),\'\n\',\'\') AS images')
                ->from('`rgzbn_gm_stock_goods_images`')
                ->group('goods_id');

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
		),\'","images":"\',IFNULL(gi.images,\'\'),\'"}\') SEPARATOR \',\'),\']\') AS goods')
                ->from('`rgzbn_gm_stock_goods_categories` AS  c')
                ->leftJoin('`rgzbn_gm_stock_goods` AS g ON g.category_id = c.id')
                ->leftJoin('`rgzbn_gm_stock_units` AS `u` ON `u`.`id` = g.unit_id')
                ->leftJoin("`rgzbn_gm_ceiling_goods_dealer_price` AS gdp ON gdp.goods_id = g.id AND gdp.dealer_id = $dealerId")
                ->leftJoin("($imagesSubQuery) as gi ON gi.goods_id = g.id")
                ->group('`c`.`id`')
                ->order('`c`.`id`,`g`.`id`');
            if(!empty($filter)){
                $query->where($filter);
            }
            //throw new Exception($query);
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

    function getActionsOnGoodsInfo($goodsId,$dateTo){
        try{
            $db = $this->getDbo();
            $receiptionQuery = $db->getQuery(true);
            $salesQuery = $db->getQuery(true);

            $salesQuery
                ->select('s.id,s.count,DATE_FORMAT(s.date_time,\'%d.%m.%Y %H:%i:%s\') AS date_time, \'Продажа\' AS `type`, CONCAT(\'№\',p.id,\' \',u.name)')
                ->from('`rgzbn_gm_stock_sales` AS s')
                ->leftJoin('`rgzbn_gm_stock_inventory` AS i ON i.id = s.inventory_id')
                ->leftJoin('`rgzbn_gm_ceiling_projects` AS p ON p.id = s.project_id')
                ->leftJoin('`rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id')
                ->leftJoin('`rgzbn_users` AS u ON u.id = c.dealer_id')
                ->where("i.goods_id = $goodsId and s.date_time<='$dateTo'");

            $receiptionQuery
                ->select('r.id,r.count,DATE_FORMAT(r.date_time,\'%d.%m.%Y %H:%i:%s\') AS date_time,\'Прием\' AS `type`,\'-\' AS info')
                ->from('`rgzbn_gm_stock_reception` AS r')
                ->leftJoin('`rgzbn_gm_stock_inventory` AS i ON i.id = r.inventory_id')
                ->where("i.goods_id = $goodsId and r.date_time<='$dateTo'");
            $query = "($receiptionQuery) UNION ALL ($salesQuery) ORDER BY `date_time`";
            $db->setQuery($query);
            $data = $db->loadObjectList();
            return $data;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addImages($hrefs){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_stock_goods_images`')
                ->columns('`goods_id`,`link`,`is_main`')
                ->values($hrefs);
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}