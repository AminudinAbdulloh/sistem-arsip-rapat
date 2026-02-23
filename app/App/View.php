<?php

namespace ArsipRapat\App;

class View
{
    public static function render(string $view, array $model = []): void
    {
        extract($model);
        require __DIR__ . '/../View/' . $view . '.php';
    }

    public static function renderWithLayout(string $view, array $model = [], string $layout = 'Layouts/main'): void
    {
        extract($model);
        $content = function() use ($view, $model) {
            extract($model);
            require __DIR__ . '/../View/' . $view . '.php';
        };
        require __DIR__ . '/../View/' . $layout . '.php';
    }
}
