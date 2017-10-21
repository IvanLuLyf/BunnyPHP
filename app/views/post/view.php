<div class="container">
    <div class="row">
        <div class="col-md-2 col-lg-2"></div>
        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
            <ul class="breadcrumb hidden-xs">
                <li>
                    <a href="index.php"><?php echo constant("TP_SITENAME");?></a> <span class="divider"></span>
                </li>
                <li>
                    <a href="index.php?mod=post">论坛</a> <span class="divider"></span>
                </li>
                <li class="active">
                    <a href="#">帖子</a>
                </li>
            </ul>
            <div class="postcard">
                <div class="postcard-title">
                    <div class="pull-left">
                        <img class="img-responsive img-circle img-avatar" src="api.php?mod=user&action=getavatar&username=<?php echo $post['username'];?>">
                    </div>
                    <div class="postcard-info">
                        <div><?php echo $post['nickname'];?>(<?php echo $post['username'];?>)</div>
                        <div class="postcard-time"><?php echo date('Y-m-d H:i:s', $post['timeline']);?></div>
                    </div>
                </div>
                <div class="postcard-body">
                    <h3><a href="#"><?php echo $post['title'];?></a></h3>
                    <p><?php echo $post['message'];?></p>
                </div>
            </div>
            <div class="postcard">
                <div class="col-lg-12">
                    <form class="form-horizontal" role="form" action="index.php?mod=post&action=comment" method="post">
                        <input type="hidden" name="tid" value="<?php echo $post['tid'];?>"></input>
                        <div class="form-group">
                            <label for="message">评论</label>
                            <textarea class="form-control" id="message" name="message" rows="3" cols="60" placeholder="内容" required></textarea>
                        </div>
                        <div class="form-group">
                            <div class="pull-right col-md-3 col-lg-3">
                                <button class="btn btn-success btn-block" type="submit">发布</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="postcard-list">
                <?php foreach($comments as $comment) : ?>
                    <div class="postcard">
                        <div class="postcard-title">
                            <div class="pull-left">
                                <img class="img-responsive img-circle img-avatar" src="api.php?mod=user&action=getavatar&username=<?php echo $comment['username'];?>">
                            </div>
                            <div class="postcard-info">
                                <div><?php echo $comment['nickname'];?>(<?php echo $comment['username'];?>)</div>
                                <div class="postcard-time"><?php echo date('Y-m-d H:i:s', $comment['timeline']);?></div>
                            </div>
                        </div>
                        <div class="postcard-body">
                            <p><?php echo $comment['message'];?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>