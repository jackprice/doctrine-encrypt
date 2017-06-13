<?php

namespace DoctrineEncrypt\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use DoctrineEncrypt\Contracts\Encryptor;

/**
 * A Doctrine type which encrypts and decrypts its values transparently.
 *
 * @author Jack Price <jack@wearenifty.co.uk>
 */
class Encrypted extends Type
{
    const ENCRYPTED = 'encrypted';

    /**
     * The encryptor to use for encrypting and decrypting values.
     *
     * @var Encryptor
     */
    private static $encryptor;

    /**
     * @inheritdoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getBinaryTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::ENCRYPTED;
    }

    /**
     * @inheritdoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return self::$encryptor->encrypt($value);
    }

    /**
     * @inheritdoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return self::$encryptor->decrypt($value);
    }

    /**
     * @inheritdoc
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * Set the encryptor to be used globally.
     *
     * @param Encryptor $encryptor
     */
    public static function setEncryptor(Encryptor $encryptor)
    {
        self::$encryptor = $encryptor;
    }
}