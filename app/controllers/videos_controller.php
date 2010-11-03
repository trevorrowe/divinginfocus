<?php

class VideosController extends MediaBaseController {

  public function index_action($params) {
    $this->videos = Video::desc_by_created_at()->paginate($params->page, 25);
    $this->add_crumb('Videos', '/videos');
  }

}
