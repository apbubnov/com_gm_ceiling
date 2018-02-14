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
class Gm_ceilingModelClientcard extends JModelList
{
	protected function populateState()
	{
		try
		{
			$app = JFactory::getApplication('com_gm_ceiling');

			// Load state from the request userState on edit or from the passed variable on default

				$id = JFactory::getApplication()->input->get('id');
				JFactory::getApplication()->setUserState('com_gm_ceiling.edit.clientcard.id', $id);

			$this->setState('clientcard.id', $id);

			// Load the parameters.
			$params       = $app->getParams();
			$params_array = $params->toArray();


			$this->setState('params', $params);
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	function &getData($id=null)
	{
		try
		{
			if ($this->_item === null)
			{
				$this->_item = false;

				if (empty($id))
				{
					$id = $this->getState('clientcard.id');
				}
				
			}
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('*')
				->from($db->quoteName('#__gm_ceiling_clients', '#__gm_ceiling_clients_2460720'))
				->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($id)));
			$db->setQuery($query);
			$item = $db->loadObject();
			return $item;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	function getCalls($id){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('a.date_time')
				->select('a.comment')
				->from($db->quoteName('#__gm_ceiling_callback', 'a'))
				->where($db->quoteName('a.client_id') . ' = ' . $db->quote($db->escape($id)));
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
	function getComments($id){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('a.date_time')
				->select('a.comment')
				->from($db->quoteName('#__gm_ceiling_client_comment', 'a'))
				->where($db->quoteName('a.client_id') . ' = ' . $db->quote($db->escape($id)));
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
	function getProjects($id){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('`a`.*')
				->select('`b`.`title` as `status`')
				->from($db->quoteName('`#__gm_ceiling_projects`', '`a`'))
				->innerJoin('`#__gm_ceiling_status` as `b` ON `a`.`project_status` = `b`.`id`')
				->where($db->quoteName('`a`.`client_id`') . ' = ' . $db->quote($db->escape($id)));
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
