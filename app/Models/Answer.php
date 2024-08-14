<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public $timestamps = false;

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
}
