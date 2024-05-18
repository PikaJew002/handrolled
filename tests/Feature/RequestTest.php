<?php

use PikaJew002\Handrolled\Http\Request;

beforeEach(function() {
    $_SERVER['REQUEST_URI'] = '/?get_key=get_value&other_key&another_key=';
    $_SERVER['REQUEST_METHOD'] = 'post';
    $_SERVER['HTTP_ACCEPT'] = 'multipart/form-data';
    $_SERVER['HTTP_CONTENT_TYPE'] = 'multipart/form-data';
    $_SERVER['body_LENGTH'] = '0';
    $_POST['post_key'] = 'post_value';
    $this->request = Request::createFromGlobals();
});

it('sets uri', function() {
    expect($this->request->getUri())->toBe('/');
});

it('sets method', function() {
    expect($this->request->getMethod())->toBe(Request::HTTP_POST);
});

it('sets server', function() {
    expect($this->request->getServer())->toHaveKeys(['REQUEST_URI', 'REQUEST_METHOD', 'HTTP_ACCEPT', 'HTTP_CONTENT_TYPE', 'body_LENGTH']);
});

it('sets headers', function() {
    expect($this->request->hasHeader('Accept'))->toBeTrue();
    expect($this->request->getHeader('Accept'))->toBe('multipart/form-data');
    expect($this->request->hasHeader('Content-Type'))->toBeTrue();
    expect($this->request->getHeader('Content-Type'))->toBe('multipart/form-data');
    expect($this->request->getHeaders())->toBe([
        'ACCEPT' => 'multipart/form-data',
        'CONTENT_TYPE' => 'multipart/form-data',
        'body_LENGTH' => '0',
    ]);
});

it('sets request', function() {
    expect($this->request->hasRequest('post_key'))->toBeTrue();
    expect($this->request->request('post_key'))->toBe('post_value');
    expect($this->request->input('post_key'))->toBe('post_value');
    expect($this->request->getRequest())->toBe(['post_key' => 'post_value']);
});

it('gets all query and request inputs', function() {
    expect($this->request->all())->toBe([
        'post_key' => 'post_value',
        'get_key' => 'get_value',
        'other_key' => '',
        'another_key' => '',
    ]);
});

it('sets query from URL when method is POST', function() {
    expect($this->request->hasQuery('get_key'))->toBeTrue();
    expect($this->request->input('get_key'))->toBe('get_value');
    expect($this->request->query('get_key'))->toBe('get_value');
    expect($this->request->getQuery())->toBe([
        'get_key' => 'get_value',
        'other_key' => '',
        'another_key' => '',
    ]);
});

it('sets query from $_GET when method is GET', function() {
    $_SERVER['REQUEST_METHOD'] = 'get';
    $_GET['get_key'] = 'get_value';
    $this->request = Request::createFromGlobals();

    expect($this->request->hasQuery('get_key'))->toBeTrue();
    expect($this->request->input('get_key'))->toBe('get_value');
    expect($this->request->query('get_key'))->toBe('get_value');
    expect($this->request->getQuery())->toBe(['get_key' => 'get_value']);
});

it('sets query to empty if empty query string', function() {
    $_SERVER['REQUEST_URI'] = '/';
    $this->request = Request::createFromGlobals();

    expect($this->request->getQuery())->toBe([]);
});

it('sets cookies from $_COOKIE', function() {
    $_COOKIE['cookie_key'] = 'cookie_value';
    $this->request = Request::createFromGlobals();
    unset($_COOKIE['cookie_key']);

    expect($this->request->hasCookie('cookie_key'))->toBeTrue();
    expect($this->request->getCookie('cookie_key'))->toBe('cookie_value');
    expect($this->request->getCookies())->toBe(['cookie_key' => 'cookie_value']);
});

it('sets cookies from $_SERVER[\'HTTP_COOKIE\']', function() {
    $_SERVER['HTTP_COOKIE'] = 'cookie1_key=cookie1_value;cookie2_key=cookie2_value';
    $this->request = Request::createFromGlobals();

    expect($this->request->hasCookie('cookie1_key'))->toBeTrue();
    expect($this->request->getCookie('cookie1_key'))->toBe('cookie1_value');
    expect($this->request->hasCookie('cookie2_key'))->toBeTrue();
    expect($this->request->getCookie('cookie2_key'))->toBe('cookie2_value');
    expect($this->request->getCookies())->toBe([
        'cookie1_key' => 'cookie1_value',
        'cookie2_key' => 'cookie2_value',
    ]);
});

it('sets files', function() {
    $_FILES['file_key'] = 'file_value';
    $this->request = Request::createFromGlobals();

    expect($this->request->getFiles())->toBe(['file_key' => 'file_value']);
});

it('sets body when empty', function() {
    expect($this->request->getBody())->toBeEmpty();
});

it('sets body with JSON', function() {
    $jsonBody = "{\"key\":\"value\"}";
    $this->request = new Request('/', 'POST', [], [], [], [], [], [], $jsonBody);
    
    expect($this->request->getBody())->toBe("{\"key\":\"value\"}");
});

it('parses body with JSON', function () {
    $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
    $jsonBody = "{\"key\":\"value\"}";
    $this->request = Request::createFromGlobals($_SERVER, [], [], $jsonBody);

    expect($this->request->getRequest())->toHaveKey('key');
    expect($this->request->getRequest()['key'])->toBe('value');
});

it('accepts JSON', function () {
    $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
    $_SERVER['HTTP_ACCEPT'] = 'application/json';
    $this->request = Request::createFromGlobals($_SERVER);

    expect($this->request->acceptsJson())->toBe(true);
});


it('sets user', function() {
    class UserClass extends \PikaJew002\Handrolled\Database\Orm\Entity implements \PikaJew002\Handrolled\Interfaces\User
    {
        protected string $tableName = 'users';
        protected $primaryKey = 'id';

        // Entity database columns
        public $id;
        public $email;
        public $first_name;
        public $last_name;
        public $password_hash;
        public $created_at;
        public $updated_at;

        public function getUsername()
        {
            return $this->email;
        }

        public function getPasswordHash(): string
        {
            return $this->password_hash;
        }
    }
    $user = new UserClass();
    $this->request->setUser($user);

    expect($this->request->user())->toBe($user);
});

it('gets null if user not set', function() {
    expect($this->request->user())->toBeNull();
});