## Description
Website Tester is a simple PHP-based dashboard for previewing multiple website projects from one place. It helps web developers review a website’s overall structure, design, and functionality before deployment, allowing potential bugs to be identified and fixed more efficiently.

## Features

- Automatically scans project folders
- Search project by name
- Preview project using iframe
- Open project directly
- Simple and lightweight interface

## Technologies Used

- PHP
- HTML
- CSS
- JavaScript

## Folder Structure

```txt
website-tester/
├── index.php
├── README.md
└── example-client/
    └── index.html

  *How It Works

Each website project is stored inside its own folder.
If the folder contains an index.html or index.php file, it will be detected and shown in the dashboard.

Example:

example-client/
└── index.html

Requirements
PHP 7.4 or newer
Local server such as XAMPP, Laragon, or a PHP-supported hosting
Usage
Put your website folders inside the main project directory.
Run the project using a local PHP server.
Open index.php in your browser.
Search and preview your projects.

Notes: This project is intended for local testing and portfolio demonstration.

php
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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Website Tester</title>

  <style>
    body {
      margin: 0;
      padding: 24px;
      background: #f5f5f5;
      color: #111;
      font-family: Arial, sans-serif;
    }

    h1 {
      margin-top: 0;
      font-size: 24px;
    }

    p {
      color: #555;
    }

    form {
      display: flex;
      gap: 8px;
      max-width: 480px;
      margin-top: 20px;
    }

    input {
      flex: 1;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    button,
    a {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      background: #fff;
      color: #111;
      font-size: 14px;
      text-decoration: none;
      cursor: pointer;
    }

    button:hover,
    a:hover {
      background: #eee;
    }

    #result {
      max-width: 480px;
      margin-top: 16px;
    }

    .item {
      padding: 12px;
      margin-bottom: 8px;
      border: 1px solid #ddd;
      border-radius: 6px;
      background: #fff;
      cursor: pointer;
    }

    .item:hover {
      background: #f0f0f0;
    }

    small {
      display: block;
      margin-top: 4px;
      color: #777;
      word-break: break-all;
    }

    #info {
      color: #666;
      font-size: 14px;
    }

    #viewer {
      display: none;
      position: fixed;
      inset: 0;
      background: #fff;
      z-index: 10;
    }

    #bar {
      min-height: 44px;
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px;
      border-bottom: 1px solid #ddd;
      background: #f8f8f8;
      flex-wrap: wrap;
    }

    #projectName {
      color: #333;
      font-size: 14px;
      margin-left: 4px;
    }

    iframe {
      width: 100%;
      height: calc(100% - 61px);
      border: 0;
      background: #fff;
    }

    @media (max-width: 500px) {
      body {
        padding: 16px;
      }

      form,
      #result {
        max-width: 100%;
      }

      iframe {
        height: calc(100% - 90px);
      }
    }
  </style>
</head>
<body>
  <h1>Website Tester</h1>
  <p>Search and preview website projects from one dashboard.</p>

  <form onsubmit="event.preventDefault(); searchProject();">
    <input id="q" type="text" placeholder="Search project..." autocomplete="off" autofocus>
    <button type="submit">Search</button>
  </form>

  <div id="result"></div>
  <p id="info"></p>

  <div id="viewer">
    <div id="bar">
      <button onclick="closeViewer()">Back</button>
      <button onclick="reloadViewer()">Reload</button>
      <a id="direct" href="#" target="_blank">Open Directly</a>
      <span id="projectName"></span>
    </div>

    <iframe id="frame"></iframe>
  </div>

  <script>
    const projects = <?php echo json_encode($projects, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const q = document.getElementById('q');
    const result = document.getElementById('result');
    const info = document.getElementById('info');
    const viewer = document.getElementById('viewer');
    const frame = document.getElementById('frame');
    const direct = document.getElementById('direct');
    const projectName = document.getElementById('projectName');

    let current = '';

    function searchProject() {
      const key = q.value.toLowerCase().trim();

      result.innerHTML = '';
      info.textContent = '';

      if (!projects.length) {
        info.textContent = 'No project found.';
        return;
      }

      if (!key) return;

      const found = projects.filter(project => {
        return (project.name + ' ' + project.folder).toLowerCase().includes(key);
      });

      if (!found.length) {
        info.textContent = 'No result.';
        return;
      }

      found.forEach(project => {
        const item = document.createElement('div');
        item.className = 'item';

        item.innerHTML = `
          <b>${project.name}</b>
          <small>./${project.folder}/</small>
        `;

        item.onclick = () => openProject(project);
        result.appendChild(item);
      });
    }

    function openProject(project) {
      current = './' + project.folder + '/';
      frame.src = current;
      direct.href = current;
      projectName.textContent = project.name;
      viewer.style.display = 'block';
    }

    function closeViewer() {
      viewer.style.display = 'none';
      frame.src = '';
      current = '';
    }

    function reloadViewer() {
      if (!current) return;
      frame.src = current + '?v=' + Date.now();
    }

    q.addEventListener('input', searchProject);

    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') {
        closeViewer();
      }
    });
  </script>
</body>
</html>
