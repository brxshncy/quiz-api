<?php 

namespace App\Enum;

enum QuestionTypes: string
{
    case MULTIPLE_CHOICES = 'multiple_choices';
    case FILL_IN_THE_BLANK = 'fill_in_the_blank';
    case ESSAY = 'essay';
    case TRUE_FALSE = 'true_false';
    case MATCHING = 'matching';
}