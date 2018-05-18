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
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('`a`.*,`c`.`client_name`,`u`.`dealer_type`')
				->from('`#__gm_ceiling_requests_from_promo` AS `a`')
				->innerJoin('`#__gm_ceiling_clients` AS `c` ON `a`.`client_id` = `c`.`id`')
				->leftJoin('`#__users` AS `u` ON `a`.`client_id` = `u`.`associated_client`')
				->order('`date_time` DESC');
			$db->setQuery($query);

			$items = $db->loadObjectList();
			
			return $items;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
}
?>