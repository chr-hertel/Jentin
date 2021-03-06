<?php
/*
 * This file is part of the Jentin framework.
 * (c) Steffen Zeidler <sigma_z@sigma-scripts.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jentin\Mvc\Request;

/**
 * Request
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Request implements RequestInterface
{

    const DEFAULT_MODULE = 'default';
    const DEFAULT_CONTROLLER = 'index';
    const DEFAULT_ACTION = 'index';

    /** @var string */
    protected static $defaultModuleName = self::DEFAULT_MODULE;

    /** @var string */
    protected static $defaultControllerName = self::DEFAULT_CONTROLLER;

    /** @var string */
    protected static $defaultActionName = self::DEFAULT_ACTION;

    /** @var string[] */
    protected $server = [
        'REQUEST_URI'   => '',
        'SCRIPT_NAME'   => '',
        'HTTP_HOST'     => 'localhost',
        'SERVER_NAME'   => '',
        'HTTPS'         => ''
    ];

    /** @var string */
    protected $moduleName = '';

    /** @var string */
    protected $controllerName = '';

    /** @var string */
    protected $actionName = '';

    /** @var array */
    protected $params = array();

    /** @var string */
    protected $baseUrl;

    /** @var string */
    protected $basePath;

    /** @var string */
    protected $host;

    /** @var string */
    protected $scheme;

    /** @var bool */
    protected $isDispatched = false;

    /** @var array */
    protected $paramNamesPost = array();

    /** @var array */
    protected $paramNamesGet = array();

    /** @var array */
    protected $cookies = array();


    /**
     * constructor
     *
     * @param array $paramsPost
     * @param array $paramsGet
     * @param array $server
     * @param array $cookies
     */
    public function __construct(array $paramsPost = null, array $paramsGet = null, array $server = null, array $cookies = null)
    {
        $paramsPost   = $paramsPost ?: $_POST;
        $paramsGet    = $paramsGet  ?: $_GET;
        $this->params = array_merge($paramsGet, $paramsPost);
        $this->paramNamesPost = array_keys($paramsPost);
        $this->paramNamesGet = array_keys($paramsGet);
        $server       = $server ?: $_SERVER;
        $this->server = array_merge($this->server, $server);
        $this->cookies = $cookies ?: $_COOKIE;

        // init module / controller / action
        $this->moduleName = self::$defaultModuleName;
        $this->controllerName = self::$defaultControllerName;
        $this->actionName = self::$defaultActionName;
    }


    /**
     * sets param by name
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }


    /**
     * gets param by name
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (isset($this->params[$name])) {
            if (is_array($this->params[$name])) {
                return $this->params[$name];
            }
            else if ($this->params[$name] !== '') {
                return trim($this->params[$name]);
            }
        }

        return $default;
    }


    /**
     * checks, if parameter is set
     *
     * @param   string  $name
     * @return  boolean
     */
    public function hasParam($name)
    {
        return array_key_exists($name, $this->params);
    }


    /**
     * gets raw data of param by name
     *
     * @param   string  $name
     * @param   mixed   $default
     * @return  mixed
     */
    public function getRawParam($name, $default = null)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return $default;
    }


    /**
     * gets params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }


    /**
     * merges existing params with given params, given params will overwrite existing params
     *
     * @param array $params
     */
    public function mergeParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
    }


    /**
     * sets params
     *
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }


    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setPostParam($key, $value)
    {
        $pos = array_search($key, $this->paramNamesGet, true);
        if ($pos !== false) {
            unset($this->paramNamesPost[$pos]);
        }

        $this->params[$key] = $value;
        if (!in_array($key, $this->paramNamesPost, true)) {
            $this->paramNamesPost[] = $key;
        }
        return $this;
    }


    /**
     * Returns true, if request param is a post parameter
     *
     * @param  string $name
     * @return bool
     */
    public function isPost($name)
    {
        return in_array($name, $this->paramNamesPost, true);
    }


    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setGetParam($key, $value)
    {
        if (!$this->isPost($key)) {
            $this->params[$key] = $value;
            if (!in_array($key, $this->paramNamesGet, true)) {
                $this->paramNamesGet[] = $key;
            }
        }
        return $this;
    }


    /**
     * Returns true, if request param is a get parameter
     *
     * @param  string $name
     * @return bool
     */
    public function isGet($name)
    {
        return in_array($name, $this->paramNamesGet, true);
    }


    /**
     * gets module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }


    /**
     * sets module name
     *
     * @param string $moduleName
     * @return $this
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
        return $this;
    }


    /**
     * @param string $defaultModuleName
     */
    public static function setDefaultModuleName($defaultModuleName)
    {
        self::$defaultModuleName = $defaultModuleName;
    }


    /**
     * @return string
     */
    public static function getDefaultModuleName()
    {
        return self::$defaultModuleName;
    }


    /**
     * sets controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }


    /**
     * sets controller name
     *
     * @param string $controllerName
     * @return $this
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
        return $this;
    }


    /**
     * @param string $defaultControllerName
     */
    public static function setDefaultControllerName($defaultControllerName)
    {
        self::$defaultControllerName = $defaultControllerName;
    }


    /**
     * @return string
     */
    public static function getDefaultControllerName()
    {
        return self::$defaultControllerName;
    }


    /**
     * gets action name
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }


    /**
     * sets action name
     *
     * @param string $actionName
     * @return $this
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
        return $this;
    }


    /**
     * @param string $defaultActionName
     */
    public static function setDefaultActionName($defaultActionName)
    {
        self::$defaultActionName = $defaultActionName;
    }


    /**
     * @return string
     */
    public static function getDefaultActionName()
    {
        return self::$defaultActionName;
    }


    /**
     * sets request uri
     *
     * @param string $requestUri
     * @return $this
     */
    public function setRequestUri($requestUri)
    {
        $this->server['REQUEST_URI'] = $requestUri;
        return $this;
    }


    /**
     * gets request uri
     *
     * @return string
     */
    public function getRequestUri()
    {
        return isset($this->server['REQUEST_URI'])
            ? $this->server['REQUEST_URI']
            : null;
    }


    /**
     * sets base path
     *
     * @param string $basePath
     * @return $this
     */
    public function setBasePath($basePath = null)
    {
        if ($basePath === null) {
            $basePath = str_replace('\\', '/', dirname($this->server['SCRIPT_NAME']));
        }
        $this->basePath = $basePath;
        return $this;
    }


    /**
     * gets base path
     *
     * @return string
     */
    public function getBasePath()
    {
        if ($this->basePath === null) {
            $this->setBasePath();
        }
        return $this->basePath;
    }


    /**
     * sets base url
     *
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl = null)
    {
        if ($baseUrl === null) {
            $basePath = $this->getBasePath();
            $baseUrl = $this->getRequestUri();

            if ($basePath && substr($baseUrl, 0, strlen($basePath)) === $basePath) {
                $basePathLength = strlen($basePath);
                if ($basePath !== '/') {
                    $basePathLength++;
                }
                $baseUrl = substr($baseUrl, 0, $basePathLength);
            }
            else {
                $pos = strpos($baseUrl, '?');
                if ($pos > 0) {
                    $baseUrl = substr($baseUrl, 0, $pos);
                }
                if ($baseUrl[strlen($baseUrl) - 1] !== '/') {
                    $baseUrl = str_replace('\\', '/', dirname($baseUrl)) . '/';
                }
            }
        }
        $this->baseUrl = $baseUrl;

        return $this;
    }


    /**
     * gets base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->baseUrl === null) {
            $this->setBaseUrl();
        }
        return $this->baseUrl;
    }


    /**
     * sets host
     *
     * @param string $host
     * @return $this
     */
    public function setHost($host = null)
    {
        if ($host === null) {
            if (isset($this->server['HTTP_HOST'])) {
                $host = $this->server['HTTP_HOST'];
            }
            else if (isset($this->server['SERVER_NAME'])) {
                $host = $this->server['SERVER_NAME'];
            }
        }

        $this->host = $host;
        return $this;
    }


    /**
     * gets host
     *
     * @return string
     */
    public function getHost()
    {
        if ($this->host === null) {
            $this->setHost();
        }
        return $this->host;
    }


    /**
     * sets scheme
     *
     * @param string $scheme
     * @return $this
     */
    public function setScheme($scheme = null)
    {
        if ($scheme === null) {
            $scheme = isset($this->server['HTTPS']) && $this->server['HTTPS'] === 'on'
                ? 'https'
                : 'http';
        }
        $this->scheme = $scheme;
        return $this;
    }


    /**
     * gets scheme
     *
     * @return string
     */
    public function getScheme()
    {
        if ($this->scheme === null) {
            $this->setScheme();
        }
        return $this->scheme;
    }


    /**
     * gets header by name
     *
     * @param  string $headerName
     * @return string|null
     */
    public function getHeader($headerName)
    {
        // Try to get header information from the $_SERVER array
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));

        if (!empty($this->server[$key])) {
            return $this->server[$key];
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();

            if (!empty($headers[$headerName])) {
                return $headers[$headerName];
            }
        }

        return null;
    }


    /**
     * gets server var
     *
     * @param  string|null $name
     * @param  mixed|null  $default
     * @return mixed
     */
    public function getServer($name = null, $default = null)
    {
        if ($name === null) {
            return $this->server;
        }

        return isset($this->server[$name])
            ? $this->server[$name]
            : $default;
    }


    /**
     * returns the method by which the request was made
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }


    /**
     * gets cookies
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }


    /**
     * gets cookie by name
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getCookie($name, $default = null)
    {
        return isset($this->cookies[$name])
            ? $this->cookies[$name]
            : $default;
    }


    /**
     * checks, if request was a xml http request, or not
     *
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        return $this->getHeader('X_REQUESTED_WITH') === 'XMLHttpRequest';
    }


    /**
     * gets url query
     *
     * @return string
     */
    public function getQuery()
    {
        $requestUri = $this->getRequestUri();
        $urlParts = parse_url($requestUri);
        if (isset($urlParts['query'])) {
            return $urlParts['query'];
        }
        return '';
    }


    /**
     * gets url fragment
     *
     * @return string
     */
    public function getFragment()
    {
        $requestUri = $this->getRequestUri();
        $urlParts = parse_url($requestUri);
        if (isset($urlParts['fragment'])) {
            return $urlParts['fragment'];
        }
        return '';
    }


    /**
     * Sets dispatched flag for the request
     *
     * @param bool $isDispatched
     * @return $this
     */
    public function setDispatched($isDispatched = true)
    {
        $this->isDispatched = $isDispatched;
        return $this;
    }


    /**
     * If the request has not been dispatched, yet, it returns false
     *
     * @return bool
     */
    public function isDispatched()
    {
        return $this->isDispatched;
    }

}
