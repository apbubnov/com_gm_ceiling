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
class Gm_ceilingModelManufacturers extends JModelList
{
	
	function getData()
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$subquery = $db->getQuery(true);
			$subquery
				->select('count(*)')
				->from("#__gm_ceiling_manufacturer_map_request as mp")
				->where("mp.manufacturer_id = u.id");
            $query
				->select('u.id,u.name,i.text,i.connect,i.request_count')
				->select("($subquery) as request_count")
                ->from('`#__users` AS `u`')
                ->leftJoin('#__gm_ceiling_manufacturer_info as i on u.id = i.manufacturer_id')
                ->where('u.dealer_type = 6');
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