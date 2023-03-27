<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SaveRecCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'save:rec';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run kafka consumer to save recommendation';

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
     * @return int
     */

     public function handle()
     {
         $conf = new \RdKafka\Conf();
         $conf->set('metadata.broker.list', 'localhost:9092');
         $rk = new \RdKafka\Consumer($conf);
         $rk->addBrokers("localhost:9092");
         $topic = $rk->newTopic("recommendations");
         $topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);

         $rec = [];

         while (true) {
             $msg = $topic->consume(0, 1000);
             if (@$msg->payload == null) {
                 continue;
             }
             $msg = json_decode($msg->payload, true);

             if ($msg == null) {
                 continue;
             }

             $rec = Cache::get('rec', []);

             $key = $msg['key'];
             $new = [];
            $new['name'] = $msg['name'];
            $new['distance'] = $msg['distance'];
            $rec[$key][] = $new;


             // print if consumer is working
             echo "key: " . $key . " name: " . $msg['name'] . " distance: " . $msg['distance'] . "\n";

             // save to cache
             Cache::put('rec', $rec, now()->addMinutes(10));
         }
     }
}
