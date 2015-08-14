<?php

$template['functions']['main'] = <<< EOF
<%='<?php'%>

//    __  __          __                                            __
//   / / / /___  ____/ /__  ___________________  ________    ____  / /_  ____
//  / / / / __ \/ __  / _ \/ ___/ ___/ ___/ __ \/ ___/ _ \  / __ \/ __ \/ __ \
// / /_/ / / / / /_/ /  __/ /  (__  ) /__/ /_/ / /  /  __/ / /_/ / / / / /_/ /
// \____/_/ /_/\__,_/\___/_/  /____/\___/\____/_/   \___(_) .___/_/ /_/ .___/
//                                                       /_/         /_/
//
// Build Version: <%=\$build_version%>

// Last Update:   <%=\$last_update%>

//

require_once __DIR__ . '/Underscore/Underscore.php';
require_once __DIR__ . '/Underscore/Bridge.php';

use Underscore\Underscore;
use Underscore\Bridge;

<%=\$content%>

EOF;

$template['functions']['function'] = <<< EOF
function _<%=\$function->name%>(<%=\$function->argumentsAsString%>)
{
	return Underscore::<%=\$function->name%>(<%=\$function->argumentsAsStringNoRefNoValue%>);
}


EOF;

$template['markdown']['main'] = <<< EOF

[![Build Status](https://travis-ci.org/bdelespierre/underscore.php.svg?branch=master)](https://travis-ci.org/bdelespierre/underscore.php)

&#187; <a href="#table-of-contents">Table of contents</a>

# Underscore.php

PHP lacks consistency, that's a fact. Many functions within a similar field — for instance array functions — may have inconsistent names and prototypes. Underscore.php aims to correct that by providing simple, consistent and data-type tolerant 80-odd functions that support both the usual functional suspects: map, select, invoke — as well as more specialized helpers: function binding, php templating, deep equality testing, and so on.

Underscore.php is __strongly__ inspired by [Underscore.js](http://underscorejs.org/) and try to be consistent with it as much as possible (PHP language limitation doesn't allow full coverage — especialy for Functions functions...) Don't hesitate to [report](https://github.com/bdelespierre/underscore.php/issues) any discrepancy with Underscore.js.

## Features

+ Made with &#9829; for PHP 5.4+
+ Type tolerant
+ Triggers exceptions instead of errors
+ Consistent function names / arguments
+ Hassle-free chaining
+ ERB-style templating
+ Extensible

## Heads up!

This library is in __beta__ phase: you are strongly encouraged to try it and to contribute.Feel free to [file an issue](https://github.com/bdelespierre/underscore.php/issues) if you encounter a bug or an unexpected result.

## About data-type tolerance

Juggling with types in PHP can be tedious. Not only types are sometimes ambiguous, they don't fit in every API function/method. For instance, if you want to map every item from an iterator using [array_map](http://php.net/manual/en/function.array-map.php), you have to translate it into an array first or write the mapping yourself using a loop. Same goes for [sort](http://php.net/manual/en/function.sort.php), [diff](http://php.net/manual/en/function.array-diff.php) or [filter](http://php.net/manual/en/function.array-filter.php)...

PHP is loosely typed, which means that the data you're manipulating are more important than their structure. Underscore.php understands that by providing a comprehensive interface that works with almost every data-type so you don't have to worry about whether you can or cannot use a function/method.

Basically, Underscore.php uses 3 main data-types:

+ Scalar (integer, float, boolean, or string)
+ Traversable (array, object, or iterator)
+ Callable (closure, function, method, or runtime-created function)

When a fuction requires a Traversable as argument, you can provide either an array, an instance of stdClass — the default `(object)` casting — an Iterator or anything that implements the Traversable interface, such as a PDOStatement object. With certain functions like [#extend](_::extend), you can even extend an array with an object instance and everything will be fine.

```PHP
<%='<?php'%>

// let's merge our configuration object with data from \$_SESSION and MySQL
\$userConfig = _::extend([],
	include "global.conf.php",              // is an array
	\$pdo->query(\$groupConfigurationQuery)   // is a PDOStatement
	\$_SESSION['user']->configuration,       // is an object
);
<%='?>'%>

```

# Table of contents

<%=\$menu%>

## Installation

##### *Composer*

Add the following require rule to composer.json and run `composer update`. See the [Packagist](https://packagist.org/packages/bdelespierre/underscore) repository for more details.

~~~
require: { "bdelespierre/underscore": "dev-master" }
~~~

##### *With Git*

~~~
git clone https://github.com/bdelespierre/underscore.php ./underscore.php
~~~

##### *Manual*

~~~
curl -sS https://github.com/bdelespierre/underscore.php/archive/master.zip > underscore.php.zip
unzip underscore.php.zip && rm underscore.zip
~~~

Or simply [download the zip](https://github.com/bdelespierre/underscore.php/archive/master.zip) and extract it where you want.

## Usage

##### *Composer*

```PHP
<%='<?php'%>

require_once "vendor/autoload.php";

use Underscore\Underscore as _;

_::each([1,2,3], function (\$i) { echo "{\$i}\\n"; });
<%='?>'%>

```

##### *Manual*

```PHP
<%='<?php'%>

require_once "path/to/underscore/src/Underscore/Underscore.php";
require_once "path/to/underscore/src/Underscore/Bridge.php";

use Underscore\Underscore as _;

_::each([1,2,3], function (\$i) { echo "{\$i}\\n"; });
<%='?>'%>

```

##### *Functions*

Underscore functions can also be used as procedural functions. To do so, include the `functions.php` library. The only limitation is that you cannot dynamically add new functions with `_::mixin`.

```PHP
<%='<?php'%>

require_once "path/to/underscore/src/functions.php";

_each([1,2,3], function (\$i) { echo "{\$i}\\n"; });
<%='?>'%>

```

<%=\$content%>

EOF;

$template['markdown']['menu'] = <<< EOF
1. [Installation](#installation)
2. [Usage](#usage)
<% \$i=1; foreach (\$categories as \$category => \$functions): %>
<%=\$i++%>. [<%=\$category%>](#<%=_mdAnchor(\$category)%>)
   * <%=implode(', ', array_map('_anchor', \$functions))%>

<% endforeach %>
EOF;

$template['markdown']['category'] = <<< EOF
## <%=\$category%>

<% foreach (\$functions as \$function): %>
* <%=\$function%>

<% endforeach %>

<%=\$content%>
EOF;

$template['markdown']['function'] = <<< EOF
### <%=\$function->name%>

-----
<% if (\$function->aliases): %>

_**Alias**_: <%=implode(', ', \$function->aliases)%>

<% endif %>

_**Description**_: <%=\$function->description%>


##### *Parameters*

<% foreach (\$function->parameters as \$parameter): %>
+ *<%=\$parameter->name%>*: <%=\$parameter->type ?: 'mixed'%>, <%=\$parameter->description ?: 'no description available...'%>

<% endforeach %>

##### *Prototype*

~~~
<%=\$function->prototype%>

~~~

<% if (\$function->examples): %>
##### *Examples*

<% foreach (\$function->examples as \$example): %>
```PHP
<%='<?php'%>

<%=\$example%>

<%='?>'%>

```

<% endforeach %>
<% endif %>
<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#<%=_mdAnchor(\$function->category)%>"><%=\$function->category%></a></p>


EOF;

return $template;