<?php

namespace DoctrineEncrypt\Encryptors;

class AES256EncryptorTest extends \PHPUnit_Framework_TestCase
{
    public function testEncryptionMultipleTimes()
    {
        $plaintext = "Hello, World!";
        $encryptor = new AES256Encryptor("aabbccddeefffgghhiijjkkllmmnnoopp");

        $ciphertext1 = $encryptor->encrypt($plaintext);
        $ciphertext2 = $encryptor->encrypt($plaintext);

        $this->assertNotEquals($plaintext, $ciphertext1);
        $this->assertNotEquals($plaintext, $ciphertext2);
        $this->assertNotEquals($ciphertext1, $ciphertext2);
    }

    public function testCanEncrypt()
    {
        $plaintext = "Hello, World!";
        $encryptor = new AES256Encryptor("aabbccddeefffgghhiijjkkllmmnnoopp");

        $ciphertext = $encryptor->encrypt($plaintext);

        $this->assertNotEquals($plaintext, $ciphertext);
    }

    public function testCanDecrypt()
    {
        $plaintext = "Hello, World!";
        $encryptor = new AES256Encryptor("aabbccddeefffgghhiijjkkllmmnnoopp");

        $ciphertext = $encryptor->encrypt($plaintext);
        $decrypted = $encryptor->decrypt($ciphertext);

        $this->assertEquals($plaintext, $decrypted);
    }

    public function testInvalidMACWillThrowException()
    {
        $plaintext = "Hello, World!";
        $encryptor = new AES256Encryptor("aabbccddeefffgghhiijjkkllmmnnoopp");

        $ciphertext = $encryptor->encrypt($plaintext);

        $ciphertext .= "\0";

        $this->expectException(\RuntimeException::class);

        $encryptor->decrypt($ciphertext);
    }

    public function testChangingPasswordWillThrowException()
    {
        $plaintext = "Hello, World!";
        $encryptor = new AES256Encryptor("aabbccddeefffgghhiijjkkllmmnnoopp");

        $ciphertext = $encryptor->encrypt($plaintext);

        $encryptor = new AES256Encryptor("zzyyxxwwvvuuttssrrqqppoonnmmllkkjj");

        $this->expectException(\RuntimeException::class);

        $encryptor->decrypt($ciphertext);
    }

    public function testMinimumKeyLengthMustBeRespected()
    {
        $this->expectException(\RuntimeException::class);

        $encrytor = new AES256Encryptor(str_repeat('a', AES256Encryptor::MINIMUM_KEY_LENGTH - 1));
    }

    public function testMinimumKeyLengthIsEnforced()
    {
        $encrytor = new AES256Encryptor(str_repeat('a', AES256Encryptor::MINIMUM_KEY_LENGTH));
    }

    public function testAlteringCiphertext()
    {
        $encrytor = new AES256Encryptor(str_repeat('a', AES256Encryptor::MINIMUM_KEY_LENGTH));
        $plaintext = "Hello, World!";

        $ciphertext = $encrytor->encrypt($plaintext);

        list($prefix, $cipher, $mac, $nonce) = explode("\0", $ciphertext);

        $character = chr(ord($cipher[0]) ^ 0xFF);
        $cipher[0] = $character;

        $ciphertext = implode("\0", [$prefix, $cipher, $mac, $nonce]);

        $this->expectException(\RuntimeException::class);

        $encrytor->decrypt($ciphertext);
    }

    public function testAlteringMAC()
    {
        $encrytor = new AES256Encryptor(str_repeat('a', AES256Encryptor::MINIMUM_KEY_LENGTH));
        $plaintext = "Hello, World!";

        $ciphertext = $encrytor->encrypt($plaintext);

        list($prefix, $cipher, $mac, $nonce) = explode("\0", $ciphertext);

        $mac[0] = chr(ord($mac[0]) ^ 0xFF);

        $ciphertext = implode("\0", [$prefix, $cipher, $mac, $nonce]);

        $this->expectException(\RuntimeException::class);

        $encrytor->decrypt($ciphertext);
    }

    public function testAlteringNonce()
    {
        $encrytor = new AES256Encryptor(str_repeat('a', AES256Encryptor::MINIMUM_KEY_LENGTH));
        $plaintext = "Hello, World!";

        $ciphertext = $encrytor->encrypt($plaintext);

        list($prefix, $cipher, $mac, $nonce) = explode("\0", $ciphertext);

        $nonce[0] = chr(ord($nonce[0]) ^ 0xFF);

        $ciphertext = implode("\0", [$prefix, $cipher, $mac, $nonce]);

        $this->expectException(\RuntimeException::class);

        $encrytor->decrypt($ciphertext);
    }
}
