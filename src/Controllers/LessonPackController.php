<?php
namespace HmtAdmissions\Api\Controllers;

use HmtAdmissions\Api\Core\Database;
use HmtAdmissions\Api\Middleware\AuthMiddleware;
use PDO;

class LessonPackController {
    public function getToday() {
        // 1. Auth Check
        (new AuthMiddleware())->handle();

        $examPath = $_GET['exam_path'] ?? '';
        $track = $_GET['track'] ?? null;
        $subject = $_GET['subject'] ?? null;
        $date = date('Y-m-d');

        if (!$examPath) {
            http_response_code(400);
            return ['error' => 'Missing exam_path'];
        }

        $pdo = Database::getConnection();

        // 2. Find Pack
        $sql = "SELECT * FROM lesson_packs WHERE date = ? AND exam_path = ?";
        $params = [$date, $examPath];

        if ($track) { $sql .= " AND track = ?"; $params[] = $track; }
        if ($subject) { $sql .= " AND subject = ?"; $params[] = $subject; }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pack = $stmt->fetch();

        if (!$pack) {
            // MVP: Return empty pack structure if not found (or 404)
            // matching the spec structure approximately
            return [
                'id' => "lp-empty-$date",
                'date' => $date,
                'exam_path' => $examPath,
                'items' => []
            ];
        }

        // 3. Fetch Questions
        $qIds = json_decode($pack['question_ids'], true);
        if (empty($qIds)) {
            return [
                'id' => $pack['id'],
                'date' => $pack['date'],
                'exam_path' => $pack['exam_path'],
                'items' => []
            ];
        }

        $placeholders = str_repeat('?,', count($qIds) - 1) . '?';
        // Note: Exclude answer_key and explanation from response
        $qSql = "SELECT id, exam_path, subject, question_type, stem, options, difficulty, tags
                 FROM questions WHERE id IN ($placeholders)";
        $qStmt = $pdo->prepare($qSql);
        $qStmt->execute($qIds);
        $questions = $qStmt->fetchAll();

        // Decode JSON columns for response
        foreach ($questions as &$q) {
            $q['options'] = json_decode($q['options']);
            $q['tags'] = json_decode($q['tags']);
        }

        return [
            'id' => $pack['id'],
            'date' => $pack['date'],
            'exam_path' => $pack['exam_path'],
            'items' => $questions
        ];
    }
}
