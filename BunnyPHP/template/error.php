<!DOCTYPE html>
<html lang="<?= $_LANG->lang ?>">
<head>
    <meta charset="utf-8">
    <title><?= $_LANG['bunny_error'] ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=0">
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol;
        }

        .title {
            border-bottom: 1px solid #9baeca;
            color: #9baeca;
            padding: 0.75rem;
        }

        .sub-title {
            margin: 0.75rem;
        }

        .link {
            color: deepskyblue;
            text-decoration: none;
        }

        .message {
            margin: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.25rem;
            background: mistyrose;
        }

        .message table {
            border-collapse: collapse;
            width: 100%;
        }

        .message table td, .message table th {
            border: 1px solid lightyellow;
            color: #666;
            height: 30px;
        }

        .message table tr:nth-child(odd) {
            background: white;
        }

        .message table tr:nth-child(even) {
            background: floralwhite;
        }
    </style>
</head>
<body>
<h1 class="title"><?= $_LANG['bunny_error'] ?></h1>
<pre class="message"><?= $bunny_error ?></pre>
<?php if (isset($bunny_error_trace)): ?>
    <h4 class="sub-title">Trace</h4>
    <div class="message">
        <table>
            <tbody>
            <tr class="bg2">
                <td>No.</td>
                <td>File</td>
                <td>Line</td>
                <td>Code</td>
            </tr>
            <?php foreach ($bunny_error_trace as $i => $t): ?>
                <tr class="bg1">
                    <td><?= ($i + 1) ?></td>
                    <td><?= $t['file'] ?? '-' ?></td>
                    <td><?= $t['line'] ?? '-' ?></td>
                    <td><?= $t['class'] ?? '' ?><?= $t['type'] ?? '' ?><?= $t['function'] ?? '' ?>()</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<p class="message">Powered By <a class="link" href="https://github.com/IvanLuLyf/BunnyPHP">BunnyPHP</a></p>
</body>
</html>
