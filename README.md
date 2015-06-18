# Selfie
##Site backup utility for Clipper CMS

Version 0.7

June 2015

###Purpose
Selfie is a utility for backing up a Clipper CMS site, including files and database in one zip file. A default configuration file allows selection of regularly required backups (e.g. just the assets folder and database). Adjustments can be made before a backup run starts for one-off inclusion or exclusion of parts of the system. The resulting archive is saved to a specified folder on the site, and is offered as a download link at the end of the process.

While oriented towards Clipper CMS, Selfie has been successfully tested on a basic MODX Evolution install and should work with an alternative manager folder location. Further development will aim to include configuration suitable for backing up Wordpress sites as well.

###System requirements: 
Selfie has been tested on systems with PHP >= 5.3:
- Linux shared hosting with Ubuntu, Apache, MySql 5.5 and PHP 5.3 (CGI/FastCGI); 
- Windows 7 PC with Apache 2.4, MySql 5.5 and PHP 5.5 (Apache module);
- Linux Centos 7 system with Nginx, MariaDB 5.5 and PHP 5.4 (FPM/FastCGI).

###Installation:
Copy the selfie folder to the target system

Set up password protection if required, e.g. with .htaccess. Selfie will use PHP_AUTH if there is anything in the password section of the configuration file. Nginx needs some entries in the server configuration to handle this.

###Configuration:
Paths and default settings are stored in arrays in the file `selfie.config.php`

*$config array*:

*site*: prefix for the archive file and SQL database dump file. Defaults to domain name if not set

*site_root*: file path to site root, either relative to the Selfie directory or an absolute path

*mgr_path*: path to Clipper manager folder, relative to Selfie directory

*archive*: path to directory where archives are stored, relative to Selfie folder or absolute path

*folders*: comma-separated list of folders (root-relative) to be included in archive by default. (Assets and manager 

folders can be overridden via checkbox)

*rootFiles*: currently `clipper` or `*` includes whichever of index.php, index-ajax.php, .htaccess and robots.txt are present in the root of the site

*PHP*: if Selfie's own password protection is being used, and system uses CGI version of PHP, this should be set to CGI

*$passwords array*: this is a set of user names and passwords in the format "user" => "password". If any are set, Selfie will use them for PHP_AUTH protection of the index.php file. 

###Operation:
Visit the URL of the Selfie folder, adjust the selection of folders, root files and database via the checkboxes, and click Start. Progress is indicated by a progress bar and percentage readout, plus list of current steps. On completion, the archive can be downloaded via the supplied hyperlink. The archive file is names using the "site" name if supplied, or domain name if not, plus date and time, e.g. MYSITE_150622_45.zip. 

On completion, the index file remains on screen. You can add to the archive by setting a checkbox that was initially cleared (and clearing the ones that were set, to avoid needless repetition) and clicking Start again. 

###Errors:
Nothing can go wrong. There are no bugs. (This information may be subject to updates).

###Future development:
Enhancements may include
- clear cache files
- trim or clear log files
- improve progress reporting behaviour 
- ensure compatibility with MODX Evo
- configurations for Wordpress sites

###Kudos: 
Recursive file save: [https://gist.github.com/MarvinMenzerath/4185113]

Server-sent events: [http://www.htmlgoodies.com/beyond/php/show-progress-report-for-long-running-php-scripts.html]

Shim for SSE in Internet Explorer: [http://html5doctor.com/server-sent-events/]
