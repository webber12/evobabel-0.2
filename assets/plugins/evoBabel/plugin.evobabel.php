<?php
if (!defined('MODX_BASE_PATH')) {
    die ('What are you doing? Get out of here!');
}
if (empty($modx->event->params['rel_tv_id']) || empty($modx->event->params['lang_template_id'])) return;

$params = $modx->event->params;
include_once __DIR__ . '../../../snippets/evoBabel/vendor/autoload.php';
$controller = new EvoBabel\Controllers\EvoBabelController($modx, 0, $params);

if (is_callable([$controller, 'listen' . $modx->event->name])) {
    $res = call_user_func([$controller, 'listen' . $modx->event->name]);
}

