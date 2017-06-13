<?php

namespace DoctrineEncrypt\Encryptors;


use DoctrineEncrypt\Contracts\Encryptor;

/**
 * This encryptor encrypts and decrypts values with the AES-256 algorithm.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
class AES256Encryptor implements Encryptor
{
    /**
     * The OpenSSL algorithm this implementation uses.
     *
     * 256-bit AES in Counter Mode is chosen. GCM mode would be preferable, but is only introduced in PHP7.
     */
    const ALGORITHM = 'aes-256-ctr';

    /**
     * The hashing algorithm used to create MACs.
     */
    const HASH_ALGORITHM = 'sha256';

    /**
     * The minimum key length we will accept.
     */
    const MINIMUM_KEY_LENGTH = 32;

    /**
     * The secret key for encryption.
     *
     * @var string
     */
    private $key;

    /**
     * AES256Encryptor constructor.
     *
     * @param string $key The encryption key.
     */
    public function __construct($key)
    {
        // Throw an exception here so that we can never be configured in an insecure way.
        if (!is_string($key) || mb_strlen($key) < self::MINIMUM_KEY_LENGTH) {
            throw new \RuntimeException('Invalid encryption key');
        }

        $this->key = $key;
    }

    /**
     * @inheritdoc
     */
    public function encrypt($data)
    {
        $nonce = $this->generateNonce();
        $plaintext = serialize($data);

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::ALGORITHM,
            $this->key,
            OPENSSL_RAW_DATA,
            $nonce
        );

        //
        // The MAC is computed as H(ALGORITHM ⨁ C ⨁ K ⨁ IV), where C is the ciphertext and K is the encryption key.
        //
        $mac = hash(self::HASH_ALGORITHM, self::ALGORITHM.$ciphertext.$this->key.$nonce, true);

        $encrypted = "<ENC>\0".base64_encode($ciphertext)."\0".base64_encode($mac)."\0".base64_encode($nonce);

        return $encrypted;
    }

    /**
     * @inheritdoc
     */
    public function decrypt($data)
    {
        if (mb_strpos($data, "<ENC>\0", 0) !== 0) {
            throw new \RuntimeException("Could not validate ciphertext");
        }

        $parts = explode("\0", $data);

        if (count($parts) !== 4) {
            throw new \RuntimeException("Could not validate ciphertext");
        }

        list($_, $ciphertext, $mac, $nonce) = $parts;

        if (($ciphertext = base64_decode($ciphertext)) === false) {
            throw new \RuntimeException("Could not validate ciphertext");
        }
        if (($mac = base64_decode($mac)) === false) {
            throw new \RuntimeException("Could not validate ciphertext");
        }
        if (($nonce = base64_decode($nonce)) === false) {
            throw new \RuntimeException("Could not validate ciphertext");
        }

        $expected = hash(self::HASH_ALGORITHM, self::ALGORITHM.$ciphertext.$this->key.$nonce, true);

        if (!hash_equals($expected, $mac)) {
            throw new \RuntimeException("Invalid MAC");
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::ALGORITHM,
            $this->key,
            OPENSSL_RAW_DATA,
            $nonce
        );

        if ($plaintext === false) {
            throw new \RuntimeException("Could not decrypt ciphertext");
        }

        $decrypted = unserialize($plaintext);

        return $decrypted;
    }

    /**
     * Generate a cryptographically-secure nonce.
     *
     * The terminology 'nonce' is used instead of IV because it strongly implies that this value should never be
     * re-used.
     *
     * @return string
     */
    protected function generateNonce()
    {
        $size = openssl_cipher_iv_length(self::ALGORITHM);
        $data = random_bytes($size);

        return $data;
    }
}