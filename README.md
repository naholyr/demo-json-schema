demo-json-schema
================

For PHP Tour Nantes 2012

```
$ wget http://getcomposer.org/composer.phar
$ php composer.phar install
$ git submodule init
$ git submodule update
```

CLI
---

Building proper object, step by step, only guided by error messages :

```
$ echo "{}" | php cli.php
JSON does not validate. Violations:
[title] is missing and it is required
[authors] is missing and it is required
[isbn] is missing and it is required
[price] is missing and it is required
```

```
$ echo '{"title":"some book", "authors": [], "isbn": "some code", "price": "some value"}' | php cli.php
JSON does not validate. Violations:
[authors] There must be a minimum of 1 in the array
[isbn] does not match the regex pattern [0-9]{13}
[price] string value found, but a number is required
```

```
$ echo '{"title":"some book", "authors": [{}], "isbn": "1234567890123", "price": "123.456"}' | php cli.php
JSON does not validate. Violations:
[authors[0].name] is missing and it is required
[authors[0].birthdate] is missing and it is required
```

```
$ echo '{"title":"some book", "authors": [{"name": "some guy", "birthdate": "some date"}], "isbn": "1234567890123", "price": "123.456"}' | php cli.php
JSON does not validate. Violations:
[authors[0].birthdate] does not match the regex pattern [0-9]{4}-[0-9]{1,2}-[0-9]{1,2}
```

```
$ echo '{"title":"some book", "authors": [{"name": "some guy", "birthdate": "1980-1-1"}], "isbn": "1234567890123", "price": "123.456"}' | php cli.php
The supplied JSON validates against the schema.
```

Hurray \o/

REST API
--------

TODO
