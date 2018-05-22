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
class Gm_ceilingModelCommercial_offer extends JModelList
{
	function getData($filter = null)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('`#__gm_ceiling_commercial_offers`');
			$query->where("$filter");
			$db->setQuery($query);
			
			$result = $db->loadObjectList();

			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function addCommOffer($subj, $text, $name, $manufac_id)
	{
		try
		{
			$db = JFactory::getDbo();
			$subj = $db->escape($subj, false);
			$text = $db->escape($text, false);
			$name = $db->escape($name, false);

			$query = $db->getQuery(true);
			$query->insert('`#__gm_ceiling_commercial_offers`');
			$query->columns('`subject`, `text`, `name`, `manufacturer_id`');
			$query->values("'$subj', '$text', '$name', $manufac_id");
			$db->setQuery($query);
			
			$db->execute();
			$last_id = $db->insertId();

			return $last_id;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
?>