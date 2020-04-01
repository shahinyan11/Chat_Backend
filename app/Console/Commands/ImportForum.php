<?php

namespace App\Console\Commands;

use App\Models\Forum;
use Illuminate\Console\Command;

use DB;

class ImportForum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:forum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import forum from old db';

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
        $forums = DB::connection('mysql3')->select('SELECT * FROM forum');
        foreach ($forums as $forum){
           $forumM = new Forum();
            $forumM->id = $forum->forumid;
            $forumM->title = $forum->title;
            $forumM->title_clean = $forum->title_clean;
            $forumM->description = $forum->description;
            $forumM->description_clean = $forum->description_clean;
            $forumM->options = $forum->options;
            $forumM->order = $forum->displayorder;
            $forumM->replycount = $forum->replycount;
            $forumM->daysprune = $forum->daysprune;
            $forumM->password = $forum->password != ''?$forum->password:NULL;
            $forumM->private = $forum->showprivate;
            $forumM->parentid = $forum->parentid;
            $forumM->parentlist = $forum->parentlist;
            $forumM->childlist = $forum->childlist;
            $forumM->threadcount = $forum->threadcount;
            $forumM->lastthread = $forum->lastthread;
            $forumM->lastthreadid = $forum->lastthreadid;
            $forumM->lastpost = $forum->lastpost;
            $forumM->lastposter = $forum->lastposter;
            $forumM->lastposterid = $forum->lastposterid;
            $forumM->lastpostid = $forum->lastpostid;
            $forumM->save();
        }

    }
}
