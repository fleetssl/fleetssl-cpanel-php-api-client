<?php

class CpanelTest extends \PHPUnit\Framework\TestCase {

  /** @var \FleetSSL\Cpanel\APIClient */
  private $client;

  protected function setUp(): void {
    $this->client = new \FleetSSL\Cpanel\APIClient(getenv('FLEETSSL_HOST'), getenv('FLEETSSL_USER'), getenv('FLEETSSL_PASSWORD'));
  }

  public function testListCertificates() {
    $resp = $this->client->listCertificates();
    $this->assertTrue($resp->success, "list-certificates should have succeeded");
    $this->assertNull($resp->errors, "errors should be null");
    $this->assertNotEmpty($resp->data, "data should not be null");
    $this->assertNotEmpty($resp->data->virtual_hosts, "virtual_hosts should be populated");
  }

  public function testIssueCertificate() {
    $resp = $this->client->issueCertificate("plugindev.ga", ["plugindev.ga", "www.plugindev.ga"], "http-01", true);
    $this->assertTrue($resp->success, "issue-certificate dry-run should have succeeded");
    $this->assertNull($resp->errors, "errors should be null");
  }

  public function testReinstallCertificate() {
    $resp = $this->client->reinstallCertificate("plugindev.ga");
    $this->assertTrue($resp->success, "reinstall-certificate should have succeeded");
    $this->assertNull($resp->errors, "errors should be null");
  }

}

?>