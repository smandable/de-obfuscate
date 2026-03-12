# de-obfuscate

A PHP CLI tool that cleans up obfuscated video filenames by extracting a readable name from the parent directory, sanitizing it, and renaming the file.

## How It Works

Run the script from a directory containing subdirectories with video files. The script:

1. Scans subdirectories recursively for video files (files in the working directory itself are ignored)
2. Uses the **parent directory name** as the basis for the new filename
3. Strips common junk patterns (resolution tags, codec info, etc.) from the name
4. Normalizes separators (spaces, underscores, dashes) to periods
5. Shows a preview of all proposed renames and asks for confirmation
6. Renames the files into the working directory and deletes the now-empty subdirectories

## Usage

```bash
cd /path/to/your/video/folders
php /path/to/de-obfuscate.php
```

### Example

Given this structure:

```
videos/
  Some.Movie.Name.1080p.x264-GROUP/
    some.movie.name.1080p.x264-group.mkv
  Another Title 720p DVDRip/
    another.title.720p.dvdrip.avi
```

The script produces:

```
videos/
  Some.Movie.Name.mkv
  Another.Title.avi
```

## Configuration

Edit the `$config` array at the top of the script:

| Key | Description |
|---|---|
| `ignoreFiles` | Filenames to skip entirely (e.g. `.DS_Store`) |
| `ignorePatterns` | Regex patterns — matching files are **deleted** (e.g. `/sample/i`) |
| `deletePatterns` | Regex patterns stripped from directory names during sanitization (e.g. `/1080p/i`, `/x264/i`) |
| `validExtensions` | Only files with these extensions are processed |
| `dryRun` | Set to `true` to preview changes without renaming |

## Requirements

- PHP 7.0+
