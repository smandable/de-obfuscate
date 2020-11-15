<?php
system("stty -icanon");

$path = getcwd() . "/";

$originalAndNewNames = array();

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
);

echo "\n";

foreach ($iterator as $file) {
    if ($file->getBasename() === '.DS_Store' || $file->getBasename() === 'deObfuscate.php' || preg_match('/sample/i', $file->getBasename())) {
        continue;
    }

    $pattern1 = '/.hd.*/i';
    $pattern2 = '/.720p.*/i';
    $pattern3 = '/.x264.*/i';
    $pattern4 = '/.DVDRip.*/i';
    $pattern5 = '/.xxx.*/i';
    $pattern6 = '/.1080p.*/i';
    $pattern7 = '/.2160p.*/i';

    $tmpFilename = $iterator->getSubPath();

    $tmpFilename = preg_replace(array($pattern1, $pattern2, $pattern3, $pattern4, $pattern5, $pattern6, $pattern7), '', $tmpFilename);

    $fileExtension = pathinfo($file->getBasename(), PATHINFO_EXTENSION);

    if (preg_match('/(mp4|mov|mpg|mpeg|wmv|mkv|avi)$/i', $fileExtension)) {

        $originalFilename = $iterator->getSubPathname();
        $newFileName = $tmpFilename . "." . $fileExtension;

        echo 'New filename: ' . $newFileName . "\n";

        $originalFilename = $path . $originalFilename;
        $newFileName = $path . $newFileName;
        $dirToDelete = $iterator->getSubPath();

        $originalAndNewNames[] = array('origFileName' => $originalFilename, 'newFileName' => $newFileName, 'dirToDelete' => $dirToDelete);
    }
}

echo "\nContinue?  Y/n ";
$handle = fopen("php://stdin", "r");
$line = trim(fread($handle, 1));
if ((trim($line) == 'Y') || (trim($line) == 'y')) {
    echo "\n";
    rename_files($originalAndNewNames);
} else {
    echo "Aborted\n";
    exit;
}
fclose($handle);

function rename_files(&$originalAndNewNames)
{
    for ($i = 0; $i < count($originalAndNewNames); $i++) {
        rename($originalAndNewNames[$i]['origFileName'], $originalAndNewNames[$i]['newFileName']);
        $dirToDelete = $originalAndNewNames[$i]['dirToDelete'];
        delete_directory($dirToDelete);
    }
}

function delete_directory($dirToDelete)
{
    if (is_dir($dirToDelete)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirToDelete, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dirToDelete);
    } else {
        unlink($dirToDelete);
    }
}
system("stty sane");
