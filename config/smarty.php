<?php

declare(strict_types=1);

$smarty = new Smarty();

$smarty->setTemplateDir(__DIR__ . '/../templates');
$smarty->setCompileDir(__DIR__ . '/../storage/cache/smarty/compile');
$smarty->setCacheDir(__DIR__ . '/../storage/cache/smarty/cache');

// В dev-режиме всегда перекомпилируем шаблоны
if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
    $smarty->setCompileCheck(Smarty::COMPILECHECK_ON);
    $smarty->caching = Smarty::CACHING_OFF;
} else {
    $smarty->setCompileCheck(Smarty::COMPILECHECK_OFF);
    $smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
    $smarty->cache_lifetime = 3600; // 1 час
}

return $smarty;