<?php

// 1. Update Controllers
$controllersPath = __DIR__ . '/app/Http/Controllers';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersPath));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $newContent = str_replace('"%{$search}%"', '"{$search}%"', $content);
        if ($content !== $newContent) {
            file_put_contents($file->getPathname(), $newContent);
            echo "Updated controller: " . $file->getPathname() . "\n";
        }
    }
}

// 2. Update Views
$viewsPath = __DIR__ . '/resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$alpineCode = ' x-data x-init="if(sessionStorage.getItem(\'search_focus\') === \'true\') { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); sessionStorage.removeItem(\'search_focus\'); }" @input.debounce.500ms="sessionStorage.setItem(\'search_focus\', \'true\'); $event.target.form.submit()"';

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        // Find inputs with name="search"
        $pattern = '/<input[^>]+name="search"[^>]*>/i';
        $newContent = preg_replace_callback($pattern, function ($matches) use ($alpineCode) {
            $tag = $matches[0];
            if (strpos($tag, 'search_focus') === false) {
                // Insert alpine code before the closing >
                if (substr($tag, -2) === '/>') {
                    return substr($tag, 0, -2) . $alpineCode . '/>';
                }
                return substr($tag, 0, -1) . $alpineCode . '>';
            }
            return $tag;
        }, $content);

        if ($content !== $newContent) {
            file_put_contents($file->getPathname(), $newContent);
            echo "Updated view: " . $file->getPathname() . "\n";
        }
    }
}
