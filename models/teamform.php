<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

/**
 * Gm_ceiling model.
 *
 * @since  1.6
 */
class Gm_ceilingModelTeamform extends JModelItem {

	// сохранение монтажника
	function SaveMounters($name, $phone, $pasport) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$name = $db->escape($name);
			$phone = $db->escape($phone);
			$pasport = $db->escape($pasport);

			$query->insert('#__gm_ceiling_mounters')
			->columns('name, phone, pasport')
			->values('"'.$name.'", "'.$phone.'", "'.$pasport.'"');
			$db->setQuery($query);
			$db->execute();

			$id = $db->insertid();
			return $id;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	// сохранение связи между бригадой и монтажниками
	function SaveMountersMap($brigade, $mounter) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->insert('#__gm_ceiling_mounters_map')
			->columns('id_mounter, id_brigade')
			->values('"'.$mounter.'", "'.$brigade.'"');
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	
}
