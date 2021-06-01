<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 13.03.2019
 * Time: 14:21
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
class Gm_ceilingModelCalcs_components extends JModelList
{
    function save($calc_id,$components){
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`#__gm_ceiling_calcs_components`')
                ->where("calc_id = $calc_id");
            $db->setQuery($query);
            $db->execute();
            foreach ($components as $component) {
                $query->clear();
                $query
                    ->insert('`#__gm_ceiling_calcs_components`')
                    ->columns('`calc_id`,`component_id`,`count`,`sum`')
                    ->values("$calc_id," . $component['id'] . "," . $component['quantity'] . "," . $component['self_dealer_total']);
                $db->setQuery($query);
                $db->execute();
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getAllComponentsOnBuildersObject($builderId){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('g.name,cgm.goods_id,SUM(cgm.count) AS `count`,`u`.`unit`')
                ->from('`rgzbn_gm_ceiling_calculations` AS c')
                ->leftJoin('`rgzbn_gm_ceiling_calcs_goods_map` AS cgm ON c.id = cgm.calc_id')
                ->leftJoin('`rgzbn_gm_ceiling_projects` AS p ON c.project_id = p.id')
                ->leftJoin('`rgzbn_gm_ceiling_clients` AS cl ON p.client_id = cl.id')
                ->innerJoin('`rgzbn_gm_stock_goods` AS g ON cgm.goods_id = g.id')
                ->innerJoin('`rgzbn_gm_stock_units` AS u ON u.id = g.unit_id')
                ->where("cl.dealer_id = $builderId")
                ->group('cgm.goods_id');
            $db->setQuery($query);
            $goods = $db->loadObjectList();
            if(empty($goods)){
                $query = "SELECT CONCAT(co.title,' ',comp.title ) as name,cc.component_id as goods_id,sum(cc.count) as `count`,'' as unit
                            FROM `rgzbn_gm_ceiling_calcs_components` AS cc
                            LEFT JOIN `rgzbn_gm_ceiling_components_option` AS comp ON comp.id = cc.component_id
                            LEFT JOIN `rgzbn_gm_ceiling_components` AS co ON co.id = comp.component_id
                            WHERE cc.calc_id IN(
                                SELECT c.id 
                                FROM `rgzbn_gm_ceiling_calculations` AS c
                                LEFT JOIN `rgzbn_gm_ceiling_projects` AS p ON p.id = c.project_id
                                LEFT JOIN `rgzbn_gm_ceiling_clients` AS cl ON cl.id = p.client_id
                                WHERE cl.dealer_id = $builderId
                            )
                            GROUP BY cc.component_id";
                $db->setQuery($query);
                $goods = $db->loadObjectList();
            }
            return $goods;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}