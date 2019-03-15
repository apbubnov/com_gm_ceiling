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
    public function getData($date_from,$date_to)
    {
        try {
            $data = $this->getAllDataByperiod($date_from,$date_to);
            $result = []; $proizvs = [];
            $mount_model = Gm_ceilingHelpersGm_ceiling::getModel('mount');
            $component_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $harpoon = $component_model->getFilteredItems("co.component_id = 195");
            $harp_dop = $component_model->getComponentsParameters(null,$harpoon[0]->component_id,$harpoon[0]->id);
            $harp_price  = $component_model->getComponentsSelfPrice($harpoon[0]->component_id,$harpoon[0]->id,$harp_dop->good_id,$harp_dop->barcode,$harp_dop->article);

            $calculation->n9 = ($calculation->n9 > 4) ? $calculation->n9-4 : 0;
            $results = $mount_model->getDataAll(1);
            foreach ($data as $item) {
                $result[$item->user_id]['id'] = $item->user_id;
                $result[$item->user_id]['projects'][]= $item->project_id;
                $result[$item->user_id]['calcs_count']+=1;
                $result[$item->user_id]['name'] = $item->user_name;
                $result[$item->user_id]['quadr'] += $item->n4;
                $result[$item->user_id][$item->name] +=$item->n4;
                $item->n9 = ($item->n9 > 4) ? $item->n9-4 : 0;
                $result[$item->user_id]['sum']+= $item->canvases_sum+$item->components_sum;
                $result[$item->user_id]['comp_sum']+=$item->components_sum;
                $result[$item->user_id]['total_self_sum']+=$item->canvas_area*($item->self_price + $item->self_price*0.05)+$item->n4*11 + $item->n31*$results->mp22 + $item->n5_shrink*$harp_price->price +  $item->n9*5+$item->self_sum;
                $result[$item->user_id]['comp_self_sum']+= $item->self_sum;
                $result[$item->user_id]['rest']+=$item->rest;
                if(!array_key_exists($item->manufacturer_id, $proizvs)&&!empty($item->manufacturer_id)){
                    $proizvs[$item->manufacturer_id] = $item->name;
                }
            }
            foreach ($result as $key=>$res_item){
                $result[$key]['projects'] = array_unique($result[$key]['projects']);
                $result[$key]['project_count'] = count($result[$key]['projects']);
                $result[$key]['diff'] = $result[$key]['sum'] - $result[$key]['total_self_sum'];
                $result[$key]['diff_comp'] = $result[$key]['comp_sum'] -  $result[$key]['comp_self_sum'];
            }

            $headers = array("name"=>"Дилер","project_count"=>"Кол-во проектов","calcs_count"=>"Кол-во потолков","quadr"=>"Квадратура");
            foreach ($proizvs as $key => $value) {
                $headers[$value]=$value;
            }
            $headers['sum'] = "Стоимость";
            $headers['total_self_sum'] = "Себестоимость";
            $headers['diff'] = "Разница";
            $headers['comp_sum'] = "Стоимость компл-х";
            $headers['comp_self_sum'] = "Себестоимость компл-х";
            $headers['diff_comp'] = "Разница по компл.";
            $headers['rest'] = 'Сост.счета';
            array_unshift($result , $headers);
            return $result;

        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getAllDataByperiod($date1,$date2){
        try{
            $db = $this->getDbo();
            $query="SELECT c.id AS calc_id,p.id AS project_id,u.id AS user_id,u.name AS user_name,p.date_of_change,MAX(canv.self_price) AS self_price,canv.manuf_id AS manufacturer_id,canv.name,
                    `c`.`n3` AS `n3`,`c`.`n4` AS `n4`,`c`.`n5` AS `n5`,`c`.`n31` AS `n31`,`c`.`n5_shrink` AS `n5_shrink`,`c`.`offcut_square` AS `offcut_square`,
                    `c`.`n9` AS `n9`,`cs`.`sum` AS `components_sum`, `c`.`canvases_sum` AS `canvases_sum`,`cut`.`canvas_area` AS `canvas_area`,cs.self_sum
                    FROM (
                    SELECT p1.id,ph.date_of_change,p1.client_id
                    FROM `rgzbn_gm_ceiling_projects` AS p1
                    INNER JOIN `rgzbn_gm_ceiling_projects_history` AS ph ON p1.id = ph.project_id
                    WHERE ph.new_status = 5 and ph.date_of_change between '$date1' and '$date2'
                    GROUP BY p1.id
                    ) AS p
                    INNER JOIN `rgzbn_gm_ceiling_calculations` AS c ON c.project_id = p.id
                    LEFT JOIN `rgzbn_gm_ceiling_clients` AS cl ON cl.id = p.client_id
                    LEFT JOIN `rgzbn_users` AS u ON u.id = cl.dealer_id
                    LEFT JOIN `rgzbn_canvases` AS canv ON canv.id = c.n3
                    LEFT JOIN `rgzbn_gm_ceiling_cuttings` AS  `cut` ON `c`.`id` = `cut`.`id`
                    LEFT JOIN `rgzbn_comp_self` AS cs ON cs.calc_id = c.id
                    
                    GROUP BY c.id";
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getProjectsOfDealers($date1,$date2){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select("u.id,u.name,GROUP_CONCAT(DISTINCT p.id SEPARATOR ';') AS projects")
                ->select("IFNULL(ROUND(SUM( DISTINCT `rmp`.`sum`),2),0) as rest")
                ->from("`#__gm_ceiling_projects` AS p")
                ->leftJoin("`#__gm_ceiling_clients` AS c ON p.client_id = c.id")
                ->innerJoin("`#__users` AS u ON c.dealer_id = u.id")
                ->innerJoin("`#__gm_ceiling_projects_history` as ph on p.id = ph.project_id")
                ->leftJoin("`#__gm_ceiling_recoil_map_project` AS `rmp` ON `rmp`.`recoil_id` = `u`.`id`")
                ->where("ph.new_status in(5) and ph.date_of_change BETWEEN '$date1' AND '$date2'")
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
            $harpoon = $component_model->getFilteredItems("co.component_id = 195");
            $harp_dop = $component_model->getComponentsParameters(null,$harpoon[0]->component_id,$harpoon[0]->id);

            $harp_price  = $component_model->getComponentsSelfPrice($harpoon[0]->component_id,$harpoon[0]->id,$harp_dop->good_id,$harp_dop->barcode,$harp_dop->article);
            
            $price_comp = $this->calculateCompSelfPrice($calculation);
            $calculation->n9 = ($calculation->n9 > 4) ? $calculation->n9-4 : 0; 
            $results = $mount_model->getDataAll(1);

           
            return array("sum" => ($calculation->canvas_area*($calculation->self_price + $calculation->self_price*$reject_rate)+/*($calculation->canvas_area - $calculation->offcut_square)*/$calculation->n4*11 + $calc->n31*$results->mp22 + $calculation->n5_shrink*$harp_price->price +  $calculation->n9*5+ $price_comp->self),
                "self_price"=>$price_comp->self);
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
   }

    function calculateCompSelfPrice($calculation = null,$components = null){
        try {
            $price_comp = 0;$project_sum = 0;
            $component_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            if(!empty($calculation)){
                $components = Gm_ceilingHelpersGm_ceiling::calculate_components($calculation->id,null,0);
            }
            if(!empty($components)){
                foreach ($components as  $value) {
                    if(gettype($value) == 'array'){
                       if(!empty($value['id']) && !empty($value['component_id']))
                            $dop_params = $component_model->getComponentsParameters($project_id,$value['component_id'],$value['id']);
                        
                        if(!empty($dop_params)){
                            $price_comp += $value['quantity']*$component_model->getComponentsSelfPrice($dop_params->component_id,$dop_params->option_id,$dop_params->good_id,$dop_params->barcode,$dop_params->article)->price;
                        }
                    }
                    if(gettype($value) == 'object'){
                         $price_comp += $value->quantity*$component_model->getComponentsSelfPrice($value->component_id,$value->option_id,$value->good_id,$value->barcode,$value->article)->price;
                         $project_sum += $value->quantity*$value->price;
                    }
                }
            }
            return (object)array("self" => $price_comp,"sum" => $project_sum);
        }
        catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
   }

    function calculateQuadratureByPeriod($date1,$date2,$select_type){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            /*
                SELECT SUM(c.n4),ph.date_of_change
                FROM `rgzbn_gm_ceiling_projects_history` AS ph
                LEFT JOIN `rgzbn_gm_ceiling_calculations` AS c ON c.project_id = ph.project_id
                WHERE ph.new_status IN (5,6,7,8,10,11,12,13,14,16,17,19,24,25,26,27,28,29) AND ph.date_of_change BETWEEN '2018-10-01' AND '2018-10-11'
                GROUP BY ph.date_of_change
            */
            $format = "%d.%m.%Y";
            if($select_type == 0){
                $format = "%M %Y";
            }
            $query
                ->select("DATE_FORMAT(ph.date_of_change,'$format') AS `date`,SUM(c.n4)")
                ->from("`#__gm_ceiling_projects_history` AS ph")
                ->leftJoin('`#__gm_ceiling_calculations` AS c ON c.project_id = ph.project_id')
                ->where("ph.new_status IN (5) AND ph.date_of_change BETWEEN '$date1' AND '$date2'")
                ->group("ph.date_of_change");
            $db->setQuery($query);
            $items = $db->loadRowList();
            return $items;
        }
        catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
   }
}
