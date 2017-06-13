<?php

namespace DoctrineEncrypt\Contracts;

/**
 * An interface for all classes which provide encryption and decryption of values.
 *
 * All implementations of this should be secure - there shouldn't be a way to do stupid things such as ROT13 encryption.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
interface Encryptor
{
    /**
     * Encrypt the given data securely.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function encrypt($data);

    /**
     * Decrypt the given data securely.
     * If the ciphertext cannot be decrypted (if for example it cannot be authenticated, or the value provided is
     * invalid) an exception should be thrown.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function decrypt($data);
}