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
		$statuses = array("common"=>"","dealers"=>"(20)","advt"=>"(21)","refuse"=>"(15)","inwork"=>"(0,2,3)","measure"=>"(1)","deals"=>"(4,5,6,7,8,10,11,12,16,17,19,24,25,26)","done"=>"(12)");
		foreach ($advt as $id => $advt_obj) {
			foreach ($statuses as $key => $status) {
				$advt[$id][$key] = 0;
			}
		}
		foreach ($statuses as $key => $value) {
			$projects = $this->getDataByParameters($dealer_id,$date1,$date2,$value);
			foreach ($projects as $project) {
				if(!empty($project->api_phone_id))
					$advt[$project->api_phone_id][$key]++; 
			}
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
			"profit"=> "Прибыль"
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
				->from('#__analytic')
				->where("client_dealer_id = $dealer_id and (advt_owner = $dealer_id OR advt_owner is NULL)");
			if(!empty($statuses)){
				$query->where("project_status in $statuses");
			}
			if(!empty($date1)&&!empty($date2)){
				$query->where("created BETWEEN '$date1' and '$date2'");
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