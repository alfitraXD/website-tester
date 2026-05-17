<?php
$ROOT = __DIR__;

function safeName($name) {
  return preg_match('/^[a-zA-Z0-9._-]+$/', $name) 
    && strpos($name, '..') === false 
    && $name !== '' 
    && $name[0] !== '_';
}

function prettyName($name) {
  return ucwords(str_replace(['-', '_'], ' ', $name));
}

function scanProjects($root) {
  $list = [];

  foreach (scandir($root) as $item) {
    if ($item === '.' || $item === '..') continue;
    if (!safeName($item)) continue;

    $dir = $root . '/' . $item;

    if (!is_dir($dir)) continue;

    if (file_exists($dir . '/index.html') || file_exists($dir . '/index.php')) {
      $list[] = [
        'name' => prettyName($item),
        'folder' => $item
      ];
    }
  }

  usort($list, function($a, $b) {
    return strtolower($a['name']) <=> strtolower($b['name']);
  });

  return $list;
}

$projects = scanProjects($ROOT);
?>
