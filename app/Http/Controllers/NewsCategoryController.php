<?php

namespace App\Http\Controllers;

use App\NewsCategory;
use Illuminate\Http\Request;

class NewsCategoryController extends Controller
{
	// Get all News Category data
	public function index (Request $req) {
		$data = NewsCategory::get();
		return response()->json([
			'message'	=> 'Fetch news categories success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}
	
	public function show ($id, Request $req) {
		$data = NewsCategory::find($id);
		if ($data) {
			return response()->json([
				'message'	=> 'Get news category data success.',
				'data'		=> $data,
				'status'	=> 'success'
			]);
		} else {
			return response()->json([
				'message'	=> 'News category with id ' . $id . ' is not found.',
				'data'		=> null,
				'status'	=> 'error'
			], 404);
		}
	}
}
