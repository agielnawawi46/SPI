<?php
Auth::loginUsingId(1);
$request = Illuminate\Http\Request::create('/pkpt/1/kertas-kerja', 'GET');
$response = app()->handle($request);
file_put_contents('test_output.html', $response->getContent());
echo "Saved";
