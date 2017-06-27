<?php

namespace B24;

class Application
{
    private $action = 'index';

    /**
     * Создаёт и инициализирует приложение
     */
    public function __construct()
    {
        session_start();
        
        $action = $_GET['action'];
        if (method_exists($this, "action" . $action)) {
            $this->action = $action;
        }
    }

    /**
     * Запускает приложение
     */
    public function run()
    {
        $actionName = "action" . $this->action;
        $this->$actionName();
    }

    /**
     * Выводит html страницу с заполненными значениями
     */
    private function actionIndex()
    {
        $domain = $_SESSION['domain'];
        $client_id = $_SESSION['client_id'];
        $client_secret = $_SESSION['client_secret'];
        $refresh_token = $_SESSION['refresh_token'];

        $error_msg = $_SESSION['error_msg'];
        unset($_SESSION['error_msg']);

        include 'views/index.php';
    }

    /**
     * Отправляет запрос для авторизации пользователя
     */
    private function actionSetup()
    {
        print_r($_POST);
        $_SESSION['domain'] = $_POST['domain'];
        $_SESSION['client_id'] = $_POST['client_id'];
        $_SESSION['client_secret'] = $_POST['client_secret'];

        $params = [
            'client_id' => $_SESSION['client_id']
        ];

        if ($_SESSION['domain']) {
            $url = "https://{$_SESSION['domain']}/oauth/authorize/?" . http_build_query($params);
        } else {
            $_SESSION['error_msg'] = "Не задано поле Домена";
            $url = './';
        }

        header('Location: ' . $url);
    }

    /**
     * Принимает ответ от приложения в Б24 и отправляет запрос для получения ключа
     */
    private function actionToken()
    {
        $server_domain = $_GET['server_domain'];

        // Если аутентификация в приложении прошла
        if ($server_domain) {
            $params = [
                'grant_type' => 'authorization_code',
                'client_id' => $_SESSION['client_id'],
                'client_secret' => $_SESSION['client_secret'],
                'code' => $_GET['code']?: ''
            ];

            $url = "https://{$server_domain}/oauth/token/?" . http_build_query($params);

            $rawResponse = file_get_contents($url);
            $response = json_decode($rawResponse, true);

            $_SESSION['refresh_token'] = $response['refresh_token'];
            $_SESSION['access_token'] = $response['access_token'];
        } else {
            $_SESSION['error_msg'] = 'Некорректно введённые данные';
        }

        header('Location: ./');
    }
}
