<?php
namespace Common {
	class Authentication{

		public static function validatePassword($password,$goodPassword)
		{
			if (crypt($password, $goodPassword) == $goodPassword) {
				return true;
			}
		}

		public static function crypt($password)
		{
			return crypt($password);
		}
	}
}
