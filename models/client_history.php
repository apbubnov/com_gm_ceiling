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
class Gm_ceilingModelClient_history extends JModelList
{
	
	function getData()
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('*')
				->from('`#__gm_ceiling_client_history`');
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getDataByClientId($client_id)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('*')
				->from('`#__gm_ceiling_client_history`')
				->where("`client_id` = $client_id ORDER BY `date_time`");
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function save($id_client, $text)
	{
		try
		{
			$db = JFactory::getDbo();

			$id_client = $db->escape($id_client, true);
			$text = $db->escape($text, true);

			$pattern = "/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/i";
			$replacement = "\$3.\$2.\$1 \$4:\$5";
			$text = preg_replace($pattern, $replacement, $text);

			$query = $db->getQuery(true);
			$query->insert('`#__gm_ceiling_client_history`')
				->columns('`client_id`, `date_time`, `text`')
				->values("$id_client , NOW(), '$text'");
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
	function updateClientId($client_id,$ids)
	{
		try
		{
			$db = JFactory::getDbo();
			foreach($ids as $id){
				$query = $db->getQuery(true);
				$query->update('#__gm_ceiling_client_history');
				$query->set('client_id = '.$client_id);
				$query->where('id = '.$id);
				$db->setQuery($query);
				$db->execute();
			}
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
?>