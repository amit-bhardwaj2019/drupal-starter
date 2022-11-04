<?php

namespace Drupal\intl_date;

/**
 * Multilingual date handling.
 */
class IntlDate {

  const LOCALE_LANGUAGE_MAP = [
    'af' => 'af_ZA.UTF-8',
    'ar' => 'ar_AR.UTF-8',
    'az' => 'az_Cyrl_AZ.UTF-8',
    'be' => 'be_BY.UTF-8',
    'bg' => 'bg_BG.UTF-8',
    'bhs' => 'bs_BA.UTF-8',
    'bn' => 'bn_BD.UTF-8',
    'bs' => 'bs_BA.UTF-8',
    'ca' => 'ca_ES.UTF-8',
    'cs' => 'cs_CZ.UTF-8',
    'da' => 'da_DK.UTF-8',
    'de' => 'de_DE.UTF-8',
    'el' => 'el_GR.UTF-8',
    'en' => 'en_US.UTF-8',
    'es' => 'es_ES.UTF-8',
    'et' => 'et_EE.UTF-8',
    'eu' => 'eu_ES.UTF-8',
    'fa' => 'fa_IR.UTF-8',
    'fi' => 'fi_FI.UTF-8',
    'fr' => 'fr_FR.UTF-8',
    'gu' => 'gu_IN.UTF-8',
    'he' => 'he_IL.UTF-8',
    'hi' => 'hi_IN.UTF-8',
    'hr' => 'hr_HR.UTF-8',
    'ht' => 'ht_HT.UTF-8',
    'hu' => 'hu_HU.UTF-8',
    'hy' => 'hy_AM.UTF-8',
    'id' => 'id_ID.UTF-8',
    'is' => 'is_IS.UTF-8',
    'it' => 'it_IT.UTF-8',
    'ja' => 'ja_JP.UTF-8',
    'ka' => 'ka_GE.UTF-8',
    'kk' => 'kk_Cyrl_KZ.UTF-8',
    'ko' => 'ko_KR.UTF-8',
    'ku' => 'ckb_IR.UTF-8',
    'ky' => 'ky_KG.UTF-8',
    'lo' => 'lo_LA.UTF-8',
    'lt' => 'lt_LT.UTF-8',
    'mk' => 'mk_MK.UTF-8',
    'mn' => 'mn_MN.UTF-8',
    'ms' => 'ms_MY.UTF-8',
    'my' => 'my_MM.UTF-8',
    'ne' => 'ne_NP.UTF-8',
    'nl' => 'nl_NL.UTF-8',
    'no' => 'no_NO.UTF-8',
    'pl' => 'pl_PL.UTF-8',
    'pt' => 'pt_PT.UTF-8',
    'pt-br' => 'pt_BR.UTF-8',
    'ro' => 'ro_MD.UTF-8',
    'ru' => 'ru_RU.UTF-8',
    'sk' => 'sk_SK.UTF-8',
    'sl' => 'sl_SI.UTF-8',
    'so' => 'so_SO.UTF-8',
    'sq' => 'sq_AL.UTF-8',
    'sr' => 'sr_Cyrl_RS.UTF-8',
    'sv' => 'sv_SE.UTF-8',
    'sw' => 'sw_TZ.UTF-8',
    'tg' => 'tg_TJ.UTF-8',
    'th' => 'th_TH.UTF-8',
    'tk' => 'tk_TM.UTF-8',
    'tr' => 'tr_TR.UTF-8',
    'uk' => 'uk_UA.UTF-8',
    'uz' => 'uz_Cyrl_UZ.UTF-8',
    'vi' => 'vi_VN.UTF-8',
    'zh-hans' => 'zh_CN.UTF-8',
  ];

  /**
   * Formats a date with intl extension.
   *
   * @param int $timestamp
   *   The date.
   * @param string $pattern
   *   See https://unicode-org.github.io/icu/userguide/format_parse/datetime/.
   * @param string|null $langcode
   *   Optional langcode, to override current language.
   * @param string|null $timezone
   *   Optional, timezone.
   *
   * @return false|string
   *   The formatted string or, if an error occurred, <b>FALSE</b>.
   */
  public static function format(int $timestamp, string $pattern, string $langcode = NULL, string $timezone = NULL) {
    if (empty($langcode)) {
      $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    }
    static $map = self::LOCALE_LANGUAGE_MAP;

    // During unit testing, there's no container present.
    if (\Drupal::hasContainer()) {
      \Drupal::moduleHandler()->alter('intl_date_locale_map', $map);
    }
    $locale = $map['en'];
    if (isset($map[$langcode])) {
      $locale = $map[$langcode];
    }
    if (empty($timezone)) {
      $timezone = NULL;
    }
    $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, $timezone, NULL, $pattern);
    $formatted_date = $formatter->format($timestamp);

    if (\Drupal::hasContainer()) {
      $context = [
        'langcode' => $langcode,
        'locale' => $locale,
        'pattern' => $pattern,
        'timestamp' => $timestamp,

      ];
      \Drupal::moduleHandler()->alter('intl_date_formatted_date', $formatted_date, $context);
    }

    return $formatted_date;
  }

  /**
   * Formats a date with intl extension using an existing format definition.
   *
   * @param int $timestamp
   *   The date.
   * @param string $date_format
   *   The ID of an existing intl_date_format.
   * @param string|null $langcode
   *   Optional langcode, to override current language.
   * @param string|null $timezone
   *   Optional, timezone.
   *
   * @return false|string
   *   The formatted string or, if an error occurred, <b>FALSE</b>.
   */
  public static function formatPattern(int $timestamp, string $date_format, string $langcode = NULL, string $timezone = NULL) {
    $pattern = \Drupal::entityTypeManager()->getStorage('intl_date_format')->load($date_format);
    if (empty($pattern)) {
      throw new \IntlException('Invalid intl date format');
    }
    return self::format($timestamp, $pattern->get('pattern'), $langcode, $timezone);

  }

}
