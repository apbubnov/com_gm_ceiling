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
class Gm_ceilingModelAnalytic extends JModelList
{
	function generateWhere($statuses,$date1,$date2){
		try
		{
			if(!empty($statuses)){
				$str = "and p.project_status ";
			}
			if(count($statuses)==1)
			{
				$str .= " = $statuses[0]";
			}
			elseif(count($statuses)>1) {
				$str .= "IN(";
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
			if(!empty($date1) && !empty($date2)){
				if(!empty($str)){
					$str .= " AND ";
				}
				$str .= "p.created BETWEEN '$date1' and '$date2'";
				
			}
			return $str;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	
	function generateSubqueryForCommon($equality,$statuses,$date1,$date2,$needJoin,$dealer_id=null){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select("COUNT(p.id)");
			$query->from("#__gm_ceiling_projects as p");
			if($needJoin){
				$query->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id");
			}
			$query->where("$equality ".$this->generateWhere($statuses,$date1,$date2));
			if($needJoin){
				$query->where("c.dealer_id =  $dealer_id");
			}			
			return $query;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function getCommonAnalytic($dealer_id = null,$date1=null,$date2=null){
		try{
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

			$eq = "p.api_phone_id = a.id";
			$common = $this->generateSubqueryForCommon($eq,[],$date1,$date2,true,$dealer_id);
			$dealers = $this->generateSubqueryForCommon($eq,[20],$date1,$date2,true,$dealer_id);
			$advt = $this->generateSubqueryForCommon($eq,[21],$date1,$date2,true,$dealer_id);
			$refuse = $this->generateSubqueryForCommon($eq,[15],$date1,$date2,true,$dealer_id);
			$inwork = $this->generateSubqueryForCommon($eq,[0,2,3],$date1,$date2,true,$dealer_id);	
			$measure = $this->generateSubqueryForCommon($eq,[1],$date1,$date2,true,$dealer_id);
			$deals = $this->generateSubqueryForCommon($eq,[4,5,6,7,8,10,11,12,16,17,19,24,25,26],$date1,$date2,true,$dealer_id);
			$done = $this->generateSubqueryForCommon($eq,[12],$date1,$date2,true,$dealer_id);
			$sum
				->select("SUM(COALESCE(IF((p.project_sum IS NULL OR p.project_sum = 0) AND (p.new_project_sum  IS NOT NULL OR p.new_project_sum <>0),p.new_project_sum,p.project_sum),0))")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id")
				->where("p.api_phone_id = a.id  ".$this->generateWhere([12],$date1,$date2)." and c.dealer_id =  $dealer_id");
			$profit
				->select("SUM(IF((project_sum IS NULL OR project_sum = 0) AND (new_project_sum  IS NOT NULL OR new_project_sum <>0),new_project_sum,project_sum) -
						IF(COALESCE(p.new_material_sum + p.new_mount_sum,0) = 0,($profit_sub),COALESCE(p.new_material_sum + p.new_mount_sum,0)))")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("#__gm_ceiling_clients as c on c.id = p.client_id")
				->where("p.api_phone_id = a.id  ".$this->generateWhere([12],$date1,$date2)." and c.dealer_id =  $dealer_id");
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
			$db->setQuery($query);
			throw new Exception($query);
			$items = $db->loadObjectList();
			$designers = $this->getCommonAnalyticByType(3,$date1,$date2);
			$wininstallers = $this->getCommonAnalyticByType(8,$date1,$date2);
			$no_advt = $this->getCommonAnalyticByType(null,$date1,$date2,$dealer_id);
			array_push($items,$designers);
			array_push($items,$wininstallers);
			array_push($items,$no_advt);
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getCommonAnalyticByType($dealer_type = null,$date1 = null,$date2 = null,$dealer_id=null){
		$title = "Без рекламы";
 		if($dealer_type == 3){
 			$title = "Отделочники";
 		}
 		if($dealer_type == 8){
 			$title = "Оконщики";	
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
		if(empty($dealer_type)){
			$eq = "p.client_id = c.id and p.api_phone_id IS NULL";
		}
		else{
			$eq = "p.client_id = c.id";
		}
		    $common = $this->generateSubqueryForCommon($eq,[],$date1,$date2,false);
			$deals =  $this->generateSubqueryForCommon($eq,[4,5,6,7,8,10,11,12,16,17,19],$date1,$date2,false);
			$inwork = $this->generateSubqueryForCommon($eq,[0,2,3],$date1,$date2,false);
			$measure = $this->generateSubqueryForCommon($eq,[1],$date1,$date2,false);
			$refuse = $this->generateSubqueryForCommon($eq,[15],$date1,$date2,false);
			$done = $this->generateSubqueryForCommon($eq,[ 12],$date1,$date2,false);
			$sum
				->select("SUM(COALESCE(p.new_project_sum,0))")
				->from("#__gm_ceiling_projects as p")
				->where("p.client_id = c.id ".$this->generateWhere([12],$date1,$date2));
			$profit
				->select("SUM(COALESCE(p.new_project_sum,0)) - (SUM(COALESCE(p.new_material_sum,0))+ SUM(COALESCE(p.new_mount_sum,0)))")
				->from("#__gm_ceiling_projects as p")
				->where("p.client_id = c.id " .$this->generateWhere([12],$date1,$date2));
		$query
			->select("'$title' as name")
			->select("SUM(($common)) as common")
			->select("0 as dealers")
			->select("0 as advt")
			->select("SUM(($refuse)) as refuse")
			->select("SUM(($inwork)) as inwork")
			->select("SUM(($measure)) as measure")
			->select("SUM(($deals)) as deals")
			->select("SUM(($done)) as done")
			->select("SUM(ifnull(($sum),0)) as sum")
			->select("SUM(ifnull(($profit),0)) as profit")
			->from('`#__gm_ceiling_clients` AS c')
			->innerJoin('`#__users` AS u ON c.dealer_id = u.id');
			if(!empty($dealer_type)){
				$query->where("u.dealer_type = $dealer_type ");
			}
			if(empty($dealer_type)){
				$query->where("u.dealer_id = $dealer_id");
			}
			
			
		$db->setQuery($query);		
		$items = $db->loadObject();
		return $items;
	}

	function getDetQuery($statuses,$date1,$date2){
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

	function getDetSubquery($statuses,$date1,$date2,$dealer_id = null){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select("COUNT(p.id)")
			->from("#__gm_ceiling_projects as p")
			->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
			->where("p.id IN (". $this->getDetQuery($statuses,$date1,$date2).")");
			if(!empty($dealer_id)){
				$query->where("cl.dealer_id = $dealer_id");
			}
		return $query;
	}
	function getDetailedAnalytic($date1 = null,$date2 = null,$dealer_id = null){
		if(empty($date1) && empty($date2)){
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
			$mounts
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
				->innerJoin("`#__gm_ceiling_projects_mounts` as pm on p.id = pm.project_id")
				->where("p.project_status NOT IN (2,3) and p.api_phone_id = a.id AND pm.date_time BETWEEN  '$date1 00:00:00' and  '$date2 23:59:59' and cl.dealer_id = $dealer_id");

			$sum_deals
                ->select("SUM(COALESCE(p.project_sum,0))")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
                ->where("p.id IN (".$this->getDetQuery([4,5],$date1,$date2).") and cl.dealer_id = $dealer_id");
            $sum_done
                ->select("SUM(COALESCE(IF((p.project_sum IS NULL OR p.project_sum = 0) AND (p.new_project_sum  IS NOT NULL OR p.new_project_sum <>0),p.new_project_sum,p.project_sum),0))")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id")
                ->where("p.id IN (".$this->getDetQuery([12],$date1,$date2).") and cl.dealer_id = $dealer_id");
            $measure = $this->getDetSubquery([1],$date1,$date2,$dealer_id);
			$ref_measure = $this->getDetSubquery([2],$date1,$date2,$dealer_id);
			$deals = $this->getDetSubquery([4,5],$date1,$date2,$dealer_id);
			$ref_deals = $this->getDetSubquery([3],$date1,$date2,$dealer_id);
			$dealers = $this->getDetSubquery([20],$date1,$date2);
			$advt = $this->getDetSubquery([21],$date1,$date2);
			$closed = $this->getDetSubquery([12],$date1,$date2,$dealer_id);
			$refused = $this->getDetSubquery([15],$date1,$date2,$dealer_id);

			$query->select('DISTINCT a.name');
			$query->select(' a.id');
            $query->select("($common) as common");
            if($dealer->dealer_type!=1){
	            $query->select("($dealers) as dealers");
	            $query->select("($advt) as advt");
	        }
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
			$query->from('`#__gm_ceiling_api_phones` AS a');
			$query->where("a.dealer_id = $dealer_id");
			$db->setQuery($query);

			$items = $db->loadObjectList();
			$designers = $this->getDetDataByDealerType(3,$date1,$date2);
			$wininstallers = $this->getDetDataByDealerType(8,$date1,$date2);
			$no_advt = $this->getDetDataByDealerType(null,$date1,$date2,$dealer_id);
			array_push($items,$designers);
			array_push($items,$wininstallers);
			array_push($items,$no_advt);
			return $items;
	}

	function getSubqueryForDealerTypeQuery($statuses,$date1,$date2,$dealer_type,$eq,$dealer_id=null){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$clients_id = $db->getQuery(true);
            $clients_id
            	->select("c.id")
            	->from("`#__gm_ceiling_clients` AS c")
            	->leftJoin("`#__users` AS u ON c.dealer_id = u.id");
            	if(empty($eq)){
            		$clients_id->where("u.dealer_type = $dealer_type");
            	}
            	else{
            		$clients_id->where("u.dealer_id = $dealer_id");
            	}
            	
		$query
			->select("COUNT(p.id)")
			->from("`#__gm_ceiling_projects_history` AS h ")
			->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
			->where("$eq p.client_id in ($clients_id) and h.new_status $statuses and h.date_of_change BETWEEN '$date1' and '$date2'");
		return $query;
	}
	function getDetDataByDealerType($dealer_type,$date1,$date2,$dealer_id){
		try{
			if(empty($dealer_type)){
				$title = "Без рекламы";
				$eq = "p.api_phone_id is NULL and";
			}
			else {
				$eq = "";
			}
			if($dealer_type == 3){
	 			$title = "Отделочники";
	 		}
	 		if($dealer_type == 8){
	 			$title = "Оконщики";	
	 		}
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
            	->leftJoin("`#__users` AS u ON c.dealer_id = u.id");
            if(!empty($dealer_type)){
            	$clients_id->where("u.dealer_type = $dealer_type");
            }
            else{
        		$clients_id->where("u.dealer_id = $dealer_id");
        	}
            $common
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("$eq p.client_id in ($clients_id) AND p.created BETWEEN '$date1' and '$date2'");

			$current_measure
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->where("$eq p.client_id in ($clients_id) AND p.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00'");

			$measure = $this->getSubqueryForDealerTypeQuery('=1',$date1,$date2,$dealer_type,$eq,$dealer_id);
			$ref_measure = $this->getSubqueryForDealerTypeQuery('=2',$date1,$date2,$dealer_type,$eq,$dealer_id);
			$deals = $this->getSubqueryForDealerTypeQuery('in(4,5)',$date1,$date2,$dealer_type,$eq,$dealer_id);
			$ref_deals = $this->getSubqueryForDealerTypeQuery('=3',$date1,$date2,$dealer_type,$eq,$dealer_id);
			$closed = $this->getSubqueryForDealerTypeQuery('=12',$date1,$date2,$dealer_type,$eq,$dealer_id);
			$refused = $this->getSubqueryForDealerTypeQuery('=15',$date1,$date2,$dealer_type,$eq,$dealer_id);

			
			$mounts
				->select("COUNT(p.id)")
				->from("#__gm_ceiling_projects as p")
				->innerJoin("`#__gm_ceiling_projects_mounts` as pm on p.id = pm.project_id")
				->where("$eq p.project_status NOT IN (2,3) and p.client_id in ($clients_id) AND pm.date_time BETWEEN  '$date1 00:00:00' and  '$date2 23:59:59'");

            $sum_deals
                ->select("SUM(COALESCE(p.project_sum,0))")
				->from("`#__gm_ceiling_projects_history` AS h ")
				->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
				->where("$eq p.client_id in ($clients_id) and h.new_status IN(4,5) and h.date_of_change BETWEEN '$date1' and '$date2'");
            $sum_done
                ->select("SUM(COALESCE(p.new_project_sum,0))")
				->from("`#__gm_ceiling_projects_history` AS h ")
				->leftJoin("`#__gm_ceiling_projects` AS p ON p.id = h.project_id")
				->where("$eq p.client_id in ($clients_id) and h.new_status = 12 and h.date_of_change BETWEEN '$date1' and '$date2'");
			$query->select("'$title' as name");
			$query->select("0 as id");
            $query->select("SUM(($common)) as common");
            $query->select("0 as dealers");
	        $query->select("0 as advt");
            $query->select("SUM(($refused)) as refused");
            $query->select("SUM(($ref_measure)) as ref_measure");
            $query->select("SUM(($measure)) as measure");
            $query->select("SUM(($current_measure)) as current_measure");
			$query->select("SUM(($ref_deals)) as ref_deals");
            $query->select("SUM(($deals)) as deals");
            $query->select("SUM(ifnull(($sum_deals),0)) as sum_deals");
			$query->select("SUM(($mounts)) as mounts");
            $query->select("SUM(($closed)) as closed");
            $query->select("SUM(ifnull(($sum_done),0)) as sum_done");
			$db->setQuery($query);
			$items = $db->loadObject();

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
