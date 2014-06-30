<?php

require_once SYSTEM.'Model.php';
require_once APP.'classes/User.php';
require_once APP.'classes/Video.php';
require_once APP.'classes/Comment.php';
require_once APP.'classes/VideoVote.php';
require_once APP.'classes/UserAction.php';
require_once APP.'classes/ChannelAction.php';

class Watch_model extends Model {

	public function getVideoById($videoId) {
		return Video::find_by_id($videoId);
	}

	public function isVideoSuspended($videoId) {
		return Video::exists(array('id' => $videoId, 'visibility' => $GLOBALS['config']['vid_visibility_suspended']));
	}

	public function getAuthorsName($videoId) {
		return UserChannel::find_by_id(Video::find_by_id($videoId)->poster_id)->name;
	}

	public function getAuthorsSubscribers($videoId) {
		return UserChannel::find_by_id(Video::find_by_id($videoId)->poster_id)->subscribers;
	}

	public function getCommentsOnVideo($videoId) {
		return Comment::all(array('conditions' => array('video_id = ?', $videoId)));
	}

	public function getCommentAuthor($comment) {
		if(is_object($comment)) {
			return UserChannel::find_by_id($comment->poster_id)->username;
		}
	}

	public function postComment($authorId, $videoId, $commentContent) {
		$timestamp = Utils::tps();

		Comment::create(array(
			'id' => Comment::generateId(6),
			'poster_id' => $authorId,
			'video_id' => $videoId,
			'comment' => $commentContent,
			'timestamp' => $timestamp,
			'likes' => 0,
			'dislikes' => 0
		));

		ChannelAction::create(array(
			'id' => ChannelAction::generateId(6),
			'channel_id' => $authorId,
			'type' => 'comment',
			'target' => $videoId,
			'timestamp' => $timestamp
		));
	}

	public function isVideoLikedByUser($videoId, $userId='nope') {
		if($userId == 'nope' && Session::isActive()) $userId = Session::get()->id;

		return VideoVote::exists(array('user_id' => $userId, 'obj_id' => $videoId, 'action' => 'like'));
	}

	public function isVideoDislikedByUser($videoId, $userId='nope') {
		if($userId == 'nope' && Session::isActive()) $userId = Session::get()->id;

		return VideoVote::exists(array('user_id' => $userId, 'obj_id' => $videoId, 'action' => 'dislike'));
	}

	public function likeVideo($videoId, $userId) {
		VideoVote::create(array('user_id' => $userId, 'type' => 'video', 'obj_id' => $videoId, 'action' => 'like'));

		$likes = Video::find_by_id($videoId)->likes;
		Video::update_all(array('set' => array('likes' => $likes + 1), 'conditions' => array('id' => $videoId)));

		UserAction::create(array(
			'id' => UserAction::generateId(6),
			'user_id' => $userId,
			'type' => 'like',
			'target' => $videoId,
			'timestamp' => Utils::tps()
		));
	}

	public function dislikeVideo($videoId, $userId) {
		VideoVote::create(array('user_id' => $userId, 'type' => 'video', 'obj_id' => $videoId, 'action' => 'dislike'));

		$dislikes = Video::find_by_id($videoId)->dislikes;
		Video::update_all(array('set' => array('dislikes' => $dislikes + 1), 'conditions' => array('id' => $videoId)));

		UserAction::create(array(
			'id' => UserAction::generateId(6),
			'user_id' => $userId,
			'type' => 'dislike',
			'target' => $videoId,
			'timestamp' => Utils::tps()
		));
	}

	public function removeLike($videoId, $userId) {
		$likes = Video::find_by_id($videoId)->likes;

		if($likes >= 1) {
			Video::update_all(array('set' => array('likes' => $likes - 1), 'conditions' => array('id' => $videoId)));
			VideoVote::delete_all(array('conditions' => array('user_id = ? and obj_id = ?', $userId, $videoId)));
		}
	}

	public function removeDislike($videoId, $userId) {
		$dislikes = Video::find_by_id($videoId)->dislikes;

		if($dislikes >= 1) {
			Video::update_all(array('set' => array('dislikes' => $dislikes - 1), 'conditions' => array('id' => $videoId)));
			VideoVote::delete_all(array('conditions' => array('user_id = ? and obj_id = ?', $userId, $videoId)));
		}
	}
	
	public function addView($vidId) {
		$video = Video::find_by_id($vidId);
		$duration = $video->duration;
		$hash = sha1($vidId.$_SERVER['REMOTE_ADDR']);
		$view = VideoView::find_by_hash($hash);
		if (!$view || Utils::tps() > $view->date + $duration) {
			$video->views++;
			$video->save();
			if ($view)
				$view->delete();
			VideoView::create(array(
				'video_id' => $vidId,
				'hash' => $hash,
				'date' => Utils::tps()
			));
		}
	}

	public function getRecommendedVideos($posterId) {
		$vids = array();
		$maxIndex = Video::count(array('conditions' => array('poster_id' => $posterId)));
		$okay = false;

		while(!$okay) {
			for($i = 0; $i < $maxIndex; $i++) {
				$indexes[$i] = rand(0, $maxIndex - 1);
			}

			$new = array_unique($indexes);
			$okay = count($new) == count($indexes);
			if($okay) $indexes = $new;
		}

		$allVids = Video::find('all');
		foreach ($indexes as $index) $vids[] = $allVids[$index];

		return $vids;
	}

}