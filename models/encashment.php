<?php
 // No direct access.
 defined('_JEXEC') or die;
 
 jimport('joomla.application.component.modelitem');
 jimport('joomla.event.dispatcher');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelEncashment extends JModelList
{
	
	function getData($date1,$date2)
	{
		try
		{
			$date1 = date('Y-m-d 00:00:00',strtotime($date1));
			$date2 = date('Y-m-d 23:59:00',strtotime($date2));
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
            $query
                ->select('date_time')
                ->select('sum')
				->select('manager_id')
				->from('#__gm_ceiling_encashment')
				->where("`date_time` between '$date1' and '$date2'")
				->order('`date_time` ASC');
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	

	function save($sum, $manager_id)
	{
		try
		{
			$db = JFactory::getDbo();
			$comment = $db->escape($comment, true);
			$id_client = $db->escape($id_client, true);
			$manager_id = $db->escape($manager_id, true);

			$query = $db->getQuery(true);
			$query->insert('`#__gm_ceiling_encashment`');
			$query->columns('`date_time`, `sum`, `manager_id`');
			$query->values("NOW(),'$sum', $manager_id");
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
?>