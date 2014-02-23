<?php

class Admin extends Controller {

	public function index() {
		if(Session::isActive() && Session::get()->rank >= $GLOBALS['config']['rank_modo']) {
			$this->loadModel('admin_model');

			$data = array();
			$data['reportedVids'] = $this->model->getReportedVideos();
			$data['lastReportedVids'] = $this->model->getReportedVideos(10);

			$this->renderView('admin/main', $data, false);
		}
		else {
			header('Location: '.WEBROOT);
		}
	}

	public function reports() {
		if(Session::isActive() && Session::get()->rank >= $GLOBALS['config']['rank_modo']) {
			$this->loadModel('admin_model');

			$data = array();
			$data['reportedVids'] = $this->model->getReportedVideos();

			$this->renderView('admin/reports', $data, false);
		}
		else {
			header('Location: '.WEBROOT);
		}
	}

	public function suspendVideo($vidId='nope') {
		if($vidId != 'nope' && Session::isActive() && Session::get()->rank >= $GLOBALS['config']['rank_modo']) {
			$this->loadModel('admin_model');

			if($this->model->videoExists($vidId)) {
				$this->model->suspendVideo($vidId);
			}
		}
	}

	public function cancelFlag($vidId = 'nope') {
		if($vidId != 'nope' && Session::isActive() && Session::get()->rank >= $GLOBALS['config']['rank_modo']) {
			$this->loadModel('admin_model');

			if($this->model->videoExists($vidId)) {
				$this->model->cancelFlag($vidId);
			}
		}
	}

}