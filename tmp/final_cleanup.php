<?php
$filePath = 'd:/xampp/htdocs/accounts/resources/views/Admin/invoices.blade.php';
$content = file_get_contents($filePath);

// Rewrite updateCurrencySelection correctly
$content = preg_replace(
    '/window.updateCurrencySelection = function\(\) \{(.*?)window.updateCurrencyConversionFromForeign = function/s',
    'window.updateCurrencySelection = function() {
            const headerSelect = document.getElementById(\'currencySelect\');
            const footerSelect = document.getElementById(\'footerCurrencySelect\');
            
            if (headerSelect && footerSelect) {
                if (window.event && window.event.target) {
                    if (window.event.target.id === \'currencySelect\') {
                        footerSelect.value = headerSelect.value;
                    } else if (window.event.target.id === \'footerCurrencySelect\') {
                        headerSelect.value = footerSelect.value;
                    }
                }
            }

            const currency = headerSelect ? headerSelect.value : \'USD\';
            
            // Show/Hide Currency Details
            const currencySection = document.getElementById(\'currency_section\');
            if (currencySection) {
                if (currency === \'INR\') {
                    currencySection.classList.add(\'d-none\');
                } else {
                    currencySection.classList.remove(\'d-none\');
                }
            }

            const gstSection = document.getElementById(\'gst_section\');
            const tdsSection = document.getElementById(\'tds_section\');
            const applyGST = document.getElementById(\'applyGST\');
            const applyTDS = document.getElementById(\'applyTDS\');

            if (currency === \'USD\') {
                if (gstSection) gstSection.classList.add(\'d-none\');
                if (tdsSection) tdsSection.classList.add(\'d-none\');
                if (applyGST) applyGST.checked = false;
                if (applyTDS) applyTDS.checked = false;
            } else {
                if (gstSection) gstSection.classList.remove(\'d-none\');
                if (tdsSection) tdsSection.classList.remove(\'d-none\');
                if (applyGST) applyGST.checked = true;
            }

            updateCurrencySymbols(currency);
            updateConversionRateForCurrency(currency);
            updateCurrencyConversionFromForeign();
            if (typeof calculateTax === \'function\') {
                calculateTax();
            }
        }

        window.updateCurrencyConversionFromForeign = function',
    $content
);

file_put_contents($filePath, $content);
echo "Final syntax cleanup successful.";
