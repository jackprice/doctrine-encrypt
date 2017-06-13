# jackprice/doctrine-encrypt

[![Build Status](https://travis-ci.org/jackprice/doctrine-encrypt.svg?branch=master)](https://travis-ci.org/jackprice/doctrine-encrypt)

A package for the safe encryption and decryption of Doctrine fields.

# Usage

Register the custom `encrypted` Doctrine type somewhere in the initialisation of your application.

```php
<?php

\Doctrine\DBAL\Types\Type::addType(
    \DoctrineEncrypt\Types\Encrypted::ENCRYPTED,
    \DoctrineEncrypt\Types\Encrypted::class
);
```

Set the encryption key.

> Important: You are responsible for securing and generating a strong key.

```php
<?php

$encryptor = new \DoctrineEncrypt\Encryptors\AES256Encryptor(sha1('Use a very strong key here'));
\DoctrineEncrypt\Types\Encrypted::setEncryptor($encryptor);
```

Use the `encrypted` type in your entities.

```php
<?php

class MyEntity
{
    /**
     * @ORM\Column(type="encrypted") 
     */
    protected $encrypted;
}

```

## TODO

- [ ] Add more documentation
- [ ] Handle unencrypted fields
- [ ] Create a CLI to encrypt fields
- [ ] Allow key rotation