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

class RtNewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:rtnews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from Russia Today RSS feeds';

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
        $url = 'https://www.rt.com/rss/news/';

        try {
            $xml = simplexml_load_file($url);

            if ($xml === false) {
                Log::error("Failed to load RSS feed: " . $url);
                $this->error("Failed to load RSS feed.");
                return 1;
            }

            $category = NewsCategory::firstOrCreate(['name' => 'general']); // Or create a category if needed. Customize as needed.
            $newsSource = NewsSource::firstOrCreate(['name' => 'Russia Today']); // RT is the source
            $country = Country::where('code', 'RU')->first();

            foreach ($xml->channel->item as $item) {
                $title = (string)$item->title;
                $link = (string)$item->link;
                $description = (string)$item->description;
                $pubDate = (string)$item->pubDate;
                $content = (string)$item->{'content:encoded'};

                $existingNews = News::where('url', $link)->first();
                if ($existingNews) {
                    continue; // Skip if news already exists
                }

                $publishedAt = Carbon::parse($pubDate)->setTimezone('Asia/Jakarta');

                $urlToImage = null;
                if (isset($item->enclosure)) {
                    $urlToImage = (string)$item->enclosure->attributes()->url;
                } else if(isset($item->description)) {
                    // Extract image URL from description if enclosure is missing
                    preg_match('/<img.*?src="(.*?)"/', $item->description, $matches);
                    if (isset($matches[1])) {
                        $urlToImage = $matches[1];
                    }
                }

                $author = null;
                if (isset($item->{'dc:creator'})) {
                    $author = (string)$item->{'dc:creator'};
                }

                $cleanedDescription = $this->cleanNewsDescription($description); // Sanitize description

                News::create([
                    'category_id' => $category->id,
                    'source_id' => $newsSource->id,
                    'country_id' => $country->id,
                    'title' => $title,
                    'url' => $link,
                    'description' => $cleanedDescription,
                    'content' => $content,
                    'author' => $author, // Add the author
                    'published_at' => $publishedAt,
                    'urltoimage' => $urlToImage,
                ]);
            }

            $this->info("RT News fetched and saved successfully.");
            return 0;

        } catch (\Exception $e) {
            Log::error("Error fetching RT News: " . $e->getMessage());
            $this->error("An error occurred: " . $e->getMessage());
            return 1;
        }
    }

    private function cleanNewsDescription($description) {
        $cleaned = strip_tags($description, '<br>'); // Allow <br> tags
        $cleaned = html_entity_decode($cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = trim($cleaned);
        return $cleaned;
    }
}
