<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SaveCharaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'save:chara';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run kafka consumer to save characteristic';

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
         $topic = $rk->newTopic("characteristic");
         $topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);

         $chara = [];

         while (true) {
             $msg = $topic->consume(0, 1000);
             if (@$msg->payload == null) {
                 continue;
             }
             $msg = json_decode($msg->payload, true);

            foreach ($msg as $key => $value) {
                $chara[$key] = $value;
            }

             // print if consumer is working
             echo "consumer is working";

             // save to cache
             Cache::put('chara', $chara, now()->addMinutes(10));
         }
    }
}
