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
		$data = News::select('id', 'title', 'urltoimage', 'published_at')->orderBy('published_at', 'desc')->get();
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
		$data = News::select('id', 'title', 'urltoimage', 'published_at')->where('country_id', $id)->orderBy('published_at', 'desc')->get();
		return response()->json([
			'message'	=> 'Fetch news data success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}

	public function getByCategory ($id, Request $req) {
		$data = News::select('id', 'title', 'urltoimage', 'published_at')->where('category_id', $id)->orderBy('published_at', 'desc')->get();
		return response()->json([
			'message'	=> 'Fetch news data success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}

	public function getBySource ($id, Request $req) {
		$data = News::select('id', 'title', 'urltoimage', 'published_at')->where('source_id', $id)->orderBy('published_at', 'desc')->get();
		return response()->json([
			'message'	=> 'Fetch news data success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}

	public function search (Request $req) {
		$data = News::whereRaw('lower(author) LIKE ?', '%'.strtolower($req->input('keyword')).'%')->orwhereRaw('lower(title) LIKE ?', '%'.strtolower($req->input('keyword')).'%')->orwhereRaw('lower(description) LIKE ?', '%'.strtolower($req->input('keyword')).'%')->orwhereRaw('lower(content) LIKE ?', '%'.strtolower($req->input('keyword')).'%')->orderBy('published_at', 'desc')->select('id', 'title', 'author', 'description', 'urltoimage', 'content', 'published_at')->get();
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
		$data = News::whereRaw('lower(title) LIKE ?', '%'.strtolower($req->input('keyword')).'%')->orderBy('published_at', 'desc')->select('id', 'title', 'urltoimage', 'published_at')->get();
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
