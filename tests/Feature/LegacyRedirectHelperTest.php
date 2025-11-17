<?php

use function PHPUnit\Framework\assertSame;

require_once __DIR__ . '/../../includes/legacy-redirect.php';

it('builds location with baseHref', function(){
    $GLOBALS['baseHref'] = '/';
    $loc = legacy_build_location('produto/1');
    assertSame('/produto/1', $loc);

    $GLOBALS['baseHref'] = '/Batrip/';
    $loc = legacy_build_location('produto/1');
    assertSame('/Batrip/produto/1', $loc);
});

it('decides 301 for GET and 307 for POST by default', function(){
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $res = legacy_redirect('produto/1', null, false);
    assertSame(301, $res['status']);

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $res = legacy_redirect('produto/1', null, false);
    assertSame(307, $res['status']);
});
