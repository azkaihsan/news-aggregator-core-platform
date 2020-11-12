<?php

namespace App\Http\Controllers;

use App\News;
use App\NewsCategory;
use App\NewsSource;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;

class FetchDataController extends Controller
{
    public function fetchTopHeadlineFromNewsAPI(Request $req)
    {
		$key = $req->header('X-Api-Key');
		$headers = [
		    'X-Api-Key' => $key
		];    	
		$client = new GuzzleClient([
		    'headers' => $headers
		]);		
		$request = $client->get('https://newsapi.org/v2/top-headlines?country=us&category=general');
		$response = $request->getBody()->getContents();
		$data = json_decode($response, true);
        return response()->json([
            'message'   => 'Fetch data success',
            'data'      => $data,
            'status'    => 'success'
        ]);
    }

	public function fetchNewsSourceFromNewsAPI(Request $req)
    {
		$key = $req->header('X-Api-Key');
		$headers = [
		    'X-Api-Key' => $key
		];    	
		$client = new GuzzleClient([
		    'headers' => $headers
		]);		
		$request = $client->get('https://newsapi.org/v2/sources');
		$response = $request->getBody()->getContents();
		$data = json_decode($response, true);
		foreach ($data['sources'] as $key) {
			$newssource = new NewsSource;
			$newssource->name 			= $key['name'];
			$newssource->slug 			= $key['id']; 
			$newssource->description 	= $key['description'];
			$newssource->url 			= $key['url'];
			$newssource->category 		= $key['category'];
			$newssource->country 		= $key['country'];
			$newssource->language 		= $key['language'];
			$newssource->save();
		}
        return response()->json([
            'message'   => 'Fetch data success',
            'data'      => $data,
            'status'    => 'success'
        ]);
    }
    public function insertNewsCategory(Request $req)
    {
    	$categorylist = array('business', 'entertainment', 'general', 'health', 'sports', 'science', 'technology');
    	foreach ($categorylist as $key) {
    		$newscategory = new NewsCategory;
    		$newscategory->name = $key;
    		$newscategory->save();
    	}
        return response()->json([
            'message'   => 'Insert new category success',
            'data'      => $categorylist,
            'status'    => 'success'
        ]);    	
    }    
}
