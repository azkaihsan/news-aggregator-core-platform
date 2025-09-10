<?php

namespace App\Http\Controllers;

use App\Country;
use App\News;
use App\NewsCategory;
use App\NewsSource;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewsTelegramController extends Controller
{
    public function store(Request $request)
    {
        // Read the JSON data
        $data = $request->json()->all();

        // Validate incoming request
        $this->validate($request, [
            'content' => 'required|string',
            'url' => 'required|url',
            'urltoimage' => 'nullable|url',
            'author' => 'nullable|string',
            'published_at' => 'required|date',
            'country_code' => 'required|string',
            'country_name' => 'required|string',
            'source_name' => 'required|string',
        ]);

        // Get the JSON data
        $data = $request->json()->all();

        // Get or create the Telegram category
        $category = NewsCategory::firstOrCreate(
            ['name' => 'Telegram'],
            ['slug' => 'telegram']
        );

        // Find or create country
        $country = Country::firstOrCreate(
            ['code' => strtoupper($data['country_code'])],
            ['name' => $data['country_name']] 
        );

        // Find or create source
        $source = NewsSource::firstOrCreate(
            ['name' => $data['source_name']],
            ['slug' => Str::slug($data['source_name'])]
        );

        // Create news entry
        $news = News::create([
            'category_id' => $category->id,
            'country_id' => $country->id,
            'source_id' => $source->id,
            'title' => $data['title'],
            'author' => $data['author'] ?? null,
            'description' => Str::limit(strip_tags($data['content']), 200),
            'url' => $data['url'],
            'urltoimage' => $data['urltoimage'] ?? null,
            'content' => $data['content'],
            'published_at' => $data['published_at'],
            'load_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'News created successfully',
            'data' => $news
        ], 201);
    }
}