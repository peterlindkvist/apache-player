#Ampache Player 
This is an old (2008) unsupported project!!

## Background
The background to this project is that I didn’t like the html interface for the [Ampache player](http://ampache.org/), I wanted it to work more like an desktop application.

The Player is written in Flex, Actionscript 3 and PHP and uses AMF to communicate between server and client. The AMFPHP service is used to make querys to a Ampache database and the filesystem.


## Installation

- if you don’t have Ampache installed, install it and make sure it works.
- if you don’t have AMFPHP installed, install it and make sure it works.
- copy the ampache folder to the installed servicefolder in amfphp
- check the variables in settings.php in the ampache service folder.
- if the installation folder struture is not the same as zip. check the require in assets.php to point to the amfphp service
- browse to ampache_player.html and hope for the best.

### optional

- Make you own stylesheet with the Flex2StyleExplorer and replace style.css.
- Write your own spectrum visualisation and upload to the visualisations folder, its easier then you think. Then you can select it in the settings tab.

### Requirements
use:

- flashplayer >= 9

install:

- amfphp installation (tested on amfphp-1.9.beta.200705139)
- ampache installation (tested on ampache-3.3.3.5)

compile:

- flex 2 SDK (tested on flex 2.01)

### Limitations

AMF has some limitations in the message size and should not exceed 40Kb which is less then 2000 songs. To be able to load the complete catalog the querys is splited into 1000 song at the time. The consequence is that a 50k songs catalog makes 50 requests to the server the first time the songs are loaded. The server might not like that since it can be pretty CPU heavy.

The speed of the filter as you type in the filter input field is proportional to the number of songs. A tests with 100k songs takes about 400ms for every character with my 2GHz single core laptop.

Local Shared Objects has a default limit to 100Kb, since the complete catalog is going to be stored the settings dialog pops up and the user has to accept a larger LSO. 100k songs becomes about 3mb of LSO.

There is a bug in the Datagrid if the same object is represented more then one time in the same grid.

## Version history

#### 0.1 (no public release)

+ browse the songs and dragdrop to playlist.
+ play songs

#### 0.2

+ visualisation
+ stylesheet
+ move things to trash from the playlist

#### 0.3

+ security fixes
+ enabled login
+ rewritten to Cairngorm
+ local shared objects to store settings and catalogs.
+ some player button fixes
+ settings tab
+ double click to move and play

####0.4

+ split download of catalog to 1000 songs at the time.
+ filtering of songs
+ random play
- hard coded urls.

#### 0.5

+ playlist support, it should be compatile with the ampache playlist.
- dropped the need for ampache. The only thing used now is the database and mp3s.

#### 0.6

+ upload support. limited by the max post size in php.ini. Do not fetch covers arts.
+ add some random files to playlist button
+ only download changes in the catalog.
+ automatic update from catalog.
+ folder drag improvements

#### 0.7
+ tripleclick on song to add whole album.
+ playlist clear button.
+ some player bugfixes.

#### Future:
- automatic update of LSO when the ampache catalog is updated. [fixed in 0.6]
- only load changes in the catalog. [fixed in 0.6]
- enable all of the settings [took away some settings]
- better working covers
- folder drop improvements [fixed in 0.6]

There is also a tread about this at [http://ampache.org/forums/viewtopic.php?pid=9273](http://ampache.org/forums/viewtopic.php?pid=9273)