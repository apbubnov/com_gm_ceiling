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
		$statuses = array("common"=>"","dealers"=>"(20)","advt"=>"(21)","refuse"=>"(15)","ref_measure"=>"(2)","measure"=>"(1)","ref_deals"=>"(3)","deals"=>"(4,5)","closed"=>"(12)","sum_done"=>"(12)","profit"=>"(12)");
		$advt[0]['id'] = "0";
		$advt[0]['advt_title'] = 'Отсутствует';
		foreach ($advt as $id => $advt_obj) {
			foreach ($statuses as $key => $status) {
				$advt[$id][$key] = 0;
				$advt[$id]['projects'] = "";
				$advt[0][$key] = 0;
			}
		}
		foreach ($statuses as $key => $value) {
			$projects = $this->getDataByParameters($dealer_id,$date1,$date2,$value);
			$sum = 0;

			foreach ($projects as $project) {
				

				if(!empty($project->api_phone_id)){
					$ids[$project->api_phone_id]['projects'][$key] .= $project->project_id . ";";
					
					$advt[$project->api_phone_id][$key]++;
					 
				}
				else{
					$ids[0]['projects'][$key] .= $project->project_id . ";";
					$advt[0][$key]++;

				}

				if($project->project_status == 12 && $key == 'done'){
					$advt[$project->api_phone_id]['sum'] += $project->sum;
					$advt[$project->api_phone_id]['profit'] += $project->profit;
					
				}
			}
			
		}
		foreach ($ids as $key => $value) {
			$advt[$key]['projects'] = $value['projects'];
		}
		$header = (object)array(
			"advt_title" => (object)array("head_name" =>"Реклама","rowspan"=>2), 
			"common" => (object)array("head_name" =>"Всего","rowspan"=>2),
			"dealers" => (object)array("head_name" =>"Дилеры","rowspan"=>2),
			"advt" => (object)array("head_name" =>"Реклама","rowspan"=>2),
			"refused" => (object)array("head_name" =>"Отказ от сотрудничества","rowspan"=>2),
			"measures" => (object)array("head_name"=>"Замеры","columns"=>array("ref_measure" => "Отказ","measure" => "Запись","current_measure" => "Текущие")),
			"deal" => (object)array("head_name"=>"Договоры","columns"=>array("ref_deals" => "Отказ","deals" => "Договора","sum_deals" => "Сумма")),
			"mounts" => (object)array("head_name" =>"Монтажи","rowspan"=>2),
			"close" => (object)array("head_name"=>"Закрытые","columns"=>array("closed" => "Кол-во","sum_done" => "Сумма"))
			);
		array_unshift($advt, $header);
		return $advt;
	}

	function getDataByParameters($dealer_id,$date1,$date2,$statuses = null){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('*')
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
			$result = $db->loadObjectList();
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function get_current_measures(){
		try{

		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function get_current_mounts(){
		try{

		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
