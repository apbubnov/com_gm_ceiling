<?php
 header('Content-type: image/png');
if ( isset( $_GET['id'] ) ) {
  // Здесь $id номер изображения
  $id = (int)$_GET['id'];
  if ( $id > 0 ) {
    throw new Exception($id);
    $db= JFactory::getDBO();
    $q = 'SELECT calc_image FROM `#__gm_ceiling_calculations` as c WHERE c.`id` ='.$calc_id;
    $db->setQuery($q);
    $image = $db->loadResult();
   
    echo  $image;
  }
}
?>