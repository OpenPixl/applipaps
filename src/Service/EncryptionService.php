<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EncryptionService
{
    public function __construct(
        public string $encryptionKey,
        private HttpClientInterface $httpClient,
    ){}

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

    public function getToken($user){
        $numCollaborator = $user->getNumcollaborator();
        $response = $this->httpClient->request('GET', 'https://papsimmo.openixl.fr/api/authentication_token/'.$numCollaborator.'/getToken');
        if ($response->getStatusCode() === 200) {
            $data = $response->toArray();
            $token = $data['token'];
            return $token;
        }

        throw new \Exception('Failed to retrieve token.');
    }
}
