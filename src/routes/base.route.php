<?php

$app->get('/search/:query', function($query) use ($app, $settings) {
	$lib_search = new Libs_Search($query);
	$keyword = $lib_search->GetSearchQueryString();
	$services = Services::whereRaw("MATCH(name) AGAINST(? IN BOOLEAN MODE)", array($keyword))->get();

	$app->render(200,array(
        'services' => $services,
    ));
});

$app->get('/search/currency/:currency', function($currency) use ($app, $settings) {
	$services = Services::where('currencies', 'LIKE', "%$currency%")->get();

	$app->render(200,array(
        'services' => $services,
    ));
});

$app->get('/count', function() use ($app, $settings) {
	$app->render(200,array(
        'count' => Services::count(),
    ));
});


$app->get('/(:page(/:column(/:order)))', function($page = 1, $column = 'rating' , $order = 'desc') use ($app, $settings) {
    $count = Services::count();
    $pages = ceil($count / RECORDSPERPAGE);
    if ($page > $pages) $app->pass();

    $services = Services::orderBy($column, $order )->skip(RECORDSPERPAGE * ($page - 1))->take(RECORDSPERPAGE)->get();

    $app->render(200,array(
        'services' => $services,
    ));
})->conditions(array('page' => '\d+'));

