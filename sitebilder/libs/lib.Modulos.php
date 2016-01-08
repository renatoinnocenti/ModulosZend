<?php
function module_exists($module) {
  $list = module_list();
  return array_key_exists($module, $list);
}
function module_list($refresh = FALSE, $sort = FALSE, $fixed_list = NULL) {
  static $list, $sorted_list;

  if ($refresh || $fixed_list) {
    $list = array();
    $sorted_list = NULL;
    if ($fixed_list) {
      foreach ($fixed_list as $name => $module) {
        drupal_get_filename('module', $name, $module['filename']);
        $list[$name] = $name;
      }
    }
    else {
        $result = db_query("SELECT name, filename, throttle FROM {system} WHERE type = 'module' AND status = 1 ORDER BY weight ASC, filename ASC");
      while ($module = db_fetch_object($result)) {
        if (file_exists($module->filename)) {
          // Determine the current throttle status and see if the module should be
          // loaded based on server load. We have to directly access the throttle
          // variables, since throttle.module may not be loaded yet.
          $throttle = ($module->throttle && variable_get('throttle_level', 0) > 0);
          if (!$throttle) {
            drupal_get_filename('module', $module->name, $module->filename);
            $list[$module->name] = $module->name;
          }
        }
      }
    }
  }
  if ($sort) {
    if (!isset($sorted_list)) {
      $sorted_list = $list;
      ksort($sorted_list);
    }
    return $sorted_list;
  }
  return $list;
}
?>