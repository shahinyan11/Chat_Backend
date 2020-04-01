<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Console\Command;

use DB;

class ImportPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import posts from old db';

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
        ini_set('memory_limit', '-1');


        $posts = DB::connection('mysql3')->select('SELECT * FROM post');
        foreach ($posts as $post){
           $postM = new Post();
            $postM->id = $post->postid;
            foreach ($postM->fillable as $field) {
                if (isset($post->{$field})) {
                    $postM->{$field} = $post->{$field};
                }
            }
          $postM->save();
        }

    }
}
