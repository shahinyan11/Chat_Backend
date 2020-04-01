<?php

use Illuminate\Database\Seeder;
use MongoDB\BSON\ObjectID;

class OauthClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $oauthClients = [
            ["id"=>1,"name"=>"Laravel Personal Access Client","secret"=>"7TfDKrU2tfTNNEAaXR3usIb65JDJBnGn1t55yb0w","redirect"=>"http://localhost","personal_access_client"=>true,"password_client"=>false,"revoked"=>false],
            ["id"=>2,"name"=>"Laravel Password Grant Client","secret"=>"R0p2vnaL1gkXMagpadPCUArAajoqnXMmEjyd3tZa","redirect"=>"http://localhost","personal_access_client"=>false,"password_client"=>true,"revoked"=>false]
        ];

        if (DB::table('oauth_clients')->count() == 0) {
            DB::table('oauth_clients')->insert($oauthClients);
        }
    }
}
