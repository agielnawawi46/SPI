<?php
$files = [
    'd:/SPI/app/resources/views/other-activities/tindak-lanjut/show.blade.php',
    'd:/SPI/app/resources/views/other-activities/qa/show.blade.php',
    'd:/SPI/app/resources/views/assurance/show.blade.php',
    'd:/SPI/app/resources/views/advisory/show.blade.php',
    'd:/SPI/app/resources/views/auditee/show.blade.php',
    'd:/SPI/app/resources/views/partials/approval-panel.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // This regex looks for the literal string "Level {{ $approval->approval_level }} " 
        // followed by anything (non-greedy) until " <span class="capitalize">"
        // and replaces it.
        $pattern = '/Level \{\{ \$approval->approval_level \}\} .*? <span class="capitalize">/s';
        $replacement = 'Level {{ $approval->approval_level }} &mdash; <span class="capitalize">';
        
        $newContent = preg_replace($pattern, $replacement, $content);
        
        if ($content !== $newContent) {
            file_put_contents($file, $newContent);
            echo "Updated $file\n";
        }
    }
}
