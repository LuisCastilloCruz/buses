<?php

namespace Modules\AiReception\Agenda;

class User extends \Illuminate\Foundation\Auth\User
{
    protected $connection = 'agenda';
    protected $table = 'aireception_users';
    protected $hidden = ['password'];
    public $timestamps = false;
}
