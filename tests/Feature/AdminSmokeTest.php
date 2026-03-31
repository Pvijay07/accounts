<?php

it('keeps the admin smoke runner available', function () {
    expect(file_exists(base_path('scripts/admin_smoke.php')))->toBeTrue();
});

it('registers the repaired admin activity log route', function () {
    $route = app('router')->getRoutes()->getByName('admin.activity-logs.index');

    expect($route)->not->toBeNull();
});
