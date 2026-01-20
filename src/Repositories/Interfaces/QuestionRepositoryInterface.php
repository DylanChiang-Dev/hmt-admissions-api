<?php

namespace App\Repositories\Interfaces;

interface QuestionRepositoryInterface
{
    public function save(array $question): void;
    public function findByFilters(string $examPath, ?string $subject, int $limit): array;
    public function findById(string $id): ?array;
    public function getRandom(string $examPath, ?string $subject, int $limit): array;
}
