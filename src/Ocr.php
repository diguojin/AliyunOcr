<?php


namespace Diguojin\AliyunOcr;


class Ocr
{
    /**
     * default use front side of IDCard
     * @param $appCode
     * @param $imgUrl
     * @param string[] $config
     * @return array|mixed
     */
    public function idCard($appCode, $imgUrl, $config = ["side" => "face"])
    {
        $url = "http://dm-51.data.aliyun.com/rest/160601/ocr/ocr_idcard.json";
        $method = "POST";
        $img_data = $this->img_base64($imgUrl);
        $request = array(
            "image" => "$img_data"
        );
        return $this->httpRequest($url, $method, $config, $this->getHeaders($appCode), $request);
    }

    private function getHeaders($appCode)
    {
        $headers = [];
        array_push($headers, "Authorization:APPCODE " . $appCode);
        array_push($headers, "Content-Type" . ":" . "application/json; charset=UTF-8");
        return $headers;
    }

    public function businessLicense($appCode, $imgUrl)
    {
        $url = 'http://dm-58.data.aliyun.com/rest/160601/ocr/ocr_business_license.json';
        $method = "POST";
        $img_data = $this->img_base64($imgUrl);
        $request = array(
            "image" => "$img_data"
        );
        return $this->httpRequest($url, $method, [], $this->getHeaders($appCode), $request);
    }

    private function httpRequest($url, $method, $config, $headers, $request)
    {
        if (count($config) > 0) {
            $request["configure"] = json_encode($config);
        }
        $body = json_encode($request);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$" . $url, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $rheader = substr($result, 0, $header_size);
        $rbody = substr($result, $header_size);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode == 200) {
            $result_str = $rbody;
//            printf("result is :\n %s\n", $result_str);
            return json_decode($result_str, true);
        } else {
//            printf("Http error code: %d\n", $httpCode);
//            printf("Error msg in body: %s\n", $rbody);
//            printf("header: %s\n", $rheader);
            return ['success' => false, 'error' => $rbody];
        }
    }

    private function img_base64($path)
    {
        //对path进行判断，如果是本地文件就二进制读取并base64编码，如果是url,则返回
        if (substr($path, 0, strlen("http")) === "http") {
            $img_data = $path;
        } else {
            if ($fp = fopen($path, "rb", 0)) {
                $binary = fread($fp, filesize($path)); // 文件读取
                fclose($fp);
                $img_data = base64_encode($binary); // 转码
            } else {
                exit("%s 图片不存在 " . $path);
            }
        }
        return $img_data;
    }
}