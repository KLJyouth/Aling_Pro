<?php
/**
 * AlingAi Pro - API Integration Tests
 * 
 * This file contains integration tests for the API endpoints
 */

use PHPUnit\Framework\TestCase;

class ApiIntegrationTest extends TestCase
{
    /**
     * @var string The base URL for API requests
     */
    protected \ = 'http://localhost:3000/api';
    
    /**
     * @var string The authentication token
     */
    protected \;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        // Get an authentication token
        \->token = \->getAuthToken();
    }
    
    /**
     * Get an authentication token
     * 
     * @return string The authentication token
     */
    protected function getAuthToken()
    {
        \ = \->baseUrl . '/v1/auth/login';
        \ = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        
        \ = \->makeRequest(\, 'POST', \);
        
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('token', \['data']);
        
        return \['data']['token'];
    }
    
    /**
     * Make an HTTP request
     * 
     * @param string \ The URL to request
     * @param string \ The HTTP method
     * @param array \ The data to send
     * @param array \ Additional headers
     * @return array The response data
     */
    protected function makeRequest(\, \ = 'GET', \ = [], \ = [])
    {
        \ = curl_init();
        curl_setopt(\, CURLOPT_URL, \);
        curl_setopt(\, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(\, CURLOPT_TIMEOUT, 10);
        
        // Set method
        if (\ === 'POST') {
            curl_setopt(\, CURLOPT_POST, true);
        } else if (\ !== 'GET') {
            curl_setopt(\, CURLOPT_CUSTOMREQUEST, \);
        }
        
        // Set data
        if (!empty(\)) {
            \ = json_encode(\);
            curl_setopt(\, CURLOPT_POSTFIELDS, \);
            \[] = 'Content-Type: application/json';
            \[] = 'Content-Length: ' . strlen(\);
        }
        
        // Set headers
        if (!empty(\)) {
            curl_setopt(\, CURLOPT_HTTPHEADER, \);
        }
        
        \ = curl_exec(\);
        \ = curl_getinfo(\, CURLINFO_HTTP_CODE);
        \找不到接受实际参数“tests/Integration”的位置形式参数。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 32
+     preg_match_all('/src=[\"\\']([^\"\\']+(\.js[^\"\\']*))[\"\\']/',  ...
+                                ~
表达式或语句中包含意外的标记“]”。

所在位置 行:1 字符: 32
+     preg_match_all('/src=[\"\\']([^\"\\']+(\.js[^\"\\']*))[\"\\']/',  ...
+                                ~
表达式中缺少右“)”。

所在位置 行:1 字符: 35
+     preg_match_all('/src=[\"\\']([^\"\\']+(\.js[^\"\\']*))[\"\\']/',  ...
+                                   ~
"[" 后面缺少类型名称。

所在位置 行:11 字符: 93
+ ... ]([^\"\\']+(\.jpg|\.jpeg|\.png|\.gif|\.svg|\.webp)[^\"\\']*)[\"\\']/i ...
+                                                                  ~
数组索引表达式丢失或无效。

所在位置 行:36 字符: 44
+     preg_match_all('/href=[\"\\']([^\"\\']*)[\"\\']/i', \$content, \$ ...
+                                            ~
表达式或语句中包含意外的标记“)”。

所在位置 行:36 字符: 46
+     preg_match_all('/href=[\"\\']([^\"\\']*)[\"\\']/i', \$content, \$ ...
+                                              ~
"[" 后面缺少类型名称。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“//”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 14
+     foreach (\$matches[1] as \$match) {
+              ~
foreach 后面缺少变量名称。

所在位置 行:1 字符: 37
+     foreach (\$matches[1] as \$match) {
+                                     ~
表达式或语句中包含意外的标记“)”。

所在位置 行:3 字符: 13
+             'type' => 'css',
+             ~~~~~~
赋值表达式无效。赋值运算符输入必须是能够接受赋值的对象，例如变量或属性。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:108 字符: 70
+ ... match_all('/href=[\"\\']([^\"\\']+(\.css[^\"\\']*))[\"\\']/', \$conte ...
+                                                                 ~
参数列表中缺少参量。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 具有指定名称 E:\Code\AlingAi\AlingAi_pro\tests 的项已存在。 找不到路径“E:\Code\AlingAi\AlingAi_pro\NUL”，因为该路径不存在。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 68
+ ... ho Starting AlingAi Pro development server... >> server.bat & echo cd ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 85
+ ... opment server... >> server.bat & echo cd %%~dp0 >> server.bat & echo  ...
+                                                     ~~~~~~~~~~~~~
已重定向此命令的 输出流。

所在位置 行:1 字符: 99
+ ... ment server... >> server.bat & echo cd %%~dp0 >> server.bat & echo se ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 120
+ ... ho cd %%~dp0 >> server.bat & echo set PORT=3000 >> server.bat & echo  ...
+                                                     ~~~~~~~~~~~~~
已重定向此命令的 输出流。

所在位置 行:1 字符: 134
+ ...  cd %%~dp0 >> server.bat & echo set PORT=3000 >> server.bat & echo ph ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 177
+ ... .bat & echo php -S localhost:%%PORT%% -t public >> server.bat & echo  ...
+                                                     ~~~~~~~~~~~~~
已重定向此命令的 输出流。

所在位置 行:1 字符: 191
+ ... at & echo php -S localhost:%%PORT%% -t public >> server.bat & echo pa ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 204
+ ... localhost:%%PORT%% -t public >> server.bat & echo pause >> server.bat
+                                                             ~~~~~~~~~~~~~
已重定向此命令的 输出流。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“echo.>start_server_new.bat”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“grep”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 找不到路径“E:\Code\AlingAi\AlingAi_pro\nul”，因为该路径不存在。 无法将“touch”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 具有指定名称 E:\Code\AlingAi\AlingAi_pro\public\api 的项已存在。 找不到路径“E:\Code\AlingAi\AlingAi_pro\nul”，因为该路径不存在。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 32
+ cd E:\Code\AlingAi\AlingAi_pro && php -v
+                                ~~
标记“&&”不是此版本中的有效语句分隔符。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 24
+ cd src\Controllers\Api && php -l AuthApiController.php
+                        ~~
标记“&&”不是此版本中的有效语句分隔符。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:1 字符: 69
+ ... src/Controllers/Frontend/ThreatVisualizationController.php && echo de ...
+                                                                ~~
标记“&&”不是此版本中的有效语句分隔符。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“EOL”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 1
+ }
+ ~
表达式或语句中包含意外的标记“}”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:1 字符: 5
+     }
+     ~
表达式或语句中包含意外的标记“}”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:1 字符: 14
+             ->withHeader('Content-Type', 'application/json');
+              ~
一元运算符“-”后面缺少表达式。

所在位置 行:1 字符: 25
+             ->withHeader('Content-Type', 'application/json');
+                         ~
表达式或语句中包含意外的标记“(”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:3 字符: 28
+         $params = $request->getQueryParams();
+                            ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:3 字符: 43
+         $params = $request->getQueryParams();
+                                           ~
表达式或语句中包含意外的标记“(”。

所在位置 行:3 字符: 44
+         $params = $request->getQueryParams();
+                                            ~
“(”后面应为表达式。

所在位置 行:4 字符: 43
+         $timeframe = $params['timeframe'] ?? 'day';
+                                           ~~
表达式或语句中包含意外的标记“??”。

所在位置 行:5 字符: 33
+         $type = $params['type'] ?? 'all';
+                                 ~~
表达式或语句中包含意外的标记“??”。

所在位置 行:8 字符: 29
+         $threatData = $this->securityService->getThreatData($timefram ...
+                             ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:8 字符: 60
+         $threatData = $this->securityService->getThreatData($timefram ...
+                                                            ~
表达式或语句中包含意外的标记“(”。

所在位置 行:11 字符: 29
+         $visualData = $this->visualizationService->formatThreatData($ ...
+                             ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:11 字符: 68
+ ...  $visualData = $this->visualizationService->formatThreatData($threatD ...
+                                                                 ~
表达式或语句中包含意外的标记“(”。

所在位置 行:13 字符: 19
+         $response->getBody()->write(json_encode([
+                   ~
必须在“-”运算符后面提供一个值表达式。

并未报告所有分析错误。请更正报告的错误并重试。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“Request”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*/”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“/**”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:2 字符: 26
+         $templateVars = [
+                          ~
"[" 后面缺少类型名称。

所在位置 行:9 字符: 26
+         $view = $request->getAttribute('view');
+                          ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:9 字符: 39
+         $view = $request->getAttribute('view');
+                                       ~
表达式或语句中包含意外的标记“(”。

所在位置 行:10 字符: 22
+         return $view->render($response, 'threat-visualization.twig',  ...
+                      ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:10 字符: 29
+         return $view->render($response, 'threat-visualization.twig',  ...
+                             ~
表达式或语句中包含意外的标记“(”。

所在位置 行:3 字符: 13
+             'page_title' => '威胁可视化',
+             ~~~~~~~~~~~~
赋值表达式无效。赋值运算符输入必须是能够接受赋值的对象，例如变量或属性。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“Request”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*/”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“/**”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:2 字符: 15
+         $this->securityService = $securityService;
+               ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:2 字符: 32
+         $this->securityService = $securityService;
+                                ~
表达式或语句中包含意外的标记“=”。

所在位置 行:3 字符: 15
+         $this->visualizationService = $visualizationService;
+               ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:3 字符: 37
+         $this->visualizationService = $visualizationService;
+                                     ~
表达式或语句中包含意外的标记“=”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“SecurityService”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“private”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:2 字符: 1
+ {
+ ~
语句块或类型定义中缺少右“}”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“*/”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“/**”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“namespace”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“strict_types=1”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“<”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 67
+ ... > src/Controllers/Frontend/ThreatVisualizationController.php << 'EOL'
+                                                                   ~
重定向运算符后面缺少文件规范。

所在位置 行:1 字符: 66
+ ... > src/Controllers/Frontend/ThreatVisualizationController.php << 'EOL'
+                                                                  ~
“<”运算符是为将来使用而保留的。

所在位置 行:1 字符: 67
+ ... > src/Controllers/Frontend/ThreatVisualizationController.php << 'EOL'
+                                                                   ~
“<”运算符是为将来使用而保留的。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 具有指定名称 E:\Code\AlingAi\AlingAi_pro\src\Controllers\Frontend 的项已存在。 找不到接受实际参数“用户不存在\等异常
            return $this->sendErrorResponse('获取个人资料失败: ' . $e->getMessage(), 404);
        }
    }

    /**
     * 更新当前认证用户的个人资料
     */
    public function updateProfile(Request $request, Response $response): Response
    {
        try {
            $userId = $request->getAttribute(AuthMiddleware::USER_ID_ATTRIBUTE);
            if (!$userId) {
                return $this->sendErrorResponse('认证失败或用户ID缺失', 401);
            }

            $data = $this->getRequestData($request);

            $success = $this->userService->updateProfile((int)$userId, $data);

            if ($success) {
                // 同时返回更新后的资料
                $updatedProfile = $this->userService->getProfile((int)$userId);
                return $this->sendSuccess($response, $updatedProfile, '个人资料更新成功');
            } else {
                return $this->sendErrorResponse('个人资料更新失败，可能没有提供任何有效字段', 400);
            }

        } catch (Throwable $e) {
            // 如果UserService抛出\用户名已存在\等异常
            return $this->sendErrorResponse('个人资料更新失败: ' . $e->getMessage(), 400);
        }
    }
}”的位置形式参数。 具有指定名称 E:\Code\AlingAi\AlingAi_pro\src\Core\Middleware 的项已存在。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 找不到路径“E:\Code\AlingAi\AlingAi_pro\nul”，因为该路径不存在。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 找不到接受实际参数“E:/Code/AlingAi/AlingAi_pro/src/AI/Visualization”的位置形式参数。 找不到与参数名称“Chord”匹配的参数。 找不到与参数名称“Chord”匹配的参数。 找不到与参数名称“Chord”匹配的参数。 找不到与参数名称“Chord”匹配的参数。 = curl_error(\);
        curl_close(\);
        
        if (\找不到接受实际参数“tests/Integration”的位置形式参数。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 32
+     preg_match_all('/src=[\"\\']([^\"\\']+(\.js[^\"\\']*))[\"\\']/',  ...
+                                ~
表达式或语句中包含意外的标记“]”。

所在位置 行:1 字符: 32
+     preg_match_all('/src=[\"\\']([^\"\\']+(\.js[^\"\\']*))[\"\\']/',  ...
+                                ~
表达式中缺少右“)”。

所在位置 行:1 字符: 35
+     preg_match_all('/src=[\"\\']([^\"\\']+(\.js[^\"\\']*))[\"\\']/',  ...
+                                   ~
"[" 后面缺少类型名称。

所在位置 行:11 字符: 93
+ ... ]([^\"\\']+(\.jpg|\.jpeg|\.png|\.gif|\.svg|\.webp)[^\"\\']*)[\"\\']/i ...
+                                                                  ~
数组索引表达式丢失或无效。

所在位置 行:36 字符: 44
+     preg_match_all('/href=[\"\\']([^\"\\']*)[\"\\']/i', \$content, \$ ...
+                                            ~
表达式或语句中包含意外的标记“)”。

所在位置 行:36 字符: 46
+     preg_match_all('/href=[\"\\']([^\"\\']*)[\"\\']/i', \$content, \$ ...
+                                              ~
"[" 后面缺少类型名称。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“//”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 14
+     foreach (\$matches[1] as \$match) {
+              ~
foreach 后面缺少变量名称。

所在位置 行:1 字符: 37
+     foreach (\$matches[1] as \$match) {
+                                     ~
表达式或语句中包含意外的标记“)”。

所在位置 行:3 字符: 13
+             'type' => 'css',
+             ~~~~~~
赋值表达式无效。赋值运算符输入必须是能够接受赋值的对象，例如变量或属性。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:108 字符: 70
+ ... match_all('/href=[\"\\']([^\"\\']+(\.css[^\"\\']*))[\"\\']/', \$conte ...
+                                                                 ~
参数列表中缺少参量。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 具有指定名称 E:\Code\AlingAi\AlingAi_pro\tests 的项已存在。 找不到路径“E:\Code\AlingAi\AlingAi_pro\NUL”，因为该路径不存在。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 68
+ ... ho Starting AlingAi Pro development server... >> server.bat & echo cd ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 85
+ ... opment server... >> server.bat & echo cd %%~dp0 >> server.bat & echo  ...
+                                                     ~~~~~~~~~~~~~
已重定向此命令的 输出流。

所在位置 行:1 字符: 99
+ ... ment server... >> server.bat & echo cd %%~dp0 >> server.bat & echo se ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 120
+ ... ho cd %%~dp0 >> server.bat & echo set PORT=3000 >> server.bat & echo  ...
+                                                     ~~~~~~~~~~~~~
已重定向此命令的 输出流。

所在位置 行:1 字符: 134
+ ...  cd %%~dp0 >> server.bat & echo set PORT=3000 >> server.bat & echo ph ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 177
+ ... .bat & echo php -S localhost:%%PORT%% -t public >> server.bat & echo  ...
+                                                     ~~~~~~~~~~~~~
已重定向此命令的 输出流。

所在位置 行:1 字符: 191
+ ... at & echo php -S localhost:%%PORT%% -t public >> server.bat & echo pa ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 204
+ ... localhost:%%PORT%% -t public >> server.bat & echo pause >> server.bat
+                                                             ~~~~~~~~~~~~~
已重定向此命令的 输出流。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“echo.>start_server_new.bat”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“grep”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 找不到路径“E:\Code\AlingAi\AlingAi_pro\nul”，因为该路径不存在。 无法将“touch”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 具有指定名称 E:\Code\AlingAi\AlingAi_pro\public\api 的项已存在。 找不到路径“E:\Code\AlingAi\AlingAi_pro\nul”，因为该路径不存在。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 32
+ cd E:\Code\AlingAi\AlingAi_pro && php -v
+                                ~~
标记“&&”不是此版本中的有效语句分隔符。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 24
+ cd src\Controllers\Api && php -l AuthApiController.php
+                        ~~
标记“&&”不是此版本中的有效语句分隔符。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:1 字符: 69
+ ... src/Controllers/Frontend/ThreatVisualizationController.php && echo de ...
+                                                                ~~
标记“&&”不是此版本中的有效语句分隔符。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“EOL”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 1
+ }
+ ~
表达式或语句中包含意外的标记“}”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:1 字符: 5
+     }
+     ~
表达式或语句中包含意外的标记“}”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:1 字符: 14
+             ->withHeader('Content-Type', 'application/json');
+              ~
一元运算符“-”后面缺少表达式。

所在位置 行:1 字符: 25
+             ->withHeader('Content-Type', 'application/json');
+                         ~
表达式或语句中包含意外的标记“(”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:3 字符: 28
+         $params = $request->getQueryParams();
+                            ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:3 字符: 43
+         $params = $request->getQueryParams();
+                                           ~
表达式或语句中包含意外的标记“(”。

所在位置 行:3 字符: 44
+         $params = $request->getQueryParams();
+                                            ~
“(”后面应为表达式。

所在位置 行:4 字符: 43
+         $timeframe = $params['timeframe'] ?? 'day';
+                                           ~~
表达式或语句中包含意外的标记“??”。

所在位置 行:5 字符: 33
+         $type = $params['type'] ?? 'all';
+                                 ~~
表达式或语句中包含意外的标记“??”。

所在位置 行:8 字符: 29
+         $threatData = $this->securityService->getThreatData($timefram ...
+                             ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:8 字符: 60
+         $threatData = $this->securityService->getThreatData($timefram ...
+                                                            ~
表达式或语句中包含意外的标记“(”。

所在位置 行:11 字符: 29
+         $visualData = $this->visualizationService->formatThreatData($ ...
+                             ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:11 字符: 68
+ ...  $visualData = $this->visualizationService->formatThreatData($threatD ...
+                                                                 ~
表达式或语句中包含意外的标记“(”。

所在位置 行:13 字符: 19
+         $response->getBody()->write(json_encode([
+                   ~
必须在“-”运算符后面提供一个值表达式。

并未报告所有分析错误。请更正报告的错误并重试。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“Request”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*/”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“/**”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:2 字符: 26
+         $templateVars = [
+                          ~
"[" 后面缺少类型名称。

所在位置 行:9 字符: 26
+         $view = $request->getAttribute('view');
+                          ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:9 字符: 39
+         $view = $request->getAttribute('view');
+                                       ~
表达式或语句中包含意外的标记“(”。

所在位置 行:10 字符: 22
+         return $view->render($response, 'threat-visualization.twig',  ...
+                      ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:10 字符: 29
+         return $view->render($response, 'threat-visualization.twig',  ...
+                             ~
表达式或语句中包含意外的标记“(”。

所在位置 行:3 字符: 13
+             'page_title' => '威胁可视化',
+             ~~~~~~~~~~~~
赋值表达式无效。赋值运算符输入必须是能够接受赋值的对象，例如变量或属性。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“Request”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*/”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“/**”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:2 字符: 15
+         $this->securityService = $securityService;
+               ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:2 字符: 32
+         $this->securityService = $securityService;
+                                ~
表达式或语句中包含意外的标记“=”。

所在位置 行:3 字符: 15
+         $this->visualizationService = $visualizationService;
+               ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:3 字符: 37
+         $this->visualizationService = $visualizationService;
+                                     ~
表达式或语句中包含意外的标记“=”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“SecurityService”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“private”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:2 字符: 1
+ {
+ ~
语句块或类型定义中缺少右“}”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“*/”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“/**”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“namespace”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“strict_types=1”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“<”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 67
+ ... > src/Controllers/Frontend/ThreatVisualizationController.php << 'EOL'
+                                                                   ~
重定向运算符后面缺少文件规范。

所在位置 行:1 字符: 66
+ ... > src/Controllers/Frontend/ThreatVisualizationController.php << 'EOL'
+                                                                  ~
“<”运算符是为将来使用而保留的。

所在位置 行:1 字符: 67
+ ... > src/Controllers/Frontend/ThreatVisualizationController.php << 'EOL'
+                                                                   ~
“<”运算符是为将来使用而保留的。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 具有指定名称 E:\Code\AlingAi\AlingAi_pro\src\Controllers\Frontend 的项已存在。 找不到接受实际参数“用户不存在\等异常
            return $this->sendErrorResponse('获取个人资料失败: ' . $e->getMessage(), 404);
        }
    }

    /**
     * 更新当前认证用户的个人资料
     */
    public function updateProfile(Request $request, Response $response): Response
    {
        try {
            $userId = $request->getAttribute(AuthMiddleware::USER_ID_ATTRIBUTE);
            if (!$userId) {
                return $this->sendErrorResponse('认证失败或用户ID缺失', 401);
            }

            $data = $this->getRequestData($request);

            $success = $this->userService->updateProfile((int)$userId, $data);

            if ($success) {
                // 同时返回更新后的资料
                $updatedProfile = $this->userService->getProfile((int)$userId);
                return $this->sendSuccess($response, $updatedProfile, '个人资料更新成功');
            } else {
                return $this->sendErrorResponse('个人资料更新失败，可能没有提供任何有效字段', 400);
            }

        } catch (Throwable $e) {
            // 如果UserService抛出\用户名已存在\等异常
            return $this->sendErrorResponse('个人资料更新失败: ' . $e->getMessage(), 400);
        }
    }
}”的位置形式参数。 具有指定名称 E:\Code\AlingAi\AlingAi_pro\src\Core\Middleware 的项已存在。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 找不到路径“E:\Code\AlingAi\AlingAi_pro\nul”，因为该路径不存在。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 找不到接受实际参数“E:/Code/AlingAi/AlingAi_pro/src/AI/Visualization”的位置形式参数。 找不到与参数名称“Chord”匹配的参数。 找不到与参数名称“Chord”匹配的参数。 找不到与参数名称“Chord”匹配的参数。 找不到与参数名称“Chord”匹配的参数。) {
            \->fail('cURL error: ' . \找不到接受实际参数“tests/Integration”的位置形式参数。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 32
+     preg_match_all('/src=[\"\\']([^\"\\']+(\.js[^\"\\']*))[\"\\']/',  ...
+                                ~
表达式或语句中包含意外的标记“]”。

所在位置 行:1 字符: 32
+     preg_match_all('/src=[\"\\']([^\"\\']+(\.js[^\"\\']*))[\"\\']/',  ...
+                                ~
表达式中缺少右“)”。

所在位置 行:1 字符: 35
+     preg_match_all('/src=[\"\\']([^\"\\']+(\.js[^\"\\']*))[\"\\']/',  ...
+                                   ~
"[" 后面缺少类型名称。

所在位置 行:11 字符: 93
+ ... ]([^\"\\']+(\.jpg|\.jpeg|\.png|\.gif|\.svg|\.webp)[^\"\\']*)[\"\\']/i ...
+                                                                  ~
数组索引表达式丢失或无效。

所在位置 行:36 字符: 44
+     preg_match_all('/href=[\"\\']([^\"\\']*)[\"\\']/i', \$content, \$ ...
+                                            ~
表达式或语句中包含意外的标记“)”。

所在位置 行:36 字符: 46
+     preg_match_all('/href=[\"\\']([^\"\\']*)[\"\\']/i', \$content, \$ ...
+                                              ~
"[" 后面缺少类型名称。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“//”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 14
+     foreach (\$matches[1] as \$match) {
+              ~
foreach 后面缺少变量名称。

所在位置 行:1 字符: 37
+     foreach (\$matches[1] as \$match) {
+                                     ~
表达式或语句中包含意外的标记“)”。

所在位置 行:3 字符: 13
+             'type' => 'css',
+             ~~~~~~
赋值表达式无效。赋值运算符输入必须是能够接受赋值的对象，例如变量或属性。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:108 字符: 70
+ ... match_all('/href=[\"\\']([^\"\\']+(\.css[^\"\\']*))[\"\\']/', \$conte ...
+                                                                 ~
参数列表中缺少参量。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 具有指定名称 E:\Code\AlingAi\AlingAi_pro\tests 的项已存在。 找不到路径“E:\Code\AlingAi\AlingAi_pro\NUL”，因为该路径不存在。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 68
+ ... ho Starting AlingAi Pro development server... >> server.bat & echo cd ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 85
+ ... opment server... >> server.bat & echo cd %%~dp0 >> server.bat & echo  ...
+                                                     ~~~~~~~~~~~~~
已重定向此命令的 输出流。

所在位置 行:1 字符: 99
+ ... ment server... >> server.bat & echo cd %%~dp0 >> server.bat & echo se ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 120
+ ... ho cd %%~dp0 >> server.bat & echo set PORT=3000 >> server.bat & echo  ...
+                                                     ~~~~~~~~~~~~~
已重定向此命令的 输出流。

所在位置 行:1 字符: 134
+ ...  cd %%~dp0 >> server.bat & echo set PORT=3000 >> server.bat & echo ph ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 177
+ ... .bat & echo php -S localhost:%%PORT%% -t public >> server.bat & echo  ...
+                                                     ~~~~~~~~~~~~~
已重定向此命令的 输出流。

所在位置 行:1 字符: 191
+ ... at & echo php -S localhost:%%PORT%% -t public >> server.bat & echo pa ...
+                                                                 ~
不允许使用与号(&)。& 运算符是为将来使用而保留的；请用双引号将与号引起来("&")，以将其作为字符串的一部分传递。

所在位置 行:1 字符: 204
+ ... localhost:%%PORT%% -t public >> server.bat & echo pause >> server.bat
+                                                             ~~~~~~~~~~~~~
已重定向此命令的 输出流。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“echo.>start_server_new.bat”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“grep”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 找不到路径“E:\Code\AlingAi\AlingAi_pro\nul”，因为该路径不存在。 无法将“touch”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 具有指定名称 E:\Code\AlingAi\AlingAi_pro\public\api 的项已存在。 找不到路径“E:\Code\AlingAi\AlingAi_pro\nul”，因为该路径不存在。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 32
+ cd E:\Code\AlingAi\AlingAi_pro && php -v
+                                ~~
标记“&&”不是此版本中的有效语句分隔符。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 24
+ cd src\Controllers\Api && php -l AuthApiController.php
+                        ~~
标记“&&”不是此版本中的有效语句分隔符。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:1 字符: 69
+ ... src/Controllers/Frontend/ThreatVisualizationController.php && echo de ...
+                                                                ~~
标记“&&”不是此版本中的有效语句分隔符。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“EOL”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 1
+ }
+ ~
表达式或语句中包含意外的标记“}”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:1 字符: 5
+     }
+     ~
表达式或语句中包含意外的标记“}”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:1 字符: 14
+             ->withHeader('Content-Type', 'application/json');
+              ~
一元运算符“-”后面缺少表达式。

所在位置 行:1 字符: 25
+             ->withHeader('Content-Type', 'application/json');
+                         ~
表达式或语句中包含意外的标记“(”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) System.Management.Automation.ParseException: 所在位置 行:3 字符: 28
+         $params = $request->getQueryParams();
+                            ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:3 字符: 43
+         $params = $request->getQueryParams();
+                                           ~
表达式或语句中包含意外的标记“(”。

所在位置 行:3 字符: 44
+         $params = $request->getQueryParams();
+                                            ~
“(”后面应为表达式。

所在位置 行:4 字符: 43
+         $timeframe = $params['timeframe'] ?? 'day';
+                                           ~~
表达式或语句中包含意外的标记“??”。

所在位置 行:5 字符: 33
+         $type = $params['type'] ?? 'all';
+                                 ~~
表达式或语句中包含意外的标记“??”。

所在位置 行:8 字符: 29
+         $threatData = $this->securityService->getThreatData($timefram ...
+                             ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:8 字符: 60
+         $threatData = $this->securityService->getThreatData($timefram ...
+                                                            ~
表达式或语句中包含意外的标记“(”。

所在位置 行:11 字符: 29
+         $visualData = $this->visualizationService->formatThreatData($ ...
+                             ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:11 字符: 68
+ ...  $visualData = $this->visualizationService->formatThreatData($threatD ...
+                                                                 ~
表达式或语句中包含意外的标记“(”。

所在位置 行:13 字符: 19
+         $response->getBody()->write(json_encode([
+                   ~
必须在“-”运算符后面提供一个值表达式。

并未报告所有分析错误。请更正报告的错误并重试。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“Request”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*/”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“/**”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:2 字符: 26
+         $templateVars = [
+                          ~
"[" 后面缺少类型名称。

所在位置 行:9 字符: 26
+         $view = $request->getAttribute('view');
+                          ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:9 字符: 39
+         $view = $request->getAttribute('view');
+                                       ~
表达式或语句中包含意外的标记“(”。

所在位置 行:10 字符: 22
+         return $view->render($response, 'threat-visualization.twig',  ...
+                      ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:10 字符: 29
+         return $view->render($response, 'threat-visualization.twig',  ...
+                             ~
表达式或语句中包含意外的标记“(”。

所在位置 行:3 字符: 13
+             'page_title' => '威胁可视化',
+             ~~~~~~~~~~~~
赋值表达式无效。赋值运算符输入必须是能够接受赋值的对象，例如变量或属性。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“Request”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*/”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“/**”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:2 字符: 15
+         $this->securityService = $securityService;
+               ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:2 字符: 32
+         $this->securityService = $securityService;
+                                ~
表达式或语句中包含意外的标记“=”。

所在位置 行:3 字符: 15
+         $this->visualizationService = $visualizationService;
+               ~
必须在“-”运算符后面提供一个值表达式。

所在位置 行:3 字符: 37
+         $this->visualizationService = $visualizationService;
+                                     ~
表达式或语句中包含意外的标记“=”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“SecurityService”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“private”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:2 字符: 1
+ {
+ ~
语句块或类型定义中缺少右“}”。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 无法将“*/”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“*”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“/**”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“use”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“namespace”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“strict_types=1”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“<”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 System.Management.Automation.ParseException: 所在位置 行:1 字符: 67
+ ... > src/Controllers/Frontend/ThreatVisualizationController.php << 'EOL'
+                                                                   ~
重定向运算符后面缺少文件规范。

所在位置 行:1 字符: 66
+ ... > src/Controllers/Frontend/ThreatVisualizationController.php << 'EOL'
+                                                                  ~
“<”运算符是为将来使用而保留的。

所在位置 行:1 字符: 67
+ ... > src/Controllers/Frontend/ThreatVisualizationController.php << 'EOL'
+                                                                   ~
“<”运算符是为将来使用而保留的。
   在 System.Management.Automation.Runspaces.PipelineBase.Invoke(IEnumerable input)
   在 Microsoft.PowerShell.Executor.ExecuteCommandHelper(Pipeline tempPipeline, Exception& exceptionThrown, ExecutionOptions options) 具有指定名称 E:\Code\AlingAi\AlingAi_pro\src\Controllers\Frontend 的项已存在。 找不到接受实际参数“用户不存在\等异常
            return $this->sendErrorResponse('获取个人资料失败: ' . $e->getMessage(), 404);
        }
    }

    /**
     * 更新当前认证用户的个人资料
     */
    public function updateProfile(Request $request, Response $response): Response
    {
        try {
            $userId = $request->getAttribute(AuthMiddleware::USER_ID_ATTRIBUTE);
            if (!$userId) {
                return $this->sendErrorResponse('认证失败或用户ID缺失', 401);
            }

            $data = $this->getRequestData($request);

            $success = $this->userService->updateProfile((int)$userId, $data);

            if ($success) {
                // 同时返回更新后的资料
                $updatedProfile = $this->userService->getProfile((int)$userId);
                return $this->sendSuccess($response, $updatedProfile, '个人资料更新成功');
            } else {
                return $this->sendErrorResponse('个人资料更新失败，可能没有提供任何有效字段', 400);
            }

        } catch (Throwable $e) {
            // 如果UserService抛出\用户名已存在\等异常
            return $this->sendErrorResponse('个人资料更新失败: ' . $e->getMessage(), 400);
        }
    }
}”的位置形式参数。 具有指定名称 E:\Code\AlingAi\AlingAi_pro\src\Core\Middleware 的项已存在。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 找不到路径“E:\Code\AlingAi\AlingAi_pro\nul”，因为该路径不存在。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 无法将“php”项识别为 cmdlet、函数、脚本文件或可运行程序的名称。请检查名称的拼写，如果包括路径，请确保路径正确，然后再试一次。 找不到接受实际参数“E:/Code/AlingAi/AlingAi_pro/src/AI/Visualization”的位置形式参数。 找不到与参数名称“Chord”匹配的参数。 找不到与参数名称“Chord”匹配的参数。 找不到与参数名称“Chord”匹配的参数。 找不到与参数名称“Chord”匹配的参数。);
        }
        
        return json_decode(\, true);
    }
    
    /**
     * Test the user profile endpoint
     */
    public function testUserProfileEndpoint()
    {
        \ = \->baseUrl . '/v1/user/profile';
        \ = ['Authorization: Bearer ' . \->token];
        
        \ = \->makeRequest(\, 'GET', [], \);
        
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('id', \['data']);
        \->assertArrayHasKey('username', \['data']);
        \->assertArrayHasKey('email', \['data']);
    }
    
    /**
     * Test the user quota endpoint
     */
    public function testUserQuotaEndpoint()
    {
        \ = \->baseUrl . '/v1/user/quota';
        \ = ['Authorization: Bearer ' . \->token];
        
        \ = \->makeRequest(\, 'GET', [], \);
        
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('daily_limit', \['data']);
        \->assertArrayHasKey('used', \['data']);
        \->assertArrayHasKey('remaining', \['data']);
    }
    
    /**
     * Test the chat message endpoint
     */
    public function testChatMessageEndpoint()
    {
        \ = \->baseUrl . '/v1/chat/message';
        \ = ['Authorization: Bearer ' . \->token];
        \ = [
            'message' => 'Hello, this is a test message'
        ];
        
        \ = \->makeRequest(\, 'POST', \, \);
        
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('reply', \['data']);
    }
    
    /**
     * Test the chat history endpoint
     */
    public function testChatHistoryEndpoint()
    {
        \ = \->baseUrl . '/v1/chat/history';
        \ = ['Authorization: Bearer ' . \->token];
        
        \ = \->makeRequest(\, 'GET', [], \);
        
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('messages', \['data']);
    }
    
    /**
     * Test the enhanced chat message endpoint
     */
    public function testEnhancedChatMessageEndpoint()
    {
        \ = \->baseUrl . '/v2/enhanced-chat/message';
        \ = ['Authorization: Bearer ' . \->token];
        \ = [
            'message' => 'Hello, this is a test message for enhanced chat',
            'model' => 'gpt-4-turbo'
        ];
        
        \ = \->makeRequest(\, 'POST', \, \);
        
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('reply', \['data']);
        \->assertArrayHasKey('session_id', \['data']);
    }
    
    /**
     * Test the enhanced chat sessions endpoint
     */
    public function testEnhancedChatSessionsEndpoint()
    {
        \ = \->baseUrl . '/v2/enhanced-chat/sessions';
        \ = ['Authorization: Bearer ' . \->token];
        
        \ = \->makeRequest(\, 'GET', [], \);
        
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('sessions', \['data']);
    }
    
    /**
     * Test creating a new enhanced chat session
     */
    public function testCreateEnhancedChatSession()
    {
        \ = \->baseUrl . '/v2/enhanced-chat/sessions';
        \ = ['Authorization: Bearer ' . \->token];
        \ = [
            'title' => 'Test Session ' . time()
        ];
        
        \ = \->makeRequest(\, 'POST', \, \);
        
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('session', \['data']);
        \->assertArrayHasKey('id', \['data']['session']);
    }
    
    /**
     * Test the system info endpoint
     */
    public function testSystemInfoEndpoint()
    {
        \ = \->baseUrl . '/v1/system/info';
        
        \ = \->makeRequest(\, 'GET');
        
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('version', \['data']);
    }
    
    /**
     * Test the system status endpoint
     */
    public function testSystemStatusEndpoint()
    {
        \ = \->baseUrl . '/v1/system/status';
        
        \ = \->makeRequest(\, 'GET');
        
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('status', \['data']);
    }
}
