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

class InfoBricsNewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:infobricsnews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from Info BRICS RSS feeds';

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
        $url = 'https://infobrics.org/rss/en/';

        $client = new GuzzleClient(); // Initialize Guzzle client

        try {
            $response = $client->get($url); // Use Guzzle to fetch the RSS feed
            $xml = simplexml_load_string($response->getBody()); // Parse the response

            if ($xml === false) {
                Log::error("Failed to parse RSS feed: " . $url);
                $this->error("Failed to parse RSS feed.");
                return 1;
            }

            $newsSource = NewsSource::firstOrCreate(['name' => 'InfoBRICS']);

            foreach ($xml->channel->item as $item) {
                $title = (string)$item->title;
                $description = (string)$item->description;
                $link = (string)$item->link;
                $pubDate = (string)$item->pubDate;

                $existingNews = News::where('url', $link)->first();
                if ($existingNews) {
                    continue; // Skip if news already exists
                }

                try {
                    $publishedAt = Carbon::parse($pubDate)->setTimezone('Asia/Jakarta');
                } catch (\Exception $e) {
                    Log::warning("Error parsing pubDate: " . $e->getMessage() . " for item: " . $title);
                    $publishedAt = null; // Handle parsing errors gracefully
                }


                $urlToImage = null;
                if (isset($item->enclosure)) {
                    $urlToImage = (string)$item->enclosure->attributes()->url;
                }

                $category = NewsCategory::firstOrCreate(['name' => 'BRICS']); // Default category
                $country = Country::firstOrCreate(['code' => 'XX', 'name' => 'BRICS', 'full_name' => 'Brazil, Russia, India, China and South Africa', 'iso3' => 'XXX', 'number' => '999', 'continent_code' => 'XX']); // Default category

                News::create([
                    'category_id' => $category->id,
                    'source_id' => $newsSource->id,
                    'country_id' => $country->id,
                    'title' => $title,
                    'description' => $description,
                    'published_at' => $publishedAt,
                    'urltoimage' => $urlToImage,
                ]);
            }

            $this->info("InfoBrics News fetched and saved successfully.");
            return 0;

        } catch (\Exception $e) {
            Log::error("Error fetching or parsing InfoBrics News: " . $e->getMessage());
            $this->error("An error occurred: " . $e->getMessage());
            return 1;
        }
    }
}
