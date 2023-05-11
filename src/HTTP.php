<?php declare(strict_types=1);

namespace Http;

abstract class HTTP
{
    /**
     * The GET method requests a representation of the specified resource.
     * Requests using GET should only retrieve data.
     */
    public const METHOD_GET = 'GET';

    /**
     * The HEAD method asks for a response identical to a GET request, but without the response body.
     */
    public const METHOD_HEAD = 'HEAD';

    /**
     * The POST method submits an entity to the specified resource, often causing a change in state or side effects on the server.
     */
    public const METHOD_POST = 'POST';

    /**
     * The PUT method replaces all current representations of the target resource with the request payload.
     */
    public const METHOD_PUT = 'PUT';

    /**
     * The DELETE method deletes the specified resource.
     */
    public const METHOD_DELETE = 'DELETE';

    /**
     * The CONNECT method establishes a tunnel to the server identified by the target resource.
     */
    public const METHOD_CONNECT = 'CONNECT';

    /**
     * The OPTIONS method describes the communication options for the target resource.
     */
    public const METHOD_OPTIONS = 'OPTIONS';

    /**
     * The TRACE method performs a message loop-back test along the path to the target resource.
     */
    public const METHOD_TRACE = 'TRACE';

    /**
     * The PATCH method applies partial modifications to a resource.
     */
    public const METHOD_PATCH = 'PATCH';

    /**
     * List of HTTP request methods
     */
    public const METHODS = [
        HTTP::METHOD_GET,
        HTTP::METHOD_HEAD,
        HTTP::METHOD_POST,
        HTTP::METHOD_PUT,
        HTTP::METHOD_DELETE,
        HTTP::METHOD_CONNECT,
        HTTP::METHOD_OPTIONS,
        HTTP::METHOD_TRACE,
        HTTP::METHOD_PATCH
    ];

    /** Yes */
    public const YES = true;

    /** No */
    public const NO = false;

    /** Optional */
    public const OPTIONAL = null;

    /**
     * HTTP request methods request has body
     */
    public const REQUEST_HAS_BODY = [
        HTTP::METHOD_GET => HTTP::OPTIONAL,
        HTTP::METHOD_HEAD => HTTP::NO,
        HTTP::METHOD_POST => HTTP::YES,
        HTTP::METHOD_PUT => HTTP::YES,
        HTTP::METHOD_DELETE => HTTP::NO,
        HTTP::METHOD_CONNECT => HTTP::YES,
        HTTP::METHOD_OPTIONS => HTTP::OPTIONAL,
        HTTP::METHOD_TRACE => HTTP::NO,
        HTTP::METHOD_PATCH => HTTP::YES
    ];

    /**
     * HTTP request methods response has body
     */
    public const RESPONSE_HAS_BODY = [
        HTTP::METHOD_GET => HTTP::YES,
        HTTP::METHOD_HEAD => HTTP::NO,
        HTTP::METHOD_POST => HTTP::YES,
        HTTP::METHOD_PUT => HTTP::YES,
        HTTP::METHOD_DELETE => HTTP::YES,
        HTTP::METHOD_CONNECT => HTTP::YES,
        HTTP::METHOD_OPTIONS => HTTP::YES,
        HTTP::METHOD_TRACE => HTTP::YES,
        HTTP::METHOD_PATCH => HTTP::YES
    ];

    public const OK = 200;
    public const BadRequest = 400;
    public const Unauthorized = 401;
    public const PaymentRequired = 402;
    public const Forbidden = 403;

    /**
     * List of reason phrases of HTTP status codes
     */
    public const PHRASES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];
}