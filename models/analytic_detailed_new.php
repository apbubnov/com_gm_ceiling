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
		$statuses = array("dealers"=>[20],"advt"=>[21],"refuse"=>[15],"ref_measure"=>[2],"measure"=>[1],"ref_deals"=>[3],"deals"=>[4,5],"closed"=>[12],"sum_done"=>[12],"profit"=>[12],"sum_deals"=>[4,5]);
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
		$projects = $this->getDataByParameters($dealer_id,$date1,$date2);
		$this->addData($advt,$projects,$dealer_id,$date1,$date2,$statuses);
		if(!$dealer_type){
			$projects = $this->getDataByParameters($dealer_id,$date1,$date2,3);
			$this->addData($advt,$projects,$dealer_id,$date1,$date2,$statuses);
			$projects = $this->getDataByParameters($dealer_id,$date1,$date2,8);
			$this->addData($advt,$ids,$projects,$dealer_id,$date1,$date2,$statuses);
		}
		$measures = $this->getCurrentMeasures($dealer_id,$date1,$date2,"(3,8)");
		$mounts = $this->getCurrentMounts($dealer_id,$date1,$date2,"(3,8)");
		$this->addCurrentData($measures,$advt,'current_measure');
		$this->addCurrentData($mounts,$advt,'mounts');
		
		foreach ($advt as $id => $advt_obj){
			foreach ($advt_obj["projects"] as $s => $ps) {
				$advt_obj["projects"][$s] = implode(";",$ps);
			}
			$result[] = $advt_obj;
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
			"close" => (object)array("head_name"=>"Закрытые","bias"=>$biases[2],"columns"=>array("closed" => "Кол-во","sum_done" => "Сумма","profit"=>"Прибыль"))
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
					->select('distinct *')
					->where("client_dealer_id = $dealer_id and (advt_owner = $dealer_id OR advt_owner is NULL)");
			}
			else{
				$query
					->select("project_id,new_status,'$advt' as api_phone_id,sum,profit")
					->where("dealer_id = $dealer_id and dealer_type = $dealer_type and (advt_owner = $dealer_id OR advt_owner is NULL)");
			}
			$query->from('#__analytic_detailed');

			if(!empty($date1)&&!empty($date2)){
				$query->where("(date_of_change BETWEEN '$date1' and '$date2' OR created BETWEEN '$date1' and '$date2')");
			}
			if(!empty($date1) && empty($date2)){
				$query->where("(date_of_change >= '$date1' OR created >= '$date1')");
			}
			if(empty($date1) && !empty($date2)){
				$query->where("(date_of_change <= '$date2' OR created <= '$date2')");
			}
			//throw new Exception($query);
			$db->setQuery($query);
			$result = $db->loadObjectList();
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function addData(&$advt,$projects,$dealer_id,$date1,$date2,$statuses){
		$date1 = strtotime($date1);
		$date2 = strtotime($date2);
		foreach ($projects as $id => $project) {
			$created = !empty($project->created) ? strtotime($project->created) : null;
			$advt_id = (empty($project->api_phone_id)) ? 0 : $project->api_phone_id;
 			if($created >= $date1 && $created <= $date2){
 				if(!in_array($project->project_id,$advt[$advt_id]['projects']['common'])){
					$advt[$advt_id]['projects']["common"][] = $project->project_id;
					$advt[$advt_id]["common"]++;
				}
			}
			foreach ($statuses as $key => $statuses_arr) {
				if($key != "sum_done" && $key != "sum_deals" && $key != "profit"){
					if(!is_null($project->new_status) && in_array($project->new_status, $statuses_arr)){
						if(!in_array($project->project_id,$advt[$advt_id]['projects'][$key])){
							$advt[$advt_id]['projects'][$key][] = $project->project_id;
							$advt[$advt_id][$key]++;
						}
					}
				}
				else{
					if(!is_null($project->new_status) && in_array($project->new_status, $statuses_arr)){
						if(!in_array($project->project_id,$advt[$advt_id]['projects'][$key])){
							$advt[$advt_id]['projects'][$key][] = $project->project_id;
							if($key!="profit"){
                                $advt[$advt_id][$key] += $project->sum;
                            }
                            if($key == "profit"){
                                $advt[$advt_id][$key] += $project->profit;
                            }

						}
					}
				}
			}
		}
	}

	function addCurrentData($data,&$advt,$field_name){
		foreach ($data as $key => $project) {
			if($project->dealer_type != 3 && $project->dealer_type != 8){
				$advt_id = (empty($project->api_phone_id)) ? 0 : $project->api_phone_id;
				if(!in_array($project->project_id,$advt[$advt_id]['projects'][$field_name])){
					$advt[$advt_id]['projects'][$field_name][] = $project->project_id;
					$advt[$advt_id][$field_name]++;
				}
			}
			elseif($project->dealer_type == 3){
				if(!in_array($project->project_id,$advt[$advt_id]['projects'][$field_name])){
					$advt['otd']['projects'][$field_name][] = $project->project_id;
					$advt['otd'][$field_name]++;
				}
			}
			elseif($project->dealer_type == 8){
				if(!in_array($project->project_id,$advt[$advt_id]['projects'][$field_name])){
					$advt['win']['projects'][$field_name][] = $project->project_id;
					$advt['win'][$field_name]++;
				}
			}
		}
	}
	function getCurrentMeasures($dealer_id,$date1,$date2,$dealer_type){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("project_id,api_phone_id,dealer_type")
				->from('`#__analytic_detailed`')
				->where("calculation_date BETWEEN '$date1' AND '$date2' and (client_dealer_id = $dealer_id OR (dealer_id = $dealer_id and dealer_type in $dealer_type)) and project_status = 1");
			$db->setQuery($query);
			$result = $db->loadObjectList();
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function getCurrentMounts($dealer_id,$date1,$date2,$dealer_type){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("project_id,api_phone_id,dealer_type")
				->from('`#__analytic_detailed`')
				->where("mount_date BETWEEN '$date1' AND '$date2' and (client_dealer_id = $dealer_id OR ( dealer_id = $dealer_id and dealer_type IN $dealer_type))");
				
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
