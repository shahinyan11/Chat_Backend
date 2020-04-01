<?php

namespace App\Observers;

use App\Models\ChatPost;
use App\Models\ChatRoom;

class ChatPostObserver
{
    /**
     * Handle the post "created" event.
     *
     * @param  \App\Models\ChatPost  $post
     * @return void
     */
    public function created(ChatPost $post)
    {

        $room = ChatRoom::where('id', $post->chat_room_id)->first();
        $room->post_count += 1;
        $room->save();
    }



    public function saved(ChatPost $post)
    {

    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Models\ChatPost $post
     * @return void
     */
    public function updated(ChatPost $post)
    {

    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\ChatPost  $post
     * @return void
     */
    public function deleted(ChatPost $post)
    {
        $room = ChatRoom::where('id', $post->chat_room_id)->first();
        $room->post_count > 0 ? $room->post_count-= 1: 0;
        $room->save();
    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\ChatPost  $post
     * @return void
     */
    public function restored(ChatPost $post)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\ChatPost  $post
     * @return void
     */
    public function forceDeleted(ChatPost $post)
    {
        //
    }
}
