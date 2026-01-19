<?php

namespace App\Repositories\MySql;

use App\Repositories\Interfaces\QuestionRepositoryInterface;
use App\Storage\Db;
use PDO;

class MySqlQuestionRepository implements QuestionRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Db::getInstance();
    }

    public function save(array $question): void
    {
        $sql = "INSERT INTO questions (
                    id, exam_path, track, subject, question_type, stem,
                    options_json, difficulty, tags_json, knowledge_point_ids_json
                ) VALUES (
                    :id, :exam_path, :track, :subject, :question_type, :stem,
                    :options_json, :difficulty, :tags_json, :knowledge_point_ids_json
                ) ON DUPLICATE KEY UPDATE
                    exam_path = VALUES(exam_path),
                    track = VALUES(track),
                    subject = VALUES(subject),
                    question_type = VALUES(question_type),
                    stem = VALUES(stem),
                    options_json = VALUES(options_json),
                    difficulty = VALUES(difficulty),
                    tags_json = VALUES(tags_json),
                    knowledge_point_ids_json = VALUES(knowledge_point_ids_json)";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $question['id'],
            ':exam_path' => $question['exam_path'] ?? null,
            ':track' => $question['track'] ?? null,
            ':subject' => $question['subject'] ?? null,
            ':question_type' => $question['question_type'] ?? null,
            ':stem' => $question['stem'] ?? null,
            ':options_json' => isset($question['options']) ? json_encode($question['options'], JSON_UNESCAPED_UNICODE) : null,
            ':difficulty' => $question['difficulty'] ?? 0,
            ':tags_json' => isset($question['tags']) ? json_encode($question['tags'], JSON_UNESCAPED_UNICODE) : null,
            ':knowledge_point_ids_json' => isset($question['knowledge_point_ids']) ? json_encode($question['knowledge_point_ids'], JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    public function findByFilters(string $examPath, ?string $track, ?string $subject, int $limit): array
    {
        $sql = "SELECT * FROM questions WHERE exam_path = :exam_path";
        $params = [':exam_path' => $examPath];

        if ($track) {
            $sql .= " AND track = :track";
            $params[':track'] = $track;
        }

        if ($subject) {
            $sql .= " AND subject = :subject";
            $params[':subject'] = $subject;
        }

        $sql .= " ORDER BY RAND() LIMIT :limit";
        // PDO limit requires integer binding if emulating prepares is off, but typically generic execute works or bindValue
        // Safe to use bindValue for limit

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();

        $rows = $stmt->fetchAll();
        return array_map([$this, 'decodeRow'], $rows);
    }

    public function findById(string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM questions WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return $this->decodeRow($row);
    }

    private function decodeRow(array $row): array
    {
        $row['options'] = isset($row['options_json']) ? json_decode($row['options_json'], true) : [];
        $row['tags'] = isset($row['tags_json']) ? json_decode($row['tags_json'], true) : [];
        $row['knowledge_point_ids'] = isset($row['knowledge_point_ids_json']) ? json_decode($row['knowledge_point_ids_json'], true) : [];

        unset($row['options_json'], $row['tags_json'], $row['knowledge_point_ids_json']);

        return $row;
    }
}
