<?php

use PHPUnit\Framework\TestCase;
use WoowUpV2\DataQuality\Tld\IanaTldProvider;
use WoowUpV2\DataQuality\Tld\TldCorrector;

/**
 * Tests for TldCorrector and IanaTldProvider.
 * Covers all acceptance criteria defined in the spec.
 */
class TldCorrectorTest extends TestCase
{
    private TldCorrector $corrector;

    protected function setUp(): void
    {
        // Use real IANA provider with a temp cache per test run.
        $iana = new IanaTldProvider(sys_get_temp_dir() . '/iana_tlds_test.cache', 3600);
        $this->corrector = new TldCorrector($iana);
    }

    // -------------------------------------------------------------------------
    // Helper: call correct() and return the correctedDomain without "@"
    // -------------------------------------------------------------------------

    private function correct(string $domain): ?string
    {
        $result = $this->corrector->correct('@' . $domain);
        if ($result->isIrrecoverable) {
            return null;
        }
        return ltrim($result->correctedDomain, '@');
    }

    private function wasCorrected(string $domain): bool
    {
        return $this->corrector->correct('@' . $domain)->wasCorrected;
    }

    private function isIrrecoverable(string $domain): bool
    {
        return $this->corrector->correct('@' . $domain)->isIrrecoverable;
    }

    // -------------------------------------------------------------------------
    // Single-domain providers (gmail, icloud) — always canonical TLD
    // -------------------------------------------------------------------------

    public function testGmailComm(): void
    {
        $this->assertSame('gmail.com', $this->correct('gmail.comm'));
        $this->assertTrue($this->wasCorrected('gmail.comm'));
    }

    public function testGmailCon(): void
    {
        $this->assertSame('gmail.com', $this->correct('gmail.con'));
    }

    public function testGmailComa(): void
    {
        $this->assertSame('gmail.com', $this->correct('gmail.coma'));
    }

    public function testGmailVom(): void
    {
        $this->assertSame('gmail.com', $this->correct('gmail.vom'));
    }

    public function testGmailXom(): void
    {
        $this->assertSame('gmail.com', $this->correct('gmail.xom'));
    }

    public function testGmailCoom(): void
    {
        $this->assertSame('gmail.com', $this->correct('gmail.coom'));
    }

    public function testGmailCoIsAlwaysCom(): void
    {
        // Single provider: gmail.co → gmail.com even though .co is valid IANA
        $this->assertSame('gmail.com', $this->correct('gmail.co'));
        $this->assertTrue($this->wasCorrected('gmail.co'));
    }

    public function testGmailComIsUnchanged(): void
    {
        $this->assertSame('gmail.com', $this->correct('gmail.com'));
        $this->assertFalse($this->wasCorrected('gmail.com'));
    }

    public function testIcloudComIsUnchanged(): void
    {
        $this->assertSame('icloud.com', $this->correct('icloud.com'));
        $this->assertFalse($this->wasCorrected('icloud.com'));
    }

    public function testIcloudCommIsCorrected(): void
    {
        $this->assertSame('icloud.com', $this->correct('icloud.comm'));
    }

    // -------------------------------------------------------------------------
    // Multi-region providers (hotmail, outlook, yahoo, etc.)
    // -------------------------------------------------------------------------

    public function testOutlookClm(): void
    {
        $this->assertSame('outlook.com', $this->correct('outlook.clm'));
    }

    public function testHotmailCcom(): void
    {
        $this->assertSame('hotmail.com', $this->correct('hotmail.ccom'));
    }

    public function testHotmailConm(): void
    {
        $this->assertSame('hotmail.com', $this->correct('hotmail.conm'));
    }

    public function testYahooComar(): void
    {
        // Prefix "com" + tail "ar" (ccTLD) → com.ar
        $this->assertSame('yahoo.com.ar', $this->correct('yahoo.comar'));
    }

    public function testHotmailComcom(): void
    {
        // Prefix "com" + tail "com" → 3 chars, not ccTLD of 2 → noise, just "com"
        $this->assertSame('hotmail.com', $this->correct('hotmail.comcom'));
    }

    public function testHotmailCommaria(): void
    {
        // Prefix "com" + tail "maria" → noise → "com"
        $this->assertSame('hotmail.com', $this->correct('hotmail.commaria'));
    }

    public function testLiveComArr(): void
    {
        // tld = "arr" → edit dist 1 of "ar" → live.com.ar
        $this->assertSame('live.com.ar', $this->correct('live.com.arr'));
    }

    public function testLiveComArm(): void
    {
        $this->assertSame('live.com.ar', $this->correct('live.com.arm'));
    }

    public function testYahooComArmaria(): void
    {
        // tld = "armaria" → edit dist > 1 → prefix "ar" + tail "maria" → noise → "ar"
        // name = "yahoo.com" → result "yahoo.com.ar"
        $this->assertSame('yahoo.com.ar', $this->correct('yahoo.com.armaria'));
    }

    public function testOutlookEss(): void
    {
        // edit dist 1 of "es"
        $this->assertSame('outlook.es', $this->correct('outlook.ess'));
    }

    public function testHotmailEsa(): void
    {
        $this->assertSame('hotmail.es', $this->correct('hotmail.esa'));
    }

    public function testHotmailXnIdn(): void
    {
        // xn-- IDN is filtered from IANA list → treated as invalid → edit dist correction
        $this->assertSame('hotmail.com', $this->correct('hotmail.xn--com-9ma'));
    }

    public function testHotmailCom9(): void
    {
        // edit dist 1 of "com"
        $this->assertSame('hotmail.com', $this->correct('hotmail.com9'));
    }

    // -------------------------------------------------------------------------
    // Multi-region: valid IANA TLDs that ARE respected
    // -------------------------------------------------------------------------

    public function testYahooComArIsUnchanged(): void
    {
        $this->assertSame('yahoo.com.ar', $this->correct('yahoo.com.ar'));
        $this->assertFalse($this->wasCorrected('yahoo.com.ar'));
    }

    public function testOutlookItIsUnchanged(): void
    {
        $this->assertSame('outlook.it', $this->correct('outlook.it'));
        $this->assertFalse($this->wasCorrected('outlook.it'));
    }

    public function testHotmailEsIsUnchanged(): void
    {
        $this->assertSame('hotmail.es', $this->correct('hotmail.es'));
        $this->assertFalse($this->wasCorrected('hotmail.es'));
    }

    // -------------------------------------------------------------------------
    // Multi-region: denylist (.co is valid IANA but providers don't use it)
    // -------------------------------------------------------------------------

    public function testYahooCoGoesToCom(): void
    {
        $this->assertSame('yahoo.com', $this->correct('yahoo.co'));
        $this->assertTrue($this->wasCorrected('yahoo.co'));
    }

    public function testHotmailCoGoesToCom(): void
    {
        $this->assertSame('hotmail.com', $this->correct('hotmail.co'));
    }

    // -------------------------------------------------------------------------
    // Custom domains (company emails)
    // -------------------------------------------------------------------------

    public function testCustomDomainCoIsUnchanged(): void
    {
        // Custom domain: empresa.co — .co is valid IANA, must NOT be touched
        $this->assertSame('empresa.co', $this->correct('empresa.co'));
        $this->assertFalse($this->wasCorrected('empresa.co'));
    }

    public function testCustomDomainComArIsUnchanged(): void
    {
        $this->assertSame('miempresa.com.ar', $this->correct('miempresa.com.ar'));
        $this->assertFalse($this->wasCorrected('miempresa.com.ar'));
    }

    public function testCustomDomainInvalidTldCorrected(): void
    {
        $this->assertSame('miempresa.com', $this->correct('miempresa.comm'));
    }

    // -------------------------------------------------------------------------
    // New/unseen suffix for multi-region provider → unchanged, just logged
    // -------------------------------------------------------------------------

    public function testYahooComPeIsUnchangedAndValid(): void
    {
        // .pe is valid IANA → not in denylist → preserve
        $this->assertSame('yahoo.com.pe', $this->correct('yahoo.com.pe'));
        $this->assertFalse($this->wasCorrected('yahoo.com.pe'));
        $this->assertFalse($this->isIrrecoverable('yahoo.com.pe'));
    }

    // -------------------------------------------------------------------------
    // Irrecoverable cases
    // -------------------------------------------------------------------------

    public function testIrrecoverableShortGibberish(): void
    {
        // ".v" is not in IANA and no edit dist/prefix match
        $this->assertNull($this->correct('empresa.v'));
        $this->assertTrue($this->isIrrecoverable('empresa.v'));
    }

    public function testIrrecoverableTest(): void
    {
        $this->assertNull($this->correct('empresa.test'));
    }

    public function testIrrecoverableAsd(): void
    {
        $this->assertNull($this->correct('empresa.asd'));
    }

    public function testIrrecoverableUnc(): void
    {
        $this->assertNull($this->correct('empresa.unc'));
    }

    // -------------------------------------------------------------------------
    // Idempotency — running twice does not change an already-valid email
    // -------------------------------------------------------------------------

    public function testIdempotentGmail(): void
    {
        $first  = $this->correct('gmail.com');
        $second = $this->correct(ltrim($first, '@'));
        $this->assertSame($first, $second);
    }

    public function testIdempotentHotmailComAr(): void
    {
        $first  = $this->correct('hotmail.com.ar');
        $second = $this->correct(ltrim($first, '@'));
        $this->assertSame($first, $second);
        $this->assertFalse($this->wasCorrected('hotmail.com.ar'));
    }
}
