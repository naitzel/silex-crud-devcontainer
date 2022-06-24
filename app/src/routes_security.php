<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */

// Routes Security
$security = $app['controllers_factory'];

$security->get('/', 'Security\Security::index')->bind('s_dashboard'); // Dashboard
$security->get('/login', 'Security\Security::login')->bind('s_login'); // Login

// Roles
$security->match('roles', 'Security\Role::index')->method('GET|POST')->bind('s_role');
$security->match('roles/create', 'Security\Role::create')->method('GET|POST')->bind('s_role_create');
$security->match('roles/edit/{id}', 'Security\Role::edit')->method('GET|POST')->bind('s_role_edit');
$security->delete('roles/delete/{id}', 'Security\Role::delete')->bind('s_role_delete');

// Users
$security->match('user', 'Security\User::index')->method('GET|POST')->bind('s_user');
$security->match('user/create', 'Security\User::create')->method('GET|POST')->bind('s_user_create');
$security->match('user/edit/{id}', 'Security\User::edit')->method('GET|POST')->bind('s_user_edit');
$security->delete('user/delete/{id}', 'Security\User::delete')->bind('s_user_delete');

// Access
$security->match('access', 'Security\Access::index')->method('GET|POST')->bind('s_access');
$security->put('access/order', 'Security\Access::order')->bind('s_access_order');
$security->match('access/create', 'Security\Access::create')->method('GET|POST')->bind('s_access_create');
$security->match('access/edit/{id}', 'Security\Access::edit')->method('GET|POST')->bind('s_access_edit');
$security->delete('access/delete/{id}', 'Security\Access::delete')->bind('s_access_delete');

// Banner Type
$security->match('banner', 'Security\BannerType::index')->method('GET|POST')->bind('s_banner_type');
$security->match('banner/create', 'Security\BannerType::create')->method('GET|POST')->bind('s_banner_type_create');
$security->match('banner/edit/{id}', 'Security\BannerType::edit')->method('GET|POST')->bind('s_banner_type_edit');
$security->delete('banner/delete/{id}', 'Security\BannerType::delete')->bind('s_banner_type_delete');

$app['converter.banner_type'] = $app->share(function (Silex\Application $app) {
    return new Naitzel\SilexCrud\Service\BannerTypeConverter($app);
});

// Banner
$security->match('banner/{banner_type}/list', 'Security\Banner::index')->method('GET|POST')->convert('banner_type', 'converter.banner_type:convert')->bind('s_banner');
$security->put('banner/{banner_type}/order', 'Security\Banner::order')->convert('banner_type', 'converter.banner_type:convert')->bind('s_banner_order');
$security->match('banner/{banner_type}/list/create', 'Security\Banner::create')->method('GET|POST')->convert('banner_type', 'converter.banner_type:convert')->bind('s_banner_create');
$security->match('banner/{banner_type}/list/edit/{id}', 'Security\Banner::edit')->method('GET|POST')->convert('banner_type', 'converter.banner_type:convert')->bind('s_banner_edit');
$security->delete('banner/{banner_type}/list/delete/{id}', 'Security\Banner::delete')->convert('banner_type', 'converter.banner_type:convert')->bind('s_banner_delete');

// Institutional Type
$security->match('institutional', 'Security\InstitutionalType::index')->method('GET|POST')->bind('s_institutional_type');
$security->match('institutional/create', 'Security\InstitutionalType::create')->method('GET|POST')->bind('s_institutional_type_create');
$security->match('institutional/edit/{id}', 'Security\InstitutionalType::edit')->method('GET|POST')->bind('s_institutional_type_edit');
$security->delete('institutional/delete/{id}', 'Security\InstitutionalType::delete')->bind('s_institutional_type_delete');

$app['converter.institutional_type'] = $app->share(function (Silex\Application $app) {
    return new Naitzel\SilexCrud\Service\InstitutionalTypeConverter($app);
});

// Institutional
$security->match('institutional/{institutional_type}', 'Security\Institutional::index')->method('GET|POST')->convert('institutional_type', 'converter.institutional_type:convert')->bind('s_institutional');
$security->match('institutional/{institutional_type}/create', 'Security\Institutional::create')->method('GET|POST')->convert('institutional_type', 'converter.institutional_type:convert')->bind('s_institutional_create');
$security->match('institutional/{institutional_type}/edit/{id}', 'Security\Institutional::edit')->method('GET|POST')->convert('institutional_type', 'converter.institutional_type:convert')->bind('s_institutional_edit');
$security->delete('institutional/{institutional_type}/delete/{id}', 'Security\Institutional::delete')->convert('institutional_type', 'converter.institutional_type:convert')->bind('s_institutional_delete');

// SEO
$security->match('seo', 'Security\Seo::index')->method('GET|POST')->bind('s_seo');
$security->put('seo/order', 'Security\Seo::order')->bind('s_seo_order');
$security->match('seo/create', 'Security\Seo::create')->method('GET|POST')->bind('s_seo_create');
$security->match('seo/edit/{id}', 'Security\Seo::edit')->method('GET|POST')->bind('s_seo_edit');
$security->delete('seo/delete/{id}', 'Security\Seo::delete')->bind('s_seo_delete');

return $security;