<?php

namespace B24;

use SQLite3;

class Application
{
    private $db = './db.sqlite3';
    private $action = 'index';
    private $uuid;

    /**
     * Создаёт и инициализирует приложение
     */
    public function __construct()
    {
        session_start();

        $this->uuid = $_SESSION['uuid']?: $_GET['uuid'];

        if (method_exists($this, "action" . $_GET['action'])) {
            $this->action = $_GET['action'];
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

            if (!$this->uuid) {
                $this->uuid = $_SESSION['uuid'] = uniqid();

                $db = new SQLite3($this->db);
                $stmt = $db->prepare('INSERT INTO
                    Appdata (uuid, domain, client_id, client_secret, refresh_token)
                    VALUES (:uuid, :domain, :client_id, :client_secret, :refresh_token)');
                $stmt->bindParam('uuid', $this->uuid);
                $stmt->bindParam('domain', $_SESSION['domain']);
                $stmt->bindParam('client_id', $_SESSION['client_id']);
                $stmt->bindParam('client_secret', $_SESSION['client_secret']);
                $stmt->bindParam('refresh_token', $_SESSION['refresh_token']);

                $stmt->execute();
            }
        } else {
            $_SESSION['error_msg'] = 'Некорректно введённые данные';
        }

        header('Location: ./');
    }

    /**
     * Восстанавливает из БД параметры приложения по uuid, добавляет их в сессию и возвращает в формате json
     */
    private function actionGet()
    {
        $result = ['error' => 'В системе нет данных.'];

        if ($this->uuid && !$_SESSION['refresh_token']) {
            $db = new SQLite3($this->db);
            $stmt = $db->prepare('SELECT * FROM Appdata WHERE uuid = ?');
            $stmt->bindParam(1, $this->uuid);
            $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

            $_SESSION += $result;
        } else {
            $result = [
                'uuid' => $this->uuid,
                'domain' => $_SESSION['domain'],
                'client_id' => $_SESSION['client_id'],
                'client_secret' => $_SESSION['client_secret'],
                'refresh_token' => $_SESSION['refresh_token']
            ];
        }

        header('Content-Type: application/json');

        echo json_encode($result);
    }

    /**
     * Очищает данные из БД и сессии
     */
    private function actionClear()
    {
        $db = new SQLite3($this->db);
        $stmt = $db->prepare('DELETE FROM Appdata WHERE uuid = ?');
        $stmt->bindParam(1, $this->uuid);
        $stmt->execute();

        session_unset();

        header('Location: ./');
    }
}
