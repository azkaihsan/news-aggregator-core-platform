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

class InternationalNewsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:internationalnews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from international RSS feeds';

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
            'RT' => 'https://www.rt.com/rss/news/',
        ];

        //https://tvbrics.com/en/turbo.xml
        //https://infobrics.org/rss/en/

        foreach ($sources as $name => $url) {
            $client = new Client();
            $response = $client->get($url);
            $rss = new SimpleXMLElement($response->getBody());

            foreach ($rss->channel->item as $item) {
                News::updateOrCreate(
                    ['url' => (string)$item->link],
                    [
                        'source_type' => 'international',
                        'source_name' => $name,
                        'content' => (string)$item->description,
                        'published_at' => date('Y-m-d H:i:s', strtotime($item->pubDate))
                    ]
                );
            }
        }
    }
}
