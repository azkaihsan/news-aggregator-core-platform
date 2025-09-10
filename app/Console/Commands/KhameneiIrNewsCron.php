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

class KhameneiIrNewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:khameneinews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from Khamenei Ir RSS feeds';

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
            'Fatwas and Legal Questions' => 'https://english.khamenei.ir/rss?tp=4',
            'Analysis' => 'https://english.khamenei.ir/rss?tp=26',
            'Around the World' => 'https://english.khamenei.ir/rss?tp=19',
            'Artworks' => 'https://english.khamenei.ir/rss?tp=15',
            'Biography' => 'https://english.khamenei.ir/rss?tp=8',
            'Bookshelf' => 'https://english.khamenei.ir/rss?tp=27',
            'Dossier' => 'https://english.khamenei.ir/rss?tp=18',
            'Flashbacks' => 'https://english.khamenei.ir/rss?tp=20',
            'Infographics' => 'https://english.khamenei.ir/rss?tp=71',
            'Interview' => 'https://english.khamenei.ir/rss?tp=28',
            'Leader\'s Opinions' => 'https://english.khamenei.ir/rss?tp=6',
            'Leader\'s Opinions' => 'https://english.khamenei.ir/rss?tp=24',
            'Messages and Letters' => 'https://english.khamenei.ir/rss?tp=3',
            'Motion Graphics' => 'https://english.khamenei.ir/rss?tp=14',
            'general' => 'https://english.khamenei.ir/rss?tp=1',
            'Photos' => 'https://english.khamenei.ir/rss?tp=9',
            'Posters' => 'https://english.khamenei.ir/rss?tp=13',
            'Reports' => 'https://english.khamenei.ir/rss?tp=17',
            'Reviews' => 'https://english.khamenei.ir/rss?tp=21',
            'Speeches' => 'https://english.khamenei.ir/rss?tp=2',
            'Videos' => 'https://english.khamenei.ir/rss?tp=10',
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

                $newsSource = NewsSource::firstOrCreate(['name' => "Khamenei Ir"]); // Create/get source
                $category = NewsCategory::firstOrCreate(['name' => $sourceName]);
                $country = Country::where('code', 'IR')->first();

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
                    if (isset($item->enclosure)) {
                        $urlToImage = (string)$item->enclosure->attributes()->url;
                    }

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
