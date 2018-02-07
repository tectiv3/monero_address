# monero_address
Small library to create/verify monero addresses

## Usage

```php
<?php

use tectiv3\MoneroAddress;

$m = new MoneroAddress('4LZerFMFcVxfdJA19zHBNa94x6KLTDGMZ8eyWBNz8wYt6nSScuLKUvPNW6h3hasuZK6JYFjYCigEUWupwBScV1e4Ldw4wbvi1ma9egjCxU');
$m->print();

$m = new MoneroAddress('4AryqSXm1ESfdJA19zHBNa94x6KLTDGMZ8eyWBNz8wYt6nSScuLKUvPNW6h3hasuZK6JYFjYCigEUWupwBScV1e4EDqFbr8');
$m->print();
echo $m->makeIntegrated('668927f1bd69114c');

```

### Special thanks to [luigi1111](https://github.com/luigi1111/xmr.llcoins.net/) and [this answer on SE](https://monero.stackexchange.com/questions/3179/what-is-an-integrated-address#3184)