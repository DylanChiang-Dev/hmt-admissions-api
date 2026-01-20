<?php

namespace App\Repositories\Interfaces;

interface LessonPackRepositoryInterface
{
    public function getToday(string $examPath, ?string $subject): array;
}
