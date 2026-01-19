<?php

namespace App\Repositories\Memory;

use App\Repositories\Interfaces\LessonPackRepositoryInterface;
use App\Exceptions\NotFoundException;

class MemoryLessonPackRepository implements LessonPackRepositoryInterface
{
    private string $jsonPath;

    public function __construct(string $jsonPath = __DIR__ . '/../../../../hmt-admissions-spec/examples/lesson-pack-today.response.json')
    {
        $this->jsonPath = $jsonPath;
    }

    public function getToday(string $examPath, ?string $track, ?string $subject): array
    {
        if (!file_exists($this->jsonPath)) {
            // If file not found, we could throw or return empty.
            // Given it's a "Memory" repo simulating data, throwing NotFound makes sense if the source is missing.
            throw new NotFoundException("Lesson pack data source not found.", "DATA_SOURCE_MISSING");
        }

        $content = file_get_contents($this->jsonPath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
             throw new \RuntimeException("Invalid JSON data source.");
        }

        // Simulate dynamic data by overriding fields with input
        $data['exam_path'] = $examPath;

        if ($track) {
            $data['track'] = $track;
        }

        if ($subject) {
            $data['subject'] = $subject;
        }

        // Also update items to look consistent (optional but nice)
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as &$item) {
                $item['exam_path'] = $examPath;
                if ($track) $item['track'] = $track;
                if ($subject) $item['subject'] = $subject;
            }
        }

        return $data;
    }
}
