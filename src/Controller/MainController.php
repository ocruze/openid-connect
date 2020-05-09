<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    private $clientId = "c01d884089ab754562a3";
    private $clientSecret = "e35a67899ea216b979d240c0e76f176c8639294d";

    /**
     * @Route("/", name="main")
     */
    public function index()
    {
        return new RedirectResponse("login");
    }

    /**
     * @Route("/login")
     */
    public function test()
    {
        return new RedirectResponse("https://github.com/login/oauth/authorize?client_id=" . $this->clientId);
    }

    /**
     * Github me répond à cette adresse et cette fonction est lancée
     * @Route("/check-login")
     */
    public function checkLogin(Request $request)
    {
        $requestParams = $request->query->all();
        var_dump($requestParams);

        $data = [
            "grant_type" => "authorization_code",
            "code" => $requestParams['code'],
            "client_id" => $this->clientId,
            "client_secret" => $this->clientSecret,
        ];

        var_dump($data);

        $httpClient = HttpClient::create();

        $response = $httpClient->request('POST', 'https://github.com/login/oauth/access_token', [
            'body' => $data,
            /* 'proxy' => 'http://proxy.ign.fr:3128', */
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $tokenResult = \json_decode($response->getContent(), true);
        var_dump($tokenResult);

        $response = $httpClient->request('GET', 'https://api.github.com/user', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $tokenResult['access_token'],
            ],
        ]);

        $finalResult = \json_decode($response->getContent(), true);
        var_dump($finalResult);

        return new Response("Welcome " . $finalResult['login'] . "!");
    }
}
