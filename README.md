Twilio-PHP5
===========

A re-factored plug-n-play Twilio PHP 5 client for custom frameworks.

Features
===========
* Direct plug-in-play integration with PHP 5 based frameworks
* Namespace utilization, ability to piggy back off your own autoloader
* Completely refactored to remove clutter and omit ridiculous constructor inheritance the original package was build with
* Various optimizations, no longer pre-loads all of the available actions when an idle instance is created
* Cleaner file structure
* Single coding standard across all of the components
* 100% phpDocumentator 2 code coverage

Usage
===========
* This package assumes that you have autoloader setup within your framework, if you do not feel free to use included composer.json. You will have to include generated outloader on top of Twilio.php
* Default namespace is setup as Library and Library\Twilio, you can either manually change it or use included namespace.sh script to update it 

Bugs & Feedback
===========
Feel free to reach me via e-mail al.ko@webfoundation.net
