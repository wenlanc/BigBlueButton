# BigBlueButton

Module for [Gibbon][gibbon]. Allows schools to include bbb video meetings in their lessons.

[gibbon]: https://gibbonedu.org

## Installation & Support

To install, just copy this folder into `GIBBON_HOME/modules` so that this file
exists: `GIBBON_HOME/modules/BigBlueButton/manifest.php`.

Then please install BigBlueButton with composer by running the following inside the BigBlueButton module directory:

`cd GIBBON_HOME/modules/BigBlueButton`  

`composer install`

And then please add following on bigbluebutton server in `/usr/share/bbb-web/WEB-INF/classes/bigbluebutton.properties` or `/etc/bigbluebutton/bbb-web.properties`

`allowRequestsWithoutSession=true`

For support visit [https://gibbonedu.org/support](https://gibbonedu.org/support).

## Compatibility

* v1.0.04 supports Gibbon v25+

## License

This module is licensed under GPLv3.
