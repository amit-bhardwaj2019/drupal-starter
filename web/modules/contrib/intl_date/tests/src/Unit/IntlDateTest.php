<?php

namespace Drupal\Tests\intl_date\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\intl_date\IntlDate;

/**
 * Test date rendering.
 *
 * @group intl_date
 */
class IntlDateTest extends UnitTestCase {

  const UNIX_TIMESTAMP = 1621322643;

  /**
   * Asserts date formats per language.
   */
  public function testDateFormat() {
    $this->assertEquals('18 May 2021', IntlDate::format(self::UNIX_TIMESTAMP, 'dd MMMM yyyy', 'en'));
    $this->assertEquals('18 május 2021', IntlDate::format(self::UNIX_TIMESTAMP, 'dd MMMM yyyy', 'hu'));
    $this->assertEquals('18 mai 2021', IntlDate::format(self::UNIX_TIMESTAMP, 'dd MMMM yyyy', 'fr'));
    $this->assertEquals('٢٠٢١ مايو ١٨', IntlDate::format(self::UNIX_TIMESTAMP, 'yyyy MMMM dd', 'ar'));
  }

  /**
   * Test that near end of year dates are correctly parsed.
   */
  public function testEndOfYear() {
    $date = 1702960000;
    $this->assertEquals('2023 December 19', date('Y F d', $date));
    $this->assertEquals('2023 December 19', IntlDate::format($date, 'yyyy MMMM dd', 'en'));

    $date = 1703960000;
    $this->assertEquals('2023 December 31 05:13:20', date('Y F d H:i:s', $date));
    $this->assertEquals('2023 December 31', date('Y F d', $date));
    $this->assertEquals('2023 December 31', IntlDate::format($date, 'yyyy MMMM dd', 'en'));
  }

}
