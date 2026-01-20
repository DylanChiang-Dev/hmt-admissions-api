<?php

namespace App\Repositories\MySql;

use App\Repositories\Interfaces\LessonPackRepositoryInterface;
use App\Repositories\Interfaces\QuestionRepositoryInterface;

class MySqlLessonPackRepository implements LessonPackRepositoryInterface
{
    private QuestionRepositoryInterface $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function getToday(string $examPath, ?string $subject): array
    {
        // Fetch 10 random questions from the repository
        $questions = $this->questionRepository->getRandom($examPath, $subject, 10);

        $date = date('Y-m-d');

        // Construct a unique ID for the lesson pack
        $idParts = ['lp', $date, $examPath];
        if ($subject) {
            $idParts[] = $subject;
        }
        $id = implode('-', $idParts);

        // Calculate metadata
        $count = count($questions);
        $goalXp = $count * 10; // 10 XP per question
        $estimatedMinutes = (int) ceil($count * 3.5); // ~3.5 minutes per question

        return [
            'id' => $id,
            'date' => $date,
            'exam_path' => $examPath,
            'subject' => $subject,
            'items' => $questions,
            'goal_xp' => $goalXp,
            'estimated_minutes' => $estimatedMinutes
        ];
    }
}
