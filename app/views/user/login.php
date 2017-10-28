<html>
<head>
    <title><?php echo constant("TP_SITENAME"); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=0;">
    <script src="/static/js/jquery.min.js"></script>
    <link href="/static/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" src="/static/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="/static/css/login.css"/>
</head>
<body>
<div class="container">
    <form action="/user/login" method="post" class="form-signin">
        <h2 class="form-signin-heading"><?php echo constant("TP_SITENAME"); ?></h2>
        <?php if (isset($tp_info_msg)): ?>
            <div id="myAlert" class="alert alert-info">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <strong>提示信息:</strong><?php echo $tp_info_msg; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($tp_error_msg)): ?>
            <div id="myAlert" class="alert alert-danger">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <strong>提示信息:</strong><?php echo $tp_error_msg; ?>
            </div>
        <?php endif; ?>
        <label for="username" class="sr-only">用户名</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="用户名" required autofocus>
        <label for="password" class="sr-only">密码</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="密码" required>
        <input type="submit" class="btn btn-lg btn-success btn-block" value="登陆"></input>
        <input type="button" class="btn btn-lg btn-info btn-block"
               onclick="window.location.href='http://tp.twimi.cn/index.php?mod=qqconnect&action=connect'"
               value="QQ账号登陆"></input>
        <input type="button" class="btn btn-lg btn-danger btn-block"
               onclick="window.location.href='http://tp.twimi.cn/index.php?mod=sinaconnect&action=connect'"
               value="微博账号登陆"></input>
        <p class="form-signin-link"><a href="index.php?mod=register">注册</a> | <a href="#">忘记密码</a></p>
    </form>
</div>
</body>
</html>