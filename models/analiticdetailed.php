<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
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
class Gm_ceilingModelAnaliticDetailed extends JModelList
{
	function getQuery($statuses,$date1,$date2){
		try
		{
			if(count($statuses)==1)
			{
				$str = " = $statuses[0]";
			}
			else {
				$str = "IN(";
				for($i=0;$i<count($statuses);$i++){
					if($i<count($statuses)-1){
						$str .= $statuses[$i].",";
					}
					else 
					{
						$str.=$statuses[$i].")";
					}
				}
					
			}
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('h.project_id')
				->from('#__gm_ceiling_projects_history as h')
				->where("h.new_status $str AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '$date1' AND '$date2'");
			return $query;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
		
	
	
	function getData($date1 = null,$date2 = null,$dealer_id = null)
	{
		try
		{
			/*SELECT DISTINCT a.name,
            (SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.created BETWEEN '2017-11-13' AND '2017-11-14') AS common,
            (SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND  p.project_status = 1 AND p.project_calculation_date BETWEEN '2017-12-14 00:00:00' AND '2018-01-25 23:59:00') AS zap_zamer,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status = 1 AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '2017-11-13' AND '2017-11-14')) AS zamer,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status = 2 AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '2017-11-13' AND '2017-11-14')) AS otk_zam,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status  IN (4,5) AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '2017-11-13' AND '2017-11-14')) AS deal,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status = 3 AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '2017-11-13' AND '2017-11-14')) AS otk_deal,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status IN(10,11,16,17) AND h.project_id = p.id AND a.id = p.api_phone_id AND p.project_mounting_date BETWEEN '2017-11-13' AND '2017-11-14')) AS mount,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status = 12 AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '2017-11-13' AND '2017-11-14')) AS closed,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status = 15 AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '2017-11-13' AND '2017-11-14')) AS refuse,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status = 20 AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '2017-11-13' AND '2017-11-14')) AS dealer,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status = 21 AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '2017-11-13' AND '2017-11-14')) AS advt
            (SELECT SUM(p.project_sum) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status IN(4,5) AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '2017-11-13' AND '2017-11-14')) AS sum_deal,
            (SELECT SUM(p.project_sum) FROM `rgzbn_gm_ceiling_projects` AS p WHERE  p.id IN (SELECT h.project_id FROM `rgzbn_gm_ceiling_projects_history` AS h WHERE h.new_status = 12 AND h.project_id = p.id AND a.id = p.api_phone_id AND h.date_of_change BETWEEN '2017-11-13' AND '2017-11-14')) AS sum_done
            FROM `rgzbn_gm_ceiling_api_phones` AS a */
			if(empty($date1)&&empty($date2)){
				$date1 = date("Y-m-d");
				$date2 = date("Y-m-d");
			}
			if(empty($dealer_id)){
				$dealer_id = 1;
			}
			$dealer = JFactory::getUser($dealer_id);
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$common = $db->getQuery(true);
			$measure =  $db->getQuery(true);
			$ref_measure = $db->getQuery(true);
			$deals = $db->getQuery(true);
			$ref_deals = $db->getQuery(true);
			$dealers = $db->getQuery(true);
			$advt = $db->getQuery(true);
			$closed = $db->getQuery(true);
			$mounts = $db->getQuery(true);
			$refused =  $db->getQuery(true);
            $mounts_sub = $db->getQuery(true);
            $sum_deals = $db->getQuery(true);
            $sum_done = $db->getQuery(true);
            $current_measure = $db->getQuery(true);

			$mounts_sub
				->select('h.project_id')
				->from('#__gm_ceiling_projects_history as h')
				->where("h.new_status IN(10,11,16,17) AND h.project_id = p.id AND a.id = p.api_phone_id AND p.project_mounting_date BETWEEN '$date1' AND '$date2' ");
			$common
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
				->where("p.api_phone_id = a.id AND p.created BETWEEN '$date1' and '$date2' and cl.dealer_id = $dealer_id");

            $current_measure
                ->select("COUNT(p.id)")
                ->from("#__gm_ceiling_projects as p")
                ->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
                ->where("p.api_phone_id = a.id AND p.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00' and cl.dealer_id = $dealer_id");
			$measure
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
				->where("p.id IN (".$this->getQuery([1],$date1,$date2).") and cl.dealer_id = $dealer_id");
			$ref_measure
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
				->where("p.id IN (".$this->getQuery([2],$date1,$date2).") and cl.dealer_id = $dealer_id");
			$deals
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
				->where("p.id IN (".$this->getQuery([4,5],$date1,$date2).") and cl.dealer_id = $dealer_id");
			$ref_deals
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
				->where("p.id IN (". $this->getQuery([3],$date1,$date2).") and cl.dealer_id = $dealer_id");
			$dealers
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.id IN (".$this->getQuery([20],$date1,$date2).")");
			$advt
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.id IN (".$this->getQuery([21],$date1,$date2).")");
			$closed	
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
				->where("p.id IN (".$this->getQuery([12],$date1,$date2).") and cl.dealer_id = $dealer_id");		
			$mounts
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
				->where("p.project_status NOT IN (2,3) and p.api_phone_id = a.id AND p.project_mounting_date BETWEEN  '$date1 00:00:00' and  '$date2 23:59:59' and cl.dealer_id = $dealer_id");
			
			$refused
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
                ->where("p.id IN (".$this->getQuery([15],$date1,$date2).") and cl.dealer_id = $dealer_id");
            $sum_deals
                ->select("SUM(COALESCE(p.project_sum,0))")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
                ->where("p.id IN (".$this->getQuery([4,5],$date1,$date2).") and cl.dealer_id = $dealer_id");
            $sum_done
                ->select("SUM(COALESCE(p.new_project_sum,0))")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
                ->where("p.id IN (".$this->getQuery([12],$date1,$date2).") and cl.dealer_id = $dealer_id");

			$query->select('DISTINCT a.name');
			$query->select(' a.id');
            $query->select("($common) as common");
            if($dealer->dealer_type!=1){
	            $query->select("($dealers) as dealers");
	            $query->select("($advt) as advt");
	            $query->select("($refused) as refused");
	        }
            $query->select("($ref_measure) as ref_measure");
            $query->select("($measure) as measure");
            $query->select("($current_measure) as current_measure");
			$query->select("($ref_deals) as ref_deals");
            $query->select("($deals) as deals");
            $query->select("ifnull(($sum_deals),0) as sum_deals");
			$query->select("($mounts) as mounts");
            $query->select("($closed) as closed");
            $query->select("ifnull(($sum_done),0) as sum_done");
			$query->from('`#__gm_ceiling_api_phones` AS a');
			$query->where("a.dealer_id = $dealer_id");
			$db->setQuery($query);
			
			$items = $db->loadObjectList();

			$designers = $this->get_data_by_dealer_type(3,$date1,$date2);
			foreach ($designers as $designer) {
				$d_common += $designer->common;
				$d_measure += $designer->measure;
				$d_ref_measure += $designer->ref_measure;
				$d_deals +=  $designer->deals;
				$d_ref_deals +=  $designer->ref_deals;
				$d_closed +=  $designer->closed;
				$d_mounts+= $designer->mounts;
				$d_refused+= $designer->refused;
				$d_sum_deals+= $designer->sum_deals;
				$d_sum_done+= $designer->sum_done;
				$d_current_mesure+= $designer->current_measure;
			}
			if($dealer_id == 0 || $dealer_id == 1 || $dealer_id == 2){
				$d_object = (object)array(
					"name" => "Отделочники",
					"id" => "",
					"common" => $d_common,
					"dealers" => 0,
					"advt" => 0,
					"refused" => $d_refused,
					"ref_measure" => $d_ref_measure,
					"measure" => $d_measure,
					"current_measure" => $d_current_mesure,
					"ref_deals" => $d_ref_deals,
					"deals" => $d_deals,
					"sum_deals" => $d_sum_deals,
					"mounts" => $d_mounts,
					"closed" => $d_closed,
					"sum_done" => $d_sum_done

				);
				array_push($items,$d_object);
			}
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function get_data_by_dealer_type($dealer_type,$date1,$date2){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$common = $db->getQuery(true);
			$measure =  $db->getQuery(true);
			$ref_measure = $db->getQuery(true);
			$deals = $db->getQuery(true);
			$ref_deals = $db->getQuery(true);
			$closed = $db->getQuery(true);
			$mounts = $db->getQuery(true);
			$refused =  $db->getQuery(true);
            $mounts_sub = $db->getQuery(true);
            $sum_deals = $db->getQuery(true);
            $sum_done = $db->getQuery(true);
            $current_measure = $db->getQuery(true);
            $clients_id = $db->getQuery(true);
            $clients_id
            	->select("c.id")
            	->from("`#__gm_ceiling_clients` AS c")
            	->leftJoin("`#__users` AS u ON c.dealer_id = u.id")
            	->where("u.dealer_type = $dealer_type");

            $common
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.client_id in ($clients_id) AND p.created BETWEEN '$date1' and '$date2'");

			$measure
				->select("COUNT(p.id)")
				->from("`#__gm_ceiling_projects_history` AS h ")
				->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
				->where("p.client_id in ($clients_id) and h.new_status = 1 and h.date_of_change BETWEEN '$date1' and '$date2'");

			$current_measure
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.client_id in ($clients_id) AND p.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00'");

			$ref_measure
				->select("COUNT(p.id)")
				->from("`#__gm_ceiling_projects_history` AS h ")
				->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
				->where("p.client_id in ($clients_id) and h.new_status = 2 and h.date_of_change BETWEEN '$date1' and '$date2'");

			$deals
				->select("COUNT(p.id)")
				->from("`#__gm_ceiling_projects_history` AS h ")
				->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
				->where("p.client_id in ($clients_id) and h.new_status in(4,5) and h.date_of_change BETWEEN '$date1' and '$date2'");

			$ref_deals
				->select("COUNT(p.id)")
				->from("`#__gm_ceiling_projects_history` AS h ")
				->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
				->where("p.client_id in ($clients_id) and h.new_status = 3 and h.date_of_change BETWEEN '$date1' and '$date2'");

			$closed	
				->select("COUNT(p.id)")
				->from("`#__gm_ceiling_projects_history` AS h ")
				->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
				->where("p.client_id in ($clients_id) and h.new_status = 12 and h.date_of_change BETWEEN '$date1' and '$date2'");
		
			$mounts
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.project_status NOT IN (2,3) and p.client_id in ($clients_id) AND p.project_mounting_date BETWEEN  '$date1 00:00:00' and  '$date2 23:59:59'");
			
			$refused
				->select("COUNT(p.id)")
				->from("`#__gm_ceiling_projects_history` AS h ")
				->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
				->where("p.client_id in ($clients_id) and h.new_status in(15) and h.date_of_change BETWEEN '$date1' and '$date2'");
            $sum_deals
                ->select("SUM(COALESCE(p.project_sum,0))")
				->from("`#__gm_ceiling_projects_history` AS h ")
				->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
				->where("p.client_id in ($clients_id) and h.new_status IN(4,5) and h.date_of_change BETWEEN '$date1' and '$date2'");
            $sum_done
                ->select("SUM(COALESCE(p.new_project_sum,0))")
				->from("`#__gm_ceiling_projects_history` AS h ")
				->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
				->where("p.client_id in ($clients_id) and h.new_status = 12 and h.date_of_change BETWEEN '$date1' and '$date2'");

            $query->select("($common) as common");
            $query->select("($refused) as refused");
            $query->select("($ref_measure) as ref_measure");
            $query->select("($measure) as measure");
            $query->select("($current_measure) as current_measure");
			$query->select("($ref_deals) as ref_deals");
            $query->select("($deals) as deals");
            $query->select("ifnull(($sum_deals),0) as sum_deals");
			$query->select("($mounts) as mounts");
            $query->select("($closed) as closed");
            $query->select("ifnull(($sum_done),0) as sum_done");
			$db->setQuery($query);
			$items = $db->loadObjectList();

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
}
?>