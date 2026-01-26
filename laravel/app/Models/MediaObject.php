<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaObject extends Model
{
    public $timestamps = false;

    // API Platformではマスアサインメントは使用されないので $fillable の定義は不要
    // protected $fillable = [
    //     'file_path',
    // ];
}
