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
class Gm_ceilingModelRequestfrompromo extends JModelList
{
	
	function getData()
	{
		try
		{
			/*

	SELECT a.project_id,c.client_name,a.date_time,a.comment,(SELECT s.title FROM `rgzbn_gm_ceiling_projects` AS p INNER JOIN `rgzbn_gm_ceiling_status` AS s ON p.project_status = s.id
	WHERE p.id = a.project_id) AS st FROM `rgzbn_gm_ceiling_callback` AS a INNER JOIN `rgzbn_gm_ceiling_clients` AS c ON a.client_id = c.id */
			// Create a new query object.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.id')
				->select('a.date_time')
				->select('a.action')
				->select('a.client_id')
				->select('c.client_name')
				->from('#__gm_ceiling_requests_from_promo as a')
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
	
	function save($action, $id_client)
	{
		try
		{
			$db = JFactory::getDbo();
			$action = $db->escape($action, true);
			$id_client = $db->escape($id_client, true);

			
			$query = $db->getQuery(true);
			$query->insert('`#__gm_ceiling_requests_from_promo`');
			$query->columns('`client_id`, `action`, `date_time`');
			$query->values("$id_client, '$action', NOW()");
			
			$db->setQuery($query);
			$db->execute();
			$last_id = $db->insertid();
			return $last_id;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	function delete($client_id){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->delete('`#__gm_ceiling_requests_from_promo`')
				->where("client_id = $client_id");
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
	
}
?>