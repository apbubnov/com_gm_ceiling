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

            $query
                ->select('u.name,i.text,i.connected,i.request_count')
                ->from('`#__users` AS `u`')
                ->leftJoin('#__gm_ceiling_manufacturer_info as i on u.id = i.manufacturer_id')
                ->where('u.dealer_type = 6');
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