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
class Gm_ceilingModelAnalitic_dealers extends JModelList
{
    public function getData()
    {
        try {
            $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $dealers_and_projects = $this->getProjectsOfDealers();
            if(!empty($dealers_and_projects)){
                foreach ($dealers_and_projects as $value) {
                   $ids = explode(';',$value->projects);
                   $new_value = array();
                   foreach ($ids as $id) {
                        $calcs = $calculation_model->getDataForAnalytic($id);
                        $sum = 0;
                        foreach ($calcs as $calc) {
                            $sum += $this->calculateSelfPrice($calc,0.05);
                        }
                        $new_value[$id] = $sum; 
                   }
                   $value->projects = $new_value;
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

    function calculateSelfPrice($calculation,$reject_rate){
        try {
            $mount_model = Gm_ceilingHelpersGm_ceiling::getModel('mount');
            $component_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $components = Gm_ceilingHelpersGm_ceiling::calculate_components($calculation->id,null,0);
            $results = $mount_model->getDataAll(1);
            return $calculation->canvas_area*($calculation->price +$calculation->price*reject_rate)+($calculation->canvas_area - $calculation->offcut_square)*11 + $calculation->n5_shrink*4 + ($calculation->n9 - 6)*$results->mp20 + components_self;
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
   }
}
