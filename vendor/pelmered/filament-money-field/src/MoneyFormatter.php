<?php

namespace Pelmered\FilamentMoneyField;

use Illuminate\Support\Number;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exception\ParserException;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Money\Parser\IntlLocalizedDecimalParser;
use NumberFormatter;

class MoneyFormatter
{
    public static function format(
        null|int|string $value,
        Currency $currency,
        string $locale,
        int $outputStyle = NumberFormatter::CURRENCY,
        int $decimals = 2,
    ): string {
        if ($value === '' || ! is_numeric($value)) {
            return '';
        }

        $numberFormatter = self::getNumberFormatter($locale, $outputStyle, $decimals);
        $moneyFormatter  = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies);

        $money = new Money((int) $value, $currency);

        return $moneyFormatter->format($money);  // Outputs something like "$1.234,56"
    }

    public static function formatAsDecimal(
        null|int|string $value,
        Currency $currency,
        string $locale,
        int $decimals = 2,
    ): string {
        return static::format($value, $currency, $locale, NumberFormatter::DECIMAL, $decimals);
    }

    public static function numberFormat(
        null|int|float|string $value,
        Currency $currency,
        string $locale,
        int $decimals = 2,
    ): string {
        if (! is_numeric($value)) {
            return '';
        }
        $numberFormatter = self::getNumberFormatter($locale, NumberFormatter::DECIMAL, $decimals);

        return (string) $numberFormatter->format((float) $value);  // Outputs something like "1.234,56"
    }

    public static function formatShort(
        null|int|string $value,
        Currency $currency,
        string $locale,
        int $decimals = 2,
        bool $showCurrencySymbol = true
    ): string {
        if (! is_numeric($value)) {
            return '';
        }

        // No need to abbreviate if the value is less than 1000
        if ($value < 100000) {
            if (! $showCurrencySymbol) {
                dump($value);

                return static::numberFormat((int) $value / 100, $currency, $locale, decimals: $decimals);
            }

            return static::format($value, $currency, $locale, $decimals);
        }

        $abbreviated = (string) Number::abbreviate((int) $value / 100, 0, abs($decimals));

        // Split the number and the suffix
        preg_match('/^(?<number>[0-9.]+)(?<suffix>[A-Z])$/', $abbreviated, $matches1);
        /** @var array{number: string, suffix: string} $matches1 */
        $abbreviatedNumber = $matches1['number'];
        $suffix            = $matches1['suffix'];

        $formattedNumber = static::numberFormat($abbreviatedNumber, $currency, $locale, decimals: $decimals);

        if (! $showCurrencySymbol) {
            return $formattedNumber.$suffix;
        }

        // Format the number
        $formattedCurrency = static::format($abbreviatedNumber, $currency, $locale, decimals: $decimals);

        // Find the formatted number
        preg_match('/(?<number>[0-9\.,\s]+)/', $formattedCurrency, $matches2);
        /** @var array{number: string} $matches2 */

        return str_replace($matches2['number'], $formattedNumber.$suffix, $formattedCurrency);
    }

    public static function parseDecimal(
        ?string $moneyString,
        Currency $currency,
        string $locale,
        int $decimals = 2
    ): string {
        if (is_null($moneyString) || $moneyString === '') {
            return '';
        }

        $numberFormatter = self::getNumberFormatter($locale, NumberFormatter::DECIMAL, $decimals);
        $moneyParser     = new IntlLocalizedDecimalParser($numberFormatter, new ISOCurrencies);

        // Remove grouping separator from the money string
        // This is needed to fix some parsing issues with small numbers such as
        // "2,00" with "," left as thousands separator in the wrong place
        // See: https://github.com/pelmered/filament-money-field/issues/20
        $formattingRules = self::getFormattingRules($locale, $currency);
        $moneyString     = str_replace($formattingRules->groupingSeparator, '', $moneyString);

        try {
            return $moneyParser->parse($moneyString, $currency)->getAmount();
        } catch (ParserException) {
            throw new ParserException('The value must be a valid numeric value.');
        }
    }

    public static function getFormattingRules(string $locale, Currency $currency): MoneyFormattingRules
    {
        $config          = config('filament-money-field');
        $numberFormatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return new MoneyFormattingRules(
            currencySymbol: $numberFormatter->getSymbol(
                $config['intl_currency_symbol']
                    ? NumberFormatter::INTL_CURRENCY_SYMBOL
                    : NumberFormatter::CURRENCY_SYMBOL
            ),
            fractionDigits: $numberFormatter->getAttribute(NumberFormatter::FRACTION_DIGITS),
            decimalSeparator: $numberFormatter->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL),
            groupingSeparator: $numberFormatter->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL),
        );
    }

    private static function getNumberFormatter(string $locale, int $style, int $decimals = 2): NumberFormatter
    {
        $config = config('filament-money-field');

        $numberFormatter = new NumberFormatter($locale, $style);

        if ($decimals < 0) {
            $numberFormatter->setAttribute(NumberFormatter::MAX_SIGNIFICANT_DIGITS, abs($decimals));
        } else {
            $numberFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
        }

        if ($config['intl_currency_symbol']) {
            $intlCurrencySymbol = $numberFormatter->getSymbol(NumberFormatter::INTL_CURRENCY_SYMBOL);
            if ($numberFormatter->getTextAttribute(NumberFormatter::POSITIVE_PREFIX) !== '') {
                // "\xc2\xa0" is a non-breaking space
                $numberFormatter->setTextAttribute(NumberFormatter::POSITIVE_PREFIX, $intlCurrencySymbol."\xc2\xa0");
            }
            if ($numberFormatter->getTextAttribute(NumberFormatter::POSITIVE_SUFFIX) !== '') {
                // "\xc2\xa0" is a non-breaking space
                $numberFormatter->setTextAttribute(NumberFormatter::POSITIVE_SUFFIX, "\xc2\xa0".$intlCurrencySymbol);
            }
        }

        return $numberFormatter;
    }
}
