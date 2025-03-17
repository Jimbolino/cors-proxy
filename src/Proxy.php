<?php

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Proxy
{
    const array ADD_HEADERS = [
        'Access-Control-Allow-Origin' => '*',
    ];

    const array REMOVE_HEADERS = [
        'Content-Disposition',
    ];

    public function start(): JsonResponse|Response
    {
        $url = $_GET['url'] ?? null;
        $json = $_GET['json'] ?? false;

        if (!$url) {
            return new Response('no url');
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return new Response('invalid url');
        }
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            return new Response('invalid protocol');
        }

        $response = $this->get($url);

        if ($json) {
            return new JsonResponse([
                'body' => $response->getBody()->getContents(),
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders()
            ], headers: self::ADD_HEADERS);
        }

        $headers = $response->getHeaders();

        foreach (self::REMOVE_HEADERS as $header) {
            unset($headers[$header]);
        }

        foreach (self::ADD_HEADERS as $headerName => $headerValue) {
            $headers[$headerName] = $headerValue;
        }

        return new Response($response->getBody(), $response->getStatusCode(), $headers);
    }

    private function get(string $url): ResponseInterface
    {
        $client = new Client();

        return $client->request('GET', $url);
    }
}
