<?php

require_once __DIR__ . '/../src/Bootstrap.php';

use App\Bootstrap;
use App\Repositories\MySql\MySqlQuestionRepository;

// Initialize app to load config/autoload
Bootstrap::init();

$sourceFile = __DIR__ . '/../../hmt-admissions-spec/examples/lesson-pack-today.response.json';

if (!file_exists($sourceFile)) {
    echo "Error: Source file not found: $sourceFile\n";
    exit(1);
}

$json = file_get_contents($sourceFile);
$data = json_decode($json, true);

if (!isset($data['items']) || !is_array($data['items'])) {
    echo "Error: Invalid JSON structure. 'items' array missing.\n";
    exit(1);
}

$repo = new MySqlQuestionRepository();
$count = 0;

foreach ($data['items'] as $item) {
    try {
        $repo->save($item);
        $count++;
    } catch (\Exception $e) {
        echo "Error saving question {$item['id']}: " . $e->getMessage() . "\n";
    }
}

echo "Imported $count questions.\n";
