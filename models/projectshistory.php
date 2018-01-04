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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	
	function save($project_id,$new_status){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->insert('`#__gm_ceiling_projects_history`')
				->columns('project_id,new_status,date_of_change')
				->values("$project_id,$new_status,NOW()");
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    function getIdsByStatusAndAdvt($advt,$statuses,$date1,$date2){
		if(empty($advt)){
            $advt = 'total';
        }
		switch(true){
            case $statuses == 'all' && $advt == 'total':
                $where  = "p.created between '$date1' and '$date2' and p.api_phone_id is NOT NULL";
                break;
             case $statuses == 'all' && $advt != 'total':
                $where  = "p.api_phone_id = $advt and p.created between '$date1' and '$date2'";
                break;
            case $statuses == 'current' && $advt == 'total':
                $where  = "p.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00'";
                break;
            case $statuses == 'mounts' && $advt == 'total':
                $where  = "p.project_mounting_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00'";
                break;
			case $statuses=='current'&&$advt!='total':
				$where  = "p.api_phone_id = $advt AND p.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:00'";
				break;
			case $statuses=='mounts'&&$advt!='total':
				$where = "p.api_phone_id = $advt AND p.project_mounting_date BETWEEN  '$date1 00:00:00' and  '$date2 23:59:59'";
                break;
            case $advt == 'total' && ($statuses!='mounts' || $statuses!= 'current' || $statuses!='all'):
                $where = "h.new_status in $statuses and h.date_of_change between '$date1' and '$date2'";
                break; 
            default:
        
				$where = "p.api_phone_id = $advt and h.new_status in $statuses and h.date_of_change between '$date1' and '$date2'";
				break;

		}
        try{
            $db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$subquery = $db->getQuery(true);
            $subquery
                ->select("SUM(COALESCE(c.components_sum,0)+COALESCE(c.canvases_sum,0)+COALESCE(c.mounting_sum,0))")
                ->from("`#__gm_ceiling_calculations` as c")
                ->where("c.project_id = p.id");
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
				->leftJoin('#__gm_ceiling_projects_history as h on h.project_id = p.id')
				->innerJoin('#__gm_ceiling_status as s on p.project_status = s.id')
				->where($where);
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
	
}
?>