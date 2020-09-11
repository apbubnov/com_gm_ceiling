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
class Gm_ceilingModelAnalytic_detailed_new extends JModelList
{
	function getData($dealer_id,$date1=null,$date2 = null){
		if(empty($date1)){
			$date1 =  date("Y-m-d");
		}
		if(empty($date2)){
			$date2 =  date("Y-m-d");
		}
		$dealer_type = JFactory::getUser($dealer_id)->dealer_type;
		$result = [];
		$api_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
		$advt = $api_model->getDealersAdvt($dealer_id);
        $db = JFactory::getDbo();

        $query = 'SET SESSION group_concat_max_len  = 1048576';
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true);
        if($dealer_type){
            $query
                ->select('IFNULL(advt.id,0) AS advt_id,IFNULL(advt.name,\'Отсутствует\') AS advt_title');
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
                ->select('SUM( CASE WHEN ph.new_status = 20 THEN 1 ELSE 0 END) AS dealers')
                ->select('GROUP_CONCAT(IF(ph.new_status = 20,p.id,NULL) SEPARATOR \',\') AS dealers_projects')
                ->select('SUM( CASE WHEN ph.new_status = 21 THEN 1 ELSE 0 END) AS advt')
                ->select('GROUP_CONCAT(IF(ph.new_status = 21,p.id,NULL) SEPARATOR \',\') AS advt_projects');
        }
        $query
            ->select('COUNT(DISTINCT p.id) AS common')
            ->select('GROUP_CONCAT(p.id SEPARATOR \',\') AS common_projects')
            ->select('SUM( CASE WHEN ph.new_status = 15 THEN 1 ELSE 0 END) AS refuse')
            ->select('GROUP_CONCAT(IF(ph.new_status = 15,p.id,NULL) SEPARATOR \',\') AS refuse_projects')
            ->select('SUM( CASE WHEN ph.new_status = 2 THEN 1 ELSE 0 END) AS refuse_measure')
            ->select('GROUP_CONCAT(IF(ph.new_status = 2,p.id,NULL) SEPARATOR \',\') AS ref_measure_projects')
            ->select('SUM( CASE WHEN ph.new_status = 1 THEN 1 ELSE 0 END) AS measure')
            ->select('GROUP_CONCAT(IF(ph.new_status = 1,p.id,NULL) SEPARATOR \',\') AS measure_projects')
            ->select('SUM( CASE WHEN p.project_calculation_date = CURDATE() THEN 1 ELSE 0 END) AS current_measure')
            ->select('GROUP_CONCAT(IF(p.project_calculation_date = CURDATE(),p.id,NULL) SEPARATOR \',\') AS current_measure_projects')
            ->select('SUM( CASE WHEN ph.new_status = 3 THEN 1 ELSE 0 END) AS ref_deals')
            ->select('GROUP_CONCAT(IF(ph.new_status = 3,p.id,NULL) SEPARATOR \',\') AS ref_deals_projects')
            ->select('SUM( CASE WHEN ph.new_status IN (4,5) THEN 1 ELSE 0 END) AS deals')
            ->select('GROUP_CONCAT(IF(ph.new_status IN (4,5),p.id,NULL) SEPARATOR \',\') AS deals_projects')
            ->select('SUM( CASE WHEN ph.new_status IN (4,5) THEN IF(p.new_project_sum <> 0,p.new_project_sum,p.project_sum) ELSE 0 END) AS deals_sum')
            ->select('SUM( CASE WHEN ph.new_status = 12 THEN 1 ELSE 0 END) AS done')
            ->select('GROUP_CONCAT(IF(ph.new_status IN (12),p.id,NULL) SEPARATOR \',\' ) AS done_projects')
            ->select('SUM( CASE WHEN ph.new_status IN (12) THEN IF(p.new_project_sum <> 0,p.new_project_sum,p.project_sum) ELSE 0 END) AS done_sum')
            ->select('SUM( CASE WHEN ph.new_status IN (12) THEN (p.new_project_sum - (p.new_material_sum+p.new_mount_sum)) ELSE 0 END) AS profit')
            ->from('`rgzbn_gm_ceiling_projects` AS p')
            ->leftJoin('`rgzbn_gm_ceiling_projects_history` AS ph  ON ph.project_id = p.id')
            ->leftJoin('`rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id')
            ->leftJoin('`rgzbn_gm_ceiling_api_phones` AS advt ON advt.id = p.api_phone_id')
            ->where("(ph.date_of_change BETWEEN '$date1' AND '$date2' )")
            ->group('advt_id');
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

        $diff = array_diff(array_keys($advt),array_keys($data));
        if(!in_array('0',array_keys($data))){
            array_push($diff,0);
        }
        if(!$dealer_type) {
            if (!in_array('otd', array_keys($data))) {
                array_push($diff, 'otd');
            }
            if (!in_array('win', array_keys($data))) {
                array_push($diff, 'win');
            }
        }
        foreach ($diff as $id){
            $item = $advt[$id];
            if(empty($item)){
                $advtTitle = "";
                switch ($id){
                    case '0':
                        $advtTitle = "Отсутсвует";
                        break;
                    case 'win':
                        $advtTitle = "Оконщики";
                        break;
                    case 'otd':
                        $advtTitle = "Отделочники";
                        break;
                }
                $item = ["id"=>$id,"advt_title"=>$advtTitle];
            }
            $advtAnalytic = (object)[
                "id" => $item["id"],
                "advt_title" => $item["advt_title"],
                "common"=> 0,
                "dealers" => 0,
                "advt" => 0,
                "refuse" => 0,
                "ref_measure" => 0,
                "measure" => 0,
                "current_measures" => 0,
                "ref_deals" => 0,
                "deals" => 0,
                "sum_deals"=> 0,
                "current_mounts" => 0,
                "closed" => 0,
                "sum_done" => 0,
                "profit"=> 0,
                "expenses"=> 0,
                "projects" => [
                    "common"=> "",
                    "dealers" => "",
                    "advt" => "",
                    "ref_measure" => "",
                    "measure" => "",
                    "current_measure" => "",
                    "ref_deals" => "",
                    "deals" => "",
                    "closed" => "",
                    "current_mounts" => ""
                ]
            ];
            $result[$item["id"]] = $advtAnalytic;
        }
        foreach ($data as $item){
            $advtAnalytic = (object)[
                "id" => $item->advt_id,
                "advt_title" => $item->advt_title,
                "common"=> $item->common,
                "dealers" => $item->dealers,
                "advt" => $item->advt,
                "refuse" => $item->refuse,
                "ref_measure" => $item->ref_measure,
                "measure" => $item->measure,
                "current_measures" => 0,
                "ref_deals" => $item->ref_deals,
                "deals" => $item->deals,
                "sum_deals"=> $item->deals_sum,
                "current_mounts" => 0,
                "closed" =>  $item->done,
                "sum_done" => $item->done_sum,
                "profit"=> $item->profit,
                "expenses"=> $item->expenses,
                "projects" => [
                    "common"=> $item->common_projects,
                    "dealers" => $item->dealers_projects,
                    "advt" => $item->advt_projects,
                    "refuse" => $item->refuse_projects,
                    "ref_measure" => $item->ref_measure_projects,
                    "measure" => $item->measure_projects,
                    "current_measures" => $item->current_measure_projects,
                    "ref_deals" => $item->ref_deals_projects,
                    "deals" => $item->deals_projects,
                    "sum_deals" => $item->deals_projects,
                    "current_mounts" => $item->current_mount_projects,
                    "closed" => $item->done_projects,
                    "sum_done" => $item->done_projects,
                    "profit" => $item->done_projects
                ]
            ];
            $result[$item->advt_id] = $advtAnalytic;
        }
		$measures = $this->getCurrentMeasures($dealer_id);
		if(!empty($measures)){
		    foreach($measures as $measure){
		        if(!empty($measure->advt_id)){
                    $result[$measure->advt_id]->common += $measure->count;
                    if(!empty($result[$measure->advt_id]->projects["common"])) {
                        $result[$measure->advt_id]->projects["common"] .= ",";
                    }
                    $result[$measure->advt_id]->projects["common"] .= "$measure->projects";
                    $result[$measure->advt_id]->current_measures = $measure->count;
                    $result[$measure->advt_id]->projects["current_measures"] = $measure->projects;
                }
            }
        }
		$mounts = $this->getCurrentMounts($dealer_id);
        if(!empty($mounts)){
            foreach($mounts as $mount){
                if(!empty($mount->advt_id)) {
                    $result[$mount->advt_id]->common += $mount->count;
                    if(!empty($result[$mount->advt_id]->projects["common"])){
                        $result[$mount->advt_id]->projects["common"] .= ",";
                    }
                    $result[$mount->advt_id]->projects["common"] .="$mount->projects";
                    $result[$mount->advt_id]->current_mounts = $mount->count;
                    $result[$mount->advt_id]->projects["current_mounts"] = $mount->projects;
                }
            }
        }
        $customAdvtId = ['0','otd','win'];
        usort($result, function($a, $b) use ($customAdvtId) {
            if(!in_array($a->id,$customAdvtId)&&!in_array($b->id,$customAdvtId)){
                return $a->id > $b->id;
            }
            else{
                return 0;
            }
        });
		if(!$dealer_type){
			$biases = [4,5,6];
		}
		else{
			$biases = [4,3,4];
		}
		$header = (object)[
			"advt_title" => (object)["head_name" =>"Реклама","rowspan"=>2],
			"common" => (object)["head_name" =>"Всего","rowspan"=>2],
			"dealers" => (object)["head_name" =>"Дилеры","rowspan"=>2],
			"advt" => (object)["head_name" =>"Реклама","rowspan"=>2],
			"refuse" => (object)["head_name" =>"Отказ от сотрудничества","rowspan"=>2],
			"measures" => (object)["head_name"=>"Замеры","bias"=>$biases[1],"columns"=>["ref_measure" => "Отказ","measure" => "Запись","current_measures" => "Текущие"]],
			"deal" => (object)["head_name"=>"Договоры","bias"=>$biases[1],"columns"=>["ref_deals" => "Отказ","deals" => "Договор","sum_deals" => "Сумма"]],
			"current_mounts" => (object)["head_name" =>"Монтажи","rowspan"=>2,"bias"=>$biases[0]],
			"close" => (object)["head_name"=>"Закрытые","bias"=>$biases[2],"columns"=>["closed" => "Кол-во","sum_done" => "Сумма","profit"=>"Прибыль"]]
			];
		if($dealer_type){
		    unset($header->dealers);
		    unset($header->advt);
        }
		array_unshift($result, $header);
		return $result;
	}

	function getCurrentMeasures($dealer_id){
		try{
		    $today = date('Y-m-d');
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
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

				->select("COUNT(DISTINCT p.id) AS `count`,GROUP_CONCAT(p.id SEPARATOR ',') AS `projects`")
				->from('`rgzbn_gm_ceiling_projects` AS p')
                ->leftJoin('`rgzbn_gm_ceiling_api_phones` AS advt ON advt.id = p.api_phone_id')
                ->leftJoin('`rgzbn_gm_ceiling_clients` AS c ON p.client_id = c.id')
                ->leftJoin('`rgzbn_users` AS u ON u.id = c.dealer_id')
				->where("(c.dealer_id = $dealer_id OR u.dealer_id = $dealer_id) AND p.project_calculation_date BETWEEN '$today 00:00:00' AND '$today 23:59:59' AND (advt.dealer_id = $dealer_id OR advt.dealer_id IS NULL) AND p.project_status = 1")
                ->group('advt_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function getCurrentMounts($dealer_id){
		try{
		    $today = date('Y-m-d');
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
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

                ->select("COUNT(DISTINCT p.id) AS `count`,GROUP_CONCAT(p.id SEPARATOR ',') AS `projects`")
                ->from('`rgzbn_gm_ceiling_projects` AS p')
                ->leftJoin('`rgzbn_gm_ceiling_api_phones` AS advt ON advt.id = p.api_phone_id')
                ->leftJoin('`rgzbn_gm_ceiling_clients` AS c ON p.client_id = c.id')
                ->leftJoin('`rgzbn_users` AS u ON u.id = c.dealer_id')
                ->leftJoin('`rgzbn_gm_ceiling_projects_mounts` AS pm ON pm.project_id = p.id')
                ->where("(c.dealer_id = $dealer_id OR u.dealer_id = $dealer_id) AND pm.date_time BETWEEN '$today 00:00:00' AND '$today 23:59:59' AND (advt.dealer_id = $dealer_id OR advt.dealer_id IS NULL)")
                ->group('advt_id');
            $db->setQuery($query);
			$result = $db->loadObjectList();
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
