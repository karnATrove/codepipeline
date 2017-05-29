<?php

namespace WarehouseBundle\Utils;


use WarehouseBundle\Exception\Utils\CurlException;
use WarehouseBundle\Exception\Utils\UrlFileNotFoundException;

class DownloadUtility
{
	const HEADER_CONTENT_DISPOSITION = 'Content-Disposition';

	/**
	 * @param string      $url
	 * @param string      $localDir
	 * @param string|null $fileName
	 * @param string|null $fileExt
	 *
	 * @throws CurlException
	 * @throws UrlFileNotFoundException
	 */
	public static function downloadFileFromUrl(string $url, string $localDir, string $fileName = null, string $fileExt = null)
	{
		if (!self::isLinkExist($url)) {
			throw new UrlFileNotFoundException("File not exist");
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$data = curl_exec($ch);
		$curlError = curl_error($ch);
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		curl_close($ch);
		if (!empty($curlError)) {
			throw new CurlException($curlError);
		}
		$headerString = substr($data, 0, $headerSize);
		$header = self::parseHeaders($headerString);
		$originalFileName = null;
		$originalExt = null;
		$originalFile = self::getFileNameFromHeaderArray($header);
		if ($originalFile) {
			$info = pathinfo($originalFile);
			$originalFileName = basename($originalFile, '.' . $info['extension']);
			$originalExt = $info['extension'];
		}
		$fileName = $fileName ? $fileName :
			($originalFileName ? $originalFileName : hash('crc32b', date('YmdHis'), false));
		$fileExt = $fileExt ? $fileExt : $originalExt;
		$body = substr($data, $headerSize);
		$file = fopen($localDir . "/".$fileName . ".".$fileExt, "w+");
		fputs($file, $body);
		fclose($file);
	}

	/**
	 * check if a url exist
	 *
	 * @param string $link
	 *
	 * @return bool
	 */
	public static function isLinkExist($link): bool
	{
		$file_headers = @get_headers($link);
		if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found'
			|| $file_headers[0] == 'HTTP/1.1 503 Backend fetch failed'
		) {
			$exists = false;
		} else {
			$exists = true;
		}
		return $exists;
	}

	/**
	 * parse header
	 *
	 * @param $headerString
	 *
	 * @return array
	 */
	public static function parseHeaders($headerString)
	{
		$headersArray = explode("\r\n", $headerString);
		$headers = [];
		foreach ($headersArray as $header) {
			if (count(explode(":", $header, 2)) == 2) {
				list($headerName, $headerValue) = explode(": ", $header, 2);
				$headers[$headerName] = $headerValue;
			} else {
				$headers[] = $header;
			}
		}
		return $headers;
	}

	/**
	 * @param array $header
	 *
	 * @return mixed|null
	 */
	public static function getFileNameFromHeaderArray(array $header)
	{
		if (!isset($header[self::HEADER_CONTENT_DISPOSITION]))
			return null;

		$contentDisposition = $header[self::HEADER_CONTENT_DISPOSITION];
		return preg_replace('/attachment; filename=/i', '', $contentDisposition);
	}
}