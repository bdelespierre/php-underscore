all:
	/usr/bin/env php build/make.php functions > src/functions.php
	/usr/bin/env php build/make.php markdown > README.md

.PHONY: tests

readme:
	/usr/bin/env php build/make.php markdown > README.md

functions:
	/usr/bin/env php build/make.php functions > src/functions.php

tests:
	vendor/bin/atoum -d tests/units

clean:
	rm README.md
	rm src/functions.php