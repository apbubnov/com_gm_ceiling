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
class Gm_ceilingModelAnalytic_Dealers extends JModelList
{
    public function getData()
    {
        try {
            $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $dealers_and_projects = $this->getProjectsOfDealers();
            if(!empty($dealers_and_projects)){
                foreach ($dealers_and_projects as $value) {
                   $ids = explode(';',$value->projects);
                   $project_count = count($ids);
                   $new_value = array();
                   $quadr = 0;$total_self_sum = 0;$calcs_count = 0;$total_canv_sum = 0;
                   $total_comp_sum = 0;$total_comp_self = 0;
                   foreach ($ids as $id) {
                        $calcs = $calculation_model->getDataForAnalytic($id);
                        $sum = 0;
                        foreach ($calcs as $calc) {
                            $data = $this->calculateSelfPrice($calc,0.05);
                            $sum += $data["sum"];
                            $quadr += $calc->n4;
                            $total_comp_self += $data["self_price"];
                        }
                        $new_value[$id] = $sum;
                        $total_self_sum += $sum;
                        $calcs_count += count($calcs); 
                        $total_canv_sum +=$calc->canvases_sum;
                        $total_comp_sum += $calc->components_sum;
                   }
                   $value->projects = $new_value;
                   $value->project_count = $project_count;
                   $value->calcs_count = $calcs_count;
                   $value->quadr = $quadr;
                   $value->sum = $total_canv_sum + $total_comp_sum;
                   $value->total_self_sum = $total_self_sum;
                   $value->comp_sum  = $total_comp_sum;
                   $value->comp_self_sum = $total_comp_self;
                }
            }
            return $dealers_and_projects;
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getProjectsOfDealers(){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select("u.id,u.name,GROUP_CONCAT(DISTINCT p.id SEPARATOR ';') AS projects")
                ->from("`#__gm_ceiling_projects` AS p")
                ->leftJoin("`#__gm_ceiling_clients` AS c ON p.client_id = c.id")
                ->innerJoin("`#__users` AS u ON c.dealer_id = u.id")
                ->where("u.dealer_type IN (0,1) and p.project_status in(6,7,8,10,11,12,13,14,16,17,19,24,25,26)")
                ->group("u.id");
            $db->setQuery($query);

            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
   }

    function calculateSelfPrice($calculation,$reject_rate,$project_id){
        try {
            $mount_model = Gm_ceilingHelpersGm_ceiling::getModel('mount');
            $component_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $components = Gm_ceilingHelpersGm_ceiling::calculate_components($calculation->id,null,0);
            $price_comp = 0;
            $harpoon = $component_model->getFilteredItems("co.component_id = 42");
            $harp_dop = $component_model->getComponentsParameters(null,$harpoon[0]->component_id,$harpoon[0]->id);
            $harp_price  = $component_model->getComponentsSelfPrice($harpoon[0]->component_id,$harpoon[0]->id,$harp_dop->good_id,$harp_dop->barcode,$harp_dop->article);
            if(!empty($components)){
                foreach ($components as  $value) {                    
                    $dop_params = $component_model->getComponentsParameters($project_id,$value['component_id'],$value['id']);
                    if(!empty($dop_params)){
                        $price_comp = $component_model->getComponentsSelfPrice($value['component_id'],$value['id'],$dop_params->good_id,$dop_params->barcode,$dop_params->article);
                    }
                    
                }
            }
           
            $results = $mount_model->getDataAll(1);
            return array("sum" => ($calculation->canvas_area*($calculation->self_price + $calculation->self_price*reject_rate)+($calculation->canvas_area - $calculation->offcut_square)*11 + $calc->n31*$results->mp22 + $calculation->n5_shrink*$harp_price->price + ($calculation->n9 - 6)*$results->mp20 + $price_comp),
                "self_price"=>$price_comp);
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
   }
}
