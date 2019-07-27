<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = [
        'title','content','category_id'
    ];
    public function user(){
        return $this->belongsTo('app\User','user_id');
    }
    public function category(){
        return $this->belongsTo('app\Category','category_id');
    }
}
