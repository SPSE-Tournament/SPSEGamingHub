<?php
class ValidationManager
{
	private $regexArr = [
		"userhexname" => "/^[a-zA-ZěščřžýáíéĚŠČŘŽÝÁÍÉÉ0-9]{0,}#[[:xdigit:]]{4}$/",
		"hexid" => "/^#[[:xdigit:]]{4}$/",
		"username" => "/^[a-zA-ZěščřžýáíéĚŠČŘŽÝÁÍÉÉ]{4,30}$/",
		"teamname" => "/^[a-zA-ZěščřžýáíéĚŠČŘŽÝÁÍÉÉ ]{3,29}$/"
	];

	public function validate(array $tests): void
	{
		foreach ($tests as $message => $test) {
			if (!$test) {
				throw new ValidationError($message);
				break;
			}
		}
	}

	public function min(string $param, int $limit): bool
	{
		if (is_string($param))
			return (strlen($param) >= $limit);
		if (is_int($param))
			return $param >= $limit;
	}

	public function max(string $param, int $limit): bool
	{
		if (is_string($param))
			return (strlen($param) < $limit);
		if (is_int($param))
			return $param < $limit;
	}

	public function hexname(string $param): bool
	{
		return (preg_match($this->regexArr['userhexname'], $param));
	}

	public function username(string $param): bool
	{
		return (preg_match($this->regexArr['username'], $param));
	}

	public function teamname(string $param): bool
	{
		return (preg_match($this->regexArr['teamname'], $param));
	}

	public function hexid(string $param): bool
	{
		return (preg_match($this->regexArr['hexid'], $param));
	}

	public function hexnameOrhexid(string $param): bool
	{
		return ($this->hexid($param) || $this->hexname($param));
	}

	public function notEmpty($param): bool
	{
		if (is_string($param))
			return !empty($param);
		if (is_array($param)) {
			foreach ($param as $key => $value) {
				if (empty($value)) {
					return false;
				}
			}
			return true;
		}
	}
}
