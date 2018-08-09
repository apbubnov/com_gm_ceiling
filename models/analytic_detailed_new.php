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
		$ids = [];
		$api_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
		$advt = $api_model->getDealersAdvt($dealer_id);
		$statuses = array("common"=>"","dealers"=>"(20)","advt"=>"(21)","refuse"=>"(15)","ref_measure"=>"(2)","measure"=>"(1)","ref_deals"=>"(3)","deals"=>"(4,5)","closed"=>"(12)","sum_done"=>"(12)","sum_deals"=>"(4,5)");
		$advt[0]['id'] = "0";
		$advt[0]['advt_title'] = 'Отсутствует';
		foreach ($advt as $id => $advt_obj) {
			foreach ($statuses as $key => $status) {
				$advt[$id][$key] = 0;
				$advt[$id]['projects'] = "";
				$advt[0][$key] = 0;
				$ids[$id][$key] = [];
			}
		}
		
		foreach ($statuses as $key => $value) {
			$projects = $this->getDataByParameters($dealer_id,$date1,$date2,$value);
			$sum = 0;
			foreach ($projects as $id => $project) {
				if(!empty($project['api_phone_id'])){
					if(!in_array($project['project_id'],$ids[$project['api_phone_id']][$key])){
						$ids[$project['api_phone_id']][$key][] = $project['project_id'];
					}
				}
				else{
					if(!in_array($project['project_id'],$ids[0][$key])){
						$ids[0][$key][] = $project['project_id'];
					}
				}
			}
		
		}
		foreach ($advt as $id => $advt_obj){
			if($advt_obj['id'] == 0){
				$current_measure = $this->getCurrentMeasures($dealer_id,null,$date1,$date2);
				$current_mounts = $this->getCurrentMounts($dealer_id,null,$date1,$date2);
			}
			else{
				$current_measure = $this->getCurrentMeasures($dealer_id,$advt_obj['id'],$date1,$date2);
				$current_mounts = $this->getCurrentMounts($dealer_id,$advt_obj['id'],$date1,$date2);
			}
			$advt[$id]['current_measure'] = $current_measure[0]->count;
			$advt[$id]['projects']['current_measure'] = $current_measure[0]->projects;
			$advt[$id]['mounts'] = $current_mounts[0]->count;
			$advt[$id]['projects']['mounts'] = $current_mounts[0]->projects;
		}
		foreach ($ids as $advt_id => $value) {
			foreach ($value as $status => $projs) {
				if($status != 'sum_done' && $status != "sum_deals"){
					$advt[$advt_id][$status] = count($projs);
				}
				else{
					foreach ($projs as $p_id) {
						$advt[$advt_id][$status] += $projects[$p_id]["sum"];				
					}	
				}
			}
			$old_val = $advt[$advt_id]['projects'];
			$advt[$advt_id]['projects'] = array_merge($old_val,$value);
		}
		$header = (object)array(
			"advt_title" => (object)array("head_name" =>"Реклама","rowspan"=>2), 
			"common" => (object)array("head_name" =>"Всего","rowspan"=>2),
			"dealers" => (object)array("head_name" =>"Дилеры","rowspan"=>2),
			"advt" => (object)array("head_name" =>"Реклама","rowspan"=>2),
			"refuse" => (object)array("head_name" =>"Отказ от сотрудничества","rowspan"=>2),
			"measures" => (object)array("head_name"=>"Замеры","bias"=>5,"columns"=>array("ref_measure" => "Отказ","measure" => "Запись","current_measure" => "Текущие")),
			"deal" => (object)array("head_name"=>"Договоры","bias"=>5,"columns"=>array("ref_deals" => "Отказ","deals" => "Договора","sum_deals" => "Сумма")),
			"mounts" => (object)array("head_name" =>"Монтажи","rowspan"=>2,"bias"=>4),
			"close" => (object)array("head_name"=>"Закрытые","bias"=>6,"columns"=>array("closed" => "Кол-во","sum_done" => "Сумма"))
			);
		array_unshift($advt, $header);
		return $advt;
	}

	function getDataByParameters($dealer_id,$date1,$date2,$statuses = null){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('distinct *')
				->from('#__analytic_detailed')
				->where("client_dealer_id = $dealer_id and (advt_owner = $dealer_id OR advt_owner is NULL)");
			if(!empty($statuses)){
				$query->where("new_status in $statuses");
			}
			if(!empty($date1)&&!empty($date2)){
				$query->where("date_of_change BETWEEN '$date1' and '$date2'");
			}
			if(!empty($date1) && empty($date2)){
				$query->where("date_of_change >= '$date1' ");
			}
			if(empty($date1) && !empty($date2)){
				$query->where("date_of_change <= '$date2' ");
			}
			$db->setQuery($query);
			$result = $db->loadAssocList('project_id');
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getCurrentMeasures($dealer_id,$advt,$date1,$date2){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("COUNT(distinct project_id) as count,group_concat(DISTINCT project_id separator ';' ) as projects")
				->from('`#__analytic_detailed`')
				->where("calculation_date BETWEEN '$date1' AND '$date2' and client_dealer_id = $dealer_id");
			if(!empty($advt)){
				$query->where("api_phone_id = $advt");
			}
			else{
				$query->where("api_phone_id Is NULL");	
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
	function getCurrentMounts($dealer_id,$advt,$date1,$date2){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("COUNT(distinct project_id) as count,group_concat(DISTINCT project_id separator ';' ) as projects")
				->from('`#__analytic_detailed`')
				->where("mount_date BETWEEN '$date1' AND '$date2' and client_dealer_id = $dealer_id");
			if(!empty($advt)){
				$query->where("api_phone_id = $advt");
			}
			else{
				$query->where("api_phone_id Is NULL");	
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
}
