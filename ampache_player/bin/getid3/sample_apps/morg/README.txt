
MORG - Music ORGanizer - a getID3() 2.0 sample application
==============================================================================
$Id: README.txt,v 1.4 2007/02/12 09:54:04 ah Exp $



1. Introduction
------------------------------------------------------------------------------

Please see the screen_shots directory for some of the things MORG does.

MORG is supposed to be an educational application that demonstrates complex
issues, such as:

- How to scan a directory structure resursively.
- How to cleverly store getID3() output in a database.
- How to handle different audio/tags formats in a uniform way.
- How to cache getID3() results.
- How to search the database/metadata with multiple options.
- How to find duplicate files.
- How to protect your project by ip/ip ranges.
- How to support non English characters (at least for European languages)
- How to stream files.

But it could also be the base for your own music-organising-and-playing 
application. By using MORG as your backbone, you may cut away lots of 
development hours and avoid handling some of the complex issues.

MORG is in the public domain. You can modify it anyway you like to fit your 
needs. Bear in mind though, that the getID3() library is licenced under the 
GNU Public License.

    

2. System Requirements
------------------------------------------------------------------------------

    UNIX operating system (recommended)
    -or- Microsoft Windows
    
    Apache webserver (recommended)
    -or- another web server
    
    PHP 5.1.4 or newer
      - with mysql support
      - with iconv support
      - with mbstring support
      - safe mode disabled
    
    MySQL 5.0.19 or newer
      - with InnoDB support

    Access to the following binaries
    	- vorbiscomment  (if music collection contains ogg vorbis files)
	- shorten binary (if music collection contains shorten files)
	- getID3() Windows Support (under Windows)
    
    crontab (UNIX only)
    -or- Task Scheduler (Windows only)



3. Installation
------------------------------------------------------------------------------

Copy/move the getid3 directory into the morg directory.

Place the morg directory somewhere under your web server's document root. Feel
free to rename morg to anything you like.
    
Copy/rename config.php.sample to config.php and edit with mysql database 
parameters.
    
Import the database dump, morg.sql.
    
Point your browser to index.php and click the gear wheel icon to finalise the 
configuration. You should start by protecting the configuration utility with a 
password. Scroll down to the Security Section and set one.


Configuring crontab (UNIX only):

    Start by makig update.php executable:				
    chmod o+x update.php 							
    
    Then execute:  
    crontab -e
    and add this line:
    5 * * * * /bin/nice /path/to/update.php -q
    
    This will run morg hour (five minutes past, i.e. 1:05, 2:05, 3:05, etc)
      

Configuring Task Scheduler (Windows only):

    Schedule      
    	php.exe < update.php
    to run once every hour



4. Supported Audio Formats
------------------------------------------------------------------------------

MORG can read many different audio formats through the getID3() library.
Please refer to http://www.getid3.org for a current list of supported formats.

Note: Shorten support requires the shorten binary.
Note: Ogg Vobis support requires the vorbiscomment binary.

The advanced features in MORG works best on properly tagged files.
