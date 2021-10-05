<!DOCTYPE html>
<html lang="<?= $_LANG->lang ?>">
<head>
    <meta charset="utf-8">
    <title><?= $_LANG['bunny_info'] ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=0">
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        .title {
            border-bottom: 1px solid #9baeca;
            color: #9baeca;
            padding: 0.75rem;
        }

        .link {
            color: deepskyblue;
            text-decoration: none;
        }

        .message {
            margin: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.25rem;
            background: aliceblue;
        }
    </style>
</head>
<body>
<h1 class="title"><?= $_LANG['bunny_info'] ?></h1>
<pre class="message"><?= $bunny_info ?></pre>
<p class="message">Powered By <a class="link" href="https://github.com/IvanLuLyf/BunnyPHP">BunnyPHP</a></p>
</body>
</html>