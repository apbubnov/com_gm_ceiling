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
		$api_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
		$advt = $api_model->getDealersAdvt($dealer_id);
		$statuses = array("dealers"=>[20],"advt"=>[21],"refuse"=>[15],"inwork"=>[0,2,3],"measure"=>[1],"deals"=>[4,5,6,7,8,10,11,12,16,17,19,24,25,26],"done"=>[12],"sum"=>[12],"profit"=>[12]);

		if(!$dealer_type) {
            $advt['otd']['id'] = "otd";
            $advt['otd']['advt_title'] = 'Отделочники';
            $advt['win']['id'] = "win";
            $advt['win']['advt_title'] = 'Оконщики';
        }
		$advt[0]['id'] = "0";
		$advt[0]['advt_title'] = 'Отсутствует';

		foreach ($advt as $id => $advt_obj) {
			foreach ($statuses as $key => $status) {
				$advt[$id][$key] = 0;
				$advt[$id]['projects'] = "";
				$advt[0][$key] = 0;
			}
		}
		$ids = [];
		$projects = $this->getDataByParameters($dealer_id,$date1,$date2);
		$this->addData($advt,$projects,$dealer_id,$dealer_type,$date1,$date2,$statuses);
		if(!$dealer_type){
			$projects = $this->getDataByParameters($dealer_id,$date1,$date2,3);
			$this->addData($advt,$projects,$dealer_id,$dealer_type,$date1,$date2,$statuses);
			$projects = $this->getDataByParameters($dealer_id,$date1,$date2,8);
			$this->addData($advt,$projects,$dealer_id,$dealer_type,$date1,$date2,$statuses);
		}
		$result = [];
		foreach ($advt as $key => $value) {
			$result[] = $value;
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
			"done" => "Завершенные",
			"sum" => "Сумма",
			"profit"=> "Прибыль",
            "expenses" => "Затраты"
			);
		
		array_unshift($result, $header);
		if($dealer_type){
			$this->unset_columns($result);
		}
		return $result;
	}
	function getDataByParameters($dealer_id,$date1,$date2,$dealer_type = null){
		try{
			if($dealer_type == 3){
				$advt = 'otd';
			}
			if($dealer_type == 8){
				$advt = 'win';
			}
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			if(empty($dealer_type)){
				$query
					->select('*')
					->where("client_dealer_id = $dealer_id and (advt_owner = $dealer_id OR advt_owner is NULL)");
			}
			else{
				$query
					->select("project_id,project_status,'$advt' as api_phone_id,sum,profit")
					->where("dealer_id = $dealer_id and dealer_type = $dealer_type and (advt_owner = $dealer_id OR advt_owner is NULL)");
			}
			$query->from('#__analytic');
				
			/*if(!empty($statuses)){
				$query->where("project_status in $statuses");
			}*/
			if(!empty($date1)&&!empty($date2)){
				$query->where("created BETWEEN '$date1' and '$date2'");
			}
			if(!empty($date1) && empty($date2)){
				$query->where("created >= '$date1' ");
			}
			if(empty($date1) && !empty($date2)){
				$query->where("created <= '$date2' ");
			}
			$db->setQuery($query);
			$result = $db->loadObjectList();
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function addData(&$advt,$projects,$dealer_id,$dealer_type,$date1,$date2,$statuses){
		foreach ($projects as $project) {
			$advt_id = (empty($project->api_phone_id)) ? 0 : $project->api_phone_id;
			$advt[$advt_id]['projects']["common"] .= $project->project_id . ";";
			$advt[$advt_id]["common"]++;
			$advt[$advt_id]['expenses'] = $project->advt_expenses;
			foreach ($statuses as $key => $statuses_arr) {
				if(!is_null($project->project_status) && in_array($project->project_status, $statuses_arr)){
					$advt[$advt_id]['projects'][$key] .= $project->project_id . ";";
					$advt[$advt_id][$key]++;
				}
				if($project->project_status == 12 && $key == 'done'){
					$advt[$advt_id]['sum'] += $project->sum;
					$advt[$advt_id]['profit'] += $project->profit;
				}
			}
		}
	}
	function unset_columns($data){
		try{

			foreach ($data as $key => $value) {
				unset($data[$key]->dealers);
				unset($data[$key]->advt);
			}
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getGaugersAnalytic($date1,$date2){
	    try{
	        $user = JFactory::getUser();
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $costQuery = $db->getQuery(true);
            $projectsQuery = $db->getQuery(true);

            $costQuery
                ->select('c.project_id,SUM(c.canvases_sum + c.components_sum + c.mounting_sum)+ 
                            (CASE pr.transport
                                WHEN 1 THEN (pr.distance_col*m.transport)
                                WHEN 2 THEN IF(pr.distance <= 50,(500*pr.distance_col),(pr.distance*pr.distance_col*m.distance))
                                ELSE 0
                            END) AS cost')
                ->from('`rgzbn_gm_ceiling_calculations` AS c')
                ->innerJoin('`rgzbn_gm_ceiling_projects` AS pr ON pr.id = c.project_id')
                ->innerJoin("`rgzbn_gm_ceiling_mount` AS m ON m.user_id = $user->dealer_id")
                ->group('c.project_id');
            $projectsQuery
                ->select('p.project_calculator,COUNT(DISTINCT p.id) AS projects_count,GROUP_CONCAT(p.id) AS projects')
                ->select('SUM(CASE 
								WHEN (p.new_project_sum IS NOT NULL AND p.new_project_sum > 0)  THEN p.new_project_sum
								ELSE p.project_sum
							END) AS total_sum')
                ->select('SUM(CASE
								WHEN (p.new_material_sum IS NOT NULL AND p.new_material_sum > 0 AND p.new_mount_sum IS NOT NULL AND p.new_mount_sum > 0 )  THEN p.new_material_sum + p.new_mount_sum
								ELSE cs.cost
							END)  AS total_cost')
                ->from('`rgzbn_gm_ceiling_projects` AS p')
                ->innerJoin('`rgzbn_gm_ceiling_projects_history` AS ph ON ph.project_id = p.id')
                ->leftJoin("($costQuery) AS cs ON cs.project_id = p.id")
                ->where("p.project_calculator IS NOT NULL AND ph.new_status IN(4,5) and ph.date_of_change between '$date1' and '$date2'")
                ->group('p.project_calculator');
            $query
                ->select('u.id,u.name,IFNULL(pr.projects_count,0) AS projects_count,IFNULL(pr.total_sum,0) AS total_sum,IFNULL(pr.total_cost,0) AS total_cost,IFNULL(pr.projects,\'\') AS projects')
                ->from('`rgzbn_users` AS u')
                ->innerJoin("`rgzbn_user_usergroup_map` AS um ON um.user_id = u.id AND (um.group_id = 22 or um.group_id = 21) and u.dealer_id = $user->dealer_id")
                ->leftJoin("($projectsQuery) AS pr ON pr.project_calculator = u.id ");
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