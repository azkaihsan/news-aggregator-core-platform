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

class IrnaNewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:irnanews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from IRNA RSS feeds';

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
        $url = 'https://en.irna.ir/rss';

        try {
            $xml = simplexml_load_file($url);

            if ($xml === false) {
                Log::error("Failed to load RSS feed: " . $url);
                $this->error("Failed to load RSS feed.");
                return 1;
            }

            $newsSource = NewsSource::firstOrCreate(['name' => 'IRNA']); // IRNA is the source
            $country = Country::where('code', 'IR')->first();

            foreach ($xml->channel->item as $item) {
                $title = (string)$item->title;
                $link = (string)$item->link;
                $description = (string)$item->description;
                $pubDate = (string)$item->pubDate;
                $categoryString = (string)$item->category; // Get the category string

                $existingNews = News::where('url', $link)->first();
                if ($existingNews) {
                    continue; // Skip if news already exists
                }

                $publishedAt = Carbon::parse($pubDate)->setTimezone('Asia/Jakarta');

                $urlToImage = null;
                if (isset($item->enclosure)) {
                    $urlToImage = (string)$item->enclosure->attributes()->url;
                }

                $category = NewsCategory::firstOrCreate(['name' => $categoryString]);

                News::create([
                    'category_id' => $category->id,
                    'source_id' => $newsSource->id,
                    'country_id' => $country->id,
                    'title' => $title,
                    'url' => $link,
                    'description' => $description,
                    'published_at' => $publishedAt,
                    'urltoimage' => $urlToImage,
                ]);
            }

            $this->info("IRNA News fetched and saved successfully.");
            return 0;

        } catch (\Exception $e) {
            Log::error("Error fetching IRNA News: " . $e->getMessage());
            $this->error("An error occurred: " . $e->getMessage());
            return 1;
        }
    }
}
