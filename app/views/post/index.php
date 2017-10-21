<p>Test</p>
<table>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?php echo $item['tid'] ?></td>
            <td><?php echo $item['username'] ?></td>
            <td><?php echo $item['message'] ?></td>
        </tr>
    <?php endforeach ?>
</table>
