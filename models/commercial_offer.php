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
	function addCommOffer($subj, $text, $name, $manufac_id)
	{
		try
		{
			$db = JFactory::getDbo();
			$subj = $db->escape($subj, true);
			$text = $db->escape($text, true);
			$name = $db->escape($name, true);

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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
}
?>