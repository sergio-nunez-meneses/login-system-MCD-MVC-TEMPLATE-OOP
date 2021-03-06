<?php
session_start();
spl_autoload_register('auto_class_loader');

function auto_class_loader($class_name)
{
  $path = $parent_folder = '';

  if ($class_name === 'Database') {
    $parent_folder = 'database';
  } elseif (substr($class_name, -5) === 'Model') {
    $parent_folder = 'models';
  } elseif (substr($class_name, -4) === 'View') {
    $parent_folder = 'views';
  } elseif (substr($class_name, -10) === 'Controller') {
    $parent_folder = 'controllers';
  }

  $path = getcwd() . "/classes/$parent_folder/$class_name.php";

  require_once($path);
}
