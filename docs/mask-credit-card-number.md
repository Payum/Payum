<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Mask credit card number.

While working with credit cards it often needed to mask the number or card holder name.
Here's the class you may use for that purpose.
It is possible to configure mask symbol and the length of shown chars.

```php
<?php

use Payum\Core\Security\Util\Mask;

echo Mask::mask("3456-7890-1234-5678");
// 3XXX-XXXX-XXXX-5678

echo Mask::mask("4567890123456789", "*");
// 4***********6789

echo Mask::mask("4928-9012-abcd-3456", null, 8);
// 4XXX-XXXX-abcd-3456
```

Back to [index](index.md).