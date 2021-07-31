<?php

require "./vendor/autoload.php";

// scssc will be loaded automatically via Composer
$scss_compiler = new scssc();
// set the path where your _mixins are
$scss_folder=getcwd()."/assets/sass/";
$css_folder=getcwd()."/assets/css/";
$file_name="app";
$scss_compiler->setImportPaths($scss_folder);
// set css formatting (normal, nested or minimized), @see http://leafo.net/scssphp/docs/#output_formatting
$scss_compiler->setFormatter("scss_formatter");

$string_sass = file_get_contents($scss_folder . $file_name . ".scss");
// compile this SASS code to CSS
$string_css = $scss_compiler->compile($string_sass);
// write CSS into file with the same filename, but .css extension
file_put_contents($css_folder . $file_name . ".css", $string_css);

echo "done!!";