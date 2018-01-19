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
class Gm_ceilingModelUsers extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */

	function getDealers()
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('`id`,`name`,`registerDate`,`associated_client`');
			$query->from('`rgzbn_users` LEFT JOIN `rgzbn_user_usergroup_map` ON `rgzbn_users`.`id`=`rgzbn_user_usergroup_map`.`user_id`');
			$query->where('`rgzbn_user_usergroup_map`.`group_id`=14 AND
				NOT ISNULL(`associated_client`) AND `dealer_type` < 2');
			$db->setQuery($query);
			$item = $db->loadObjectList();
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

	function getDesigners()
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('`id`,`name`,`registerDate`,`associated_client`');
			$query->from('`rgzbn_users`');
			$query->where('NOT ISNULL(`associated_client`) AND `dealer_type` = 3');
			$db->setQuery($query);
			$item = $db->loadObjectList();
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

	function addCommercialOfferCode($user_id, $code)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('`rgzbn_users_commercial_offer`');
			$query->where("`user_id` = $user_id");
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->insert('`rgzbn_users_commercial_offer`');
			$query->columns('`user_id`,`code`');
			$query->values("$user_id, '$code'");
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function acceptCommercialOfferCode($code)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('`rgzbn_users_commercial_offer`');
			$query->where("`code` = '$code'");
			$db->setQuery($query);
			$item = $db->loadObject();

			if (empty($item))
			{
				throw new Exception('Code not found');
			}
			if ($item->status == 0)
			{
				$client_id = JFactory::getUser($item->user_id)->associated_client;

				$callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
				$callback_model->save(date('Y-m-d H:i:s'),'Просмотрено коммерческое предложение',
					$client_id,1);

				$query = $db->getQuery(true);
				$query->update('`rgzbn_users_commercial_offer`');
				$query->set('`status` = 1');
				$query->where("`user_id` = $item->user_id");
				$db->setQuery($query);
				$db->execute();
			}
			return $item->id;
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