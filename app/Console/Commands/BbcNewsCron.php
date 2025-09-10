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

class BbcNewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:bbcnews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from BBC GB RSS feeds';

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
        $url = 'https://feeds.bbci.co.uk/news/rss.xml';

        try {
            $xml = simplexml_load_file($url);

            if ($xml === false) {
                Log::error("Failed to load RSS feed: " . $url);
                $this->error("Failed to load RSS feed.");
                return 1;
            }

            $category = NewsCategory::firstOrCreate(['name' => 'general']); // Or create a category if needed. Customize as needed.
            $newsSource = NewsSource::firstOrCreate(['name' => 'BBC News']); // BBC is the source
            $country = Country::where('code', 'GB')->first();

            foreach ($xml->channel->item as $item) {
                $title = (string)$item->title;
                $description = (string)$item->description;
                $link = (string)$item->link;
                $pubDate = (string)$item->pubDate;

                // Use GUID to check for existing news, as permaLink is often false
                $existingNews = News::where('url', $link)->first();
                if ($existingNews) {
                    continue; // Skip if news already exists
                }

                $publishedAt = Carbon::parse($pubDate)->setTimezone('Asia/Jakarta');

                $urlToImage = null;
                if (isset($item->{'media:thumbnail'})) {
                    $urlToImage = (string)$item->{'media:thumbnail'}->attributes()->url;
                }

                News::create([
                    'category_id' => $category->id,
                    'source_id' => $newsSource->id,
                    'country_id' => $country->id,
                    'title' => $title,
                    'description' => $description,
                    'url' => $link,
                    'published_at' => $publishedAt,
                    'urltoimage' => $urlToImage,
                    // Add other fields as needed (author, content, etc.)
                ]);
            }

            $this->info("BBC News fetched and saved successfully.");
            return 0;

        } catch (\Exception $e) {
            Log::error("Error fetching BBC News: " . $e->getMessage());
            $this->error("An error occurred: " . $e->getMessage());
            return 1;
        }
    }
}
