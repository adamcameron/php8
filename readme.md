# PHP8 containers

## Dependencies

- Docker installed (from https://www.docker.com/get-started).
- Git installed (from https://git-scm.com/downloads).

## Installation

Clone the repository (URL TBC):
```bash
~/src$ git clone git@github.com:adamcameron/php8.git
```

In a terminal, navigate to the `docker` directory, and run the following commands:
```bash
~/src$ cd php8/docker
~/src/php8/docker$ docker-compose build
[usual docker output elided]
~/src/php8/docker$ docker-compose up --detach
[+] Running 3/3
 ⠿ Network php8_default    Created                                                                                                                                                                       0.0s
 ⠿ Container php8-php-1    Started                                                                                                                                                                       0.7s
 ⠿ Container php8-nginx-1  Started
~/src/php8/docker$ 
```

In dev, composer dependencies will not have been installed, so will need to be installed manually:
- open a terminal and navigate to the `php8` directory:

```bash
~/src/php8/docker$ docker exec -it php8-php-1 /bin/bash
/var/www#
```
- then run `composer install`:

```bash
/var/www# composer install

Installing dependencies from lock file (including require-dev)
Verifying lock file contents can be installed on current platform.
Package operations: 49 installs, 0 updates, 0 removals
  - Downloading composer/pcre (3.1.0)
  - Downloading psr/log (3.0.0)
  - Downloading psr/cache (3.0.0)
  - [etc]
  Generating autoload files
41 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
/var/www#
```

This will create and populate the `vendor` directory, using the versions of the dependencies specified in `composer.lock`.

The app should now be runnable. Test the installation:

```bash
/var/www# composer test
```

This will - at a minimum - test that:
- PHPUnit is operational.
- the correct PHP version (`8.2.x`) is installed
- `composer validate` passes.

Sample output:
    
```bash
/var/www# composer test
> phpunit --testdox test
PHPUnit 9.5.28 by Sebastian Bergmann and contributors.

Tests of the Composer installation
 ✔ It passes composer validate

Tests of the PHP installation
 ✔ It has the expected PHP version

Time: 00:03.278, Memory: 8.00 MB

OK (2 tests, 2 assertions)

Generating code coverage report in HTML format ... done [00:01.929]
/var/www#
```

## Usage

- Browse to `http://localhost:8008/test.php` to see a dump of `phpinfo()`
- Browse to `http://localhost:8008/test-coverage-report/` to see the PHPUnit test coverage report.
Currently it is not showing 100% test coverage by design,
to demonstrate how code coverage is presented in the output report.
