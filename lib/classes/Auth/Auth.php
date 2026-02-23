<?php

namespace Demo\Api\Classes\Auth;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application,
    \Bitrix\Main\Context,
    \Bitrix\Main\Request,
    \Bitrix\Main\Server;
use \Bitrix\Main\Web\Jwt;


class AuthCheck
{
    private $tokenData;
    private $request;

    public static function init(): self
    {
        //Отклоняем префайр запросы и обрабатываем только те, которые предназначены для нашего API, например, по определенному URL или наличию определенного заголовка.
        header("Referrer-Policy: no-referrer-when-downgrade");
        header("Content-type: application/json");
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            header('Access-Control-Allow-Origin: *');
            header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
            header("HTTP/1.1 200 OK");
            die();
        }
        $instance = new static();
        return $instance;
    }

    public function parseRequest()
    {
        $context = Application::getInstance()->getContext();
        $this->request = $context;
        return [
            'query' => $context->getRequest('query'),
            'headers' => $context->getRequest()->getHeaders()->get('Authorization'),
        ];
    }

    public function parseJWTTokenData()
    {
        // Получаем токен из заголовков и декодируем его.
        // Ключи для декодирования хранить в .env файле или в настройках модуля
        try {
            $token = str_replace('Bearer ', '', $this->parseRequest()['headers']);
            if ($token) {
                return $this->tokenData = Jwt::decode($token, '1234567', ['HS256']);
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isAuthenticated(): bool
    {
        $tokenData = $this->parseJWTTokenData();
        if ($tokenData) {
            // Здесь можно добавить дополнительную логику проверки, например, проверку роли пользователя или срока действия токена.
            return true;
        }
        return false;
    }
}
