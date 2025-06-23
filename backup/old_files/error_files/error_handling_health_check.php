<?php
/**
 * 错误处理健康检查
 */
require_once __DIR__ . "/production_error_handler.php";

function checkErrorHandlingHealth() {
    return ProductionErrorHandler::checkHealth();
}

return checkErrorHandlingHealth();
