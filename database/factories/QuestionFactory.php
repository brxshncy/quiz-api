<?php

namespace Database\Factories;

use App\Enum\QuestionTypes;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Question::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            QuestionTypes::MULTIPLE_CHOICES,
            QuestionTypes::FILL_IN_THE_BLANK,
            QuestionTypes::ESSAY,
            QuestionTypes::TRUE_FALSE,
            QuestionTypes::MATCHING,
        ];

        $type = $this->faker->randomElement($types);

        return [
            'question' => $this->faker->sentence() . '?',
            'type' => $type,
            'quiz_id' => Quiz::factory(),
            'options' => $this->getOptionsForType($type),
            'correct_answer' => $this->getCorrectAnswerForType($type),
            'media_url' => $this->faker->optional(0.2)->url(),
        ];
    }

    /**
     * Generate options based on question type
     */
    private function getOptionsForType(QuestionTypes $type): ?array
    {
        return match ($type) {
            QuestionTypes::MULTIPLE_CHOICES => [
                'A' => $this->faker->word(),
                'B' => $this->faker->word(),
                'C' => $this->faker->word(),
                'D' => $this->faker->word(),
            ],
            QuestionTypes::MATCHING => [
                $this->faker->word() => $this->faker->word(),
                $this->faker->word() => $this->faker->word(),
                $this->faker->word() => $this->faker->word(),
            ],
            default => null,
        };
    }

    /**
     * Generate correct answer based on question type
     */
    private function getCorrectAnswerForType(QuestionTypes $type): string
    {
        return match ($type) {
            QuestionTypes::MULTIPLE_CHOICES => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            QuestionTypes::TRUE_FALSE => $this->faker->randomElement(['true', 'false']),
            QuestionTypes::FILL_IN_THE_BLANK => $this->faker->word(),
            QuestionTypes::ESSAY => $this->faker->sentence(),
            QuestionTypes::MATCHING => 'Option1-Answer1, Option2-Answer2',
        };
    }

    /**
     * Create a multiple choice question
     */
    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionTypes::MULTIPLE_CHOICES,
            'options' => [
                'A' => 'Option A',
                'B' => 'Option B',
                'C' => 'Option C',
                'D' => 'Option D',
            ],
            'correct_answer' => 'A',
        ]);
    }

    /**
     * Create a fill in the blank question
     */
    public function fillInTheBlank(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionTypes::FILL_IN_THE_BLANK,
            'options' => null,
            'correct_answer' => $this->faker->word(),
        ]);
    }

    /**
     * Create an essay question
     */
    public function essay(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionTypes::ESSAY,
            'options' => null,
            'correct_answer' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a true/false question
     */
    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionTypes::TRUE_FALSE,
            'options' => null,
            'correct_answer' => $this->faker->randomElement(['true', 'false']),
        ]);
    }

    /**
     * Create a matching question
     */
    public function matching(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionTypes::MATCHING,
            'options' => [
                'Term 1' => 'Definition 1',
                'Term 2' => 'Definition 2',
                'Term 3' => 'Definition 3',
            ],
            'correct_answer' => 'Term 1-Definition 1, Term 2-Definition 2, Term 3-Definition 3',
        ]);
    }
} 