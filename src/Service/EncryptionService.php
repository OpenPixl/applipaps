<?php

namespace App\Service;

class EncryptionService
{
    private string $encryptionKey;

    public function __construct(string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
    }

    public function encrypt(string $iban): string
    {
        $iv = random_bytes(16); // Générer un vecteur d'initialisation sécurisé
        $ciphertext = openssl_encrypt($iban, 'aes-256-cbc', $this->encryptionKey,OPENSSL_RAW_DATA, $iv);

        // Concaténer IV et ciphertext
        return base64_encode($iv . $ciphertext);
    }

    public function decrypt(string $encryptedIban): string
    {
        $data = base64_decode($encryptedIban);
        $iv = substr($data, 0, 16); // Extraire le IV
        $ciphertext = substr($data, 16);

        return openssl_decrypt($ciphertext, 'aes-256-cbc', $this->encryptionKey, OPENSSL_RAW_DATA, $iv);
    }
}
