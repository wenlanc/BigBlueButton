# BigBlueButton

Module of [Gibbon][gibbon]. Allows schools to include bbb video meeting in the lesson.

[gibbon]: https://gibbonedu.org

## Installation & Support

To install, just copy this folder into `GIBBON_HOME/modules` so that this file
exists: `GIBBON_HOME/modules/BigBlueButton/manifest.php`.

Then please install bigbluebutton by composer.

composer require bigbluebutton/bigbluebutton-api-php:~2.0.0

And then please add following on bigbluebutton server

/usr/share/bbb-web/WEB-INF/classes/bigbluebutton.properties

allowRequestsWithoutSession=true

For support visit [https://gibbonedu.org/support](https://gibbonedu.org/support).

## Compatibility

* v1.0.00 supports Gibbon v25+

## License

This module is licensed to the GPLv3.