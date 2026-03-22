<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotResponse extends Model
{
    protected $fillable = [
        'trigger',
        'response',
        'match_type',
        'is_active',
    ];
}
