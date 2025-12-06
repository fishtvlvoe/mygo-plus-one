<?php
/**
 * Debug Log 查看器
 */

defined('ABSPATH') or die;

// 檢查權限
if (!current_user_can('manage_options')) {
    wp_die('您沒有權限訪問此頁面');
}

$logFile = WP_CONTENT_DIR . '/debug.log';
$logExists = file_exists($logFile);
$logSize = $logExists ? filesize($logFile) : 0;
$logSizeMB = round($logSize / 1024 / 1024, 2);

// 處理清空 log
if (isset($_POST['clear_log']) && wp_verify_nonce($_POST['_wpnonce'], 'clear_debug_log')) {
    if ($logExists) {
        file_put_contents($logFile, '');
        echo '<div class="notice notice-success"><p>Debug log 已清空</p></div>';
        $logSize = 0;
        $logSizeMB = 0;
    }
}

// 讀取最後 1000 行
$lines = [];
if ($logExists && $logSize > 0) {
    $lines = file($logFile);
    $lines = array_slice($lines, -1000); // 只顯示最後 1000 行
}

// 過濾 MYGO 相關的 log
$mygoLines = array_filter($lines, function($line) {
    return strpos($line, 'MYGO') !== false;
});
?>

<div class="wrap">
    <h1>Debug Log 查看器</h1>
    
    <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <strong>Log 檔案：</strong> <?php echo $logExists ? '存在' : '不存在'; ?><br>
                <strong>檔案大小：</strong> <?php echo $logSizeMB; ?> MB (<?php echo number_format($logSize); ?> bytes)<br>
                <strong>顯示：</strong> 最後 1000 行
            </div>
            <div>
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('clear_debug_log'); ?>
                    <button type="submit" name="clear_log" class="button" onclick="return confirm('確定要清空 debug log 嗎？')">清空 Log</button>
                </form>
                <button type="button" class="button button-primary" onclick="location.reload()">重新整理</button>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label>
                <input type="checkbox" id="filter-mygo" checked>
                只顯示 MYGO 相關的 log (<?php echo count($mygoLines); ?> 筆)
            </label>
        </div>
        
        <?php if ($logExists && !empty($lines)): ?>
        <div id="log-content" style="background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 8px; max-height: 600px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.6;">
            <?php foreach ($lines as $index => $line): ?>
                <?php 
                $isMYGO = strpos($line, 'MYGO') !== false;
                $class = $isMYGO ? 'mygo-log' : 'other-log';
                $style = $isMYGO ? 'color: #4ec9b0;' : 'color: #d4d4d4;';
                ?>
                <div class="<?php echo $class; ?>" style="<?php echo $style; ?> margin-bottom: 4px;">
                    <?php echo esc_html($line); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php elseif ($logExists): ?>
        <div style="padding: 40px; text-align: center; color: #666;">
            Log 檔案是空的
        </div>
        <?php else: ?>
        <div style="padding: 40px; text-align: center; color: #666;">
            Debug log 檔案不存在<br>
            <small>請確認 wp-config.php 中已啟用 WP_DEBUG_LOG</small>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('filter-mygo').addEventListener('change', function() {
    const showOnlyMYGO = this.checked;
    const otherLogs = document.querySelectorAll('.other-log');
    
    otherLogs.forEach(function(log) {
        log.style.display = showOnlyMYGO ? 'none' : 'block';
    });
});

// 預設只顯示 MYGO log
document.addEventListener('DOMContentLoaded', function() {
    const otherLogs = document.querySelectorAll('.other-log');
    otherLogs.forEach(function(log) {
        log.style.display = 'none';
    });
    
    // 自動捲動到最底部
    const logContent = document.getElementById('log-content');
    if (logContent) {
        logContent.scrollTop = logContent.scrollHeight;
    }
});
</script>
