<?php

// app/Models/UserScreenshot.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserScreenshot extends Model
{
    protected $table = 'hr_user_screenshots';
    protected $fillable = ['user_id','path','url','width','height','page_url','user_agent'];
}
