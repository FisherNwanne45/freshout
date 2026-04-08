<?php
/**
 * Theme stub — delegates to the root session.php.
 * Keeps existing include('session.php') calls in theme pages working.
 */
require_once dirname(__DIR__, 2) . '/session.php';
