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
class Gm_ceilingModelAnaliticcommon extends JModelList
{
	
	function getData($dealer_id = null)
	{
		try
		{
			/* SELECT  DISTINCT a.name,
			(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id ) AS count1,
			(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.who_calculate = 2 ) AS dealers,
			(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.who_calculate = 3 ) AS advt,
			(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19) ) AS deals,
			(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status IN (0,2,3) ) AS inwork,
			(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 1) AS measure,
			(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 15) AS refuse,
			(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 12) AS done,
			(SELECT SUM(COALESCE(p.new_project_sum,0)) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 12) AS `sum`,
			(SELECT  SUM(COALESCE(p.new_project_sum,0)) - (SUM(COALESCE(p.new_material_sum,0))+ SUM(COALESCE(p.new_mount_sum,0))) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 12) AS cost
			FROM `#__gm_ceiling_projects` AS p INNER JOIN `#__gm_ceiling_api_phones` AS a ON p.api_phone_id = a.id
 */
			if(empty($dealer_id)){
				$dealer_id  = 1;
			}
			$dealer = JFactory::getUser($dealer_id);
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$common = $db->getQuery(true);
			$dealers = $db->getQuery(true);
			$advt = $db->getQuery(true);
			$deals = $db->getQuery(true);
			$inwork = $db->getQuery(true);
			$measure =  $db->getQuery(true);
			$refuse =  $db->getQuery(true);
			$done =  $db->getQuery(true);
			$sum =  $db->getQuery(true);
			$profit =  $db->getQuery(true);
			$profit_sub = $db->getQuery(true);
			$profit_sub
                ->select("SUM(c.components_sum+c.canvases_sum+c.mounting_sum)")
                ->from("`#__gm_ceiling_calculations` as c")
                ->where("c.project_id = p.id");
			$common
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id")
				->where("p.api_phone_id = a.id and c.dealer_id =  $dealer_id");

			$dealers
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id AND p.project_status = 20");
			
			$advt
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id AND p.project_status = 21");
			$refuse
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id")
				->where("p.api_phone_id = a.id  AND p.project_status = 15 and c.dealer_id =  $dealer_id");
			$inwork
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id")
				->where("p.api_phone_id = a.id AND  p.project_status IN (0,2,3) and c.dealer_id =  $dealer_id");			
			$measure
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id")
				->where("p.api_phone_id = a.id  AND p.project_status = 1 and c.dealer_id =  $dealer_id");
			$deals
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id")
				->where("p.api_phone_id = a.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19,24,25,26) and c.dealer_id =  $dealer_id");
			$done
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id")
				->where("p.api_phone_id = a.id  AND p.project_status = 12 and c.dealer_id =  $dealer_id");
			$sum
				->select("SUM(COALESCE(IF((p.project_sum IS NULL OR p.project_sum = 0) AND (p.new_project_sum  IS NOT NULL OR p.new_project_sum <>0),p.new_project_sum,p.project_sum),0))")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id")
				->where("p.api_phone_id = a.id  AND p.project_status = 12 and c.dealer_id =  $dealer_id");
			$profit
				->select("SUM(IF((project_sum IS NULL OR project_sum = 0) AND (new_project_sum  IS NOT NULL OR new_project_sum <>0),new_project_sum,project_sum) -
IF(COALESCE(p.new_material_sum + p.new_mount_sum,0) = 0,($profit_sub),COALESCE(p.new_material_sum + p.new_mount_sum,0)))")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id")
				->where("p.api_phone_id = a.id  AND p.project_status = 12 and c.dealer_id =  $dealer_id");
			$query->select(' DISTINCT a.name');
			$query->select("($common) as common");
			if($dealer->dealer_type != 1){
				$query->select("($dealers) as dealers");
            	$query->select("($advt) as advt");
			}
            $query->select("($refuse) as refuse");
            $query->select("($inwork) as inwork");
            $query->select("($measure) as measure");
			$query->select("($deals) as deals");
			$query->select("($done) as done");
			$query->select("($sum) as sum");
			$query->select("($profit) as profit");
			$query->from('`#__gm_ceiling_api_phones` AS a');
			$query->where("a.dealer_id = $dealer_id or a.dealer_id is null");
			//throw new Exception($query);
				/*->from('`#__gm_ceiling_api_phones` AS a')
				->leftJoin('`#__gm_ceiling_projects` AS p ON p.api_phone_id = a.id and a.dealer_id ='.$dealer_id);*/
				//throw new Exception($dealer_id);
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			$designers = $this->get_designers_analytics(3);

			$d_common = 0;
			$d_deals = 0;
			$d_inwork = 0;
			$d_measure = 0;
			$d_refuse =  0;
			$d_done =  0;
			$d_sum =  0;
			$d_profit = 0;
			if (!empty($designers)) {
				foreach ($designers as $designer) {
					$d_common += $designer->common;
					$d_deals += $designer->deals;
					$d_inwork += $designer->inwork;
					$d_measure +=  $designer->measure;
					$d_refuse +=  $designer->refuse;
					$d_done +=  $designer->done;
					$d_sum +=  $designer->sum;
					$d_profit+= $designer->profit;
				}
			}
			if($dealer_id == 0 || $dealer_id == 1 || $dealer_id == 2){
				$d_object = (object)array(
					"name" => "Отделочники",
					"common" => $d_common,
					"dealers" => 0,
					"advt" => 0,
					"refuse" => $d_refuse,
					"inwork" => $d_inwork,
					"measure" => $d_measure,
					"deals" => $d_deals,
					"done" => $d_done,
					"sum" => $d_sum,
					"profit" => $d_profit
				);
				array_push($items,$d_object);
			}
				$d_common = 0;
				$d_deals = 0;
				$d_inwork = 0;
				$d_measure = 0;
				$d_refuse = 0;
				$d_done =  0;
				$d_sum =  0;
				$d_profit = 0;
			$wininstallers = $this->get_designers_analytics(8);
			foreach ($wininstallers as $designer) {
				$d_common += $designer->common;
				$d_deals += $designer->deals;
				$d_inwork += $designer->inwork;
				$d_measure +=  $designer->measure;
				$d_refuse +=  $designer->refuse;
				$d_done +=  $designer->done;
				$d_sum +=  $designer->sum;
				$d_profit+= $designer->profit;
			}
			if($dealer_id == 0 || $dealer_id == 1 || $dealer_id == 2){
				$d_object = (object)array(
					"name" => "Оконщики",
					"common" => $d_common,
					"dealers" => 0,
					"advt" => 0,
					"refuse" => $d_refuse,
					"inwork" => $d_inwork,
					"measure" => $d_measure,
					"deals" => $d_deals,
					"done" => $d_done,
					"sum" => $d_sum,
					"profit" => $d_profit
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
	function getDataByPeriod($date1,$date2,$dealer_id = null){
		try
		{
			/*SELECT  DISTINCT a.name,
	(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id ) AS common,
	(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.who_calculate = 2 ) AS dealers,
	(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.who_calculate = 3 ) AS advt,
	(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19) ) AS deals,
	(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status IN (0,2,3) ) AS inwork,
	(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 1) AS measure,
	(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 15) AS refuse
	 FROM `#__gm_ceiling_projects` AS p INNER JOIN `#__gm_ceiling_api_phones` AS a ON p.api_phone_id = a.id 
	 WHERE p.created BETWEEN '2017-11-06' AND '2017-11-10'*/
	 		if(empty($dealer_id)){
	 			$dealer_id = 1;
	 		}
	 		$dealer = JFactory::getUser($dealer_id);
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$common = $db->getQuery(true);
			$dealers = $db->getQuery(true);
			$advt = $db->getQuery(true);
			$deals = $db->getQuery(true);
			$inwork = $db->getQuery(true);
			$measure =  $db->getQuery(true);
			$refuse =  $db->getQuery(true);
			$done =  $db->getQuery(true);
			$sum =  $db->getQuery(true);
            $profit =  $db->getQuery(true);
            
			$common
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id AND p.created BETWEEN '$date1' AND '$date2'");

			$dealers
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id AND p.project_status = 20 AND p.created BETWEEN '$date1' AND '$date2'");
			
			$advt
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id AND p.project_status = 21 AND p.created BETWEEN '$date1' AND '$date2'");
			$deals
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19) AND p.created BETWEEN '$date1' AND '$date2'");
			$inwork
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id AND  p.project_status IN (0,2,3) AND p.created BETWEEN '$date1' AND '$date2'");
			$measure
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id  AND p.project_status = 1 AND p.created BETWEEN '$date1' AND '$date2'");
			$refuse
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id  AND p.project_status = 15 AND p.created BETWEEN '$date1' AND '$date2'");	
            $done
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id  AND p.project_status = 12 AND p.created BETWEEN '$date1' AND '$date2'");
			$sum
				->select("SUM(COALESCE(p.new_project_sum,0))")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id  AND p.project_status = 12 AND p.created BETWEEN '$date1' AND '$date2'");
			$profit
				->select("SUM(COALESCE(p.new_project_sum,0)) - (SUM(COALESCE(p.new_material_sum,0))+ SUM(COALESCE(p.new_mount_sum,0)))")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id  AND p.project_status = 12 AND p.created BETWEEN '$date1' AND '$date2'");
			$query->select(' DISTINCT a.name');
			$query->select("($common) as common");
			if($dealer->dealer_type!=1){
				$query->select("($dealers) as dealers");
				$query->select("($advt) as advt");
			}
			$query->select("($refuse) as refuse");
			$query->select("($inwork) as inwork");
			$query->select("($measure) as measure");
            $query->select("($deals) as deals");
            $query->select("($done) as done");
			$query->select("ifnull(($sum),0) as sum");
			$query->select("ifnull(($profit),0) as profit");
			$query->from('`#__gm_ceiling_api_phones` AS a');
			$query->leftJoin('`#__gm_ceiling_projects` AS p ON p.api_phone_id = a.id');
			$query->where("a.dealer_id = $dealer_id");
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			$designers = $this->get_designers_analytics(3,$date1,$date2);
			foreach ($designers as $designer) {
				$d_common += $designer->common;
				$d_deals += $designer->deals;
				$d_inwork += $designer->inwork;
				$d_measure +=  $designer->measure;
				$d_refuse +=  $designer->refuse;
				$d_done +=  $designer->done;
				$d_sum +=  $designer->sum;
				$d_profit+= $designer->profit;
			}
			if($dealer_id == 0 || $dealer_id == 1 || $dealer_id == 2){
				$d_object = (object)array(
					"name" => "Отделочники",
					"common" => $d_common,
					"dealers" => 0,
					"advt" => 0,
					"refuse" => $d_refuse,
					"inwork" => $d_inwork,
					"measure" => $d_measure,
					"deals" => $d_deals,
					"done" => $d_done,
					"sum" => $d_sum,
					"profit" => $d_profit
				);
				array_push($items,$d_object);
			}
				$d_common = 0;
				$d_deals = 0;
				$d_inwork = 0;
				$d_measure = 0;
				$d_refuse = 0;
				$d_done =  0;
				$d_sum =  0;
				$d_profit = 0;
			$wininstallers = $this->get_designers_analytics(8,$date1,$date2);
			foreach ($wininstallers as $designer) {
				$d_common += $designer->common;
				$d_deals += $designer->deals;
				$d_inwork += $designer->inwork;
				$d_measure +=  $designer->measure;
				$d_refuse +=  $designer->refuse;
				$d_done +=  $designer->done;
				$d_sum +=  $designer->sum;
				$d_profit+= $designer->profit;
			}
			if($dealer_id == 0 || $dealer_id == 1 || $dealer_id == 2){
				$d_object = (object)array(
					"name" => "Оконщики",
					"common" => $d_common,
					"dealers" => 0,
					"advt" => 0,
					"refuse" => $d_refuse,
					"inwork" => $d_inwork,
					"measure" => $d_measure,
					"deals" => $d_deals,
					"done" => $d_done,
					"sum" => $d_sum,
					"profit" => $d_profit
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
	function get_designers_analytics($dealer_type = null,$date1 = null,$date2 = null){
		/* SELECT c.client_name,c.dealer_id,
(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19)) AS deals,
(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status IN (0,2,3) ) AS inwork,
(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status = 1) AS measure,
(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status = 15) AS refuse,
(SELECT COUNT(p.id) FROM `#__gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status  = 12) AS done,
(SELECT SUM(COALESCE(p.new_project_sum,0)) FROM `#__gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status = 12) AS `sum`,
(SELECT SUM(COALESCE(p.new_project_sum,0)) - (SUM(COALESCE(p.new_material_sum,0))+ SUM(COALESCE(p.new_mount_sum,0))) FROM `#__gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status = 12) AS cost
 FROM `#__gm_ceiling_clients` AS c
 LEFT JOIN `#__users` AS u ON c.dealer_id = u.id
 WHERE u.dealer_type = 3 */
 		if(!empty($date1) && !empty($date2)){
 			$where = "and p.created BETWEEN '$date1' and '$date2'";
 		}
 		else{
 			$where = '';
 		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$common = $db->getQuery(true);
		$deals = $db->getQuery(true);
		$inwork = $db->getQuery(true);
		$measure =  $db->getQuery(true);
		$refuse =  $db->getQuery(true);
		$done =  $db->getQuery(true);
		$sum =  $db->getQuery(true);
		$profit =  $db->getQuery(true);
		
		$common
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id $where");
		$deals
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19) $where");
		$inwork
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND  p.project_status IN (0,2,3) $where");
		$measure
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status = 1 $where");
		$refuse
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status = 15 $where");	
		$done
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status = 12 $where");
		$sum
			->select("SUM(COALESCE(p.new_project_sum,0))")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status = 12 $where");
		$profit
			->select("SUM(COALESCE(p.new_project_sum,0)) - (SUM(COALESCE(p.new_material_sum,0))+ SUM(COALESCE(p.new_mount_sum,0)))")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status = 12 $where");
		$query
			->select(' DISTINCT c.dealer_id')
			->select("($common) as common")
			->select("($refuse) as refuse")
			->select("($inwork) as inwork")
			->select("($measure) as measure")
			->select("($deals) as deals")
			->select("($done) as done")
			->select("ifnull(($sum),0) as sum")
			->select("ifnull(($profit),0) as profit")
			->from('`#__gm_ceiling_clients` AS c')
			->leftJoin('`#__users` AS u ON c.dealer_id = u.id')
			->where("u.dealer_type = $dealer_type ");
		$db->setQuery($query);
		$items = $db->loadObjectList();
		return $items;
	}
}
?>