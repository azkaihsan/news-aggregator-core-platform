<?php

namespace App\Console\Commands;

use App\Country;
use App\News;
use App\NewsCategory;
use App\NewsSource;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;  // Critical addition
use Carbon\Carbon;                   // Also ensure Carbon is imported

class NewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:newsapi';

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
        //API dari NewsAPI
        $key = '5e31c6e2c2b04761994d458ac62e82bc'; // Sesuaikan Kunci API yang dipakai
        $headers = [
            'X-Api-Key' => $key
        ];
        $client = new GuzzleClient([
            'headers' => $headers
        ]);        
        $countries = Country::whereIn('code', ['US', 'GB', 'ID', 'AU', 'CN', 'IR', 'IQ', 'JP', 'KR', 'RU', 'SA', 'SG', 'TR', 'MY'])->get();
        $newscategories = NewsCategory::whereIn('name', ['business', 'technology'])->get();
        
        $results = []; // Array to store results for each country/category combination

        foreach ($countries as $country) {
            foreach ($newscategories as $newscategory) {
                $createdCount = 0;
                $updatedCount = 0;
                $status = 'success'; // Assume success initially
        
                try {
                    sleep(1);
        
                    $response = $client->get('https://newsapi.org/v2/top-headlines', [
                        'query' => [
                            'pageSize' => 100,
                            'country' => $country->code,
                            'category' => $newscategory->name,
                            'apiKey' => $key,
                        ],
                    ]);
        
                    $data = json_decode($response->getBody(), true);
        
                    if (!isset($data['articles']) || $data['status'] !== 'ok') {
                        $status = 'error';
                        Log::error("NewsAPI Error for {$country->code}/{$newscategory->name}", $data);
                    } else {
                        foreach ($data['articles'] as $article) {
                            if (empty($article['title']) || empty($article['url'])) {
                                continue;
                            }
        
                            $newssource = NewsSource::firstOrCreate(
                                ['name' => $article['source']['name']],
                                ['name' => $article['source']['name']]
                            );
        
                            $published_at = Carbon::parse($article['publishedAt'])
                                ->setTimezone('Asia/Jakarta');
        
                            $news = News::updateOrCreate(
                                [
                                    'url' => $article['url'],
                                    'published_at' => $published_at,
                                ],
                                [
                                    'category_id' => $newscategory->id,
                                    'country_id' => $country->id,
                                    'source_id' => $newssource->id,
                                    'title' => $article['title'],
                                    'author' => $article['author'] ?? 'Unknown',
                                    'description' => $article['description'] ?? '',
                                    'urltoimage' => $article['urlToImage'] ?? null,
                                    'content' => $article['content'] ?? '',
                                    'published_at' => $published_at,
                                ]
                            );
        
                            if ($news->wasRecentlyCreated) {
                                $createdCount++;
                            } else {
                                $updatedCount++;
                            }
                        }
                    }
        
        
                } catch (\Exception $e) {
                    $status = 'error';
                    Log::error("News fetching failed for {$country->code}/{$newscategory->name}: " . $e->getMessage());
                }
        
                $results[] = [
                    'country' => $country->code,
                    'category' => $newscategory->name,
                    'created' => $createdCount,
                    'updated' => $updatedCount,
                    'status' => $status,
                ];
            }
        }
        
        // Output the results as JSON
        echo json_encode($results);
    }
}
