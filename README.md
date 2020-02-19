# deObfuscate
Fixes obfuscated filenames by renaming them to the name of the directory they are contained in.

When one obtains a file via NNTP, or BitTorrent, or whatever, they are often "obfuscated". 

What this means is that, when it's done downloading, you'll have a directory named (for example): 

ShowTitle.S01E01.1080p.HDTV.x264-AVS 

This is all well and good- but the file contained within this directory is named something slightly less useful:

41948484cf014f3f994f605c2e842aa6.mp4

This script will rename each file with the name of its parent folder, and will also find and remove common patterns in said directory name, like "1080p", "720p", "x264", "DVDRip", etc.

The example given above would be renamed to "ShowTitle.S01E01.mp4".

It'll work on however many folders you put in the directory it's working in (I've got a "de-obfuscate" folder I use), and will also delete the original folders so you'll be left with nothing but the filenames.



