<?php

include ("../../inc/includes.php");

//change mimetype
header("Content-type: text/css");

//get custom css content
if (file_exists(CUSTOM_CSS_PATH)) {
   echo file_get_contents(CUSTOM_CSS_PATH);
}