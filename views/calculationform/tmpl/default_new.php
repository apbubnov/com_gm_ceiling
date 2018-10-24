<?php
    defined('_JEXEC') or die;
    JHtml::_('behavior.keepalive');
    //JHtml::_('behavior.tooltip');
    JHtml::_('behavior.formvalidation');
    $lang = JFactory::getLanguage();
    $lang->load('com_gm_ceiling', JPATH_SITE);
    $doc = JFactory::getDocument();
    $doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js'); 

    $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel("canvases");
    $canvases_data = $canvases_model->getFilteredItemsCanvas("count>0");
    print_r($canvases_data);
?>