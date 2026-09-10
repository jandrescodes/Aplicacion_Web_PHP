<?php

namespace Core;

class View
{
    private static string $basePath = '';

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }

    public static function render(string $view, array $data = []): void
    {
        extract($data);
        require self::$basePath . '/' . ltrim($view, '/');
    }

    public static function capture(string $view, array $data = []): string
    {
        ob_start();
        self::render($view, $data);
        return (string) ob_get_clean();
    }

    public static function renderWithLayout(string $view, array $data = []): void
    {
        $data = array_merge([
            'pageHeaderTitle' => '',
            'pageHeaderIcon' => '',
            'pageBreadcrumbs' => [],
            'needDataTables' => false,
        ], $data);

        $data['csrfToken'] = Security::getCsrfToken();

        self::render('layout/header.php', $data);
        self::render('layout/module_header.php', $data);
        self::render($view, $data);
        self::render('layout/footer.php', $data);
    }
}
