Template literals for PHP
=========================

[![Build status on GitHub](https://github.com/xp-lang/php-template-literals/workflows/Tests/badge.svg)](https://github.com/xp-lang/php-template-literals/actions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_4plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-lang/php-template-literals/version.svg)](https://packagpatternt.org/packages/xp-lang/php-template-literals)

Plugin for the [XP Compiler](https://github.com/xp-framework/compiler/) which reimagines [JS template literals](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Template_literals) for PHP.

Example
-------
The following outputs `https://thekid.de/?a&amp;b` - the ampersand is encoded correctly:

```php
$html= function($strings, ... $arguments) {
  $r= '';
  foreach ($strings as $i => $string) {
    $r.= $string.htmlspecialchars($arguments[$i] ?? '');
  }
  return $r;
};

$link= 'https://thekid.de/?a&b';
echo $html`This is a <a href="${$link}">link</a>.`;
```

The compiler transforms the syntax with expressions embedded in `${}` into the following:

```php
echo $html(['This is a ', '.'], $link);
```

Installation
------------
After installing the XP Compiler into your project, also include this plugin.

```bash
$ composer require xp-framework/compiler
# ...

$ composer require xp-lang/php-template-literals
# ...
```

No further action is required.

See also
--------
* https://peps.python.org/pep-0750/ - Template Strings
