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

class FoxNewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:foxnews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from Fox News RSS feeds';

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
        $sources = [
            'FoxNews Latest' => 'https://moxie.foxnews.com/google-publisher/latest.xml',
            'FoxNews World' => 'https://moxie.foxnews.com/google-publisher/world.xml',
            'FoxNews Politics' => 'https://moxie.foxnews.com/google-publisher/politics.xml',
            'FoxNews Science' => 'https://moxie.foxnews.com/google-publisher/science.xml',
            'FoxNews Health' => 'https://moxie.foxnews.com/google-publisher/health.xml',
            'FoxNews Sports' => 'https://moxie.foxnews.com/google-publisher/sports.xml',
            'FoxNews Travel' => 'https://moxie.foxnews.com/google-publisher/travel.xml',
            'FoxNews Technology' => 'https://moxie.foxnews.com/google-publisher/tech.xml',
            'FoxNews Opinion' => 'https://moxie.foxnews.com/google-publisher/opinion.xml',
            'FoxNews Videos' => 'https://moxie.foxnews.com/google-publisher/videos.xml',
            'FoxNews US' => 'https://moxie.foxnews.com/google-publisher/us.xml',
        ];

        foreach ($sources as $sourceName => $url) {
            $this->info("Fetching news from: " . $sourceName); // Informative output

            try {
                $xml = simplexml_load_file($url);

                if ($xml === false) {
                    Log::error("Failed to load RSS feed: " . $url);
                    $this->error("Failed to load RSS feed: " . $url); // Output to console
                    continue; // Skip to the next source
                }

                $newsSource = NewsSource::firstOrCreate(['name' => "Fox News"]); // Create/get source

                // Try to get the category from the source name, or use a default
                $categoryName = 'general'; // Default category

                // Check for specific sources that should be "General"
                $generalSources = ['FoxNews Latest', 'FoxNews World', 'FoxNews Videos', 'FoxNews US'];
                if (!in_array($sourceName, $generalSources)) {
                    // If not one of the "general" sources, try to extract the category
                    $categoryName = explode('FoxNews ', $sourceName)[1] ?? 'General'; // Extract category name, fallback to General
                }
                $category = NewsCategory::firstOrCreate(['name' => $categoryName]);
                $country = Country::where('code', 'US')->first();

                foreach ($xml->channel->item as $item) {
                    $title = (string)$item->title;
                    $link = (string)$item->link;
                    $description = (string)$item->description;
                    $pubDate = (string)$item->pubDate;

                    $existingNews = News::where('url', $link)->first();
                    if ($existingNews) {
                        continue; // Skip if news already exists
                    }

                    $publishedAt = Carbon::parse($pubDate)->setTimezone('Asia/Jakarta');

                    $cleanedDescription = $this->cleanNewsDescription($description);

                    $urlToImage = null;
                    if (isset($item->{'media:content'})) {  // Handle the media:content namespace
                        $mediaContent = $item->{'media:content'};
                        if (is_array($mediaContent)) { // Check if it's an array (sometimes it is)
                            foreach($mediaContent as $media) {
                                if ((string)$media->attributes()->type == 'image/jpeg') {
                                    $urlToImage = (string)$media->attributes()->url;
                                    break; // Exit inner loop after finding the first image
                                }
                            }
                        } else { // It's a single SimpleXMLElement object
                          if ((string)$mediaContent->attributes()->type == 'image/jpeg') {
                            $urlToImage = (string)$mediaContent->attributes()->url;
                          }
                        }
                    }
        
        
                    $content = null;
                    if (isset($item->{'content:encoded'})) {
                        $content = (string)$item->{'content:encoded'};
                    }

                    News::create([
                        'category_id' => $category->id,
                        'source_id' => $newsSource->id,
                        'country_id' => $country->id,
                        'title' => $title,
                        'url' => $link,
                        'description' => $cleanedDescription,
                        'published_at' => $publishedAt,
                        'urltoimage' => $urlToImage, // Add the image URL
                        'content' => $content,          // Add the content
                    ]);
                }

                $this->info($sourceName . " fetched and saved successfully.");

            } catch (\Exception $e) {
                Log::error("Error fetching " . $sourceName . ": " . $e->getMessage());
                $this->error("An error occurred fetching " . $sourceName . ": " . $e->getMessage()); // Output to console
                continue; // Continue to the next source
            }
        } // End of foreach loop

        return 0;
    }
    private function cleanNewsDescription($description) {
        $cleaned = strip_tags($description);
        $cleaned = html_entity_decode($cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = trim($cleaned);
        // Add more cleaning steps as needed (e.g., removing specific Fox News artifacts)
        return $cleaned;
    }
}
