
CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
-----------
 * DBLOG FILTER module allows you to store only filtered db log messages based
   on log type and log level.
 * Useful to restrict unwanted message types and reduce watchdog size for
    better performance.
 * Majorly useful in production sites when we want to log only
    limited messages.
 * Logging can be restricted either by the Levels. To Log only Error Level logs
   for a site, you can check "Errors" under "Select Severity Levels." in the
   configuration page.
 * Custom logging can also be done ignoring all the core logs too, by setting
   the values under "Enter DB Log type and Severity Levels."

INSTALLATION
------------
 * Install as you would normally install a contributed drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-8
   for further information.

CONFIGURATION
-------------
 * After installation copy below lines in sites/default/services.yml
    services:  
       logger.dblog:    
          alias: dblog_filter.log
 * Don't forgot to Clear cache of your site.
 * Go to http://YOURSITE/admin/config/development/dblog-filter
   to configure type and level.
 * To restrict by Severity Level, Select the allowed severity levels.
 * To restrict by Custom Logging, set it up under "Enter DB Log type
   and Severity Levels."
   Give one per line as (TYPE|LEVEL)
    - php|notice,error,alert
    - mymodule|notice,warning
   This shows up all the messages of type "php" and "mymodule", with with their
   respective severity levels.
   Logs Recorded(as per the above settings):
   1. \Drupal::logger('mymodule')->notice('@build:',
        array('@build' => print_r($build, true)));
   2. \Drupal::logger('php')->alert('@build:',
        array('@build' => print_r($build, true)));
   Logs not recorded:
   1. \Drupal::logger('php')->warning('@build:',
       array('@build' => print_r($build, true)));
