<?php
	require_once('config.php');

	class DB {
		const DB_NAME = 'votes.sqlite';
		protected $db;

		function __construct() {
			$this->db = new PDO('sqlite:'.self::DB_NAME);
		}

		function init() {
			global $teams; // From config.php

			$this->db->exec('CREATE TABLE IF NOT EXISTS teams (id INTEGER PRIMARY KEY, name TEXT, votes INTEGER);');
			$this->db->exec('CREATE TABLE IF NOT EXISTS voters (id INTEGER PRIMARY KEY, phone_number TEXT, voted_for INTEGER);');

			foreach ($teams as $team)
			{
				$this->add_team($team);
			}
		}

		function add_team($name) {
			// Check to make sure the team name doesn't already exist
			$stmt = $this->db->prepare('SELECT * FROM teams WHERE name=?');
			$stmt->execute(array($name));

			// If not, insert it
			if ($stmt->fetchColumn() == 0)
			{
				$stmt = $this->db->prepare('INSERT INTO teams (name, votes) VALUES (?, 0)');
				$stmt->execute(array($name));
			}
		}
		
		function get_teams() {
			$result = $this->db->query('SELECT * FROM teams');

			foreach ($result as $row)
			{
				$team['id'] = $row['id'];
				$team['name'] = $row['name'];
				$team['votes'] = $row['votes'];

				$teams[] = $team;
			}

			return $teams;
		}
		
		function save_vote($phone_number, $voted_for) {
			$phone_number = preg_replace('/\D/', '', $phone_number);
			
			// Check to see if person has already voted
			$stmt = $this->db->prepare('SELECT * FROM voters WHERE phone_number=?');
			$stmt->execute(array($phone_number));

			// If not, save their vote
			if ($stmt->fetchColumn() == 0)
			{
				// Save voter
				$stmt = $this->db->prepare('INSERT INTO voters (phone_number, voted_for) VALUES (?, ?)');
				$stmt->execute(array($phone_number, $voted_for));

				// Update vote count
				$stmt = $this->db->prepare('UPDATE teams SET votes=votes + 1 WHERE id=?');
				$stmt->execute(array($voted_for));

				return 'Thank you, your vote has been recorded';
			}
			else {
				return 'Sorry, you can only vote once.';
			}
		}
	}
?>
