<?php
use Tracy\Debugger;

function db($var, $title = NULL) {
	Debugger::barDump($var, $title);
}