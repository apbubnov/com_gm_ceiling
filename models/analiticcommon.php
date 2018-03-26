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
	
	function getData()
	{
		try
		{
			/* SELECT  DISTINCT a.name,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id ) AS count1,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.who_calculate = 2 ) AS dealers,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.who_calculate = 3 ) AS advt,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19) ) AS deals,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status IN (0,2,3) ) AS inwork,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 1) AS measure,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 15) AS refuse,
			(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 12) AS done,
			(SELECT SUM(COALESCE(p.new_project_sum,0)) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 12) AS `sum`,
			(SELECT  SUM(COALESCE(p.new_project_sum,0)) - (SUM(COALESCE(p.new_material_sum,0))+ SUM(COALESCE(p.new_mount_sum,0))) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 12) AS cost
			FROM `rgzbn_gm_ceiling_projects` AS p INNER JOIN `rgzbn_gm_ceiling_api_phones` AS a ON p.api_phone_id = a.id
 */
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
				->where("p.api_phone_id = a.id");

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
				->where("p.api_phone_id = a.id  AND p.project_status = 15");
			$inwork
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id AND  p.project_status IN (0,2,3)");			
			$measure
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id  AND p.project_status = 1");
			$deals
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19)");
			$done
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id  AND p.project_status = 12");
			$sum
				->select("SUM(COALESCE(p.new_project_sum,0))")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id  AND p.project_status = 12");
			$profit
				->select("SUM(COALESCE(p.new_project_sum,0)) - (SUM(COALESCE(p.new_material_sum,0))+ SUM(COALESCE(p.new_mount_sum,0)))")
				->from("#__gm_ceiling_projects as p")
				->where("p.api_phone_id = a.id  AND p.project_status = 12");
			$query
				->select(' DISTINCT a.name')
				->select("($common) as common")
				->select("($dealers) as dealers")
	            ->select("($advt) as advt")
	            ->select("($refuse) as refuse")
	            ->select("($inwork) as inwork")
	            ->select("($measure) as measure")
				->select("($deals) as deals")
				->select("($done) as done")
				->select("($sum) as sum")
				->select("($profit) as profit")
				->from('`#__gm_ceiling_api_phones` AS a')
				->leftJoin('`#__gm_ceiling_projects` AS p ON p.api_phone_id = a.id');
				//throw new Exception($query);
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			$designers = $this->get_designers_analytics();
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

			return $items;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	function getDataByPeriod($date1,$date2){
		try
		{
			/*SELECT  DISTINCT a.name,
	(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id ) AS common,
	(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.who_calculate = 2 ) AS dealers,
	(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.who_calculate = 3 ) AS advt,
	(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19) ) AS deals,
	(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status IN (0,2,3) ) AS inwork,
	(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 1) AS measure,
	(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.api_phone_id = a.id AND p.project_status = 15) AS refuse
	 FROM `rgzbn_gm_ceiling_projects` AS p INNER JOIN `rgzbn_gm_ceiling_api_phones` AS a ON p.api_phone_id = a.id 
	 WHERE p.created BETWEEN '2017-11-06' AND '2017-11-10'*/
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
			$query
				->select(' DISTINCT a.name')
				->select("($common) as common")
				->select("($dealers) as dealers")
				->select("($advt) as advt")
				->select("($refuse) as refuse")
				->select("($inwork) as inwork")
				->select("($measure) as measure")
                ->select("($deals) as deals")
                ->select("($done) as done")
				->select("ifnull(($sum),0) as sum")
				->select("ifnull(($profit),0) as profit")
				->from('`#__gm_ceiling_api_phones` AS a')
				->leftJoin('`#__gm_ceiling_projects` AS p ON p.api_phone_id = a.id');
				//throw new Exception($query);
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	function get_designers_analytics(){
		/* SELECT c.client_name,c.dealer_id,
(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19)) AS deals,
(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status IN (0,2,3) ) AS inwork,
(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status = 1) AS measure,
(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status = 15) AS refuse,
(SELECT COUNT(p.id) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status  = 12) AS done,
(SELECT SUM(COALESCE(p.new_project_sum,0)) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status = 12) AS `sum`,
(SELECT SUM(COALESCE(p.new_project_sum,0)) - (SUM(COALESCE(p.new_material_sum,0))+ SUM(COALESCE(p.new_mount_sum,0))) FROM `rgzbn_gm_ceiling_projects` AS p WHERE p.client_id = c.id AND p.project_status = 12) AS cost
 FROM `rgzbn_gm_ceiling_clients` AS c
 LEFT JOIN `rgzbn_users` AS u ON c.dealer_id = u.id
 WHERE u.dealer_type = 3 */
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
			->where("p.client_id = c.id");
		$deals
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status IN (4,5,6,7,8,10,11,12,16,17,19)");
		$inwork
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND  p.project_status IN (0,2,3)");
		$measure
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status = 1 ");
		$refuse
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status = 15 ");	
		$done
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status = 12 ");
		$sum
			->select("SUM(COALESCE(p.new_project_sum,0))")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status = 12 ");
		$profit
			->select("SUM(COALESCE(p.new_project_sum,0)) - (SUM(COALESCE(p.new_material_sum,0))+ SUM(COALESCE(p.new_mount_sum,0)))")
			->from("#__gm_ceiling_projects as p")
			->where("p.client_id = c.id AND p.project_status = 12 ");
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
			->where("u.dealer_type = 3");
		$db->setQuery($query);
		$items = $db->loadObjectList();
		return $items;
	}
}
?>