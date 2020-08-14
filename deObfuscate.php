<?php

$path = getcwd() . "/";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getBasename() === '.DS_Store' || $file->getBasename() === 'de-obfuscate.php' || preg_match('/sample/i', $file->getBasename())) {
        continue;
    }

    $pattern1 = '/.1080p.*/i';
    $pattern2 = '/.720p.*/i';
    $pattern3 = '/.x264.*/i';
    $pattern4 = '/.DVDRip.*/i';

    $tmpFilename = $file->getSubPath();

    $tmpFilename = preg_replace(array($pattern1, $pattern2, $pattern3, $pattern4, $pattern5), '', $tmpFilename);

    $fileExtension = pathinfo($file->getBasename(), PATHINFO_EXTENSION);

    if (preg_match('/(mp4|mov|mpg|mpeg|wmv|mkv|avi)$/i', $fileExtension)) {
        
        $originalFilename = $file->getSubPathname();
        $newFilename = $tmpFilename . "." . $fileExtension;

        rename($path . $originalFilename, $path . $newFilename);

        echo 'New filename: ' . $newFilename . "\n";

        Delete_recursive($file->getSubPath());
    }
}

function Delete_recursive($toDelete)
{
    if (is_dir($toDelete)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($toDelete, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($toDelete);
    } else {
        unlink($toDelete);
    }
}
