<?php
/**
 * @package  Teamdaily
 * @author   Ryan Bouche <rbouche@gmail.com>
 * @version  1.0.0
 */

namespace Plugin\Teamdaily;

class Controller extends \Controller {

	function __construct($f3) {
		$f3->set("UI", $f3->get("UI") . ";./app/plugin/teamdaily/view/");
	}


	/**
	 * Handle HTTP GET request
	 * @param Base  $f3
	 * @param array $params
	 */
	public function dashboard($f3, $params) {
		$this->_requireLogin();
		$db = $f3->get("db.instance");
		if(empty($params['id'])) $params['id'] = $f3->get('td.default.team');
		if(!$f3->get('GET.date')) {
			$date = date("Y-m-d", strtotime("Yesterday"));
		} else {
			$date = date("Y-m-d", strtotime($f3->get('GET.date')));
		}
		$members = new \Model\Custom("user_group_user");
		foreach($members->find(array("group_id = ?  AND deleted_date IS NULL", $params['id'])) as $r) {
			$team_members[] = $r->user_id;
		}
		$team_ids = implode(",", $team_members);

		$sprint = new \Model\Sprint;
		$sprint->load("NOW() BETWEEN start_date AND end_date");
		$f3->set("sprint", $sprint);

		$scores = $db->exec(
			"SELECT u.id, u.name, u.username,
				SUM(IF(f.field = 'hours_spent', f.new_value - f.old_value, 0)) as hours,
				SUM(IF(f.field = 'status' AND f.new_value = 3, 1, 0)) as closed,
				COUNT(DISTINCT i.id) as late
			FROM user u
			LEFT JOIN issue_update_detail d on u.id = d.user_id AND DATE(CONVERT_TZ(d.created_date, 'GMT', '".$f3->get('site.timezone')."')) = '$date'
			LEFT JOIN issue_update_field f ON d.id = f.issue_update_id
			LEFT JOIN issue i on u.id = i.owner_id AND i.due_date = '$date' AND i.closed_date IS NULL AND (i.closed_date IS NULL OR i.due_date < DATE(CONVERT_TZ(i.closed_date, 'GMT', '".$f3->get('site.timezone')."')))
			WHERE u.id IN ($team_ids)  GROUP BY u.id ORDER BY u.name");


		//Get the Team user
		$user = new \Model\User();
		$user->load($params['id']);
		$teamscore = array(
			'id' =>  $user->id,
			'name' =>$user->name,
			'username' => $user->username,
			'hours' => 0,
			'closed' => 0,
			'late' => 0,
			'status' => 'warning',
			'tasks' => array(),
			'avatar' =>  $user->avatar(128)
			);
		$teampoints = 0;

		$user ->reset();
		$issue = new \Model\Issue();

		foreach ($scores as &$score) {
			//Increment Team Scores
			$teamscore['hours'] 	+= $score['hours'];
			$teamscore['closed'] 	+= $score['closed'];
			$teamscore['late'] 	+= $score['late'];
			$points = 0;
			if($score['hours']  > 5 ) {
				$points  +=2;
			}

			if($score['closed']  > 0 ) {
				$points  +=1;
			}

			if($score['late']  == 0 ) {
				$points  +=2;
			}

			if ($points >= 4) {
				$score['status'] = 'success';
				$teampoints += 1;
			} else if($points >= 2) {
				$score['status'] = 'warning';

			} else {
				$score['status'] = 'danger';
			}

			//Load the User's avatar

			$user->load($score['id']);
			$score['avatar']  = $user->avatar(128);


			//Load the user's top issues
			$score['tasks'] ['Active']= $issue->findone(array("owner_id = ? AND closed_date IS NULL AND deleted_date IS NULL AND status= '2'", $user->id), array('order' => 'due_date, priority DESC'));
			$score['tasks'] ['Due']= $issue->findone(array("owner_id = ? AND closed_date IS NULL AND deleted_date IS NULL AND date(due_date) < DATE_ADD(NOW(), INTERVAL 2 day)", $user->id), array('order' => 'due_date, priority DESC'));
			$score['tasks'] ['High']= $issue->findone(array("owner_id = ? AND closed_date IS NULL AND deleted_date IS NULL AND   priority > '0'", $user->id), array('order' => 'priority DESC'));
			$user->reset();



		}

		//Calculate team overall success then add team to scores
		if($teampoints > (count($scores) / 2) ) {
			$teamscore['status'] = 'success';
		} else {
			$teamscore['status'] = 'danger';
		}
		$scores[] = $teamscore;
		$f3->set("results", $scores);

		if(true) {

			$this->_render("td_dashboard.html");
		} else {
			$f3->error(404);
		}
	}


}
