<?php

class ExceptionHandler
{
	public static function getMessage(Exception $e): string
	{
		switch (get_class($e)) {
			case "ValidationError":
				return $e->getMessage();
				break;
			case "UserError":
				return "UserError";
				break;
			case "PDOException":
				return "Oops, něco se pokazilo";
				break;
		}
	}
}
