<?php
require_once 'config.php';
$submitted = $_SESSION['submitted_flags'] ?? [];
$last = $_SESSION['last_flag_status'] ?? null;
?>
<div class="flag-widget">
	<form method="POST" action="flag_handler.php" class="flag-form">
		<input type="text" name="flag" placeholder="Submit flag{...}" class="flag-input" />
		<button type="submit" class="btn btn-primary btn-flag">Submit</button>
	</form>
	<?php if ($last): ?>
		<div class="flag-status <?php echo $last['ok'] ? 'ok' : 'err'; ?>">
			<?php if ($last['ok']): ?>
				✓ Flag accepted: <strong><?php echo htmlspecialchars($last['flag']); ?></strong>
				<?php if (!empty($last['desc'])): ?>
					<span class="flag-desc">(<?php echo htmlspecialchars($last['desc']); ?>)</span>
				<?php endif; ?>
			<?php else: ?>
				✗ Incorrect flag. Try again.
			<?php endif; ?>
		</div>
		<?php unset($_SESSION['last_flag_status']); ?>
	<?php endif; ?>
	<?php if (!empty($submitted)): ?>
		<div class="flag-progress">
			<strong>Captured (<?php echo count($submitted); ?>/7):</strong>
			<ul>
				<?php foreach ($submitted as $f): ?>
					<li><?php echo htmlspecialchars($f); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>
<style>
.flag-widget { position: fixed; right: 20px; bottom: 20px; width: 320px; z-index: 9999; }
.flag-form { display: flex; gap: 8px; margin-bottom: 8px; }
.flag-input { flex: 1; padding: 10px; border: 2px solid #ecf0f1; border-radius: 8px; }
.btn-flag { padding: 10px 14px; }
.flag-status { padding: 10px 12px; border-radius: 8px; margin-top: 4px; font-weight: 600; }
.flag-status.ok { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
.flag-status.err { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
.flag-progress { background: #ffffff; border: 1px solid #ecf0f1; border-radius: 8px; padding: 10px 12px; margin-top: 8px; max-height: 200px; overflow: auto; }
.flag-progress ul { margin: 6px 0 0 18px; }
@media (max-width: 600px) { .flag-widget { width: calc(100% - 20px); left: 10px; right: 10px; bottom: 10px; } }
</style>







