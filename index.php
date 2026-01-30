<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$products = load_products();
$originalProducts = load_original_products();
?>
<!DOCTYPE html>
<html lang="da" class="scroll-smooth">
<?php include __DIR__ . '/includes/head.php'; ?>
<body class="bg-white dark:bg-gray-900">
  <?php include __DIR__ . '/partials/hero.php'; ?>
  <?php include __DIR__ . '/partials/products.php'; ?>
  <?php include __DIR__ . '/partials/supporters.php'; ?>
  <?php include __DIR__ . '/partials/founders.php'; ?>
  <?php include __DIR__ . '/partials/contributors.php'; ?>
  <?php include __DIR__ . '/partials/quote.php'; ?>
  <?php include __DIR__ . '/partials/share.php'; ?>
  <?php include __DIR__ . '/partials/footer.php'; ?>
  <script src="assets/js/app.js"></script>
</body>
</html>
