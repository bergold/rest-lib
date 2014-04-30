<?php

require 'rest.php';

// tests:

echo '<pre>';
echo '  REQUEST_URI: ' . $_SERVER['REQUEST_URI'];
echo '<br />';
echo '  SCRIPT_NAME: ' . $_SERVER['SCRIPT_NAME'];
echo '<br />';
echo '<br />';
echo 'Environment:';
echo '<br />';
echo '  BASE_URI: ' . $env->getBaseURL();
echo '<br />';
echo '  PATH: ' . $env->getPath();
echo '<br />';
echo '  ARGS: '; var_dump($env->getArgs());
echo '</pre>';

echo '<hr />';
echo '<pre>';

// example:

when("/", "base");

when("/user/:userName", "user");

when("/info/:cmd*/g/:add?", "user");

otherwise("error");

echo '</pre>';
