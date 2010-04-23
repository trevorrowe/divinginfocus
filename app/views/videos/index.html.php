<?php echo $this->title('Videos') ?>
<?php echo $this->paginate($videos) ?>
<ul>
<?php foreach($videos as $video): ?>
  <li><?php echo $this->link_to($video->url(), $this->video_url($video)) ?></li>
<?php endforeach; ?>
</ul>
