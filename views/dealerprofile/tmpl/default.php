<?php
/**
 * Created by PhpStorm.
 * User: popovaa
 * Date: 02.02.2018
 * Time: 12:20
 */

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user       = JFactory::getUser();
$userId     = $user->get('id');
?>