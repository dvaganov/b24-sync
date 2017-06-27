<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="views/css/custom.css">
        <title>Получение ключа для выгрузки данных из 1с</title>
    </head>
    <body>
        <div class="container-fluid">
            <?php if ($refresh_token) : ?>
            <h2 class="form-horizontal">Все данные успешно получены. Можно приступать к работе!</h2>
            <?php else : ?>
            <form class="form-horizontal" action="./?action=setup" method="post">
                <?php if ($error_msg) : ?>
                    <div class="alert alert-danger">
                        <?= $error_msg ?>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="control-label col-sm-4" for="domain">Домен Битрикса24</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="domain" type="text" name="domain" value="<?= $domain ?>" placeholder="portal.bitrix24.ru">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="client_id">Код приложения</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="client_id" type="text" name="client_id" value="<?= $client_id ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="client_secret">Ключ приложения</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="client_secret" type="text" name="client_secret" value="<?= $client_secret ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="refresh_token">Ключ для обработки 1С</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="refresh_token" type="text" name="refresh_token" value="<?= $refresh_token ?>" disabled="disabled">
                    </div>
                </div>
                <div class="form-group">
                    <input type="submit" name="" value="Получить" class="btn btn-lg btn-primary btn-block">
                </div>
            </form>
        <?php endif; ?>
    </body>
</html>
