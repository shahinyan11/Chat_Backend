<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ChatPost;
use App\Models\ChatAttachment;

class UpdateDbTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tables:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update db tables';

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
       $attachments =  ChatAttachment::all();
        foreach ($attachments as $attachment) {
            $attachment->owner_id = ChatPost::where(['id' => $attachment->chat_post_id])->first()->owner_id;
            $attachment->save();
        }
    }
}
