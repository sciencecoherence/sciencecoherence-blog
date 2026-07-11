<?php
require_once __DIR__ . '/community/inc/layout.php';

$rows = db()->query(
    "SELECT p.id, p.type, p.channel, p.title, p.slug, p.approved_at, u.username,
            (SELECT COUNT(*) FROM posts p2 WHERE p2.channel = p.channel AND p2.type = p.type AND p2.status = 'approved'
               AND (p2.approved_at < p.approved_at OR (p2.approved_at = p.approved_at AND p2.id <= p.id))) AS seq
     FROM posts p JOIN users u ON u.id = p.user_id
     WHERE p.status = 'approved'
     ORDER BY p.approved_at DESC"
)->fetchAll();

$transmissions = array_filter($rows, fn($r) => $r['channel'] === 'site' && $r['type'] === 'transmission');
$notes = array_filter($rows, fn($r) => $r['channel'] === 'site' && $r['type'] !== 'transmission');
$community = array_filter($rows, fn($r) => $r['channel'] !== 'site');

function index_table(array $rows, bool $showAuthor): void {
    if (!$rows) {
        echo '  <div class="empty-state">Nothing here yet.</div>' . "\n";
        return;
    }
    ?>
  <table class="log">
    <thead>
      <tr>
        <th style="width:120px;">DATE</th>
        <th style="width:80px;">No.</th>
        <th>TITLE</th>
        <th style="width:140px;"><?= $showAuthor ? 'AUTHOR' : 'TYPE' ?></th>
        <th style="width:100px;">STATUS</th>
      </tr>
    </thead>
    <tbody>
<?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e(date('d/m/Y', strtotime($r['approved_at']))) ?></td>
        <td><?= e(str_pad((string) $r['seq'], 4, '0', STR_PAD_LEFT)) ?></td>
        <td><a href="<?= e(post_url($r)) ?>"><?= e($r['title']) ?></a></td>
        <td><?= $showAuthor ? e($r['username']) : e(strtoupper($r['type'])) ?></td>
        <td class="flag-ok">PUBLIC</td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
    <?php
}

page_head('Index', 'Chronological index of all transmissions, notes & community posts', 'index', 'INDEX');
?>

  <h2>Transmissions <span class="tag">Voice-originated</span></h2>
<?php index_table(array_values($transmissions), false); ?>

  <h2>Notes <span class="tag">Framework notes &amp; papers</span></h2>
<?php index_table(array_values($notes), false); ?>

  <h2>Community <span class="tag">Reader-contributed, reviewed</span></h2>
<?php index_table(array_values($community), true); ?>

<?php
page_foot('index');
