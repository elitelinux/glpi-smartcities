<?php

use Zend\Loader\SplAutoloader;

class PluginGenericobjectAutoloader implements SplAutoloader
{
   protected $paths = array();

   public function __construct($options = null)
   {
      if (null !== $options) {
         $this->setOptions($options);
      }
   }

   public function setOptions($options)
   {
      if (!is_array($options) && !($options instanceof \Traversable)) {
         throw new \InvalidArgumentException();
      }

      foreach ($options as $path) {
         if (!in_array($path, $this->paths)) {
            $this->paths[] = $path;
         }
      }
      return $this;
   }

   public function processClassname($classname)
   {
      preg_match("/Plugin([A-Z][a-z0-9]+)([A-Z]\w+)/",$classname,$matches);

      if (count($matches) < 3) {
         return false;
      } else {
         return $matches;
      }

   }

   public function autoload($classname)
   {
      //Toolbox::logDebug($classname);

      $matches = $this->processClassname($classname);
      //Toolbox::logDebug($matches);

      if($matches !== false) {
         $plugin_name = strtolower($matches[1]);
         $class_name = strtolower($matches[2]);

         //Toolbox::logDebug($plugin_name);
         if ( $plugin_name !== "genericobject" ) {
            return false;
         }

         $filename = implode(".", array(
            $class_name,
            "class",
            "php"
         ));

         //Toolbox::logDebug($filename);

         foreach ($this->paths as $path) {
            $test = $path . DIRECTORY_SEPARATOR . $filename;
            //Toolbox::logDebug($test);
            if (file_exists($test)) {
               return include($test);
            }
         }
      }
      return false;
   }

   public function register()
   {
      spl_autoload_register(array($this, 'autoload'));
   }
}

