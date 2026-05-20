<?php
/**
 * 存活探测脚本
 * 用于检测目标主机/网站是否在线
 */

// 防止超时
set_time_limit(300);

/**
 * 检测单个URL存活状态
 * @param string $url 目标URL
 * @param int $timeout 超时时间(秒)
 * @return array 包含状态信息的结果数组
 */
function checkUrlAlive($url, $timeout = 5) {
    $result = [
        'url' => $url,
        'status' => 'offline',
        'http_code' => 0,
        'response_time' => 0,
        'error' => ''
    ];
    
    try {
        $startTime = microtime(true);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $endTime = microtime(true);
        
        $result['response_time'] = round(($endTime - $startTime) * 1000, 2); // 毫秒
        $result['http_code'] = $httpCode;
        
        if (curl_errno($ch)) {
            $result['error'] = curl_error($ch);
            $result['status'] = 'error';
        } elseif ($httpCode >= 200 && $httpCode < 400) {
            $result['status'] = 'online';
        } else {
            $result['status'] = 'offline';
        }
        
        curl_close($ch);
        
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
        $result['status'] = 'error';
    }
    
    return $result;
}

/**
 * 检测多个URL存活状态
 * @param array $urls URL列表
 * @param int $timeout 超时时间(秒)
 * @return array 结果数组
 */
function checkMultipleUrls($urls, $timeout = 5) {
    $results = [];
    foreach ($urls as $url) {
        $results[] = checkUrlAlive($url, $timeout);
    }
    return $results;
}

// 处理POST请求
$results = [];
$targetUrls = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $targetUrls = isset($_POST['urls']) ? trim($_POST['urls']) : '';
    $timeout = isset($_POST['timeout']) ? intval($_POST['timeout']) : 5;
    
    if (!empty($targetUrls)) {
        // 支持换行或逗号分隔的URL
        $urls = preg_split('/[\n,]+/', $targetUrls);
        $urls = array_map('trim', $urls);
        $urls = array_filter($urls, function($url) {
            return !empty($url);
        });
        
        // 为没有协议的URL添加http://
        $urls = array_map(function($url) {
            if (!preg_match('/^https?:\/\//', $url)) {
                return 'http://' . $url;
            }
            return $url;
        }, $urls);
        
        $results = checkMultipleUrls($urls, $timeout);
    }
}
?>
<!DOCTYPE html>
<html lang="cn">
<head>
    <meta charset="utf-8">
    <title>存活探测工具</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            min-height: 150px;
            padding: 10px;
            font-family: monospace;
            font-size: 14px;
        }
        input[type="number"] {
            width: 100px;
            padding: 5px;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .result-table th,
        .result-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .result-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .status-online {
            color: #28a745;
            font-weight: bold;
        }
        .status-offline {
            color: #dc3545;
            font-weight: bold;
        }
        .status-error {
            color: #ffc107;
            font-weight: bold;
        }
        .stats {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .stats span {
            margin-right: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>存活探测工具</h1>
    
    <form method="post" action="">
        <div class="form-group">
            <label>目标URL列表（每行一个或用逗号分隔）：</label>
            <textarea name="urls" placeholder="例如：&#10;https://www.baidu.com&#10;https://www.google.com&#10;http://192.168.1.1"><?php echo htmlspecialchars($targetUrls); ?></textarea>
        </div>
        
        <div class="form-group">
            <label>超时时间（秒）：</label>
            <input type="number" name="timeout" value="5" min="1" max="30">
        </div>
        
        <input type="submit" value="开始探测">
    </form>
    
    <?php if (!empty($results)): ?>
        <?php
        // 统计信息
        $total = count($results);
        $online = count(array_filter($results, function($r) { return $r['status'] === 'online'; }));
        $offline = count(array_filter($results, function($r) { return $r['status'] === 'offline'; }));
        $error = count(array_filter($results, function($r) { return $r['status'] === 'error'; }));
        ?>
        
        <div class="stats">
            <span><strong>总计:</strong> <?php echo $total; ?></span>
            <span class="status-online"><strong>在线:</strong> <?php echo $online; ?></span>
            <span class="status-offline"><strong>离线:</strong> <?php echo $offline; ?></span>
            <span class="status-error"><strong>错误:</strong> <?php echo $error; ?></span>
        </div>
        
        <table class="result-table">
            <thead>
                <tr>
                    <th>URL</th>
                    <th>状态</th>
                    <th>HTTP状态码</th>
                    <th>响应时间(ms)</th>
                    <th>错误信息</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($result['url']); ?></td>
                        <td class="status-<?php echo $result['status']; ?>">
                            <?php 
                            switch($result['status']) {
                                case 'online': echo '✓ 在线'; break;
                                case 'offline': echo '✗ 离线'; break;
                                case 'error': echo '⚠ 错误'; break;
                            }
                            ?>
                        </td>
                        <td><?php echo $result['http_code'] > 0 ? $result['http_code'] : '-'; ?></td>
                        <td><?php echo $result['response_time'] > 0 ? $result['response_time'] : '-'; ?></td>
                        <td><?php echo !empty($result['error']) ? htmlspecialchars($result['error']) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
