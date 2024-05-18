<?php

namespace PikaJew002\Handrolled\Http;

use PikaJew002\Handrolled\Interfaces\Request as RequestInterface;
use PikaJew002\Handrolled\Interfaces\User as AuthenticatableEntity;

class Request implements RequestInterface
{
    public const HTTP_GET = 'GET';
    public const HTTP_HEAD = 'HEAD';
    public const HTTP_POST = 'POST';
    public const HTTP_PUT = 'PUT';
    public const HTTP_PATCH = 'PATCH';
    public const HTTP_DELETE = 'DELETE';

    protected string $method;
    protected string $uri;
    protected array $server;
    protected array $headers;
    protected array $request;
    protected array $query;
    protected array $cookies;
    protected array $files;
    protected $body;
    protected ?AuthenticatableEntity $user = null;

    public function __construct(
        string $uri = '',
        string $method = 'GET',
        array $server = [],
        array $headers = [],
        array $request = [],
        array $query = [],
        array $cookies = [],
        array $files = [],
        $body = ''
    )
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->server = $server;
        $this->headers = $headers;
        $this->body = $body;
        $this->query = $query;
        $this->request = $request;
        $this->files = $files;
        $this->cookies = $cookies;
    }

    public static function createFromGlobals(?array $server = null, ?array $files = null, ?array $cookies = null, $body = null): self
    {
        if(is_null($server)) {
            $server = $_SERVER;
        }
        if(is_null($files)) {
            $files = isset($_FILES) ? $_FILES : [];
        }
        if(is_null($cookies)) {
            $cookies = isset($_COOKIE) ? $_COOKIE : [];
        }
        if(is_null($body)) {
            $body = file_get_contents('php://input');
        }
        $uri = self::parseURI($server['REQUEST_URI']);
        $method = strtoupper($server['REQUEST_METHOD']);
        $headers = self::parseHeaders($server);
        $query = self::parseQuery($method, $server['REQUEST_URI']);
        $request = self::parseRequest($method, $headers, $body);
        $files = self::parseFiles($files);
        $cookies = self::parseCookies($headers, $cookies);
        return new self($uri, $method, $server, $headers, $request, $query, $cookies, $files, $body);
    }

    public static function mock(
        string $uri,
        string $method = 'GET',
        array $server = [],
        array $headers = [],
        array $request = [],
        array $query = [],
        array $cookies = [],
        array $files = [],
        $body = ''): self
    {
        return new self($uri, $method, $server, $headers, $request, $query, $cookies, $files, $body);
    }

    protected static function parseURI(string $uri): string
    {
        $pos = strpos($uri, '?');
        if($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }

        return rawurldecode($uri);
    }

    protected static function parseHeaders(array $server): array
    {
        $headers = [];
        foreach($server as $key => $value) {
            if(strncmp($key, 'HTTP_', 5) === 0) {
                $headers[substr($key, 5)] = $value;
            } else if(in_array($key, ['body_TYPE', 'body_LENGTH', 'body_MD5'], true)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    protected static function parseRequest(string $method, array $headers, string $body): array
    {
        if(
            $method === self::HTTP_POST &&
            array_key_exists('CONTENT_TYPE', $headers) &&
            in_array($headers['CONTENT_TYPE'], ['application/x-www-form-urlencoded', 'multipart/form-data'])
        ) {
            return isset($_POST) ? $_POST : [];
        }
        if(
            array_key_exists('CONTENT_TYPE', $headers) &&
            in_array($headers['CONTENT_TYPE'], ['application/json', 'application/x-json']) &&
            $body !== ''
        ) {
            return json_decode($body, true);
        }

        return [];
    }

    protected static function parseQuery(string $method = 'GET', string $uri = ''): array
    {
        if($method === self::HTTP_GET) {
            return isset($_GET) ? $_GET : [];
        }
        $queryString = parse_url($uri, PHP_URL_QUERY);
        if(!is_null($queryString)) {
            $queryArr = [];
            foreach(explode('&', rawurldecode($queryString)) as $query) {
                if(false !== strpos($query, '=')) {
                    $keyValuePair = explode('=', $query);
                    $queryArr[$keyValuePair[0]] = $keyValuePair[1];
                    continue;
                }
                $queryArr[$query] = '';
            }

            return $queryArr;
        }

        return [];
    }

    protected static function parseFiles(): array
    {
        return isset($_FILES) ? $_FILES : [];
    }

    protected static function parseCookies(array $headers): array
    {
        $cookiesFromHeaders = [];
        $cookiesFromHeadersStr = isset($headers['COOKIE']) ? $headers['COOKIE'] : '';
        $headerCookieArr = $cookiesFromHeadersStr !== '' ? explode(';', $cookiesFromHeadersStr) : [];
        foreach($headerCookieArr as $cookieRaw) {
            if($cookieRaw === '') continue;
            $cookieParsed = explode('=', $cookieRaw);
            if(isset($cookieParsed[0]) && isset($cookieParsed[1])) {
                $cookiesFromHeaders[$cookieParsed[0]] = $cookieParsed[1];
            }
        }
        $cookiesFromGlobals = isset($_COOKIE) ? $_COOKIE : [];

        return $cookiesFromHeaders + $cookiesFromGlobals;
    }

    public function getBody()
    {
        if(is_resource($this->body)) {
            rewind($this->body);
            return stream_get_contents($this->body);
        }

        return $this->body;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getServer(): array
    {
        return $this->server;
    }

    protected function normalizeHeader(string $header): string
    {
        return str_replace('-', '_', strtoupper($header));
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $header): bool
    {
        return isset($this->headers[$this->normalizeHeader($header)]);
    }

    public function getHeader(string $header): ?string
    {
        return $this->hasHeader($header) ? $this->headers[$this->normalizeHeader($header)] : null;
    }

    public function acceptsJson(): bool
    {
        return $this->hasHeader('Accept') && $this->getHeader('Accept') === 'application/json';
    }

    public function acceptsHtml(): bool
    {
        return $this->hasHeader('Accept') && $this->getHeader('Accept') === 'text/html';
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function hasCookie(string $cookie): bool
    {
        return isset($this->cookies[$cookie]);
    }

    public function getCookie(string $cookie): ?string
    {
        return $this->hasCookie($cookie) ? $this->cookies[$cookie] : null;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setUser(AuthenticatableEntity $user): RequestInterface
    {
        $this->user = $user;

        return $this;
    }

    public function user(): ?AuthenticatableEntity
    {
        return $this->user;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function hasQuery($key): bool
    {
        return isset($this->query[$key]);
    }

    public function query($key, $default = null)
    {
        return $this->hasQuery($key) ? $this->query[$key] : $default;
    }

    public function getRequest(): array
    {
        return $this->request;
    }

    public function hasRequest($key): bool
    {
        return isset($this->request[$key]);
    }

    public function request($key, $default = null)
    {
        return $this->hasRequest($key) ? $this->request[$key] : $default;
    }

    public function all(): array
    {
        return array_merge($this->request, $this->query);
    }

    public function input($key, $default = null)
    {
        if($this->hasRequest($key)) {
            return $this->request($key);
        }
        if($this->hasQuery($key)) {
            return $this->query($key);
        }

        return $default;
    }
}
