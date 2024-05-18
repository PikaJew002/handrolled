<?php

namespace PikaJew002\Handrolled\Interfaces;

interface Request
{
    /*
     * Returns the body of the request
     */
    public function getBody();

    /*
     * Returns the request method
     */
    public function getMethod(): string;

    /*
     * Returns the URI of the request
     */
    public function getUri(): string;

    /*
     * Returns an array of server-set request variables.
     * Is usually set from $_SERVER.
     */
    public function getServer(): array;

     /*
     * Returns an array of request headers.
     */
    public function getHeaders(): array;

     /*
     * Request has a header.
     */
    public function hasHeader(string $header): bool;

    /*
     * Get request header value.
     */
    public function getHeader(string $header): ?string;

    /*
     * Request accepts HTML as Reponse Content-Type.
     * This function should check for the existance of the Accept header
     * and check if it is set to the value 'text/html'.
     */
    public function acceptsHtml(): bool;

    /*
     * Returns an array of cookies.
     */
    public function getCookies(): array;

    /*
     * Request has a cookie.
     */
    public function hasCookie(string $cookie): bool;

    /*
     * Get cookie value.
     */
    public function getCookie(string $cookie): ?string;

    /*
     * Returns an array of files.
     */
    public function getFiles(): array;

    /*
     * Returns an array of query string parameters.
     */
    public function getQuery(): array;

    /*
     * Request has a query string parameter.
     */
    public function hasQuery($key): bool;

    /*
     * Get a query string parameter value.
     */
    public function query($key, $default = null);

    /*
     * Returns an array of request parameters parsed the request body.
     */
    public function getRequest(): array;

    /*
     * Request has a request parameter.
     */
    public function hasRequest($key): bool;

    /*
     * Get a request parameter value.
     */
    public function request($key, $default = null);

    /*
     * Get a request or query string parameter value.
     */
    public function input($key, $default = null);

    /*
     * Returns an array of all parameters on the reqest.
     * Should include both request parameters and query parameters.
     */
    public function all(): array;

    /*
     * Sets the user property to the value provided.
     */
    public function setUser(User $user): Request;

    /*
     * Returns the value of the user property or null if not set.
     */
    public function user(): ?User;
}