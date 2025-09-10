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

class PressTvNewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:presstvnews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from Press TV RSS feeds';

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
            'World' => 'https://www.presstv.ir/rss.xml',
            'West Asia' => 'https://www.presstv.ir/rss/rss-102.xml',
            'Asia-Pacific' => 'https://www.presstv.ir/rss/rss-104.xml',
            'Africa' => 'https://www.presstv.ir/rss/rss-105.xml',
            'US' => 'https://www.presstv.ir/rss/rss-103.xml',
            'Europe' => 'https://www.presstv.ir/rss/rss-106.xml',
            'UK' => 'https://www.presstv.ir/rss/rss-108.xml',
            'Americas' => 'https://www.presstv.ir/rss/rss-107.xml',
            'Society' => 'https://www.presstv.ir/rss/rss-12001.xml',
            'Arts' => 'https://www.presstv.ir/rss/rss-12002.xml',
            'Sports' => 'https://www.presstv.ir/rss/rss-12005.xml',
            'Sports' => 'https://www.presstv.ir/rss/rss-10107.xml',
            'Conversations' => 'https://www.presstv.ir/rss/rss-125.xml',
            'Iran' => 'https://www.presstv.ir/rss/rss-101.xml',
            'Iran' => 'https://www.presstv.ir/rss/rss-15015.xml',
            'Politics' => 'https://www.presstv.ir/rss/rss-10101.xml',
            'Economy' => 'https://www.presstv.ir/rss/rss-10102.xml',
            'Energy' => 'https://www.presstv.ir/rss/rss-10103.xml',
            'Nuclear Energy' => 'https://www.presstv.ir/rss/rss-10104.xml',
            'Culture' => 'https://www.presstv.ir/rss/rss-10105.xml',
            'Defense' => 'https://www.presstv.ir/rss/rss-10106.xml',
            'Definitive Revenge' => 'https://www.presstv.ir/rss/rss-10115.xml',
            'People\'s President' => 'https://www.presstv.ir/rss/rss-10116.xml',
            'Shows' => 'https://www.presstv.ir/rss/rss-150.xml',
            '10 Minutes' => 'https://www.presstv.ir/rss/rss-150111.xml',
            'Africa Today' => 'https://www.presstv.ir/rss/rss-15034.xml',
            'Economic Divide' => 'https://www.presstv.ir/rss/rss-15067.xml',
            'Interview' => 'https://www.presstv.ir/rss/rss-15031.xml',
            'In a Nutshell' => 'https://www.presstv.ir/rss/rss-150106.xml',
            'Hidden Files' => 'https://www.presstv.ir/rss/rss-150112.xml',
            'Iran Tech' => 'https://www.presstv.ir/rss/rss-150105.xml',
            'Iran Today' => 'https://www.presstv.ir/rss/rss-15006.xml',
            'Mideastream' => 'https://www.presstv.ir/rss/rss-15095.xml',
            'Palestine Declassified' => 'https://www.presstv.ir/rss/rss-150108.xml',
            'Spotlight' => 'https://www.presstv.ir/rss/rss-15057.xml',
            'Eye on Islam' => 'https://www.presstv.ir/rss/rss-150116.xml',
            'Black and White' => 'https://www.presstv.ir/rss/rss-15046.xml',
            'The Conversation' => 'https://www.presstv.ir/rss/rss-150122.xml',
            'Israel Watch' => 'https://www.presstv.ir/rss/rss-150124.xml',
            'Broadcast the Web' => 'https://www.presstv.ir/rss/rss-150126.xml',
            'Exposé' => 'https://www.presstv.ir/rss/rss-150131.xml',
            'Explainer' => 'https://www.presstv.ir/rss/rss-150129.xml',
            'Have It Out with Galloway!' => 'https://www.presstv.ir/rss/rss-150133.xml',
            'Sobh' => 'https://www.presstv.ir/rss/rss-150137.xml',
            'Songs of Resistance' => 'https://www.presstv.ir/rss/rss-150139.xml',
            'Al-Aqsa Flood' => 'https://www.presstv.ir/rss/rss-150138.xml',
            'From Beirut' => 'https://www.presstv.ir/rss/rss-150140.xml',
            'Unscripted' => 'https://www.presstv.ir/rss/rss-150141.xml',
            'Women of Resistance' => 'https://www.presstv.ir/rss/rss-150142.xml',
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

                $newsSource = NewsSource::firstOrCreate(['name' => "Press TV"]); // Create/get source
                $category = NewsCategory::firstOrCreate(['name' => $sourceName]);
                $country = Country::where('code', 'IR')->first();

                // Handle lastBuildDate (if needed)
                if (isset($xml->channel->lastBuildDate)) {
                    $lastBuildDateString = (string)$xml->channel->lastBuildDate;
                    try {
                        $lastBuildDate = Carbon::parse($lastBuildDateString)->setTimezone('Asia/Jakarta');
                        // You can now use $lastBuildDate (e.g., log it, store it, etc.)
                        $this->info("Last build date: " . $lastBuildDate); // Example
                    } catch (\Exception $e) {
                        Log::warning("Error parsing lastBuildDate: " . $e->getMessage());
                    }
                }

                foreach ($xml->channel->item as $item) {
                    $title = (string)$item->title;
                    $link = (string)$item->link;
                    $description = (string)$item->description;

                    $existingNews = News::where('url', $link)->first();
                    if ($existingNews) {
                        continue; // Skip if news already exists
                    }

                    News::create([
                        'category_id' => $category->id,
                        'source_id' => $newsSource->id,
                        'country_id' => $country->id,
                        'title' => $title,
                        'url' => $link,
                        'description' => $description,
                        'published_at' => $lastBuildDate,
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
