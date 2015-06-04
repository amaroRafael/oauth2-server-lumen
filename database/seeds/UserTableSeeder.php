<?php
/**
 * Created by PhpStorm.
 * User: ramaro
 * Date: 6/2/15
 * Time: 10:17 PM
 */

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserTableSeeder extends Seeder {

    public function run()
    {
        DB::table('users')->forceDelete();

        $hasher = app()->make('hash');

        $user = app()->make('Rapiro\Models\User');

        $user->fill([
            'name' => 'User',
            'email' => 'user@user.com',
            'password' => $hasher->make('1234')
        ]);

        $user->save();
    }

}