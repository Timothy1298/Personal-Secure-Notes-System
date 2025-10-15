<?php
namespace App\Views;

class View
{
    private $basePath;

    public function __construct(string $basePath = __DIR__)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Load a view file and pass data to it
     */
    public function load(string $view, array $data = [])
    {
        // Make variables from the $data array available in the view
        extract($data);

        $file = $this->basePath . '/' . $view . '.php';

        if (file_exists($file)) {
            include $file;
        } else {
            echo "<p style='color:red;'>View not found: {$file}</p>";
        }
    }
}


/*Request ID: a4258bd2-411a-4004-a907-312d87c97fdf
{"error":"ERROR_USER_ABORTED_REQUEST","details":{"title":"User aborted request.","detail":"Tool call ended before result was received","isRetryable":false,"additionalInfo":{},"buttons":[],"planChoices":[]},"isExpected":true}
ConnectError: [aborted] Error
    at HWl.$endAiConnectTransportReportError (vscode-file://vscode-app/tmp/.mount_CursorliPzcz/usr/share/cursor/resources/app/out/vs/workbench/workbench.desktop.main.js:7347:371721)
    at lMr._doInvokeHandler (vscode-file://vscode-app/tmp/.mount_CursorliPzcz/usr/share/cursor/resources/app/out/vs/workbench/workbench.desktop.main.js:489:35946)
    at lMr._invokeHandler (vscode-file://vscode-app/tmp/.mount_CursorliPzcz/usr/share/cursor/resources/app/out/vs/workbench/workbench.desktop.main.js:489:35688)
    at lMr._receiveRequest (vscode-file://vscode-app/tmp/.mount_CursorliPzcz/usr/share/cursor/resources/app/out/vs/workbench/workbench.desktop.main.js:489:34453)
    at lMr._receiveOneMessage (vscode-file://vscode-app/tmp/.mount_CursorliPzcz/usr/share/cursor/resources/app/out/vs/workbench/workbench.desktop.main.js:489:33275)
    at cEt.value (vscode-file://vscode-app/tmp/.mount_CursorliPzcz/usr/share/cursor/resources/app/out/vs/workbench/workbench.desktop.main.js:489:31369)
    at _e._deliver (vscode-file://vscode-app/tmp/.mount_CursorliPzcz/usr/share/cursor/resources/app/out/vs/workbench/workbench.desktop.main.js:49:2962)
    at _e.fire (vscode-file://vscode-app/tmp/.mount_CursorliPzcz/usr/share/cursor/resources/app/out/vs/workbench/workbench.desktop.main.js:49:3283)
    at udt.fire (vscode-file://vscode-app/tmp/.mount_CursorliPzcz/usr/share/cursor/resources/app/out/vs/workbench/workbench.desktop.main.js:7332:12154)
    at MessagePort.<anonymous> (vscode-file://vscode-app/tmp/.mount_CursorliPzcz/usr/share/cursor/resources/app/out/vs/workbench/workbench.desktop.main.js:9400:18292)
*/








