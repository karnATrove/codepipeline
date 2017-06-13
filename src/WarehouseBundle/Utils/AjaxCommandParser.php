<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-09
 * Time: 3:37 PM
 */

namespace WarehouseBundle\Utils;


use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use WarehouseBundle\DTO\AjaxResponse\AjaxCommandDTO;

class AjaxCommandParser
{
	/**
	 * @param $ajaxCommands
	 *
	 * @return array
	 */
	public static function parseAjaxCommands($ajaxCommands)
	{
		$serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
		$ajaxCommandList = $serializer->normalize($ajaxCommands, AjaxCommandDTO::class . "[]");
		$resp = [];
		foreach ($ajaxCommandList as $ajaxCommand) {
			$resp['ajaxCommand'][]=$ajaxCommand;
		}
		return $resp;
	}
}