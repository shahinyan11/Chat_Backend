<?php

namespace App\Console\Commands;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Console\Command;

use DB;

class ImportThreads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:threads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import threads from old db';

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
        $threads = DB::connection('mysql3')->select('SELECT * FROM thread');
        foreach ($threads as $thread){
           $threadM = new Thread();
           $threadM->id = $thread->threadid;
           $threadM->forum_id = $thread->forumid;
            foreach ($threadM->fillable as $field) {
                if (isset($thread->{$field})) {
                    $threadM->{$field} = $thread->{$field};
                }
            }
          $threadM->save();
        }

    }
}
