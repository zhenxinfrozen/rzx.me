<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/Models/video_data.php';

header('Content-Type: text/html; charset=UTF-8');

$videoGroups = get_all_video_groups(false);

echo "<h1>Video Groups Debug</h1>";
echo "<pre>";
foreach ($videoGroups as $groupId => $groupData) {
    echo "Group: $groupId\n";
    echo "  Videos: " . count($groupData['videos']) . "\n";
    if (!empty($groupData['videos'][0])) {
        echo "  First video: " . $groupData['videos'][0]['file'] . "\n";
        echo "  Poster: " . ($groupData['videos'][0]['poster'] ?? 'none') . "\n";
    }
    echo "\n";
}
echo "</pre>";

$configPath = __DIR__ . '/../../app/Config/video_gallery_sort.php';
$config = include $configPath;

echo "<h2>Config</h2>";
echo "<pre>";
print_r($config);
echo "</pre>";
