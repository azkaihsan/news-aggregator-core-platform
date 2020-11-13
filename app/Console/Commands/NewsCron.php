<?php

namespace App\Console\Commands;

use App\Country;
use App\News;
use App\NewsCategory;
use App\NewsSource;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Console\Command;

class NewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Routine ETL News Data from NewsAPI';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $key = '135664285ec84c2cbb2d50f7d93a1942'; // Sesuaikan Kunci API yang dipakai
        $headers = [
            'X-Api-Key' => $key
        ];
        $category_id = 1; // Sesuaikan kategori berita
        $country_id = 19; // Sesuaikan negara asal berita terbit        
        $client = new GuzzleClient([
            'headers' => $headers
        ]);
        $country = Country::find($country_id);       
        $newscategory = NewsCategory::find($category_id); 
        $request = $client->get('https://newsapi.org/v2/top-headlines?pageSize=100&country='.$country->code.'&category='.$newscategory->name);
        $response = $request->getBody()->getContents();
        $data = json_decode($response, true);
        foreach ($data['articles'] as $key) {
            $newssource = NewsSource::where('name', $key['source']['name'])->first();
            if (!$newssource) {
                $newssource = new NewsSource;
                $newssource->name           = $key['source']['name'];
                $newssource->save();
            }
            $news = News::firstOrCreate(['category_id' => $category_id, 'country_id' => $country_id, 'source_id' => $newssource->id, 'title' => $key['title'], 'author' => $key['author'], 'description' => $key['description'], 'url' => $key['url'], 'urltoimage' => $key['urlToImage'], 'content' => $key['content'], 'published_at' => date('Y-m-d H:i:s', strtotime($key['publishedAt']))]);
        }
    }
}
