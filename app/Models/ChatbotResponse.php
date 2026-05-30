<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotResponse extends Model
{
    protected $fillable = [
        'trigger',
        'response',
        'follow_up_question',
        'suggested_triggers',
        'match_type',
        'is_active',
    ];
}
