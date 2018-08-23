<?php

namespace Yan\Foundation\Kernel\Http;

use Yan\Foundation\Kernel\Support\Collection;
use Yan\Foundation\Kernel\Support\XML;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Http\Message\ResponseInterface;

class Response extends GuzzleResponse
{
    public function getBodyContents()
    {
        $this->getBody()->rewind();
        $contents = $this->getBody()->getContents();
        $this->getBody()->rewind();
        return $contents;
    }

    public static function buildFromPsrResponse(ResponseInterface $response)
    {
        return new static(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function toArray()
    {
        $content = $this->removeControlCharacters($this->getBodyContents());
        if (false !== stripos($this->getHeaderLine('Content-Type'), 'xml') || 0 === stripos($content, '<xml')) {
            return XML::parse($content);
        }
        $array = json_decode($content, true);
        if (JSON_ERROR_NONE === json_last_error()) {
            return (array) $array;
        }
        return [];
    }

    public function toCollection()
    {
        return new Collection($this->toArray());
    }

    public function toObject()
    {
        return json_decode($this->toJson());
    }

    public function __toString()
    {
        return $this->getBodyContents();
    }

    protected function removeControlCharacters(string $content)
    {
        return \preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $content);
    }
}