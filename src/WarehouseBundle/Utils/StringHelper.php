<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-04-20
 * Time: 10:14 AM
 */

namespace WarehouseBundle\Utils;


class StringHelper
{
	public static function printCamel($string)
	{
		return ucfirst(preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $string));
	}
}