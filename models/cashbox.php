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
	
	function getData($date1 = null,$date2 = null)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			if(empty($date1) && empty($date2)){
				$date1 = date('Y-m-01');
				$date2 = date('Y-m-t'); 
			}
			$query
				->select('p.closed')
				->select('p.id')
				->select('s.title as status')
				->select('u.name')
				->select('p.new_project_sum')
				->select('p.new_mount_sum')
				->select('p.project_status')
				->select('p.new_material_sum')
				->select('p.new_project_mounting')
				->select('p.check_mount_done as `done`')
				
				->from('#__gm_ceiling_projects as p')
				->innerJoin('#__users as u ON p.project_mounter = u.id')
				->innerJoin('#__gm_ceiling_status as s on p.project_status = s.id')
				->where("p.project_status in (12,17) and p.closed between '$date1' and '$date2'");
			$db->setQuery($query);
			$items = $db->loadObjectList();
			$encashment_model = Gm_ceilingHelpersGm_ceiling::getModel('Encashment');
			$encashments = $encashment_model->getData($date1,$date2);
			$new_encash = [];
			foreach($encashments as $value){
				$el = array(
					'id'=>null,
					'closed'=>$value->date_time,
					'name'=>null,
					'status'=>null,
					'done'=>null,
					'project_status' =>null,
					'new_project_sum'=>null,
					'new_project_mounting'=>null,
					'new_mount_sum'=>null,
					'new_material_sum'=>null,
					'sum'=>$value->sum,
				);
				array_push($new_encash,(object)$el);
			}

			$items = array_merge($items,$new_encash);

			for($i=0; $i<count($items); $i++){
				for($j=$i+1; $j<count($items); $j++){
					if(strtotime($items[$i]->closed)>strtotime($items[$j]->closed)){
						$temp = $items[$j];
						$items[$j] = $items[$i];
						$items[$i] = $temp;
					}
			   }         
			}
			$result = [];
			for($i=0; $i<count($items); $i++){
				$el['closed']=$items[$i]->date_time;
				$el['id'] = $items[$i]->id;
				$el['status'] = $items[$i]->status;
				$el['name'] = $items[$i]->name;
				$el['new_project_sum'] = $items[$i]->new_project_sum;
				$el['new_mount_sum'] = $items[$i]->new_mount_sum;
				if($items[$i]->done!=1&&$items[$i]->project_status != 12){
					$el['not_issued'] =  $items[$i]->new_mount_sum - $items[$i]->new_project_mounting;
				}
				else
				{
					$el['not_issued'] = 0;
				}
				$el['new_material_sum'] = $items[$i]->new_material_sum;
				$el['residue'] = $items[$i]->new_project_sum - $items[$i]->new_mount_sum -$items[$i]->new_material_sum;
				$el['cashbox'] += $el['residue'] - $encash;
				$encash = 0;
				$encash = $item->sum;
				$el['sum'] = $items[$i]->sum;
				array_push($result ,(object)$el);
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