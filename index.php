<?php declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

$defaultContent = require __DIR__ . '/portfolio/content_defaults.php';
$contentPath = __DIR__ . '/portfolio/data/content.json';
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

$meta = $content['meta'] ?? [];
$hero = $content['hero'] ?? [];
$objective = $content['objective'] ?? [];
$experience = $content['experience'] ?? [];
$projects = $content['projects'] ?? [];
$skills = $content['skills'] ?? [];
$education = $content['education'] ?? [];
$contact = $content['contact'] ?? [];
$about = $content['about'] ?? [];
$footer = $content['footer'] ?? [];

$heroContacts = $hero['contacts'] ?? [];
$resumeLink = 'Kaan Aldemir - CV.pdf';
$resumeLabel = 'Resume';
foreach ($heroContacts as $contactItem) {
    $icon = $contactItem['icon'] ?? '';
    if (is_string($icon) && strpos($icon, 'fa-file') !== false && !empty($contactItem['url'])) {
        $resumeLink = $contactItem['url'];
        $resumeLabel = $contactItem['title'] ?? $resumeLabel;
        break;
    }
}

$personSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $hero['name'] ?? '',
    'jobTitle' => $hero['job_title'] ?? '',
    'url' => $meta['canonical'] ?? '',
    'sameAs' => array_values(array_filter(array_map(
        static function (array $contactItem): ?string {
            $url = $contactItem['url'] ?? '';
            return (is_string($url) && strpos($url, 'http') === 0) ? $url : null;
        },
        $heroContacts
    ))),
    'email' => $hero['email'] ?? '',
    'telephone' => $hero['phone'] ?? '',
];

if (!empty($hero['location']) && is_array($hero['location'])) {
    $personSchema['address'] = [
        '@type' => 'PostalAddress',
        'addressLocality' => $hero['location']['locality'] ?? '',
        'addressRegion' => $hero['location']['region'] ?? '',
        'addressCountry' => $hero['location']['country'] ?? '',
    ];
}

$websiteSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'url' => $meta['canonical'] ?? '',
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => ($meta['canonical'] ?? '') . '?s={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
];

function esc(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function escAttr(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
}

function linkTarget(string $url): string
{
    if (preg_match('/\.pdf($|\?)/i', $url)) {
        return '_blank';
    }
    return (strpos($url, 'http') === 0) ? '_blank' : '_self';
}

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

$objectiveTitle = $objective['title'] ?? 'Objective';
$experienceTitle = $experience['title'] ?? 'Experience';
$projectsTitle = $projects['title'] ?? 'Projects';
$skillsTitle = $skills['title'] ?? 'Skills';
$educationTitle = $education['title'] ?? 'Education';
$contactTitle = $contact['title'] ?? 'Contact';
$aboutTitle = $about['title'] ?? 'About This Site';
$objectiveNav = $objective['nav_label'] ?? $objectiveTitle;
$experienceNav = $experience['nav_label'] ?? $experienceTitle;
$projectsNav = $projects['nav_label'] ?? $projectsTitle;
$skillsNav = $skills['nav_label'] ?? $skillsTitle;
$educationNav = $education['nav_label'] ?? $educationTitle;
$contactNav = $contact['nav_label'] ?? $contactTitle;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($meta['title'] ?? 'Portfolio') ?></title>
    <?php if (!empty($meta['description'])): ?>
        <meta name="description" content="<?= escAttr($meta['description']) ?>">
    <?php endif; ?>
    <?php if (!empty($meta['author'])): ?>
        <meta name="author" content="<?= escAttr($meta['author']) ?>">
    <?php endif; ?>
    <?php if (!empty($meta['canonical'])): ?>
        <link rel="canonical" href="<?= escAttr($meta['canonical']) ?>">
        <meta property="og:url" content="<?= escAttr($meta['canonical']) ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">
    <?php if (!empty($meta['title'])): ?>
        <meta property="og:title" content="<?= escAttr($meta['title']) ?>">
    <?php endif; ?>
    <?php if (!empty($meta['description'])): ?>
        <meta property="og:description" content="<?= escAttr($meta['description']) ?>">
    <?php endif; ?>
    <?php if (!empty($meta['og_image'])): ?>
        <meta property="og:image" content="<?= escAttr($meta['og_image']) ?>">
    <?php endif; ?>
    <script type="application/ld+json">
<?= json_encode($personSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n" ?>
    </script>
    <script type="application/ld+json">
<?= json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n" ?>
    </script>
    <link rel="icon" type="image/png" sizes="16x16" href="web/16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="web/32.png">
    <link rel="preload" as="style" href="awesome/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="awesome/css/all.min.css">
    </noscript>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <nav>
        <div class="container nav-container">
            <div class="logo">
                <h1 class="main-header"><?= esc($hero['name'] ?? '') ?></h1>
            </div>
            <button id="hamburger-menu" class="hamburger-menu" aria-label="Toggle Navigation">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            <div class="nav-links">
                <a href="#objective"><i class="fa fa-bullseye"></i> <?= esc($objectiveNav) ?></a>
                <a href="#experience"><i class="fa fa-briefcase"></i> <?= esc($experienceNav) ?></a>
                <a href="#projects"><i class="fa fa-tasks"></i> <?= esc($projectsNav) ?></a>
                <a href="#skills"><i class="fa fa-tools"></i> <?= esc($skillsNav) ?></a>
                <a href="#education"><i class="fa fa-graduation-cap"></i> <?= esc($educationNav) ?></a>
                <a href="#contact"><i class="fa fa-envelope"></i> <?= esc($contactNav) ?></a>
                <?php $resumeTarget = linkTarget($resumeLink); ?>
                <a class="view-resume"
                   href="<?= escAttr($resumeLink) ?>"
                   target="<?= escAttr($resumeTarget) ?>"
                   <?php if ($resumeTarget === '_blank'): ?>rel="noopener noreferrer"<?php endif; ?>>
                    <i class="fa fa-file"></i> <?= esc($resumeLabel) ?>
                </a>
            </div>
        </div>
    </nav>
</header>

<div id="menu-overlay"></div>

<section class="hero">
    <div class="container">
        <?php
        $profileLink = $meta['canonical'] ?? '#';
        foreach ($heroContacts as $contactItem) {
            if (isset($contactItem['icon']) && strpos($contactItem['icon'], 'github') !== false && !empty($contactItem['url'])) {
                $profileLink = $contactItem['url'];
                break;
            }
        }
        ?>
        <?php $profileTarget = linkTarget($profileLink); ?>
        <a href="<?= escAttr($profileLink) ?>"
           target="<?= escAttr($profileTarget) ?>"
           <?php if ($profileTarget === '_blank'): ?>rel="noopener noreferrer"<?php endif; ?>>
            <img src="<?= escAttr($hero['profile_image'] ?? 'images/me.avif') ?>"
                 alt="Photo of <?= escAttr($hero['name'] ?? 'Kaan Aldemir') ?>"
                 class="profile-pic">
        </a>
        <h2><?= esc($hero['headline'] ?? '') ?> <span class="highlight"><?= esc($hero['name'] ?? '') ?></span></h2>
        <p id="typewriterText"><?= esc($hero['typewriter'] ?? '') ?></p>
        <?php if (!empty($heroContacts)): ?>
            <div class="hero-contacts">
                <?php foreach ($heroContacts as $contactItem): ?>
                    <?php
                    $url = $contactItem['url'] ?? '#';
                    $title = $contactItem['title'] ?? '';
                    $icon = $contactItem['icon'] ?? '';
                    ?>
                    <?php $contactTarget = linkTarget($url); ?>
                    <a href="<?= escAttr($url) ?>"
                       title="<?= escAttr($title) ?>"
                       target="<?= escAttr($contactTarget) ?>"
                       <?php if ($contactTarget === '_blank'): ?>rel="noopener noreferrer"<?php endif; ?>>
                        <i class="<?= escAttr($icon) ?>"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section id="objective" class="card-to-animate content-section">
    <div class="container card">
        <h3><?= esc($objectiveTitle) ?></h3>
        <p><?= esc($objective['body'] ?? '') ?></p>
    </div>
</section>

<section id="experience" class="card-to-animate content-section">
    <div class="container card">
        <h3><?= esc($experienceTitle) ?></h3>
        <?php if (!empty($experience['jobs']) && is_array($experience['jobs'])): ?>
            <?php foreach ($experience['jobs'] as $job): ?>
                <div class="job">
                    <h4><?= esc($job['position'] ?? '') ?></h4>
                    <p><em><?= esc($job['location'] ?? '') ?></em></p>
                    <?php if (!empty($job['bullets']) && is_array($job['bullets'])): ?>
                        <ul>
                            <?php foreach ($job['bullets'] as $bullet): ?>
                                <li><?= esc($bullet) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No experience entries available yet.</p>
        <?php endif; ?>
    </div>
</section>

<section id="projects" class="card-to-animate content-section">
    <div class="container card">
        <h3><?= esc($projectsTitle) ?></h3>
        <?php if (!empty($projects['items']) && is_array($projects['items'])): ?>
            <ul>
                <?php foreach ($projects['items'] as $project): ?>
                    <li>
                        <strong><?= esc($project['name'] ?? '') ?>:</strong>
                        <?= esc($project['description'] ?? '') ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

<section id="skills" class="card-to-animate content-section">
    <div class="container card">
        <h3><?= esc($skillsTitle) ?></h3>
        <?php if (!empty($skills['items']) && is_array($skills['items'])): ?>
            <ul>
                <?php foreach ($skills['items'] as $skill): ?>
                    <li>
                        <strong><?= esc($skill['name'] ?? '') ?><?= empty($skill['description']) ? '' : ':' ?></strong>
                        <?= esc($skill['description'] ?? '') ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

<section id="education" class="card-to-animate content-section">
    <div class="container card">
        <h3><?= esc($educationTitle) ?></h3>
        <?php if (!empty($education['entries']) && is_array($education['entries'])): ?>
            <?php foreach ($education['entries'] as $entry): ?>
                <p><strong><?= esc($entry['text'] ?? '') ?></strong></p>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section id="contact" class="card-to-animate content-section">
    <div class="container card contact-wrapper">
        <div class="contact-info">
            <h3><?= esc($contactTitle) ?></h3>
            <?php if (!empty($contact['methods']) && is_array($contact['methods'])): ?>
                <?php foreach ($contact['methods'] as $method): ?>
                    <?php
                    $url = $method['url'] ?? '';
                    $value = $method['value'] ?? '';
                    ?>
                    <p>
                        <i class="<?= escAttr($method['icon'] ?? '') ?>"></i>
                        <strong><?= esc($method['label'] ?? '') ?>:</strong>
                        <?php if (!empty($url)): ?>
                            <?php $methodTarget = linkTarget($url); ?>
                            <a href="<?= escAttr($url) ?>"
                               target="<?= escAttr($methodTarget) ?>"
                               <?php if ($methodTarget === '_blank'): ?>rel="noopener noreferrer"<?php endif; ?>>
                                <?= esc($value) ?>
                            </a>
                        <?php else: ?>
                            <?= esc($value) ?>
                        <?php endif; ?>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="contact-form">
            <h3>Send a Message</h3>
            <div class="form-row">
                <label class="visually-hidden" for="msgName">Name</label>
                <input id="msgName" maxlength="50" placeholder="John Doe">
                <div class="email-group">
                    <label class="visually-hidden" for="msgEmail1">Email part 1</label>
                    <input id="msgEmail1" maxlength="50" placeholder="john">
                    <span>@</span>
                    <label class="visually-hidden" for="msgEmail2">Email part 2</label>
                    <input id="msgEmail2" maxlength="50" placeholder="doe.com" autocomplete="new-password">
                </div>
                <div class="tel-group">
                    <label class="visually-hidden" for="msgCountryCode">Country Code</label>
                    <input id="msgCountryCode" maxlength="5" pattern="^\\+?[0-9]{1,5}$" value="+90">
                    <label class="visually-hidden" for="msgTel">Telephone</label>
                    <input id="msgTel" maxlength="10" placeholder="5555555555" pattern="[0-9]{10}">
                </div>
            </div>
            <div class="form-row">
                <label class="visually-hidden" for="msgText">Message</label>
                <textarea id="msgText" maxlength="500" placeholder="Let's hear it!"></textarea>
            </div>
            <button id="sendButton" disabled>Send Message</button>
        </div>
    </div>
</section>

<section id="about-site" class="card-to-animate content-section">
    <div class="container card note">
        <h3><?= esc($aboutTitle) ?></h3>
        <p><?= esc($about['body'] ?? '') ?></p>
        <?php if (!empty($about['page_speed']) && is_array($about['page_speed'])): ?>
            <div class="pagespeed-widget" id="pagespeed-widget">
                <?php foreach ($about['page_speed'] as $metric): ?>
                    <?php
                    $metricLabel = $metric['label'] ?? '';
                    $metricId = 'ps-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $metricLabel));
                    ?>
                    <div class="metric" id="<?= escAttr($metricId) ?>">
                        <a href="<?= escAttr($metric['url'] ?? '#') ?>" target="_blank">
                            <div class="circle">
                                <span class="score"><?= esc($metric['score'] ?? '') ?></span>
                            </div>
                            <p><?= esc($metricLabel) ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($about['note'])): ?>
            <p class="pagespeed-indicator"><?= esc($about['note']) ?></p>
        <?php endif; ?>
    </div>
</section>

<footer>
    <div class="container">
        <p><?= esc($footer['text'] ?? '') ?></p>
    </div>
</footer>

<div id="toast-container"></div>
<button id="themeToggleBtn" class="theme-toggle-btn" aria-label="Toggle theme">
    <i class="fa fa-moon"></i>
</button>
<div class="scroll-buttons-container">
    <button id="upButton" class="scroll-button" aria-label="Scroll up">
        <i class="fa fa-arrow-up"></i>
    </button>
    <button id="downButton" class="scroll-button" aria-label="Scroll down">
        <i class="fa fa-arrow-down"></i>
    </button>
</div>

<script src="script.js"></script>
</body>
</html>
