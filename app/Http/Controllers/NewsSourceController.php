<?php

namespace App\Http\Controllers;

use App\NewsSource;
use Illuminate\Http\Request;

class NewsSourceController extends Controller
{
	// Get all News Source data
	public function index (Request $req) {
		$data = NewsSource::select('id', 'name')->get();
		return response()->json([
			'message'	=> 'Fetch news sources success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}
	
	public function show ($id, Request $req) {
		$data = NewsSource::find($id);
		if ($data) {
			return response()->json([
				'message'	=> 'Get news source data success.',
				'data'		=> $data,
				'status'	=> 'success'
			]);
		} else {
			return response()->json([
				'message'	=> 'News source with id ' . $id . ' is not found.',
				'data'		=> null,
				'status'	=> 'error'
			], 404);
		}
	}

	public function search (Request $req) {
		$data = NewsSource::whereRaw('lower(name) LIKE ?', '%'.strtolower($req->input('keyword')).'%')->select('id', 'name')->orderBy('id', 'asc')->get();
		if ($data) {
			return response()->json([
				'message'	=> 'Get news source data success.',
				'data'		=> $data,
				'status'	=> 'success'
			]);
		}
	}
}
