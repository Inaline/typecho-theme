<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 引入主题核心文件（包含所有必要的类）
require_once(__DIR__ . "/core/core.php");

// 初始化性能监控
Inaline::initPerformanceMonitor();