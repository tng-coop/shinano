<?php

namespace NAMES;
include_once(__DIR__ . "/./ingredients/utilities.php");


$tpl = new TemplateAndConfigs();

$tpl->page_title = "index php here";
$tpl->content_actual = "Actual contents here";

              
$tpl->eval_template("template.html");


?>
