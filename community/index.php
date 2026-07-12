<?php
require_once __DIR__ . '/inc/layout.php';

$page = max(1, (int) ($_GET['p'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$st = db()->prepare(
    "SELECT p.id, p.type, p.channel, p.title, p.slug, p.excerpt, p.body, p.approved_at, u.username,
            (SELECT GROUP_CONCAT(DISTINCT kind ORDER BY kind SEPARATOR ',') FROM media m WHERE m.post_id = p.id) AS kinds
     FROM posts p JOIN users u ON u.id = p.user_id
     WHERE p.status = 'approved' AND p.channel = 'community'
     ORDER BY p.approved_at DESC LIMIT :limit OFFSET :offset"
);
$st->bindValue(':limit', $limit + 1, PDO::PARAM_INT);
$st->bindValue(':offset', $offset, PDO::PARAM_INT);
$st->execute();
$posts = $st->fetchAll();

$hasMore = false;
if (count($posts) > $limit) {
    $hasMore = true;
    array_pop($posts);
}

page_head('Community', 'Reader-contributed articles — reviewed before publication', 'community', 'COMMUNITY');
?>
<?php if (!$posts): ?>
  <div class="empty-state">No community posts yet. <b>Be the first</b> — <a href="register.php">join</a> and <a href="write.php">write</a>.</div>
<?php else: ?>
<?php foreach ($posts as $p): ?>
  <article class="entry">
    <div class="stamp">
      <div class="date"><?= e(date('d/m/Y', strtotime($p['approved_at']))) ?></div>
      <div class="no type-line">COMMUNITY <?= e(strtoupper($p['type'])) ?></div>
    </div>
    <div class="body">
      <div class="serif-title"><?= e($p['title']) ?></div>
      <div class="serif-excerpt">
        <?= e($p['excerpt'] !== '' ? $p['excerpt'] : mb_substr(preg_replace('/\s+/', ' ', $p['body']), 0, 140) . '…') ?>
      </div>
      <div class="micro">
        <span>BY <b><?= e($p['username']) ?></b></span>
<?php if (!empty($p['kinds'])): ?>
<?php foreach (explode(',', $p['kinds']) as $k): ?>
        <span><?= e(strtoupper($k)) ?> ATTACHED</span>
<?php endforeach; ?>
<?php endif; ?>
      </div>
      <a class="more" href="<?= e(post_url($p)) ?>">Open post →</a>
    </div>
  </article>
<?php endforeach; ?>
<?php endif; ?>

<?php if ($page > 1 || $hasMore): ?>
  <nav class="cross-nav">
<?php if ($hasMore): ?>
    <a href="?p=<?= $page + 1 ?>">← Older</a>
<?php else: ?>
    <a href="#" class="disabled">← Older</a>
<?php endif; ?>
    <span class="spacer"></span>
<?php if ($page > 1): ?>
    <a href="?p=<?= $page - 1 ?>">Newer →</a>
<?php else: ?>
    <a href="#" class="disabled">Newer →</a>
<?php endif; ?>
  </nav>
<?php endif; ?>

<?php
page_foot();
