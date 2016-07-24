<?php

$a1 = array(1=>a,2=>b);
$a2 = array(1=>a,2=>b,3=>c);

$ma = array_merge($a1,$a2);
var_dump($ma);

$ma = array_merge_recursive($a1,$a2);
var_dump($ma);
