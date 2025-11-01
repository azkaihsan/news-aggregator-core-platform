<?php

namespace App\Http\Controllers;

use App\Country;
use App\News;
use App\NewsCategory;
use App\NewsSource;
use Illuminate\Http\Request;

class NewsDashboardController extends Controller
{
	// Get all News Source data
	public function index (Request $req) {
	    if($req->input('take')) {
	        $data = News::with('newssource')->orderBy('published_at', 'desc')->take($req->take)->get();
	    } else {
            $data = News::with('newssource')->orderBy('published_at', 'desc')->take(100)->get();	        
	    }
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

	public function getByCountry ($id, Request $req) {
	    if($req->input('take')) {
	        $data = News::with('newssource')->select('id', 'source_id', 'title', 'description', 'url', 'urltoimage', 'published_at', 'author')->where('country_id', $id)->orderBy('published_at', 'desc')->take($req->take)->get();
	    } else {
            $data = News::with('newssource')->select('id', 'source_id', 'title', 'description', 'url', 'urltoimage', 'published_at', 'author')->where('country_id', $id)->orderBy('published_at', 'desc')->get();	        
	    }
		
		return response()->json([
			'message'	=> 'Fetch news data success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}

	public function getByCategory ($id, Request $req) {
	    if($req->input('take')) {
	        $data = News::with('newssource')->select('id', 'source_id', 'title', 'description', 'url', 'urltoimage', 'published_at', 'author')->where('category_id', $id)->orderBy('published_at', 'desc')->take($req->take)->get();
	    } else {
            $data = News::with('newssource')->select('id', 'source_id', 'title', 'description', 'url', 'urltoimage', 'published_at', 'author')->where('category_id', $id)->orderBy('published_at', 'desc')->get();        
	    }
		
		return response()->json([
			'message'	=> 'Fetch news data success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}

	public function getBySource ($id, Request $req) {
	    if($req->input('take')) {
	        $data = News::with('newssource')->select('id', 'source_id', 'title', 'description', 'url', 'urltoimage', 'published_at', 'author')->where('source_id', $id)->orderBy('published_at', 'desc')->take($req->take)->get();
	    } else {
            $data = News::with('newssource')->select('id', 'source_id', 'title', 'description', 'url', 'urltoimage', 'published_at', 'author')->where('source_id', $id)->orderBy('published_at', 'desc')->get();      
	    }
		return response()->json([
			'message'	=> 'Fetch news data success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}

	public function search (Request $req) {
        $keyword = $req->input('keyword');
        $take = $req->input('take', 1000);
    
        $data = News::with('newssource')
            ->select('id', 'source_id', 'title', 'description', 'url', 'urltoimage', 'published_at', 'author')
            ->whereRaw("searchable @@ plainto_tsquery('english', ?)", [$keyword])
            ->orderBy('published_at', 'desc')
            ->paginate(30);
		if ($data) {
			return response()->json([
				'message'	=> 'Get news data success.',
				'data'		=> $data,
				'status'	=> 'success'
			]);
		} else {
			return response()->json([
				'message'	=> 'News with keyword ' . $keyword . ' is not found.',
				'data'		=> null,
				'status'	=> 'error'
			], 404);
		}		
	}
	public function searchTitleOnly (Request $req) {
        $keyword = $req->input('keyword');
        $take = $req->input('take', 1000);
    
        $data = News::with('newssource')
            ->select('id', 'source_id', 'title', 'description', 'url', 'urltoimage', 'published_at', 'author')
            ->whereRaw("searchable @@ plainto_tsquery('english', ?)", [$keyword])
            ->orderBy('published_at', 'desc')
            ->paginate(30);
		
		if ($data) {
			return response()->json([
				'message'	=> 'Get news data success.',
				'data'		=> $data,
				'status'	=> 'success'
			]);
		} else {
			return response()->json([
				'message'	=> 'News with keyword ' . $keyword . ' is not found.',
				'data'		=> null,
				'status'	=> 'error'
			], 404);
		}		
	}					
}
