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

class CnnNewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:cnnnews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from CNN US RSS feeds';

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
        $url = 'http://rss.cnn.com/rss/edition.rss';

        try {
            $xml = simplexml_load_file($url);

            if ($xml === false) {
                Log::error("Failed to load RSS feed: " . $url);
                $this->error("Failed to load RSS feed.");
                return 1;
            }

            $category = NewsCategory::firstOrCreate(['name' => 'general']); // Or create a category if needed. Customize as needed.
            $newsSource = NewsSource::firstOrCreate(['name' => 'CNN']); // CNN is the source
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

                $urlToImage = null;
                if (isset($item->{'media:group'}->{'media:content'})) { // Accessing media:content within media:group
                    $mediaContent = $item->{'media:group'}->{'media:content'};

                    if (is_array($mediaContent)) { // Check if it's an array (multiple images)
                        foreach ($mediaContent as $media) {
                            if ((string)$media->attributes()->type == 'image/jpeg') {
                                $urlToImage = (string)$media->attributes()->url;
                                break; // Found an image, exit inner loop
                            }
                        }
                    } else { // Single media:content element
                        if ((string)$mediaContent->attributes()->type == 'image/jpeg') {
                            $urlToImage = (string)$mediaContent->attributes()->url;
                        }
                    }
                }


                News::create([
                    'category_id' => $category->id,
                    'source_id' => $newsSource->id,
                    'country_id' => $country->id,
                    'title' => $title,
                    'url' => $link,
                    'description' => $description,
                    'published_at' => $publishedAt,
                    'urltoimage' => $urlToImage, // Add the image URL
                    // Add other fields as needed (author, content, etc.)
                ]);
            }

            $this->info("CNN News fetched and saved successfully.");
            return 0;

        } catch (\Exception $e) {
            Log::error("Error fetching CNN News: " . $e->getMessage());
            $this->error("An error occurred: " . $e->getMessage());
            return 1;
        }
    }
}
