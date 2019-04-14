<?php

namespace FleetSSL\Cpanel;

class APIClient {

  private $hostname;
  private $username;
  private $password;

  function __construct($hostname, $username, $password) {
    $this->hostname = $hostname;
    $this->username = $username;
    $this->password = $password;
  }

  public function listCertificates() {
    return $this->makeRequest("POST", "list-certificates", 1, null);
  }

  public function issueCertificate($virtualHost, $dnsIdentifiers, $challengeMethod = "http-01", $dryRun = false) {
    if (empty($virtualHost)) {
      throw new \InvalidArgumentException("You must provide a target virtual_host");
    }
    if (empty($dnsIdentifiers) || !is_array($dnsIdentifiers)) {
      throw new \InvalidArgumentException("You must provide at least a single DNS identifier to include on the certificate");
    }
    return $this->makeRequest("POST", "issue-certificate", 1, [
      "virtual_host" => $virtualHost,
      "dns_identifiers" => $dnsIdentifiers,
      "challenge_method" => $challengeMethod,
      "dry_run" => $dryRun,
    ], 360 * 1000);
  }

  public function removeCertificate($virtualHost) {
    if (empty($virtualHost)) {
      throw new \InvalidArgumentException("You must provide a target virtual_host");
    }
    return $this->makeRequest("POST", "remove-certificate", 1, [
      "virtual_host" => $virtualHost,
    ]);
  }

  public function reinstallCertificate($virtualHost) {
    if (empty($virtualHost)) {
      throw new \InvalidArgumentException("You must provide a destination virtual_host");
    }
    return $this->makeRequest("POST", "reinstall-certificate", 1, [
      "virtual_host" => $virtualHost,
    ]);
  }

  public function reuseCertificate($srcVirtualHost, $destVirtualHost) {
    if (empty($srcVirtualHost) || empty($destVirtualHost)) {
      throw new \InvalidArgumentException("You must provide both a source and destination virtual_host");
    }
    return $this->makeRequest("POST", "reuse-certificate", 1, [
      "src_virtual_host" => $srcVirtualHost,
      "dest_virtual_host" => $destVirtualHost,
    ]);
  }

  public function removeCertificateReuse($destVirtualHost) {
    if (empty($destVirtualHost)) {
      throw new \InvalidArgumentException("You must provide a destination virtual_host");
    }
    return $this->makeRequest("POST", "remove-certificate-reuse", 1, [
      "dest_virtual_host" => $destVirtualHost,
    ]);
  }

  private function makeRequest($httpMethod, $api_function, $api_version, $body = null, $timeoutMs = 30 * 1000) {
    $ch = curl_init("https://{$this->hostname}:2083/frontend/paper_lantern/letsencrypt/" .
      "letsencrypt.live.cgi?api_version={$api_version}&api_function={$api_function}");

    $headers = [
      "Accept: application/json",
    ];

    if ($body) {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
      array_push($headers, "Content-Type: application/json");
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERNAME, $this->username);
    curl_setopt($ch, CURLOPT_PASSWORD, $this->password);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeoutMs);

    $result = curl_exec($ch);
    curl_close($ch);

    if (!$result) {
      $err = curl_error($ch);
      throw new \Exception($err);
    }

    $resultDecoded = json_decode($result);
    if (!$resultDecoded) {
      throw new \Exception("Unable to decode JSON response: $result");
    }

    return $resultDecoded;
  }

}

?>