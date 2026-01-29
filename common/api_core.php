<?php
	function callAPI(&$error, $url, $data, $method="GET", $timeout_interval=30, $usedefault_header=false, $header=null)
	{
		$curl = curl_init();
		switch ($method)
		{
			case "POST":
				curl_setopt($curl, CURLOPT_POST, true);

				if (is_array($data))
					curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
				else
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

				if ($header != null)
					curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
				break;
			case "GET":
				if (is_array($data))
					$url = sprintf("%s?%s", $url, http_build_query($data));
				else
					$url = sprintf("%s?%s", $url, $data);
				if($header != null) {
					// echo "header != null\n";
					curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
				}
				break;
			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, true);
				break;
			default:
				if ($data)
					$url = sprintf("%s?%s", $url, http_build_query($data));
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		// ✅ 加入 Timeout 設定
		//curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); // 最多等待 10 秒建立連線
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout_interval);        // 最多等待 30 秒完成請求

		// ❌ Add these lines to bypass SSL verification
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		
		if ($usedefault_header) {
			// echo "use default header\n";
			$header=array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data));
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}

		$result = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);

		return $result;
	}
	function jCallAPI(&$error, $url, $data, $method="GET", $content_type ='application/json', $usedefault_header=false, $header=null)
	{
		// echo $content_type."\n";
		$curl = curl_init();
		switch ($method)
		{
			case "POST":
				curl_setopt($curl, CURLOPT_POST, true);

				if (is_array($data)) {
					// echo "CURLOPT_POSTFIELDS 01\n";
					curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
				} else {
					// echo "CURLOPT_POSTFIELDS 02\n";
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				}

				if ($header != null)
					curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
				break;

			case "GET":
				if (is_array($data))
					$url = sprintf("%s?%s", $url, http_build_query($data));
				else
					$url = sprintf("%s?%s", $url, $data);
				if($header != null) {
					// echo "header != null\n";
					curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
				}
				break;

			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, true);
				break;

			default:
				if ($data)
					$url = sprintf("%s?%s", $url, http_build_query($data));
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		// ✅ 加入 Timeout 設定
		//curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); // 最多等待 10 秒建立連線
		//curl_setopt($curl, CURLOPT_TIMEOUT, 30);        // 最多等待 30 秒完成請求
		
		if ($usedefault_header) {
			if ($content_type == "text/xml") {
				$header = array('Content-Type: '.$content_type);
			} else {
				$header = array(
					'Content-Type: '.$content_type,
					'Content-Length: '.strlen($data));
			}
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}

		$response = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);

		// echo "error :";
		// echo $error."\n";
		return $response;
	}
?>
