<?php


namespace App\Http\Controllers\Manages;


class Auth extends Base
{
    public function rule()
    {

        return view('manages.auth');
    }

}