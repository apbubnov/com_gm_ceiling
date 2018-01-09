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
class Gm_ceilingModelCashbox extends JModelList
{
	
	function getData()
	{
		try
		{
			/*

	SELECT a.project_id,c.client_name,a.date_time,a.comment,(SELECT s.title FROM `rgzbn_gm_ceiling_projects` AS p INNER JOIN `rgzbn_gm_ceiling_status` AS s ON p.project_status = s.id
	WHERE p.id = a.project_id) AS st FROM `rgzbn_gm_ceiling_callback` AS a INNER JOIN `rgzbn_gm_ceiling_clients` AS c ON a.client_id = c.id */
			// Create a new query object.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('p.id')
				->select('p.closed')
				->select('u.name')
				->select('p.new_project_sum')
				->select('p.new_mount_sum')
				->select('p.new_material_sum')
				->from('#__gm_ceiling_projects as p')
				->innerJoin('#__users as u ON p.project_mounter = u.id')
				->where('p.project_status=12')
				->order('`p.closed` ASC');
			$db->setQuery($query);
			$items = $db->loadObjectList();
			$encashment_model = $this->getModel('Encashment','Gm_ceiling');
			$encashments = $encashment_model->getData();
			for($i=0;$i<count($encashments);$i++){
				for($j=0;$j<count($items);$j++){
					if(strtotime($encashments[$i]->date_time)>=strtotime($items[$j]->closed)){
						array_splice( $items,$j,0,$encashments[$i]);
						break;
					}
				}
			}
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
?>