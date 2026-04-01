<?php
$filePath = 'd:/xampp/htdocs/accounts/resources/views/Admin/standard_expenses.blade.php';
$content = file_get_contents($filePath);

// Update line 113 block
$content = preg_replace(
    '/<input type="text" name="mobile_number" id="mobile_number" class="form-control"\s+placeholder="Optional"\s+style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"\s+maxlength="10" minlength="10">/',
    '<input type="text" name="mobile_number" id="mobile_number" class="form-control" placeholder="Optional" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, \'\');">',
    $content
);

file_put_contents($filePath, $content);
echo "Successfully updated standard_expenses.blade.php";
