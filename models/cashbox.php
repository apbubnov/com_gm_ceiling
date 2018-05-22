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
			$items1 = $db->loadObjectList();
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

			$items1 = array_merge($items1,$new_encash);

			for($i=0; $i<count($items1); $i++){
				for($j=$i+1; $j<count($items1); $j++){
					if(strtotime($items1[$i]->closed)>strtotime($items1[$j]->closed)){
						$temp = $items1[$j];
						$items1[$j] = $items1[$i];
						$items1[$i] = $temp;
					}
			   }         
			}
			
		 	$items = [];
			for($i=0; $i<count($items1); $i++){
				$new_el['closed']=$items1[$i]->closed;
				$new_el['id'] = $items1[$i]->id;
				$new_el['status'] = $items1[$i]->status;

				$new_el['name'] = $items1[$i]->name;
				$new_el['new_project_sum'] = $items1[$i]->new_project_sum;
				$new_el['new_mount_sum'] = $items1[$i]->new_mount_sum;
				if($items1[$i]->done!=1&&$items1[$i]->project_status != 12){
					$new_el['not_issued'] =  $items1[$i]->new_mount_sum - $items1[$i]->new_project_mounting;
				}
				else
				{
					$new_el['not_issued'] = 0;
				}
				$new_el['new_material_sum'] = $items1[$i]->new_material_sum;
				$new_el['residue'] = $items1[$i]->new_project_sum - $items1[$i]->new_mount_sum -$items1[$i]->new_material_sum;
				$new_el['cashbox'] += $new_el['residue'] - $encash;
				$encash = 0;
				$encash = $items1[$i]->sum;
				$new_el['sum'] = $items1[$i]->sum;
				array_push($items,(object)$new_el);
			}
			return $items; 

		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	
	
}
?>