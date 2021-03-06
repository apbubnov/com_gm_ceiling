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
class Gm_ceilingModelProjectshistory extends JModelList
{
	
	function getData()
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.id')
				->select('a.date_time')
				->select('a.comment')
				->select('a.manager_id')
				->select('a.client_id')
				->select('c.client_name')
				->from('#__gm_ceiling_callback as a')
				->innerJoin('#__gm_ceiling_clients as c ON a.client_id = c.id ORDER BY `date_time` DESC');
			$db->setQuery($query);
		
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	function save($project_id,$new_status){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("*")
				->from('`#__gm_ceiling_projects_history`')
				->where("project_id = $project_id and new_status = $new_status");
			$db->setQuery($query);
			$items = $db->loadObjectList();
			if(empty($items)){
				$query
					->insert('`#__gm_ceiling_projects_history`')
					->columns('project_id,new_status,date_of_change')
					->values("$project_id,$new_status,NOW()");
			}
			else{
				$query
					->update('`#__gm_ceiling_projects_history`')
					->set('date_of_change = NOW()')
					->where("project_id = $project_id and new_status = $new_status");
			}
			
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function delete($project_id){
    	try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->delete("`#__gm_ceiling_projects_history`")
				->where("project_id = $project_id and new_status in (5,10,19)");
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getIdsByStatusAndAdvt($dealer_id,$advt,$statuses,$date1,$date2){
		
        try{
        	if(empty($dealer_id)){
        		$dealer_id = 1;
        	}
            $db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$subquery = $db->getQuery(true);
			$subquery_advt = $db->getQuery(true);
			$subquery_dealer_users = $db->getQuery(true);
			if($advt == 'Отделочники'){
				$dealer_type = '(3)';
			}
			elseif($advt == 'Оконщики'){
				$dealer_type = '(8)';
			}
			elseif($advt == 'total'){
				$dealer_type = '(3,8)';
			}
            $subquery
                ->select("SUM(COALESCE(c.components_sum,0)+COALESCE(c.canvases_sum,0)+COALESCE(c.mounting_sum,0))")
                ->from("`#__gm_ceiling_calculations` as c")
                ->where("c.project_id = p.id");

            $subquery_advt
                ->select("id")
                ->from("`#__gm_ceiling_api_phones`")
                ->where("dealer_id = $dealer_id");
			$clients_id = $db->getQuery(true);
            $clients_id
            	->select("c.id")
            	->from("`#__gm_ceiling_clients` AS c")
            	->leftJoin("`#__users` AS u ON c.dealer_id = u.id")
            	->where("u.dealer_type in $dealer_type");

            $subquery_dealer_users
            	->select("distinct id")
            	->from("`#__users`")
                ->leftJoin('`rgzbn_users_dealer_id_map` as dm on dm.user_id = u.id')
            	->where("(u.dealer_id = $dealer_id or dm.dealer_id = $dealer_id)");
			switch(true){
            	case $statuses == 'all' && $advt == 'total':
	                $where  = "p.created between '$date1' and '$date2' and (p.api_phone_id in ($subquery_advt) or p.client_id in($clients_id) )";
	                break;
	            case $statuses == 'all' && $advt != 'total' && $advt != "Отделочники" && $advt != "Оконщики" :
	                $where  = "p.api_phone_id = $advt and p.created between '$date1' and '$date2'";
	                break;
	            case $statuses == 'current' && $advt == 'total':
	            	if($dealer_id == 1){
	            		$where  = "p.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00' and cl.dealer_id in ($subquery_dealer_users)";
	            	}
	                else{
	                	$where  = "p.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00' and cl.dealer_id = $dealer_id";
	                }
	                break;
	            case $statuses == 'current' && $advt == 'Отделочники':
	            	$where  = "p.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00' and p.client_id in ($clients_id)";
	            	break;
	            case $statuses == 'current' && $advt == 'Оконщики':
	            	$where  = "p.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00' and p.client_id in ($clients_id)";
	            	break;
	            case $statuses == 'all' && $advt == 'Отделочники':
	            	$where  = "p.created between '$date1' and '$date2' and  p.client_id in($clients_id)";
	            	break;
	            case $statuses == 'all' && $advt == 'Оконщики':
	            	$where  = "p.created between '$date1' and '$date2' and  p.client_id in($clients_id)";
	            	break;
	            case $statuses == 'mounts' && $advt == 'total':
	                $where  = "mp.date_time BETWEEN '$date1 00:00:00' AND '$date2 23:59:00' and cl.dealer_id in ($subquery_dealer_users)";
	                break;
				case $statuses=='current' && $advt!='total':
					$where  = "p.api_phone_id = $advt AND p.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00'";
					break;
				case $statuses == 'mounts' && $advt == 'Отделочники':
					$where  = "mp.date_time BETWEEN '$date1 00:00:00' AND '$date2 23:59:00' and p.client_id in ($clients_id)";
					break;
				case $statuses == 'mounts' && $advt == 'Оконщики':
					$where  = "mp.date_time BETWEEN '$date1 00:00:00' AND '$date2 23:59:00' and p.client_id in ($clients_id)";
					break;
				case $statuses=='mounts' && $advt!='total':
					$where = "p.api_phone_id = $advt AND mp.date_time BETWEEN  '$date1 00:00:00' and  '$date2 23:59:59'";
	                break;
	            case $advt == 'total' && ($statuses!='mounts' || $statuses!= 'current' || $statuses!='all'):
		            if($dealer_id == 1){
		                $where = "h.new_status in $statuses and h.date_of_change between '$date1' and '$date2'  and (cl.dealer_id = $dealer_id and p.api_phone_id in ($subquery_advt) or p.client_id in ($clients_id))";
		            }
		            else{
						$where = "h.new_status in $statuses and h.date_of_change between '$date1' and '$date2' and cl.dealer_id = $dealer_id and p.api_phone_id in ($subquery_advt)";
		            }
	                break; 
				case $advt == 'Отделочники' && ($statuses!='mounts' || $statuses!= 'current' || $statuses!='all'):
		               $where = "h.new_status in $statuses and h.date_of_change between '$date1' and '$date2' and p.client_id in ($clients_id)";
		           
	                break; 
	            case $advt == 'Оконщики' && ($statuses!='mounts' || $statuses!= 'current' || $statuses!='all'):
		               $where = "h.new_status in $statuses and h.date_of_change between '$date1' and '$date2' and p.client_id in ($clients_id)";
		           
	                break; 
	            default:
					$where = "p.api_phone_id = $advt and h.new_status in $statuses and h.date_of_change between '$date1' and '$date2' and cl.dealer_id = $dealer_id";
					break;

			}
			$query
				->select('distinct p.id')
				->select('s.title as `status`')
				->select('p.project_info')
				->select('COALESCE(p.project_sum,0) as project_sum')
				->select('COALESCE(p.new_project_sum,0) as new_project_sum')
				->select('COALESCE(p.new_mount_sum,0) as new_mount_sum')
				->select('COALESCE(p.new_material_sum,0) as new_material_sum')
				->select('client_id')
				->select("ifnull(($subquery),0) as cost")
                ->from('#__gm_ceiling_projects as p')
                ->innerJoin('`#__gm_ceiling_projects_mounts` as mp on p.id = mp.project_id')
				->leftJoin('#__gm_ceiling_projects_history as h on h.project_id = p.id')
				->innerJoin('#__gm_ceiling_status as s on p.project_status = s.id')
				->innerJoin("`#__gm_ceiling_clients` as cl on p.client_id = cl.id ")
				->innerJoin("`#__users` as u on cl.dealer_id = u.id")
				->where($where);
			$db->setQuery($query);
			//throw new Exception($query);
			
			$items = $db->loadObjectList();
			return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getManagersProjects($date1,$date2){
	    try{
	        $user = JFactory::getUser();
	        $dealerId = $user->dealer_id;
	        $db = JFactory::getDbo();

            $query = 'SET SESSION group_concat_max_len  = 1000000';
            $db->setQuery($query);
            $db->execute();

	        $query = $db->getQuery(true);
	        $subquery = $db->getQuery(true);
	        $subquery
                ->select('p.created_by,p.read_by_manager,ph.new_status,COUNT(ph.project_id) AS `count`')
                ->select('GROUP_CONCAT(distinct CONCAT(\'{"id":"\',ph.project_id,\'","created":"\',p.created_by,\'","read":"\',p.read_by_manager,\'"}\') SEPARATOR \',\') AS projects/* p.created_by,p.read_by_manager,*/')
                ->from('`rgzbn_gm_ceiling_projects_history` as ph')
                ->innerJoin('`rgzbn_gm_ceiling_projects` as p ON p.id = ph.project_id')
                ->innerJoin('`rgzbn_gm_ceiling_clients` as c on c.id = p.client_id')
                ->where("ph.date_of_change between '$date1' and '$date2' and ph.new_status in (1,3,4,5) and c.dealer_id = $dealerId")
                ->group('ph.new_status,p.created_by,p.read_by_manager');
	        $query
                ->select('DISTINCT u.id,u.name')
                ->select('CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"status":"\',pr.new_status,\'","projects":[\',pr.projects,\']}\')),\']\') AS `data`')
                ->from('`rgzbn_users` as u')
                ->from('`rgzbn_users_dealer_id_map` as dm on dm.user_id = u.id')
                ->innerJoin('`rgzbn_user_usergroup_map` as map on u.id = map.user_id')
                ->innerJoin("($subquery) as pr on pr.created_by = u.id OR pr.read_by_manager = u.id")
                ->where('map.group_id = 16 or dm.group_id = 16')
                ->group('u.id');
	        $db->setQuery($query);
            //throw new Exception($query);
	        $result = $db->loadObjectList();
	        return $result;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	
}
?>