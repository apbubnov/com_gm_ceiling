<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

class Gm_ceilingModelGoods extends JModelList{
    public function get($id = null){
        try {
            $db = $this->getDbo();
            $sessionQuery = 'SET SESSION group_concat_max_len  = 1048576';
            $db->setQuery($sessionQuery);
            $db->execute();


            $query = $db->getQuery(true);
            $query
                ->select('`g`.`id` as goodsID,
                    `g`.`category_id` as categoryID, 
                    `g`.`name`, 
                    `u`.`unit`, 
                    `g`.`price`,
                    CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"id":"\',gi.id,\'","link":"\',gi.link,\'"}\') SEPARATOR \',\'),\']\') AS imageID')
                ->from('`#__gm_stock_goods` as `g`')
                ->leftjoin('`rgzbn_gm_stock_goods_images` AS gi ON g.id = gi.goods_id')
                ->leftjoin('`rgzbn_gm_stock_units` AS u ON u.id = g.unit_id')
                ->group('`g`.`id`');
            if (!empty($id)) {
                $query->where("`g`.`id`= $id");
            }

            $db->setQuery($query);
            if (!empty($id)) {
                $items = $db->loadObject();
            }
            else{
                $items = $db->loadObjectList();
            }
            //$items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getInfo($id){
        try{
            $db = $this->getDbo();
            $sessionQuery = 'SET SESSION group_concat_max_len  = 1048576';
            $db->setQuery($sessionQuery);
            $db->execute();


            $query = $db->getQuery(true);
            $query
                ->select('`g`.`id` as goodsID,
                    `g`.`baunet_category_id` as categoryID, 
                    `g`.`name`, 
                    `g`.`unit_id` as `unit`, 
                    `g`.`price`,
                    `inf`.`info` as goodsInfo,
                    CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"id":"\',gi.id,\'","link":"\',gi.link,\'"}\') SEPARATOR \',\'),\']\') AS imageID')
                ->from('`#__gm_stock_goods` as `g`')
                ->leftjoin('`rgzbn_gm_stock_goods_images` AS gi ON g.id = gi.goods_id')
                ->leftjoin('`rgzbn_gm_stock_goods_info` AS inf ON inf.goods_id = g.id')
                ->group('`g`.`id`');
            if (!empty($id)) {
                $query->where("`g`.`id`= $id");
            }

            $db->setQuery($query);
            if (!empty($id)) {
                $items = $db->loadObject();
            }
            else{
                $items = $db->loadObjectList();
            }
            //$items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getByCategory($category_id,$main){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`g`.`id` as goodsID,
                    `g`.`baunet_category_id` as categoryID, 
                    `g`.`name`, 
                    `g`.`unit_id` as `unit`, 
                    `g`.`price`,
                    `inf`.`info` as goodsInfo,
                    CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"id":"\',gi.id,\'","link":"\',gi.link,\'"}\') SEPARATOR \',\'),\']\') AS imageID')
                ->from('`#__gm_stock_goods` as `g`')
                ->leftjoin('`rgzbn_gm_stock_goods_images` AS gi ON g.id = gi.goods_id')
                ->leftjoin('`rgzbn_gm_stock_goods_info` AS inf ON inf.goods_id = g.id')
                ->group('`g`.`id`');
            if (!empty($category_id)) {
                if($main == 0){
                    $query->where("`g`.`baunet_category_id`= $category_id");
                }
                if($main == 1){
                    $categorySubQuery = $db->getQuery(true);
                    $categorySubQuery
                        ->select('id')
                        ->from('`rgzbn_gm_ceiling_baunet_catalog_category`')
                        ->where("parent_id = $category_id OR id = $category_id");
                    $query->where("`g`.`baunet_category_id` in ($categorySubQuery)");
                }
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

    function addAvailabilityType($title){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_stock_goods_availability`')
                ->columns('`title`')
                ->values("'$title'");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getGoodsAvailabilityTypes(){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`rgzbn_gm_stock_goods_availability`');
            $db->setQuery($query);
            $types = $db->loadObjectList();
            return $types;

        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addGoods($category,$goodsName,$goodsUnit,$goodsMultiplicity,$goodsPrice,$goodsInfo=null) {
        try{
            $user = JFactory::getUser();
            $dealerId = !empty($user->dealer_id) ? $user->dealer_id : 1;
            if(empty($category)){
                $category = 'NULL';
            }
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_stock_goods`')
                ->columns('`name`,`category_id`,`baunet_category_id`,`unit_id`,`multiplicity`,`price`,`created_by`,`visibility`')
                ->values($db->quote($db->escape($goodsName,true)).",8,$category,$goodsUnit,$goodsMultiplicity,$goodsPrice,$dealerId,3");
            $db->setQuery($query);
            $db->execute();
            $goodsId = $db->insertId();
            if(!empty($goodsInfo)){

                $query
                    ->insert('`rgzbn_gm_stock_goods_info`')
                    ->columns('`goods_id`,`info`')
                    ->values("$goodsId,'$goodsInfo'");
                $db->setQuery($query);
                $db->execute();
            }
            return $goodsId;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function update($goodsId,$category,$goodsName,$goodsUnit,$goodsMultiplicity,$goodsPrice){
        try{
            $result = 0;
            if(!empty($goodsId) && (!empty($category) || !empty($goodsName) || !empty($goodsUnit) || !empty($goodsMultiplicity) || !empty($goodsPrice))) {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query
                    ->update('`rgzbn_gm_stock_goods`')
                    ->where("id=$goodsId");
                if(!empty($category)){
                    $query->set("baunet_category_id = $category");
                }
                if(!empty($goodsName)){
                    $query->set("name = '$goodsName'");
                }
                if(!empty($goodsUnit)){
                    $query->set("unit_id = $goodsUnit");
                }
                if(!empty($goodsMultiplicity)){
                    $query->set("multiplicity = $goodsMultiplicity");
                }
                if(!empty($goodsPrice)){
                    $query->set("price = $goodsPrice");
                }
                $db->setQuery($query);
                $db->execute();
                $result = 1;
            }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateInfo($goodsId,$info){
        try{
            $result = 0;
            if(!empty($goodsId) && !empty($info)){
                $info = str_replace(array("\r\n", "\r", "\n"),'',$info);
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->select('*')
                    ->from('`rgzbn_gm_stock_goods_info`')
                    ->where("goods_id = $goodsId");
                $db->setQuery($query);
                $existInfo = $db->loadObjectList();
                if(!empty($existInfo)){
                    $query = $db->getQuery(true);
                    $query
                        ->update('`rgzbn_gm_stock_goods_info`')
                        ->set("info = '$info'")
                        ->where("goods_id = $goodsId");
                    $db->setQuery($query);
                    $db->execute();
                }
                else{
                    $query = $db->getQuery(true);
                    $query
                        ->insert('`rgzbn_gm_stock_goods_info`')
                        ->columns("goods_id,info")
                        ->values("$goodsId,'$info'");
                    $db->setQuery($query);
                    $db->execute();
                }
                $result = 1;
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
                ->from('`rgzbn_gm_stock_goods`')
                ->where("id = $id");
            $db->setQuery($query);
            $db->execute();
            $result = 1;
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getGoodsJobsMap($ids){
        try{
            $result = [];
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $categorySubQuery = $db->getQuery(true);
            $categorySubQuery
                ->select('category_id')
                ->from('`rgzbn_gm_stock_goods`')
                ->where("id in($ids)");
            $query
                ->select('f.goods_category_id, GROUP_CONCAT(DISTINCT fj.job_id) as job_ids')
                ->from('`rgzbn_gm_ceiling_fields` AS f')
                ->leftJoin('`rgzbn_gm_ceiling_fields_jobs_map` AS fj ON fj.field_id = f.id ')
                ->leftJoin('`rgzbn_gm_stock_goods` AS g ON g.category_id = f.goods_category_id')
                ->where("f.goods_category_id IN ($categorySubQuery)");
            $db->setQuery($query);
            $goodsCategories = $db->loadObjectList('goods_category_id');
            $categories = array_keys($goodsCategories);
            $stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
            $goods = [];
            foreach ($categories as $category) {
                $goods = array_merge($goods, $stockModel->getGoodsByCategory($category));
            }

            foreach ($goods as $goodsItem){
               $jobs = explode(',', $goodsCategories[$goodsItem->category_id]->job_ids);

               foreach ($jobs as $job){
                   $result[$goodsItem->id][] = (object)["job_id"=>$job,"count"=>1];
               }
            }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function checkExist($ids){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id')
                ->from('`rgzbn_gm_stock_goods`')
                ->where("id in ($ids)");
            $db->setQuery($query);
            $result = $db->loadColumn();
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}