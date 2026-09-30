<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Forventer: $title, $description. Valgfrit: $canonical, $socialDescription, $robots, $schema,
// $ogImage, $ogImageAlt
$canonical = $canonical ?? null;
$socialDescription = $socialDescription ?? $description;
$robots = $robots ?? 'index, follow, max-image-preview:large, max-snippet:-1';
$ogImage = $ogImage ?? SITE_URL . 'og-image.jpg';
$ogImageAlt = $ogImageAlt ?? $title;
?>
<!DOCTYPE html>
<html lang="da" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($title); ?></title>
  <meta name="description" content="<?php echo e($description); ?>">
  <meta name="robots" content="<?php echo e($robots); ?>">
  <meta name="theme-color" content="#c8102e">

<?php if ($canonical): ?>
  <!-- Canonical URL -->
  <link rel="canonical" href="<?php echo e($canonical); ?>" />
<?php endif; ?>

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="manifest" href="/site.webmanifest">

  <!-- Preconnect for performance -->
  <link rel="preconnect" href="https://cdn.tailwindcss.com">
  <link rel="preconnect" href="https://www.google.com">
  <link rel="dns-prefetch" href="https://api.github.com">

  <!-- Open Graph / Facebook / LinkedIn -->
  <meta property="og:type" content="website">
<?php if ($canonical): ?>
  <meta property="og:url" content="<?php echo e($canonical); ?>">
<?php endif; ?>
  <meta property="og:title" content="<?php echo e($title); ?>">
  <meta property="og:description" content="<?php echo e($socialDescription); ?>">
  <meta property="og:image" content="<?php echo e($ogImage); ?>">
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="<?php echo e($ogImageAlt); ?>">
  <meta property="og:locale" content="da_DK">
  <meta property="og:site_name" content="Den danske tech stack">

  <!-- Twitter / X -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo e($title); ?>">
  <meta name="twitter:description" content="<?php echo e($socialDescription); ?>">
  <meta name="twitter:image" content="<?php echo e($ogImage); ?>">
  <meta name="twitter:image:alt" content="<?php echo e($ogImageAlt); ?>">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
<?php if (!empty($schema)): ?>

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
<?php echo $schema; ?>

  </script>
<?php endif; ?>
</head>
<body class="bg-white dark:bg-gray-900">
