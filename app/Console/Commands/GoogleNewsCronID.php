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
use SimpleXMLElement; // For parsing RSS

class GoogleNewsCronID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:googlenewsid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Routine ETL News Data from Google News ID';

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
        $url = "https://news.google.com/rss?hl=id&gl=ID&ceid=ID:id";
        
        try {
            $xml = simplexml_load_file($url);

            if ($xml === false) {
                Log::error("Failed to load RSS feed: " . $url);
                $this->error("Failed to load RSS feed.");
                return 1; // Indicate failure
            }

            $category = NewsCategory::where('name', 'general')->first(); // Or create a category if needed. Customize as needed.
            $country = Country::where('code', 'ID')->first();

            foreach ($xml->channel->item as $item) {
                $title = (string)$item->title;
                $link = (string)$item->link;
                $description = (string)$item->description;
                $pubDate = (string)$item->pubDate;

                // Check if the news already exists based on URL
                $existingNews = News::where('url', $link)->first();

                if (!$existingNews) {  // Only insert if it doesn't already exist
                    $sourceName = null;
                    if (isset($item->source)) {
                        $sourceName = (string)$item->source;
                    } else {
                        // Attempt to extract source from title if source tag is missing (common in Google News RSS)
                        preg_match('/- (.*)$/', $title, $matches);
                        if(isset($matches[1])) {
                            $sourceName = $matches[1];
                        }
                    }

                    if ($sourceName) { // Only proceed if a source is identified
                        $newsSource = NewsSource::firstOrCreate(['name' => $sourceName]);

                        $publishedAt = Carbon::parse($pubDate)->setTimezone('Asia/Jakarta'); // Use your desired timezone

                        $description = preg_replace('/(\.{3,})$/', '', trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($description)))));

                        News::create([
                            'category_id' => $category->id,
                            'source_id' => $newsSource->id,
                            'country_id' => $country->id,
                            'title' => $title,
                            'url' => $link,
                            'description' => $description,
                            'published_at' => $publishedAt,
                            // Add other fields as needed (author, content, etc.)
                        ]);
                    } else {
                        Log::warning("Could not identify source for news item: " . $title);
                    }
                }
            }

            $this->info("Google News fetched and saved successfully.");
            return 0; // Indicate success

        } catch (\Exception $e) {
            Log::error("Error fetching Google News: " . $e->getMessage());
            $this->error("An error occurred: " . $e->getMessage());
            return 1; // Indicate failure
        }            
    }
}
