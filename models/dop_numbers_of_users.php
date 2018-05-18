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
class Gm_ceilingModelDop_numbers_of_users extends JModelList
{
	function getData($user_id)
	{
		try
		{
			// Create a new query object.
			$db = JFactory::getDbo();
			$user_id = $db->escape($user_id, true);

			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('`#__gm_ceiling_dop_numbers_of_users`');
			$query->where("`user_id` = $user_id");
			$db->setQuery($query);
			$item = $db->loadObject();

			return $item;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
?>