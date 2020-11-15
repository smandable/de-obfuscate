<?php

system("stty -icanon"); // this is so that you don't have to hit enter when you type 'Y' to continue

$path = getcwd() . "/"; // current directory we are working in 

$originalAndNewNames = array();

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
);

echo "\n";

foreach ($iterator as $file) {

    // ignore the following:
    if ($file->getBasename() === '.DS_Store' || $file->getBasename() === 'deObfuscate.php' || preg_match('/sample/i', $file->getBasename())) {
        continue;
    }

    // patterns to be deleted right away
    $pattern1 = '/.720p.*/i';
    $pattern2 = '/.1080p.*/i';
    $pattern3 = '/.2160p.*/i';
    $pattern4 = '/.x264.*/i';
    $pattern5 = '/.DVDRip.*/i';
    $pattern6 = '/.hd.*/i';
    $pattern7 = '/.mp4.*/i';

    $tmpFilename = $iterator->getSubPath();  //get directory name

    $tmpFilename = preg_replace(array($pattern1, $pattern2, $pattern3, $pattern4, $pattern5, $pattern6, $pattern7), '', $tmpFilename); // get rid of these right away

    $tmpFilename = basicFunctions($tmpFilename); // periods, underscores, dashes, etc. to spaces

    $tmpFilename = titleCase($tmpFilename); // convert to title case

    $tmpFilename = preg_replace('/\D+$/', '', trim($tmpFilename)); // gets rid of everything after the last number in case anything is missed

    $fileExtension = pathinfo($file->getBasename(), PATHINFO_EXTENSION); // add file extension

    if (preg_match('/(mp4|mov|mpg|mpeg|wmv|mkv|avi)$/i', $fileExtension)) { // make sure they are video files

        $originalFilename = $iterator->getSubPathname(); // get name of video file in current directory being processed
        $newFileName = $tmpFilename . "." . $fileExtension;  // add extension to new filename

        echo 'New filename: ' . $newFileName . "\n"; // display new name to verify it looks good

        $originalFilename = $path . $originalFilename;  // add path to file to be renamed
        $newFileName = $path . $newFileName;  // add path to new filename
        $dirToDelete = $iterator->getSubPath(); // get directory to be deleted

        // Everything into object array to be passed to rename_files function
        $originalAndNewNames[] = array('origFileName' => $originalFilename, 'newFileName' => $newFileName, 'dirToDelete' => $dirToDelete);
    }
}

echo "\nContinue?  Y/n "; // prompt to continue

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

function basicFunctions($tmpFilename)
{
    $tmpFilename = preg_replace('/\./', ' ', trim($tmpFilename)); // periods to spaces
    $tmpFilename = preg_replace('/_/', ' ', trim($tmpFilename)); // underscores to spaces
    $tmpFilename = preg_replace('/-/', ' ', trim($tmpFilename)); // dash to space
    $tmpFilename = preg_replace('/\s+/', ' ', trim($tmpFilename)); // filter multiple spaces
    $tmpFilename = preg_replace('/\.+/', '.', trim($tmpFilename)); // filter multiple periods
    $tmpFilename = preg_replace('/^\.+/', '', trim($tmpFilename)); // trim leading period
    $tmpFilename = preg_replace('/\s+$/', '', trim($tmpFilename)); // trim trailing space

    return $tmpFilename;
}

function titleCase($tmpFilename)
{
    $delimiters = array(" ");
    $exceptions = array(
        "the", "a", "an", "and", "as", "at", "be", "but", "by", "for", "in", "it", "is", "of", "off",
        "on", "or", "per", "to", "up", "via", "and", "nor", "or", "so", "yet", "with"
    );
    /*
     * Exceptions in lower case are words not to be converted
     * Exceptions all in upper case are any words not to be converted to title case
     *   but should be converted to upper case, e.g.:
     *   king henry viii or king henry Viii should be King Henry VIII
     */
    $tmpFilename = mb_convert_case($tmpFilename, MB_CASE_TITLE, "UTF-8");
    foreach ($delimiters as $dlnr => $delimiter) {
        $words = explode($delimiter, $tmpFilename);
        $newwords = array();
        foreach ($words as $wordnr => $word) {
            if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
                // check exceptions list for any words that should be in upper case
                $word = mb_strtoupper($word, "UTF-8");
            } elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
                // check exceptions list for any words that should be in upper case
                $word = mb_strtolower($word, "UTF-8");
            } elseif (!in_array($word, $exceptions)) {
                // convert to uppercase (non-utf8 only)
                $word = ucfirst($word);
            }
            //$word = ucfirst($word);

            array_push($newwords, $word);
        }
        $tmpFilename = join($delimiter, $newwords);
        $tmpFilename = ucfirst($tmpFilename);
    }

    return $tmpFilename;
}

function rename_files(&$originalAndNewNames) // passing array in by reference
{
    for ($i = 0; $i < count($originalAndNewNames); $i++) {
        rename($originalAndNewNames[$i]['origFileName'], $originalAndNewNames[$i]['newFileName']);
        $dirToDelete = $originalAndNewNames[$i]['dirToDelete'];
        delete_directory($dirToDelete);
    }
}

function delete_directory($dirToDelete)
{
    // have to do this recursively in case directory not empty
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

system("stty sane"); // reset terminal back
