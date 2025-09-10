<?php

namespace App\Http\Controllers;

use App\Country;
use App\News;
use App\NewsCategory;
use App\NewsSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsFilterController extends Controller
{
	public function getByPublishedDate (Request $req) {
		$validator = Validator::make($req->all(), [
			'from'		=> 'required|date',
			'to'		=> 'required|date|after:from'
		]);
			
		if ($validator->fails()) {
			return response()->json([
				'message'	=> 'Validation error.',
				'errors'	=> $validator->errors(),
				'status'    => 'error'
			], 500);
		}		
		$from = $req->input('from');
		$to = $req->input('to');
		$data = News::select('id', 'title', 'urltoimage', 'published_at')->whereBetween('published_at', [$from, $to])->orderBy('published_at', 'desc')->get();
		return response()->json([
			'message'	=> 'Fetch news data success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);
	}

	public function newsFilter (Request $req) {
		$validator = Validator::make($req->all(), [
			'from'		=> 'nullable|date',
			'to'		=> 'required_with:from|date|after:from'
		]);
		if ($validator->fails()) {
			return response()->json([
				'message'	=> 'Validation error.',
				'errors'	=> $validator->errors(),
				'status'    => 'error'
			], 500);
		}
		$filter_fields = [];

		if ($req->input('category_id')) {
			$filter_fields['category_id'] = $req->input('category_id');
		}		
		if ($req->input('country_id')) {
			$filter_fields['country_id'] = $req->input('country_id');
		}
		if ($req->input('source_id')) {
			$filter_fields['source_id'] = $req->input('source_id');
		}				
		if ($req->input('keyword')) {
			$keyword = '%'.strtolower($req->input('keyword')).'%';
			$nestedfilter = array('title', 'ilike', $keyword);
			array_push($filter_fields, $nestedfilter);
		}
		if ($req->input('from') && $req->input('to')) {
			$from = $req->input('from');
			$to = $req->input('to');			
			$data = News::where($filter_fields)->whereBetween('published_at', [$from, $to])->orderBy('published_at', 'desc')->select('id', 'title', 'author', 'description', 'urltoimage', 'content', 'published_at')->get();
		} else {
			$data = News::where($filter_fields)->orderBy('published_at', 'desc')->select('id', 'title', 'author', 'description', 'urltoimage', 'content', 'published_at')->get();
		}
		return response()->json([
			'message'	=> 'Fetch news data success.',
			'data'		=> $data,
			'status'	=> 'success'
		]);				
	}
}
