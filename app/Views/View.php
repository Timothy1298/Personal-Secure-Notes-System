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
