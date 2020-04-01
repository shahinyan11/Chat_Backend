<?php

use App\Models\ChatRoom;
use App\User;
use Illuminate\Database\Seeder;
use DB;

class RoomTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rooms = [
           ['name'=>'Main lobby', 'privacy'=>'public','owner_id'=>3],
           ['name'=>'Total Exposure', 'privacy'=>'public','owner_id'=>3],
           ['name'=>'Unblurred', 'privacy'=>'public','owner_id'=>3],
           ['name'=>'Blurred Reposts', 'privacy'=>'public','owner_id'=>3],
           ['name'=>'No rules', 'privacy'=>'public','owner_id'=>3],
           ['name'=>'Mature Wife', 'privacy'=>'public','owner_id'=>3],
           ['name'=>'USA', 'privacy'=>'country','owner_id'=>3],
           ['name'=>'UK', 'privacy'=>'country','owner_id'=>3],
           ['name'=>'Germany', 'privacy'=>'country','owner_id'=>3],
        ];
        if(ChatRoom::count() == 0){
            DB::table('chat_room')->insert($rooms);
        }

    }
}
