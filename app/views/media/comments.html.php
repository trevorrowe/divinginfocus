<div id='comments'>

  <h2>Comments</h2>

  <?php if($this->logged_in()): ?>
  <form method='post' action='<?php echo $this->media_url($media, 'comment') ?>'>
    <fieldset>
      <legend>Leave your comment here</legend>
      <?php echo $this->text_area($comment, 'text') ?>
      <div><?php echo $this->submit_tag('Comment') ?></div>
    </fieldset>
  </form>
  <?php else: ?>
    <p>You need to <?php echo $this->link_to('log in or signup', '/login') ?> to leave a coment.</p>
  <?php endif ?>

  <?php if($this->comments->total > 0): ?>
  <dl>
    <?php foreach($this->comments as $comment): ?>
    <dt class='avatar'><img src='/images/avatars/missing.jpg' alt='' /></dt>
    <dt class='username'><?php echo $comment->username ?></dt>
    <dd><?php echo h($comment->text) ?></dd>
    <?php endforeach ?>
  </dl>
  <?php endif ?>

</div>
