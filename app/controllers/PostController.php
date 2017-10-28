<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/5
 * Time: 23:14
 */
class PostController extends Controller
{
    public function index()
    {
        $items = (new PostModel)->where()->fetchAll();
        $this->assign('title', '帖子');
        $this->assign('items', $items);
        $this->render();
    }

    public function view($id = 0)
    {
        if ($id == 0 && isset($_GET['tid'])) {
            $id = $_GET['tid'];
        }
        if ($id != 0) {
            $post = (new PostModel())
                ->join("tp_user", ["tp_posts.username=tp_user.username"], "LEFT")
                ->where(["tid = ?"], [$id])
                ->fetch("tp_posts.*,tp_user.nickname");
            $comments = (new CommentModel())->where(["tid = ? AND aid=1"], [$id])->fetchAll();

            $this->assign('title', $post['title']);
            $this->assign('post', $post);
            $this->assign('comments', $comments);

            $this->render();
        } else {
            header('Location: /post/index');
        }
    }
}