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
}