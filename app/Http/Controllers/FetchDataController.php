<?php

namespace App\Http\Controllers;

use App\Country;
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
		$category_id = $req->input('category_id');
		$country_id = $req->input('country_id');    	
		$client = new GuzzleClient([
		    'headers' => $headers
		]);
		$country = Country::find($country_id);
		if(!$country) {
			return response()->json([
			    'message'   => 'Listed country is not found',
			    'data'      => null,
			    'status'    => 'error'
			]);			
		}		
		$newscategory = NewsCategory::find($category_id);
		if(!$newscategory) {
			return response()->json([
			    'message'   => 'News Category is not found',
			    'data'      => null,
			    'status'    => 'error'
			]);			
		}	
		$request = $client->get('https://newsapi.org/v2/top-headlines?pageSize=100&country='.$country->code.'&category='.$newscategory->name);
		$response = $request->getBody()->getContents();
		$data = json_decode($response, true);
		foreach ($data['articles'] as $key) {
			$newssource = NewsSource::where('name', $key['source']['name'])->first();
			if (!$newssource) {
				$newssource = new NewsSource;
				$newssource->name 			= $key['source']['name'];
				$newssource->save();
			}
			$news = News::firstOrCreate(['category_id' => $category_id, 'country_id' => $country_id, 'source_id' => $newssource->id, 'title' => $key['title'], 'author' => $key['author'], 'description' => $key['description'], 'url' => $key['url'], 'urltoimage' => $key['urlToImage'], 'content' => $key['content'], 'published_at' => date('Y-m-d H:i:s', strtotime($key['publishedAt']))]);
		}
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
