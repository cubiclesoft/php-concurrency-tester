PHP-based Concurrency Tester
============================

A simple program that executes another PHP command-line script and (hopefully) collects output in CSV format for later analysis.  Mostly for performance testing/verifying localhost TCP/IP servers.

[![Donate](https://cubiclesoft.com/res/donate-shield.png)](https://cubiclesoft.com/donate/) [![Discord](https://img.shields.io/discord/777282089980526602?label=chat&logo=discord)](https://cubiclesoft.com/product-support/github/)

Features
--------

* A cheesy 100-ish line script that executes another script.
* Nothing particularly special beyond that.
* Has a liberal open source license.  MIT or LGPL, your choice.
* Designed for relatively painless integration into your project.
* Sits on GitHub for all of that pull request and issue tracker goodness to easily submit changes and ideas respectively.

Getting Started
---------------

Write a script that communicates with a TCP/IP service similar to [this one](https://github.com/cubiclesoft/php-license-server/blob/master/concurrency_test.php).

Run `run_tests.php`:

```
php run_tests.php yourscript.php 20
```

Runs the script `yourscript.php` at a concurrency level of 20.  The first parameter passed to `yourscript.php` is a timestamp to start running.  This allows the tester to start the correct number of processes.

Note that if the TCP/IP server is fast enough, connecting and disconnecting can starve the process of available TCP/IP socket handles.  Instead, simulate connection times by using a microsecond sleep function.

This isn't the world's greatest benchmarking tool.  In fact, it is probably severely flawed in some fundamental way as are most benchmarking tools.  It's merely here to provide some basic performance checks against custom TCP/IP servers.
