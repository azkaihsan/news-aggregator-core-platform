<?php

namespace App\Console\Commands;

use App\Country;
use App\News;
use App\NewsCategory;
use App\NewsSource;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;  // Critical addition
use Carbon\Carbon;                   // Also ensure Carbon is imported
use SimpleXMLElement; // For parsing RSS

class TvBricsNewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:tvbricsnews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from TV BRICS RSS feeds';

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
        $url = 'https://tvbrics.com/en/turbo.xml';

        $client = new Client();

        try {
            $response = $client->get($url);
            $xml = simplexml_load_string($response->getBody());

            if ($xml === false) {
                Log::error("Failed to parse RSS feed: " . $url);
                $this->error("Failed to parse RSS feed.");
                return 1;
            }

            $newsSource = NewsSource::firstOrCreate(['name' => 'TV BRICS']);
            $category = NewsCategory::firstOrCreate(['name' => 'BRICS']); // Default category

            foreach ($xml->item as $item) {
                $title = (string)$item->title;
                $link = (string)$item->link;
                $description = (string)$item->description;
                $pubDate = (string)$item->pubDate;
                $content = (string)$item->{'turbo:content'}; // Get content from turbo:content

                $existingNews = News::where('url', $link)->first();
                if ($existingNews) {
                    continue; // Skip if news already exists
                }

                try {
                    $publishedAt = Carbon::parse($pubDate)->setTimezone('Asia/Jakarta');
                } catch (\Exception $e) {
                    Log::warning("Error parsing pubDate: " . $e->getMessage() . " for item: " . $title);
                    $publishedAt = null;
                }

                $urlToImage = null;
                if (isset($item->enclosure)) {
                    $urlToImage = (string)$item->enclosure->attributes()->url;
                } else if (isset($item->image)) { // Fallback to <image> tag if no <enclosure>
                    $urlToImage = (string)$item->image->url;
                }

                $cleanedDescription = $this->cleanNewsDescription($description); // Clean description
                $country = Country::where('name', 'BRICS')->first();

                News::create([
                    'category_id' => $category->id,
                    'source_id' => $newsSource->id,
                    'country_id' => $country->id,
                    'title' => $title,
                    'url' => $link,
                    'description' => $cleanedDescription,
                    'published_at' => $publishedAt,
                    'urltoimage' => $urlToImage,
                    'content' => $content, // Store the content
                ]);
            }

            $this->info("TV BRICS News fetched and saved successfully.");
            return 0;

        } catch (\Exception $e) {
            Log::error("Error fetching or parsing TV BRICS News: " . $e->getMessage());
            $this->error("An error occurred: " . $e->getMessage());
            return 1;
        }
    }

    private function cleanNewsDescription($description) {
        $cleaned = strip_tags($description);
        $cleaned = html_entity_decode($cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = trim($cleaned);
        return $cleaned;
    }
}
