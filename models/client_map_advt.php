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
class Gm_ceilingModelClient_map_advt extends JModelList
{
	public function save($client_id,$advt_id){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->insert('`#__gm_ceiling_client_map_advertising`');
			$query->columns('`client_id`,`date_time`, `api_phone_id`');
			$query->values("$client_id, NOW(),$advt_id");
			
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
