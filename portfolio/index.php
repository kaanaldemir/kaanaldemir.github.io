<?php declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

$defaultContent = require __DIR__ . '/content_defaults.php';
$contentPath = __DIR__ . '/data/content.json';
$presetsPath = __DIR__ . '/data/presets.json';

function mergeWithDefaults(array $data, array $defaults): array
{
    foreach ($defaults as $key => $value) {
        if (!array_key_exists($key, $data)) {
            $data[$key] = $value;
            continue;
        }
        if (is_array($value) && is_array($data[$key]) && isAssociativeArray($value)) {
            $data[$key] = mergeWithDefaults($data[$key], $value);
        }
    }
    return $data;
}

function isAssociativeArray(array $array): bool
{
    $keys = array_keys($array);
    return array_keys($keys) !== $keys;
}

$content = $defaultContent;
if (is_readable($contentPath)) {
    $json = file_get_contents($contentPath);
    if ($json !== false) {
        $decoded = json_decode($json, true);
        if (is_array($decoded) && !empty($decoded)) {
            $content = mergeWithDefaults($decoded, $defaultContent);
        }
    }
}

$presets = [];
if (is_readable($presetsPath)) {
    $json = file_get_contents($presetsPath);
    if ($json !== false) {
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            $presets = $decoded;
        }
    }
}

$initialJson = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($initialJson === false) {
    $initialJson = '{}';
}
$defaultJson = json_encode($defaultContent, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($defaultJson === false) {
    $defaultJson = '{}';
}
$presetsJson = json_encode($presets, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($presetsJson === false) {
    $presetsJson = '{}';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portfolio Content Editor</title>
    <link rel="stylesheet" href="../awesome/css/all.min.css">
    <style>
        :root {
            --primary-color: #003366;
            --secondary-color: #01848e;
            --accent-color: #f5a623;
            --background-color: #e6edf2;
            --surface-color: #ffffff;
            --text-color: #333333;
            --text-muted: #555555;
            --card-background: rgba(255, 255, 255, 0.92);
            --border-color: #d0d7de;
            --link-color: var(--primary-color);
            --hover-color: var(--accent-color);
            --input-background: rgba(255, 255, 255, 0.95);
        }

        body.dark-mode {
            --primary-color: #003366;
            --secondary-color: #01848e;
            --accent-color: #f78166;
            --background-color: #0d1117;
            --surface-color: #0d1117;
            --text-color: #c9d1d9;
            --text-muted: #8b949e;
            --card-background: rgba(22, 27, 34, 0.92);
            --border-color: #30363d;
            --link-color: #58a6ff;
            --hover-color: #79c0ff;
            --input-background: rgba(13, 17, 23, 0.92);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            transition: background 0.3s ease, color 0.3s ease;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('../images/ltt.avif');
            background-size: cover;
            background-repeat: repeat-y;
            background-position: center calc(var(--bg-y, 0));
            filter: blur(68px) brightness(1.05);
            transform: scale(1.4) rotate(-18deg);
            z-index: -2;
        }

        body::after {
            top: 0;
            bottom: auto;
            transform: scale(1.4) rotate(18deg) scaleY(-1);
            opacity: 0.75;
            z-index: -3;
            background-position: center calc(-1 * var(--bg-y, 0));
        }

        body.dark-mode::before,
        body.dark-mode::after {
            filter: blur(68px) brightness(0.55);
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.2));
            z-index: -1;
            pointer-events: none;
        }

        a { color: var(--link-color); text-decoration: none; }

        header {
            background: var(--primary-color);
            color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12);
        }

        body.dark-mode header {
            background: rgba(13, 17, 23, 0.92);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .admin-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1080px;
            margin: 0 auto;
            padding: 0.75rem 1.5rem;
        }

        .admin-nav .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .admin-nav .brand h1 {
            margin: 0;
            font-size: 1.35rem;
            letter-spacing: 0.05rem;
            color: #fff;
        }

        body.dark-mode .admin-nav .brand h1 {
            color: var(--link-color);
        }

        .admin-nav .nav-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-nav .nav-links a {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .admin-nav .nav-links a:hover {
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
        }

        body.dark-mode .admin-nav .nav-links a {
            color: var(--text-color);
        }

        body.dark-mode .admin-nav .nav-links a:hover {
            background: rgba(88, 166, 255, 0.15);
            color: var(--link-color);
        }

        .theme-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #fff;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, border 0.2s ease;
        }

        .theme-toggle:hover {
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.2);
        }

        body.dark-mode .theme-toggle {
            background: transparent;
            border-color: var(--border-color);
            color: var(--text-color);
        }

        body.dark-mode .theme-toggle:hover {
            border-color: var(--link-color);
            color: var(--link-color);
            background: rgba(88, 166, 255, 0.12);
        }

        main {
            max-width: 1080px;
            margin: 3rem auto 5rem;
            padding: 0 1.5rem 5rem;
        }

        .editor-group {
            background: var(--card-background);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            padding: 2.25rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 18px 42px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(12px);
        }

        body.dark-mode .editor-group {
            border-color: var(--border-color);
            box-shadow: 0 28px 58px rgba(0, 0, 0, 0.45);
        }

        .editor-group h2 {
            margin: 0 0 1rem;
            font-size: 1.35rem;
            letter-spacing: 0.03rem;
            color: var(--link-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .field-wrapper {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            margin-top: 1rem;
        }

        .field-wrapper:first-of-type { margin-top: 0; }

        .field-wrapper > label {
            font-weight: 600;
            color: var(--text-muted);
        }

        .field-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .field-control input,
        .field-control textarea {
            flex: 1;
            padding: 0.6rem 0.9rem;
            background: var(--input-background);
            color: var(--text-color);
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border 0.2s ease, box-shadow 0.2s ease;
        }

        body.dark-mode .field-control input,
        body.dark-mode .field-control textarea {
            border: 1px solid var(--border-color);
        }

        .field-control textarea {
            min-height: 110px;
            resize: vertical;
        }

        .field-control input:focus,
        .field-control textarea:focus {
            outline: none;
            border-color: var(--link-color);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.25);
        }

        .field-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(88, 166, 255, 0.1);
            border: 1px dashed var(--link-color);
            color: var(--link-color);
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            line-height: 1;
            min-height: 28px;
        }

        .field-reset:hover { background: rgba(88, 166, 255, 0.2); }

        .hint {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        body.dark-mode .hint {
            color: rgba(240, 246, 252, 0.65);
        }

        .two-column {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
        }

        .list-container { margin-top: 1.25rem; }

        .list-entry {
            position: relative;
            background: var(--card-background);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            padding: 1.1rem 1.1rem 1.1rem 1.6rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            cursor: grab;
        }

        body.dark-mode .list-entry {
            border-color: var(--border-color);
            box-shadow: 0 20px 44px rgba(0, 0, 0, 0.45);
        }
        .list-entry.dragging { opacity: 0.7; cursor: grabbing; }
        .drag-handle { position: absolute; top: 0.85rem; right: 0.85rem; background: transparent; border: none; color: var(--link-color); cursor: grab; font-size: 1rem; }
        .remove-entry { position: absolute; top: 0.5rem; left: 0.5rem; background: transparent; border: none; color: #f85149; cursor: pointer; font-size: 0.85rem; }
        .list-entry .list-field { display: flex; flex-direction: column; gap: 0.45rem; margin-top: 0.75rem; }
        .list-entry .list-field label { font-weight: 600; }
        .list-entry .list-field input,
        .list-entry .list-field textarea {
            padding: 0.55rem 0.85rem;
            background: var(--input-background);
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 8px;
            color: var(--text-color);
        }

        body.dark-mode .list-entry .list-field input,
        body.dark-mode .list-entry .list-field textarea {
            border-color: var(--border-color);
            background: rgba(255, 255, 255, 0.03);
        }
        .list-actions { margin-top: 0.75rem; }
        .list-actions button { background: transparent; border: 1px dashed var(--link-color); color: var(--link-color); padding: 0.6rem 1rem; border-radius: 8px; cursor: pointer; }
        .list-actions button:hover { background: rgba(88, 166, 255, 0.15); }

        .button-bar { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2.5rem; }
        .button-bar button { background: var(--primary-color); color: #fff; border: none; padding: 0.75rem 1.6rem; border-radius: 999px; cursor: pointer; font-size: 0.95rem; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .button-bar button.secondary { background: transparent; color: var(--link-color); border: 1px solid var(--link-color); }
        .button-bar button:hover { transform: translateY(-1px); box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2); }

        .preset-bar { display: flex; align-items: center; gap: 0.75rem; margin-top: 1.5rem; }
        .preset-save-btn { background: var(--accent-color); color: #0d1117; border: none; padding: 0.7rem 1.6rem; border-radius: 999px; cursor: pointer; font-size: 0.95rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .preset-save-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 20px rgba(0, 0, 0, 0.25); }
        .preset-dropdown { position: relative; }
        .preset-toggle { background: transparent; border: 1px solid var(--link-color); color: var(--link-color); padding: 0.65rem 1.1rem; border-radius: 999px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; transition: background 0.2s ease, color 0.2s ease; }
        .preset-toggle:hover { background: rgba(88, 166, 255, 0.15); }
        .preset-menu { position: absolute; bottom: calc(100% + 0.5rem); right: 0; background: var(--card-background); border: 1px solid rgba(0, 0, 0, 0.08); border-radius: 12px; box-shadow: 0 16px 32px rgba(0, 0, 0, 0.16); min-width: 220px; display: none; flex-direction: column; overflow: hidden; z-index: 20; }
        .preset-menu.open { display: flex; }
        .preset-item { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: var(--text-color); }
        body.dark-mode .preset-menu { border-color: var(--border-color); box-shadow: 0 24px 48px rgba(0, 0, 0, 0.45); }
        .preset-item .preset-load-btn {
            background: transparent;
            color: var(--link-color);
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .preset-item .preset-load-btn:hover {
            color: var(--hover-color);
        }

        .preset-item .preset-delete-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: transparent;
            border: 1px solid transparent;
            cursor: pointer;
            color: var(--accent-color);
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: background 0.2s ease, color 0.2s ease, border 0.2s ease;
        }

        .preset-item .preset-delete-btn i { font-size: 0.8rem; }

        .preset-item .preset-delete-btn:hover {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.08);
        }

        body.dark-mode .preset-item .preset-delete-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--border-color);
        }
        .preset-empty { padding: 0.7rem 1rem; font-size: 0.85rem; color: var(--text-muted); }
        body.dark-mode .preset-empty { color: rgba(240, 246, 252, 0.6); }

        .status { margin-top: 1.25rem; padding: 0.85rem 1rem; border-radius: 10px; display: none; font-weight: 600; }
        .status.success { display: block; background: rgba(35, 134, 54, 0.2); border: 1px solid rgba(63, 185, 80, 0.5); color: #3fb950; }
        .status.error { display: block; background: rgba(248, 81, 73, 0.12); border: 1px solid rgba(248, 81, 73, 0.35); color: #ff7b72; }

        .scroll-buttons { position: fixed; right: 1.5rem; bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem; z-index: 30; }
        .scroll-buttons button { width: 46px; height: 46px; border-radius: 50%; border: none; background: var(--secondary-color); color: #fff; cursor: pointer; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .scroll-buttons button:hover { transform: translateY(-2px); box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4); }

        @media (max-width: 860px) { .admin-nav .nav-links { display: none; } }
        @media (max-width: 640px) {
            main { padding: 0 1rem 3rem; }
            .button-bar { flex-direction: column; align-items: stretch; }
            .preset-bar { flex-direction: column; align-items: stretch; }
            .preset-save-btn,
            .preset-toggle { width: 100%; justify-content: center; }
            .preset-dropdown { width: 100%; }
            .preset-menu { right: auto; left: 0; }
        }
    </style>
</head>
<body>
<header>
    <nav class="admin-nav">
        <div class="brand">
            <i class="fa fa-pen-nib fa-lg"></i>
            <h1>Portfolio Editor</h1>
        </div>
        <div class="nav-links">
            <a href="#meta"><i class="fa fa-sliders-h"></i> Meta</a>
            <a href="#hero"><i class="fa fa-id-card"></i> Hero</a>
            <a href="#objective"><i class="fa fa-bullseye"></i> Objective</a>
            <a href="#experience"><i class="fa fa-briefcase"></i> Experience</a>
            <a href="#projects"><i class="fa fa-diagram-project"></i> Projects</a>
            <a href="#skills"><i class="fa fa-screwdriver-wrench"></i> Skills</a>
            <a href="#education"><i class="fa fa-graduation-cap"></i> Education</a>
            <a href="#contact"><i class="fa fa-envelope"></i> Contact</a>
            <a href="#about"><i class="fa fa-circle-info"></i> About</a>
        </div>
        <button class="theme-toggle" id="themeToggleBtn" type="button">
            <i class="fa fa-moon"></i>
            <span>Dark</span>
        </button>
    </nav>
</header>

<main>
    <form id="editorForm">
        <section id="meta" class="editor-group">
            <h2><i class="fa fa-sliders-h"></i> Meta</h2>
            <div class="two-column">
                <div class="field-wrapper">
                    <label for="metaTitle">Title</label>
                    <div class="field-control">
                        <input type="text" id="metaTitle" name="metaTitle" data-content-path="meta.title">
                    </div>
                </div>
                <div class="field-wrapper">
                    <label for="metaAuthor">Author</label>
                    <div class="field-control">
                        <input type="text" id="metaAuthor" name="metaAuthor" data-content-path="meta.author">
                    </div>
                </div>
            </div>
            <div class="field-wrapper">
                <label for="metaDescription">Description</label>
                <div class="field-control">
                    <textarea id="metaDescription" name="metaDescription" data-content-path="meta.description"></textarea>
                </div>
            </div>
            <div class="two-column">
                <div class="field-wrapper">
                    <label for="metaCanonical">Canonical URL</label>
                    <div class="field-control">
                        <input type="url" id="metaCanonical" name="metaCanonical" data-content-path="meta.canonical">
                    </div>
                </div>
                <div class="field-wrapper">
                    <label for="metaOgImage">Open Graph Image URL</label>
                    <div class="field-control">
                        <input type="url" id="metaOgImage" name="metaOgImage" data-content-path="meta.og_image">
                    </div>
                </div>
            </div>
        </section>

        <section id="hero" class="editor-group">
            <h2><i class="fa fa-id-card"></i> Hero</h2>
            <div class="two-column">
                <div class="field-wrapper">
                    <label for="heroHeadline">Headline</label>
                    <div class="field-control">
                        <input type="text" id="heroHeadline" name="heroHeadline" data-content-path="hero.headline">
                    </div>
                </div>
                <div class="field-wrapper">
                    <label for="heroName">Name</label>
                    <div class="field-control">
                        <input type="text" id="heroName" name="heroName" data-content-path="hero.name">
                    </div>
                </div>
            </div>
            <div class="two-column">
                <div class="field-wrapper">
                    <label for="heroJobTitle">Job Title</label>
                    <div class="field-control">
                        <input type="text" id="heroJobTitle" name="heroJobTitle" data-content-path="hero.job_title">
                    </div>
                </div>
                <div class="field-wrapper">
                    <label for="heroTypewriter">Typewriter Text</label>
                    <div class="field-control">
                        <input type="text" id="heroTypewriter" name="heroTypewriter" data-content-path="hero.typewriter">
                    </div>
                </div>
            </div>
            <div class="two-column">
                <div class="field-wrapper">
                    <label for="heroProfileImage">Profile Image Path</label>
                    <div class="field-control">
                        <input type="text" id="heroProfileImage" name="heroProfileImage" data-content-path="hero.profile_image">
                    </div>
                    <small class="hint">Relative to /kaan/, e.g. images/me.avif</small>
                </div>
                <div class="field-wrapper">
                    <label for="heroEmail">Email (mailto:)</label>
                    <div class="field-control">
                        <input type="text" id="heroEmail" name="heroEmail" data-content-path="hero.email">
                    </div>
                </div>
            </div>
            <div class="two-column">
                <div class="field-wrapper">
                    <label for="heroPhone">Phone (tel:)</label>
                    <div class="field-control">
                        <input type="text" id="heroPhone" name="heroPhone" data-content-path="hero.phone">
                    </div>
                </div>
                <div class="field-wrapper">
                    <label for="heroLocationLocality">City</label>
                    <div class="field-control">
                        <input type="text" id="heroLocationLocality" name="heroLocationLocality" data-content-path="hero.location.locality">
                    </div>
                </div>
            </div>
            <div class="two-column">
                <div class="field-wrapper">
                    <label for="heroLocationRegion">Region</label>
                    <div class="field-control">
                        <input type="text" id="heroLocationRegion" name="heroLocationRegion" data-content-path="hero.location.region">
                    </div>
                </div>
                <div class="field-wrapper">
                    <label for="heroLocationCountry">Country</label>
                    <div class="field-control">
                        <input type="text" id="heroLocationCountry" name="heroLocationCountry" data-content-path="hero.location.country">
                    </div>
                </div>
            </div>

            <h3>Hero Links</h3>
            <div class="list-container" id="heroContacts"></div>
            <div class="list-actions">
                <button type="button" data-action="add-hero-contact"><i class="fa fa-plus"></i> Add hero link</button>
            </div>
        </section>

        <section id="objective" class="editor-group">
            <h2><i class="fa fa-bullseye"></i> Objective</h2>
            <div class="field-wrapper">
                <label for="objectiveTitle">Title</label>
                <div class="field-control">
                    <input type="text" id="objectiveTitle" name="objectiveTitle" data-content-path="objective.title">
                </div>
            </div>
            <div class="field-wrapper">
                <label for="objectiveNavLabel">Navigation label</label>
                <div class="field-control">
                    <input type="text" id="objectiveNavLabel" name="objectiveNavLabel" data-content-path="objective.nav_label">
                </div>
            </div>
            <div class="field-wrapper">
                <label for="objectiveBody">Description</label>
                <div class="field-control">
                    <textarea id="objectiveBody" name="objectiveBody" data-content-path="objective.body"></textarea>
                </div>
            </div>
        </section>

        <section id="experience" class="editor-group">
            <h2><i class="fa fa-briefcase"></i> Experience</h2>
            <div class="field-wrapper">
                <label for="experienceTitle">Title</label>
                <div class="field-control">
                    <input type="text" id="experienceTitle" name="experienceTitle" data-content-path="experience.title">
                </div>
            </div>
            <div class="field-wrapper">
                <label for="experienceNavLabel">Navigation label</label>
                <div class="field-control">
                    <input type="text" id="experienceNavLabel" name="experienceNavLabel" data-content-path="experience.nav_label">
                </div>
            </div>
            <div class="list-container" id="experienceJobs"></div>
            <div class="list-actions">
                <button type="button" data-action="add-job"><i class="fa fa-plus"></i> Add role</button>
            </div>
        </section>
        <section id="projects" class="editor-group">
            <h2><i class="fa fa-diagram-project"></i> Projects</h2>
            <div class="field-wrapper">
                <label for="projectsTitle">Title</label>
                <div class="field-control">
                    <input type="text" id="projectsTitle" name="projectsTitle" data-content-path="projects.title">
                </div>
            </div>
            <div class="field-wrapper">
                <label for="projectsNavLabel">Navigation label</label>
                <div class="field-control">
                    <input type="text" id="projectsNavLabel" name="projectsNavLabel" data-content-path="projects.nav_label">
                </div>
            </div>
            <div class="list-container" id="projectsList"></div>
            <div class="list-actions">
                <button type="button" data-action="add-project"><i class="fa fa-plus"></i> Add project</button>
            </div>
        </section>

        <section id="skills" class="editor-group">
            <h2><i class="fa fa-screwdriver-wrench"></i> Skills</h2>
            <div class="field-wrapper">
                <label for="skillsTitle">Title</label>
                <div class="field-control">
                    <input type="text" id="skillsTitle" name="skillsTitle" data-content-path="skills.title">
                </div>
            </div>
            <div class="field-wrapper">
                <label for="skillsNavLabel">Navigation label</label>
                <div class="field-control">
                    <input type="text" id="skillsNavLabel" name="skillsNavLabel" data-content-path="skills.nav_label">
                </div>
            </div>
            <div class="list-container" id="skillsList"></div>
            <div class="list-actions">
                <button type="button" data-action="add-skill"><i class="fa fa-plus"></i> Add skill</button>
            </div>
        </section>

        <section id="education" class="editor-group">
            <h2><i class="fa fa-graduation-cap"></i> Education</h2>
            <div class="field-wrapper">
                <label for="educationTitle">Title</label>
                <div class="field-control">
                    <input type="text" id="educationTitle" name="educationTitle" data-content-path="education.title">
                </div>
            </div>
            <div class="field-wrapper">
                <label for="educationNavLabel">Navigation label</label>
                <div class="field-control">
                    <input type="text" id="educationNavLabel" name="educationNavLabel" data-content-path="education.nav_label">
                </div>
            </div>
            <div class="list-container" id="educationList"></div>
            <div class="list-actions">
                <button type="button" data-action="add-education"><i class="fa fa-plus"></i> Add entry</button>
            </div>
        </section>

        <section id="contact" class="editor-group">
            <h2><i class="fa fa-envelope"></i> Contact</h2>
            <div class="field-wrapper">
                <label for="contactTitle">Title</label>
                <div class="field-control">
                    <input type="text" id="contactTitle" name="contactTitle" data-content-path="contact.title">
                </div>
            </div>
            <div class="field-wrapper">
                <label for="contactNavLabel">Navigation label</label>
                <div class="field-control">
                    <input type="text" id="contactNavLabel" name="contactNavLabel" data-content-path="contact.nav_label">
                </div>
            </div>
            <div class="list-container" id="contactMethods"></div>
            <div class="list-actions">
                <button type="button" data-action="add-contact-method"><i class="fa fa-plus"></i> Add contact method</button>
            </div>
        </section>

        <section id="about" class="editor-group">
            <h2><i class="fa fa-circle-info"></i> About</h2>
            <div class="field-wrapper">
                <label for="aboutTitle">Title</label>
                <div class="field-control">
                    <input type="text" id="aboutTitle" name="aboutTitle" data-content-path="about.title">
                </div>
            </div>
            <div class="field-wrapper">
                <label for="aboutBody">Description</label>
                <div class="field-control">
                    <textarea id="aboutBody" name="aboutBody" data-content-path="about.body"></textarea>
                </div>
            </div>
            <div class="field-wrapper">
                <label for="aboutNote">Note</label>
                <div class="field-control">
                    <input type="text" id="aboutNote" name="aboutNote" data-content-path="about.note">
                </div>
            </div>
            <h3>PageSpeed Metrics</h3>
            <div class="list-container" id="pageSpeedList"></div>
            <div class="list-actions">
                <button type="button" data-action="add-pagespeed"><i class="fa fa-plus"></i> Add metric</button>
            </div>
        </section>

        <section id="footer" class="editor-group">
            <h2><i class="fa fa-copyright"></i> Footer</h2>
            <div class="field-wrapper">
                <label for="footerText">Footer text</label>
                <div class="field-control">
                    <input type="text" id="footerText" name="footerText" data-content-path="footer.text">
                </div>
            </div>
        </section>

        <div class="button-bar">
            <button type="submit"><i class="fa fa-save"></i> Save changes</button>
            <button type="button" class="secondary" id="resetButton"><i class="fa fa-rotate-left"></i> Reset</button>
            <button type="button" class="secondary" id="restoreDefaultsButton"><i class="fa fa-seedling"></i> Restore defaults</button>
        </div>

        <div class="preset-bar">
            <button type="button" class="preset-save-btn" id="savePresetButton"><i class="fa fa-bookmark"></i> Save preset</button>
            <div class="preset-dropdown">
                <button type="button" class="preset-toggle" id="presetsToggle" aria-expanded="false">
                    <i class="fa fa-layer-group"></i>
                    Presets
                </button>
                <div class="preset-menu" id="presetMenu"></div>
            </div>
        </div>

        <div id="statusMessage" class="status"></div>
    </form>
</main>

<div class="scroll-buttons">
    <button type="button" id="upButton" aria-label="Scroll up"><i class="fa fa-arrow-up"></i></button>
    <button type="button" id="downButton" aria-label="Scroll down"><i class="fa fa-arrow-down"></i></button>
</div>

<script>
    window.initialContent = <?= $initialJson ?>;
    window.defaultContent = <?= $defaultJson ?>;
    window.initialPresets = <?= $presetsJson ?>;
</script>
<script src="editor.js"></script>
</body>
</html>




