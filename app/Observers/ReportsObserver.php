<?php

namespace App\Observers;

use App\Models\ChatAttachment;
use App\Models\ChatPost;
use App\Models\ChatRoom;
use App\Models\Reports;

class ReportsObserver
{
    /**
     * Handle the report "created" event.
     *
     * @param  \App\Models\Reports  $report
     * @return void
     */
    public function created(Reports $report)
    {
        switch ($report->property_type) {
            case 'chat_post':
                $post = ChatPost::where('id', $report->property_id)->first();
                $post->report_count += 1;
                $post->save();
                $room = ChatRoom::where('id', $post->chat_room_id)->first();
                $room->report_count += 1;
                $room->save();
                break;
            case 'chat_attachment':
                $attachment = ChatAttachment::where('id', $report->property_id)->first();
                $attachment->report_count += 1;
                $attachment->save();
                $room = ChatRoom::where('id', $attachment->chat_room_id)->first();
                $room->report_count += 1;
                $room->save();
                break;
            default:
                break;
        }

    }



    public function saved(Reports $post)
    {

    }

    /**
     * Handle the report "updated" event.
     *
     * @param  \App\Models\Reports $report
     * @return void
     */
    public function updated(Reports $report)
    {

    }

    /**
     * Handle the report "deleted" event.
     *
     * @param  \App\Models\Reports  $report
     * @return void
     */
    public function deleted(Reports $report)
    {
        switch ($report->property_type) {
            case 'chat_post':
                $post = ChatPost::where('id', $report->property_id)->first();
                $post->report_count > 0 ? $post->report_count-= 1: 0;
                $post->save();
                $room = ChatRoom::where('id', $post->chat_room_id)->first();
                $room->report_count > 0 ? $room->report_count-= 1: 0;
                $room->save();
                break;
            case 'chat_attachment':
                $attachment = ChatAttachment::where('id', $report->property_id)->first();
                $attachment->report_count > 0 ? $attachment->report_count-= 1: 0;
                $attachment->save();
                $room = ChatRoom::where('id', $attachment->chat_room_id)->first();
                $room->report_count > 0 ? $room->report_count-= 1: 0;
                $room->save();
                break;
            default:
                break;
        }
    }

    /**
     * Handle the report "restored" event.
     *
     * @param  \App\Reports  $report
     * @return void
     */
    public function restored(Reports $report)
    {
        //
    }

    /**
     * Handle the report "force deleted" event.
     *
     * @param  \App\Reports  $report
     * @return void
     */
    public function forceDeleted(Reports $report)
    {
        //
    }
}
