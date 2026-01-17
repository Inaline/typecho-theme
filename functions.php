<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 尽早初始化性能监控（在加载任何其他代码之前）
require_once(__DIR__ . "/core/Modules/Inaline/Inaline.php");
Inaline::initPerformanceMonitor();

// 引入主题核心文件
require_once(__DIR__ . "/core/core.php");