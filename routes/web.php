<?php

$router->get('/', function () {
    return view('main');
});

$router->get('/{any}', function () {
    return view('main');
});