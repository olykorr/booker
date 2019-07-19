<?php
include 'config.php';

spl_autoload_register(function($class) {
  $path = "../../app/lib/{$class}.php";
  if (file_exists($path))
  {
      require_once ($path);
  }
  elseif (file_exists($path = "../../app/models/{$class}.php"))
  {
      require_once ($path);
  }
  elseif (file_exists($path = "../../app/controllers/{$class}.php"))
  {
      require_once ($path);
  }
  else
  {
      die ("File {$class} not found!");
  }
});

