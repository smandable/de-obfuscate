<?php

$config = [
    'ignoreFiles' => ['.DS_Store', 'de-obfuscate-no-normalizing.php'],
    'ignorePatterns' => ['/sample/i'],
    'deletePatterns' => [
    '/480p/i',
    '/720p/i',
    '/1080p/i',
    '/2160p/i',
    '/4k/i',
    '/x264/i',
    '/DVDRip/i',
    '/hd/i',
    '/mp4/i'
],
    'validExtensions' => ['mp4', 'mov', 'mkv', 'avi', 'wmv', 'mpeg', 'flv'],
    'dryRun' => false // Set to false to enable actual file renaming and directory deletion
];

function shouldIgnore($file, $ignoreFiles, $ignorePatterns = []) {
    $basename = $file->getBasename();

    // Exact match ignore
    if (in_array($basename, $ignoreFiles)) {
        return true;
    }

    // Pattern-based ignore (e.g., filenames containing "sample")
    foreach ($ignorePatterns as $pattern) {
        if (preg_match($pattern, $basename)) {
            unlink($file->getPathname());
            echo "Deleted pattern-matched file: {$basename}\n";
            return true;
        }
    }

    return false;
}

function sanitizeFilename($filename, $patterns)
{
    $lowestPos = strlen($filename);
    $matchFound = false;

    // Find the earliest occurrence of any delete pattern
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $filename, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];
            if ($pos < $lowestPos) {
                $lowestPos = $pos;
                $matchFound = true;
            }
        }
    }

    // If a match is found, truncate the filename at the earliest match
    if ($matchFound) {
        $filename = substr($filename, 0, $lowestPos);
    }

    // Replace spaces, underscores, and dashes with periods
    $filename = preg_replace(['/[_\s\-]+/'], ['.'], $filename);

    // Collapse multiple periods into one
    $filename = preg_replace('/\.+/', '.', $filename);

    // Trim leading or trailing periods
    $filename = trim($filename, '.');

    return $filename;
}

function isValidExtension($extension, $validExtensions)
{
    return in_array(strtolower($extension), $validExtensions);
}

function generateNewFilename($baseName, $extension)
{
    if (substr($baseName, -strlen($extension) - 1) === ".$extension") {
        return $baseName; // Already has the correct extension
    }
    return $baseName . '.' . $extension;
}

function confirmChanges($filePairs)
{
    usort($filePairs, function ($a, $b) {
        return strcmp(strtolower(basename($a['newFileName'])), strtolower(basename($b['newFileName'])));
    });

    echo "\nThe following filenames will be created (sorted alphabetically):\n\n";
    foreach ($filePairs as $pair) {
        echo basename($pair['newFileName']) . "\n";
    }
    echo "\nContinue? Y/n: ";
    system("stty -icanon");
    $handle = fopen("php://stdin", "r");
    $line = fgetc($handle);
    fclose($handle);
    system("stty sane");
    echo "\n\n";
    return strtolower(trim($line)) === 'y';
}

function deleteDirectoryRecursively($directory)
{
    if (!is_dir($directory)) {
        return;
    }

    $files = array_diff(scandir($directory), ['.', '..']);

    foreach ($files as $file) {
        $filePath = "$directory/$file";
        if (is_dir($filePath)) {
            deleteDirectoryRecursively($filePath);
        } else {
            unlink($filePath);
        }
    }

    rmdir($directory);
    // echo "Deleted directory: $directory\n";
}

function processRenames($filePairs, $dryRun)
{
    usort($filePairs, function ($a, $b) {
        return strcmp(strtolower(basename($a['newFileName'])), strtolower(basename($b['newFileName'])));
    });

    foreach ($filePairs as $index => $pair) {
        // echo "Renaming: " . basename($pair['origFileName']) . " to " . basename($pair['newFileName']) . "\n";
        if (!$dryRun) {
            if (rename($pair['origFileName'], $pair['newFileName'])) {
                // After renaming, delete the now-empty original directory
                $originalDirectory = dirname($pair['origFileName']);
                if (is_dir($originalDirectory)) {
                    deleteDirectoryRecursively($originalDirectory);
                }
            } else {
                echo "Failed to rename.\n";
            }
        }
    }
    echo "\n";
}

// Main Script
$path = getcwd() . "/";

// Check if there are any subdirectories
$hasSubdirectories = false;
$dirIterator = new DirectoryIterator($path);
foreach ($dirIterator as $item) {
    if ($item->isDir() && !$item->isDot()) {
        $hasSubdirectories = true;
        break;
    }
}

if (!$hasSubdirectories) {
    echo "No directories found, aborting.\n";
    exit;
}
$originalAndNewNames = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (shouldIgnore($file, $config['ignoreFiles'], $config['ignorePatterns'])) {
    continue;
}

    // Skip files that are directly in the current working directory (not in a subdirectory)
    if ($file->getPath() === rtrim($path, '/')) {
        continue;
    }

    // Extract the parent directory name
    $parentDir = basename($file->getPath());
    // echo "Parent Directory: $parentDir\n";

    // Sanitize the parent directory name
    $sanitizedDirName = sanitizeFilename($parentDir, $config['deletePatterns']);
    // echo "Sanitized Directory Name: $sanitizedDirName\n";

    // Validate the file extension
    $fileExtension = strtolower(pathinfo($file->getBasename(), PATHINFO_EXTENSION));
    if (!isValidExtension($fileExtension, $config['validExtensions'])) {
        // echo "Invalid Extension Skipped: $fileExtension\n";
        continue;
    }

    // Generate the new filename
    $newFileName = $path . generateNewFilename($sanitizedDirName, $fileExtension);
    // echo "Generated New Filename: $newFileName\n";

    $originalAndNewNames[] = [
        'origFileName' => $file->getPathname(),
        'newFileName' => $newFileName
    ];
}

if (empty($originalAndNewNames)) {
    echo "No files to process.\n";
    exit;
}

if (confirmChanges($originalAndNewNames)) {
    processRenames($originalAndNewNames, $config['dryRun']);
} else {
    echo "Operation aborted.\n";
}
