<?php

/**
 * @package    Com_Gm_ceiling
 * @author     Alexandr <al.p.bubnov@gmail.com>
 * @copyright  GM
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelAnalytic_new extends JModelList
{
    function getData($dealer_id,$date1=null,$date2=null){
        $dealer_type = JFactory::getUser($dealer_id)->dealer_type;
        $result = [];
       //$statuses = array("dealers"=>[20],"advt"=>[21],"refuse"=>[15],"inwork"=>[0,2,3],"measure"=>[1],"deals"=>[4,5,6,7,8,10,11,12,16,17,19,24,25,26],"done"=>[12],"sum"=>[12],"profit"=>[12]);
        $advtModel = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
        $allAdvt = $advtModel->getDealersAdvt($dealer_id);
        $db = JFactory::getDbo();

        $sessionQuery = 'SET SESSION group_concat_max_len  = 1048576';
        $db->setQuery($sessionQuery);
        $db->execute();

        $query = $db->getQuery(true);
        if($dealer_type){
            $query
                ->select('IFNULL(advt.id,0) AS advt_id,IFNULL(advt.name,\'Отсутствует\') AS advt_title,advt.expenses');
        }
        else{
            $query
                ->select('CASE
                            WHEN p.api_phone_id IS NULL AND u.dealer_type = 3 THEN \'otd\'
                            WHEN p.api_phone_id IS NULL AND u.dealer_type = 8 THEN \'win\'
                            WHEN p.api_phone_id IS NULL AND u.dealer_type NOT IN(3,8) THEN 0
                            ELSE p.api_phone_id END  AS advt_id')
                ->select('CASE
                            WHEN p.api_phone_id IS NULL AND u.dealer_type = 3 THEN \'Отделочники\'
                            WHEN p.api_phone_id IS NULL AND u.dealer_type = 8 THEN \'Оконщики\'
                            WHEN p.api_phone_id IS NULL AND u.dealer_type NOT IN(3,8) THEN \'Отсутствует\'
                            ELSE advt.name END AS advt_title')
                ->select('SUM( CASE WHEN p.project_status = 20 THEN 1 ELSE 0 END) AS dealers')
                ->select('GROUP_CONCAT(IF(p.project_status = 20,p.id,NULL) SEPARATOR \',\') AS dealers_projects')
                ->select('SUM( CASE WHEN p.project_status = 21 THEN 1 ELSE 0 END) AS advt')
                ->select('GROUP_CONCAT(IF(p.project_status = 21,p.id,NULL) SEPARATOR \',\') AS advt_projects')
                ;
        }
        $query
            ->select('COUNT(DISTINCT p.id) AS common')
            ->select('GROUP_CONCAT(p.id ORDER BY p.id ASC SEPARATOR \',\') AS common_projects')
            ->select('SUM( CASE WHEN p.project_status = 15 THEN 1 ELSE 0 END) AS refuse')
            ->select('GROUP_CONCAT(IF(p.project_status = 15,p.id,NULL) ORDER BY p.id ASC SEPARATOR \',\') AS refuse_projects')
            ->select('SUM( CASE WHEN p.project_status IN (0,2,3) THEN 1 ELSE 0 END) AS inwork')
            ->select('GROUP_CONCAT(IF(p.project_status IN (0,2,3),p.id,NULL) ORDER BY p.id ASC SEPARATOR \',\') AS inwork_projects')
            ->select('SUM( CASE WHEN p.project_status = 1 THEN 1 ELSE 0 END) AS measure')
            ->select('GROUP_CONCAT(IF(p.project_status IN (1),p.id,NULL) ORDER BY p.id ASC SEPARATOR \',\') AS measure_projects')
            ->select('SUM( CASE WHEN p.project_status IN (4,5,6,7,8,10,11,16,17,19,24,25,26) THEN 1 ELSE 0 END) AS deals')
            ->select('GROUP_CONCAT(IF(p.project_status IN (4,5,6,7,8,10,11,16,17,19,24,25,26),p.id,NULL) ORDER BY p.id ASC SEPARATOR \',\') AS deals_projects')
            ->select('SUM( CASE WHEN p.project_status IN (4,5,6,7,8,10,11,16,17,19,24,25,26) THEN IF(p.new_project_sum <> 0,p.new_project_sum,p.project_sum) ELSE 0 END) AS deals_sum')
            ->select('SUM( CASE WHEN p.project_status = 12 THEN 1 ELSE 0 END) AS done')
            ->select('GROUP_CONCAT(IF(p.project_status IN (12),p.id,NULL) ORDER BY p.id ASC SEPARATOR \',\') AS done_projects')
            ->select('SUM( CASE WHEN p.project_status IN (12) THEN
                                (CASE
                                    WHEN p.new_project_sum != 0 THEN p.new_project_sum
                                    WHEN (p.new_project_sum = 0 OR p.new_project_sum IS NULL) AND p.project_sum != 0 AND p.project_sum IS NOT NULL THEN p.project_sum
                                    ELSE 0
                                END)
                            ELSE 0
                           END) AS done_sum')
            ->select('SUM( CASE WHEN p.project_status IN (12) THEN (
                        CASE
                            WHEN p.new_material_sum != 0 AND p.new_material_sum IS NOT NULL  AND p.new_mount_sum != 0  AND p.new_mount_sum IS NOT NULL THEN p.new_material_sum+p.new_mount_sum
                            ELSE 	
                                (
                                SELECT SUM(calc.canvases_sum + calc.components_sum + calc.mounting_sum) 
                                FROM `rgzbn_gm_ceiling_calculations`  AS calc 
                                WHERE calc.project_id = p.id
                                )
                            +
                                ROUND(CASE 
                                    WHEN p.transport = 1 THEN (p.distance_col*`di`.`transport`)
                                    WHEN p.transport = 2 THEN IF(`p`.`distance` < 50,500*`p`.`distance_col`,`p`.`distance_col`*`p`.`distance`*`di`.`distance`)
                                    ELSE 0
                                    END,0)
                        END
                        ) 
                        ELSE 0 END) AS cost')
            ->from('`rgzbn_gm_ceiling_projects` AS p')
            ->leftJoin('`rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id')
            ->leftJoin('`rgzbn_gm_ceiling_api_phones` AS advt ON advt.id = p.api_phone_id')
            ->leftJoin('`rgzbn_gm_ceiling_dealer_info` AS di ON di.dealer_id = c.dealer_id')
            ->where("(advt.dealer_id = $dealer_id or advt.dealer_id IS NULL)")
            ->group('advt_id');
        if(!empty($date1)&&!empty($date2)){
            $query->where("p.created BETWEEN '$date1' and '$date2'");
        }
        if(!empty($date1) && empty($date2)){
            $query->where("p.created >= '$date1' ");
        }
        if(empty($date1) && !empty($date2)){
            $query->where("p.created <= '$date2' ");
        }
        if(empty($date1)&& empty($date2)){
            $query->where('p.created <= CURDATE()');
        }
        if($dealer_type){
            $query->where("c.dealer_id = $dealer_id");
        }
        else{
            $query
                ->leftJoin('`rgzbn_users` AS u ON u.id = c.dealer_id')
                ->where("(c.dealer_id = $dealer_id OR u.dealer_id = $dealer_id) and u.dealer_type != 7");
        }
        $db->setQuery($query);
        $data = $db->loadObjectList('advt_id');

        $diff = array_diff(array_keys($allAdvt),array_keys($data));
        foreach ($diff as $id){
            $item = $allAdvt[$id];
            $advtAnalytic = (object)[
                "id" => $item["id"],
                "advt_title" => $item["advt_title"],
                "common"=> 0,
                "dealers" => 0,
                "advt" => 0,
                "refuse" => 0,
                "inwork" => 0,
                "measure" => 0,
                "deals" => 0,
                "deals_sum"=> 0,
                "done" => 0,
                "sum" => 0,
                "profit"=> 0,
                "expenses"=> 0,
                "projects" => [
                    "common"=> "",
                    "dealers" => "",
                    "advt" => "",
                    "refuse" => "",
                    "inwork" => "",
                    "measure" => "",
                    "deals" => "",
                    "deals_sum" => "",
                    "done" => "",
                    "sum" => "",
                    "profit" => ""
                ]
            ];
            $result[] = $advtAnalytic;
        }
        foreach ($data as $item){
            $advtAnalytic = (object)[
                "id" => $item->advt_id,
                "advt_title" => $item->advt_title,
                "common"=> $item->common,
                "dealers" => $item->dealers,
                "advt" => $item->advt,
                "refuse" => $item->refuse,
                "inwork" => $item->inwork,
                "measure" => $item->measure,
                "deals" => $item->deals,
                "deals_sum"=> $item->deals_sum,
                "done" =>  $item->done,
                "sum" => $item->done_sum,
                "profit"=> $item->done_sum - $item->cost,
                "expenses"=> $item->expenses,
                "projects" => [
                    "common"=> $item->common_projects,
                    "dealers" => $item->dealers_projects,
                    "advt" => $item->advt_projects,
                    "refuse" => $item->refuse_projects,
                    "inwork" => $item->inwork_projects,
                    "measure" => $item->measure_projects,
                    "deals" => $item->deals_projects,
                    "deals_sum" => $item->deals_projects,
                    "done" => $item->done_projects,
                    "sum" => $item->done_projects,
                    "profit" => $item->done_projects
                ]
            ];
            $result[] = $advtAnalytic;
        }

        $header = (object)array(
            "advt_title" => "Реклама",
            "common" => "Всего",
            "dealers" => "Дилеры",
            "advt" => "Реклама",
            "refuse" => "Отказ от сотрудничества",
            "inwork" => "В работе",
            "measure" => "Замеры",
            "deals" => "Договоры",
            "deals_sum" => "Сумма",
            "done" => "Завершенные",
            "sum" => "Сумма",
            "profit"=> "Прибыль"
        );

        $customAdvtId = ['0','otd','win'];
        usort($result, function($a, $b) use ($customAdvtId) {
            if(!in_array($a->id,$customAdvtId)&&!in_array($b->id,$customAdvtId)){
                return $a->id > $b->id;
            }
            else{
                return 0;
            }
        });
        if($dealer_type){
            unset($header->dealers);
            unset($header->advt);
        }
        array_unshift($result, $header);
        return $result;
    }

    function getGaugersAnalytic($date1,$date2){
        try{
            $user = JFactory::getUser();
            $dealerId = $user->dealer_id;
            $db = JFactory::getDbo();

            $query = 'SET SESSION group_concat_max_len  = 81920';
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $gaugersQuery = $db->getQuery(true);

            $gaugersQuery
                ->select('u.id,u.name,
                        0 as all_measures_count,
                        0 as complete_measure_count,
                        0 as deals_count,
                        0 as refused_deals_count,
                        0 as closed_count,
                        0 as deals_sum,
                        0 as deals_cost,
                        0 as closed_sum,
                        0 as closed_cost,
                        \'\' as all_measures,
                        \'\' as complete_measures,
                        \'\' as deals,
                        \'\' as refused_deals,
                        \'\' as closed')
                ->from('`rgzbn_users` as u')
                ->innerJoin('`rgzbn_user_usergroup_map` as um on um.user_id = u.id')
               /* ->where('u.dealer_id = 697 and (um.group_id = 22 OR um.group_id = 21)');*/
                ->where("u.dealer_id = $dealerId and (um.group_id = 22 OR um.group_id = 21)");
            $db->setQuery($gaugersQuery);
            $gaugersData = $db->loadObjectList('id');
            $query
                ->select('p.project_calculator AS calculator_id,ph.project_id,COUNT(DISTINCT c.id) AS calcs_count')
                ->select('(CASE WHEN (p.new_project_sum IS NOT NULL AND p.new_project_sum > 0)  THEN p.new_project_sum ELSE p.project_sum END) AS total_sum')
                ->select('(CASE WHEN (p.new_material_sum IS NOT NULL AND p.new_material_sum > 0 AND p.new_mount_sum IS NOT NULL AND p.new_mount_sum > 0 )
                                  THEN p.new_material_sum + p.new_mount_sum
                                  ELSE 	
                                  (
                                   SELECT SUM(calc.canvases_sum + calc.components_sum + calc.mounting_sum)
                                                            FROM `rgzbn_gm_ceiling_calculations` AS calc
                                                            WHERE calc.project_id = p.id
                                   ) + CASE p.transport
                                    WHEN 1 THEN (p.distance_col*di.transport)
                                    WHEN 2 THEN IF(p.distance <= 50,(500*p.distance_col),(p.distance*p.distance_col*di.distance))
                                    ELSE 0
                                END
                               END
                             ) AS total_cost')
                ->select('CONCAT(\'[\',GROUP_CONCAT( DISTINCT CONCAT(\'{"status":"\',ph.new_status,\'","date":"\',ph.date_of_change,\'"}\') ORDER BY ph.new_status ASC SEPARATOR \',\'),\']\') AS history')
                ->from('`rgzbn_gm_ceiling_projects_history` AS ph')
                ->innerJoin('`rgzbn_gm_ceiling_projects` AS p ON p.id = ph.project_id')
                ->innerJoin('`rgzbn_users` AS u ON u.id = p.project_calculator')
                ->leftJoin('`rgzbn_gm_ceiling_calculations` AS c ON c.project_id= ph.project_id')
               /* ->innerJoin('`rgzbn_gm_ceiling_dealer_info` AS di ON di.dealer_id = 697')*/
                ->innerJoin("`rgzbn_gm_ceiling_dealer_info` AS di ON di.dealer_id = $dealerId")
                /*->where("ph.new_status IN (1,2,3,4,5,12,15) AND ph.date_of_change BETWEEN '2020-07-01' AND '2020-07-29' AND p.project_calculator IS NOT NULL AND u.dealer_id = 697")*/
                ->where("ph.new_status IN (1,2,3,4,5,12,15) AND ph.date_of_change BETWEEN '$date1' AND '$date2' AND p.project_calculator IS NOT NULL AND u.dealer_id = $dealerId")
                ->group('ph.project_id');
            $db->setQuery($query);
            //throw new Exception($query);
            $data = $db->loadObjectList();
            foreach ($data as $key => $value) {
                $projectHistory = json_decode($value->history);
                $measure = array_filter($projectHistory, function ($e){
                    return $e->status == 1;
                });
                $refuse_measure = array_filter($projectHistory, function ($e) use (&$value) {
                    return ($e->status == 2 ) || (($e->status == 3 || $e->status == 15) && $value->calcs_count == 0);
                });
                $refuse_deal = array_filter($projectHistory, function ($e) use (&$value) {
                    return ($e->status == 3 || $e->status == 15) && $value->calcs_count > 0;
                });
                $deal = array_filter($projectHistory, function ($e){
                    return $e->status == 4 || $e->status == 5;
                });
                $closed = array_filter($projectHistory, function ($e){
                    return $e->status == 12;
                });
                if(!empty($measure)){
                    $gaugersData[$value->calculator_id]->all_measures_count++;
                    if(!empty( $gaugersData[$value->calculator_id]->all_measures)){
                        $gaugersData[$value->calculator_id]->all_measures .= ",$value->project_id";
                    }
                    else{
                        $gaugersData[$value->calculator_id]->all_measures .= $value->project_id;
                    }
                    if(!empty($refuse_deal || $deal)){
                        $gaugersData[$value->calculator_id]->complete_measure_count++;
                        if(!empty( $gaugersData[$value->calculator_id]->complete_measures)){
                            $gaugersData[$value->calculator_id]->complete_measures .= ",$value->project_id";
                        }
                        else{
                            $gaugersData[$value->calculator_id]->complete_measures .= $value->project_id;
                        }
                    }
                    if(!empty($refuse_measure) && $refuse_measure->date>=$measure->date){
                        $gaugersData[$value->calculator_id]->complete_measure_count--;
                    }
                    if(!empty($deal)){
                        $gaugersData[$value->calculator_id]->deals_count++;
                        $gaugersData[$value->calculator_id]->deals_sum+=$value->total_sum;
                        $gaugersData[$value->calculator_id]->deals_cost+=$value->total_cost;
                        if(!empty( $gaugersData[$value->calculator_id]->deals)){
                            $gaugersData[$value->calculator_id]->deals .= ",$value->project_id";
                        }
                        else{
                            $gaugersData[$value->calculator_id]->deals .= $value->project_id;
                        }
                    }
                    if(!empty($refuse_deal)){
                        $gaugersData[$value->calculator_id]->refused_deals_count++;
                        if(!empty( $gaugersData[$value->calculator_id]->refused_deals)){
                            $gaugersData[$value->calculator_id]->refused_deals .= ",$value->project_id";
                        }
                        else{
                            $gaugersData[$value->calculator_id]->refused_deals .= $value->project_id;
                        }
                    }
                    if(!empty($closed)){
                        $gaugersData[$value->calculator_id]->closed_count++;
                        $gaugersData[$value->calculator_id]->closed_sum+=$value->total_sum;
                        $gaugersData[$value->calculator_id]->closed_cost+=$value->total_cost;
                        if(!empty( $gaugersData[$value->calculator_id]->closed)){
                            $gaugersData[$value->calculator_id]->closed .= ",$value->project_id";
                        }
                        else{
                            $gaugersData[$value->calculator_id]->closed .= $value->project_id;
                        }
                    }
                }
            }

            return $gaugersData;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getManagersAnalytic($date1,$date2){
        try{
            $user = JFactory::getUser();
            $dealerId = $user->dealer_id;
            $db = JFactory::getDbo();

            $group = $dealerId == 1 ? 16 : 13;
            $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $managers = $userModel->getUsersByGroupAndDealer($group,$dealerId);
            $data = [];
            foreach ($managers as $manager){
                $data[$manager->id] = (object)[
                    'name' => $manager->name,
                    'projects' => '',
                    'projects_count' => 0,
                    'clients_count' => 0,
                    'clients' => ''
                ];
            }
            $query = 'SET SESSION group_concat_max_len  = 81920';
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $historyQuery = $db->getQuery(true);

            $historyQuery
                ->select('CONCAT(\'{"project_id":"\',p.id,\'","calcs_count":"\',COUNT(DISTINCT c.id),\'","history":[\',GROUP_CONCAT(DISTINCT CONCAT(\'{"status":"\',ph.new_status,\'","date":"\',ph.date_of_change,\'"}\'),\']}\' SEPARATOR \',\')) AS history')
                ->from('`rgzbn_gm_ceiling_projects_history` AS ph')
                ->leftJoin('`rgzbn_gm_ceiling_projects` AS p ON p.id = ph.project_id')
                ->leftJoin('`rgzbn_gm_ceiling_calculations` AS c ON c.project_id = ph.project_id')
                ->where("ph.date_of_change BETWEEN '$date1' AND '$date2' AND p.client_id = ch.client_id AND ph.new_status IN(1,2,3,15)");
            $query
                ->select("ch.client_id,ch.manager_id,u.name,($historyQuery) as projects")
                ->from('`rgzbn_gm_ceiling_calls_status_history` AS ch')
                ->leftJoin('`rgzbn_users` AS u ON u.id = ch.manager_id')
                ->leftJoin('`rgzbn_user_usergroup_map` AS um ON um.user_id = u.id')
                ->where("ch.change_time BETWEEN '$date1 00:00:00' AND '$date2 23:59:59' AND u.dealer_id = $dealerId and um.group_id = $group")
                ->group('ch.client_id');

            $db->setQuery($query);
            $history = $db->loadObjectList();

            foreach ($history as $value){
                $data[$value->manager_id]->clients_count++;
                $data[$value->manager_id]->clients .= empty($data[$value->manager_id]->clients) ? $value->client_id : ",$value->client_id";
                $projects = json_decode($value->projects);
                $measure = array_filter($projects->history, function ($e){
                    return $e->status == 1;
                });
                if(!empty($measure)){
                    $refuse_measure = array_filter($projects->history, function ($e) use (&$projects) {
                        return ($e->status == 2 ) || (($e->status == 3 || $e->status == 15) && $projects->calcs_count == 0);
                    });

                    if(empty($refuse_measure) || $refuse_measure->date<$measure->date || $value->calcs_count >0){
                        $data[$value->manager_id]->projects_count++;
                        $data[$value->manager_id]->projects .= empty($data[$value->manager_id]->projects) ? $projects->project_id : ",$projects->project_id";
                    }
                }

            }
            return $data;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getMountServiceAnalytic($dateFrom,$dateTo){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('DISTINCT p.id,u.name,p.calcs_mounting_sum,SUM(calc.mounting_sum) AS mounting_sum')
                ->from('`rgzbn_gm_ceiling_projects` AS p')
                ->leftJoin('`rgzbn_gm_ceiling_projects_history` AS ph ON ph.project_id = p.id')
                ->leftJoin('`rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id')
                ->leftJoin('`rgzbn_gm_ceiling_calculations` AS calc ON calc.project_id = p.id')
                ->leftJoin('`rgzbn_gm_ceiling_dealer_info` AS di ON di.dealer_id = c.dealer_id')
                ->leftJoin('`rgzbn_users` AS u ON u.id = c.dealer_id')
                ->where("ph.new_status = 5 AND ph.date_of_change BETWEEN '$dateFrom' AND '$dateTo' AND calcs_mounting_sum != '' AND c.dealer_id NOT IN(1,2785)")
                ->group('p.id');
            $db->setQuery($query);
            $result = $db->loadObjectList();
            if(!empty($result)){
                $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
                foreach ($result as $key => $value) {
                    $calcsMountSums = json_decode($value->calcs_mounting_sum);
                    foreach ($calcsMountSums as $item){
                        $result[$key]->serviceSum += $item;
                    }
                    $mountSum = $projectModel->getMountingSum($value->id,1);
                    $result[$key]->mounting_sum = !empty($mountSum->price_sum) ? $mountSum->price_sum : 0;
                }

            }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getGoodsAnalytic($dateFrom,$dateTo,$goodsId = null){
        try{
            if(empty($dateFrom)){
                $dateFrom = date('Y-m-d 00:00:00');
            }
            if(empty($dateTo)){
                $dateTo = date('Y-m-d 23:59:59');
            }
            $db = JFactory::getDbo();
            $inventoryCostQuery = '(SELECT mov.to_inventory_id AS inventory_id,rec.cost_price
                                    FROM `rgzbn_gm_stock_moving` AS mov
                                    INNER JOIN `rgzbn_gm_stock_inventory` AS i ON i.id = mov.to_inventory_id
                                    INNER JOIN `rgzbn_gm_stock_reception` AS rec ON rec.inventory_id = mov.from_inventory_id)
                                    UNION ALL
                                    (SELECT inventory_id,cost_price
                                    FROM `rgzbn_gm_stock_reception`)';
            $query = $db->getQuery(true);
            $query
                ->select(' g.id,g.name,u.unit,SUM(s.count) AS total_count,SUM(s.sale_price*s.count) AS total_sum,SUM(s.count*c.cost_price) AS total_cost')
                ->from('`rgzbn_gm_stock_sales` AS s')
                ->leftJoin('`rgzbn_gm_stock_inventory` AS i ON i.id = s.inventory_id')
                ->leftJoin("($inventoryCostQuery) AS c ON c.inventory_id = s.inventory_id")
                ->leftJoin('`rgzbn_gm_stock_goods` AS g ON g.id = i.goods_id')
                ->innerJoin('`rgzbn_gm_stock_units` AS u ON u.id = g.unit_id')
                ->where("s.date_time BETWEEN '$dateFrom' AND '$dateTo'")
                ->group('g.id')
                ->order('total_count DESC');
            if(!empty($goodsId)){
                $query->where("g.id IN ($goodsId)");
            }
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}