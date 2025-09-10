<?php

namespace App\Http\Controllers;

use App\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
	// Get all Country data
	public function index (Request $req) {
		$data = Country::select('id', 'code', 'name')->get();
		return response()->json([
			'message'	=> 'Fetch countries success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}
	
	public function show ($id, Request $req) {
		$data = Country::find($id);
		if ($data) {
			return response()->json([
				'message'	=> 'Get country data success.',
				'data'		=> $data,
				'status'	=> 'success'
			]);
		} else {
			return response()->json([
				'message'	=> 'Country with id ' . $id . ' is not found.',
				'data'		=> null,
				'status'	=> 'error'
			], 404);
		}
	}
}
