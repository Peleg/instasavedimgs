<?php

function exception_error_handler($errno, $errstr, $errfile, $errline) {
  die('FUCK');
  throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}

set_error_handler("exception_error_handler");