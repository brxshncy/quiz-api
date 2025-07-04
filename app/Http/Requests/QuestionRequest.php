<?php

namespace App\Http\Requests;

use App\Enum\QuestionTypes;
use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string|max:1000',
            'type' => 'required|in:' . implode(',', [
                QuestionTypes::MULTIPLE_CHOICES->value,
                QuestionTypes::FILL_IN_THE_BLANK->value,
                QuestionTypes::ESSAY->value,
                QuestionTypes::TRUE_FALSE->value,
                QuestionTypes::MATCHING->value,
            ]),
            'quiz_id' => 'required|exists:quizzes,id',
            'options' => 'nullable|array',
            'correct_answer' => 'nullable|string|max:500',
            'media_url' => 'nullable|url',
        ];
    }
} 