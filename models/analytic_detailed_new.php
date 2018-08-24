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
		$advt['otd']['id'] = "otd";
		$advt['otd']['advt_title'] = 'Отделочники';
		$advt['win']['id'] = "win";
		$advt['win']['advt_title'] = 'Оконщики';
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
		$sum_done = [];
		$sum_deals = [];
		foreach ($statuses as $key => $value) {
			$projects = $this->getDataByParameters($dealer_id,$date1,$date2,$value);
			$this->addData($advt,$ids,$projects,$dealer_id,$dealer_type,$date1,$date2,$key);
			if($key == "sum_done"){
				
				foreach ($projects as $project) {
					$sum_done[$project["api_phone_id"]] += $project["sum"];
				}
			}
			if($key == "sum_deals"){
				
				foreach ($projects as $project) {
					$sum_deals[$project["api_phone_id"]] += $project["sum"];
				}
			}
			if(!$dealer_type){
				$projects = $this->getDataByParameters($dealer_id,$date1,$date2,$value,3);
				$this->addData($advt,$ids,$projects,$dealer_id,$dealer_type,$date1,$date2,$key);
				$projects = $this->getDataByParameters($dealer_id,$date1,$date2,$value,8);
				$this->addData($advt,$ids,$projects,$dealer_id,$dealer_type,$date1,$date2,$key);
			}
		}
		foreach ($advt as $id => $advt_obj){
			if($advt_obj['id'] === "0"){
				$current_measure = $this->getCurrentMeasures($dealer_id,null,$date1,$date2);
				$current_mounts = $this->getCurrentMounts($dealer_id,null,$date1,$date2);
			}
			elseif($advt_obj['id'] =='otd'){
				$current_measure = $this->getCurrentMeasures($dealer_id,$advt_obj['id'],$date1,$date2,3);
				$current_mounts = $this->getCurrentMounts($dealer_id,$advt_obj['id'],$date1,$date2,3);
			}
			elseif($advt_obj['id'] =='win'){
				$current_measure = $this->getCurrentMeasures($dealer_id,$advt_obj['id'],$date1,$date2,8);
				$current_mounts = $this->getCurrentMounts($dealer_id,$advt_obj['id'],$date1,$date2,8);
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
					if($status == 'sum_done'){
						$advt[$advt_id][$status] += $sum_done[$advt_id];			
					}
					if($status == 'sum_deals'){
						$advt[$advt_id][$status] += $sum_deals[$advt_id];			
					}
					
				}
			}
			$old_val = $advt[$advt_id]['projects'];
			foreach ($value as $s => $ps) {
				$value[$s] = implode(";",$ps);
			}
			$advt[$advt_id]['projects'] = array_merge($old_val,$value);
		}
		foreach ($advt as $key => $value) {
			$result[] = $value;
		}
		if(!$dealer_type){
			$biases = [4,5,6];
		}
		else{
			$biases = [4,3,4];
		}
		$header = (object)array(
			"advt_title" => (object)array("head_name" =>"Реклама","rowspan"=>2), 
			"common" => (object)array("head_name" =>"Всего","rowspan"=>2),
			"dealers" => (object)array("head_name" =>"Дилеры","rowspan"=>2),
			"advt" => (object)array("head_name" =>"Реклама","rowspan"=>2),
			"refuse" => (object)array("head_name" =>"Отказ от сотрудничества","rowspan"=>2),
			"measures" => (object)array("head_name"=>"Замеры","bias"=>$biases[1],"columns"=>array("ref_measure" => "Отказ","measure" => "Запись","current_measure" => "Текущие")),
			"deal" => (object)array("head_name"=>"Договоры","bias"=>$biases[1],"columns"=>array("ref_deals" => "Отказ","deals" => "Договора","sum_deals" => "Сумма")),
			"mounts" => (object)array("head_name" =>"Монтажи","rowspan"=>2,"bias"=>$biases[0]),
			"close" => (object)array("head_name"=>"Закрытые","bias"=>$biases[2],"columns"=>array("closed" => "Кол-во","sum_done" => "Сумма"))
			);
		array_unshift($result, $header);
		if($dealer_type){
			$this->unset_columns($result);
		}
		return $result;
	}

	function getDataByParameters($dealer_id,$date1,$date2,$statuses = null,$dealer_type = null){
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
					->select('distinct *')
					->where("client_dealer_id = $dealer_id and (advt_owner = $dealer_id OR advt_owner is NULL)");
			}
			else{
				$query
					->select("project_id,new_status,'$advt' as api_phone_id,sum,profit")
					->where("dealer_id = $dealer_id and dealer_type = $dealer_type and (advt_owner = $dealer_id OR advt_owner is NULL)");
			}
			$query->from('#__analytic_detailed');
			if(!empty($statuses)){
				$query->where("new_status in $statuses");
			}
			else{
				$query->where("created BETWEEN '$date1' and '$date2'");
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

	function addData(&$advt,&$ids,$projects,$dealer_id,$dealer_type,$date1,$date2,$key){
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
	function getCurrentMeasures($dealer_id,$advt,$date1,$date2,$dealer_type = null){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("COUNT(distinct project_id) as count,group_concat(DISTINCT project_id separator ';' ) as projects")
				->from('`#__analytic_detailed`');
			if(empty($dealer_type)){
				$query->where("calculation_date BETWEEN '$date1' AND '$date2' and client_dealer_id = $dealer_id and project_status = 1");
			}
			else{
				$query->where("calculation_date BETWEEN '$date1' AND '$date2' and dealer_id = $dealer_id and dealer_type = $dealer_type and project_status = 1");
			}
			if(!empty($advt) && empty($dealer_type)){
				$query->where("api_phone_id = $advt");
			}
			else{
				$query->where("api_phone_id Is NULL");	
			}
			/*if(!empty($dealer_type)){
				throw new Exception($query);
			}*/
			
			$db->setQuery($query);
			$result = $db->loadObjectList();
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function getCurrentMounts($dealer_id,$advt,$date1,$date2,$dealer_type = null){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("COUNT(distinct project_id) as count,group_concat(DISTINCT project_id separator ';' ) as projects")
				->from('`#__analytic_detailed`');
				if(empty($dealer_type)){
					$query->where("mount_date BETWEEN '$date1' AND '$date2' and client_dealer_id = $dealer_id");
				}
				else{
					$query->where("mount_date BETWEEN '$date1' AND '$date2' and dealer_id = $dealer_id and dealer_type = $dealer_type");
			}
			if(!empty($advt) && empty($dealer_type)){
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
}
