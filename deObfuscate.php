<?php

$path = getcwd() . "/";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
    if ($fileInfo->getBasename() === '.DS_Store' || $fileInfo->getBasename() === 'processDir.php'|| preg_match('/sample/i', $fileInfo->getBasename())) {
        continue;
    }

    $pattern1 = '/.1080p.*/i';
    $pattern2 = '/.720p.*/i';
    $pattern3 = '/.x264.*/i';
    $pattern4 = '/.DVDRip.*/i';

    $tmpFilename = $iterator->getSubPath();

    $tmpFilename = preg_replace(array($pattern1, $pattern2, $pattern3, $pattern4), '', $tmpFilename);

    $fileExtension = pathinfo($iterator->getBasename(), PATHINFO_EXTENSION);

    if (preg_match('/(mp4|mov|mpg|mpeg|wmv|mkv|avi)$/i', $fileExtension)) {
        $originalFilename = $iterator->getSubPathname();
        $newFilename = $tmpFilename . "." . $fileExtension;

        rename($path.$originalFilename, $path.$newFilename);

        echo 'New filename: ' . $newFilename . "\n";

        delete_recursive($iterator->getSubPath());
    }
}

function delete_recursive($toDelete)
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
