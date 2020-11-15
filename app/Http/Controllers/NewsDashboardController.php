<?php

namespace App\Http\Controllers;

use App\Country;
use App\News;
use App\NewsCategory;
use App\NewsSource;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;

class NewsDashboardController extends Controller
{
	// Get all News Source data
	public function index (Request $req) {
		$data = News::select('id', 'title', 'urltoimage', 'published_at')->get();
		return response()->json([
			'message'	=> 'Fetch news data success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}
	
	public function show ($id, Request $req) {
		$data = News::with('country')->with('newscategory')->with('newssource')->find($id);
		if ($data) {
			return response()->json([
				'message'	=> 'Get news data success.',
				'data'		=> $data,
				'status'	=> 'success'
			]);
		} else {
			return response()->json([
				'message'	=> 'News with id ' . $id . ' is not found.',
				'data'		=> null,
				'status'	=> 'error'
			], 404);
		}
	}
}
