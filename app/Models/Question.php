<?php

namespace App\Models;

use App\Enum\QuestionTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'type',
        'quiz_id',
        'options',
        'correct_answer',
        'media_url',
    ];

    protected $casts = [
        'type' => QuestionTypes::class,
        'options' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
