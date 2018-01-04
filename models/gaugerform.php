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
class Gm_ceilingModelGaugerform extends JModelItem {

	// сохранение монтажника
	function SaveGaugerPassport($id_gauger, $passport) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$passport = $db->escape($passport);

			$query->insert('#__gm_ceiling_gaugers_passport')
			->columns('id_gauger, passport')
			->values("'$id_gauger', '$passport'");
			$db->setQuery($query);
			$db->execute();

			$id = $db->insertid();
			return $id;
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
