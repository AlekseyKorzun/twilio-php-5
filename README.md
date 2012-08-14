Twilio-PHP-5
===========

A re-factored plug-n-play PHP 5 client for Twilio's REST API for custom frameworks.

Features
===========
- Direct plug-in-play integration with PHP 5 based frameworks
- Namespace utilization, ability to piggy back off your own autoloader
- Completely refactored to remove clutter and omit ridiculous constructor inheritance the original package was build with
- Various optimizations, no longer pre-loads all of the available actions when an idle instance is created
- Cleaner file structure
- Single coding standard across all of the components
- 100% phpDocumentator 2 code coverage
- 100% PSR2 standard coverage

Usage
===========
If you have your own autoloader, simply update namespaces and drop the files
into your frameworks library.

For people that do not have that setup, you can visit http://getcomposer.org to install
composer on your system. After installation simply run `composer install` in parent
directory of this distribution to generate vendor/ directory with a cross system autoloader.

Please see Examples directory for a simple run down of functionality.

Advanced
===========

Default namespace is setup as Twilio, you can either manually change it or use included namespace.sh script to update it

Bugs & Feedback
===========
Feel free to reach me via e-mail al.ko@webfoundation.net
