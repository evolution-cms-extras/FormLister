<?php
/**
* userHelper
*
* addition to FormLister
*
* @category    plugin
* @version     1.12.1
* @internal    @properties &logoutKey=Request key;text;logout &cookieName=Cookie Name;text;WebLoginPE &cookieLifetime=Cookie Lifetime, seconds;text;157680000 &maxFails=Max failed logins;text;3 &blockTime=Block for, seconds;text;3600 &trackWebUserActivity=Track web user activity;list;No,Yes;No
* @internal    @events OnWebAuthentication,OnWebPageInit,OnPageNotFound,OnWebLogin
* @internal    @modx_category Content
* @internal    @disabled 1
**/

$logoutKey = 'logout';
$cookieName= 'WebLoginPE';
$cookieLifetime= 157680000;
$maxFails = 3;
$blockTime = 3600;
$trackWebUserActivity = 'No';

Event::listen('evolution.OnWebAuthentication', function ($params) {
    require MODX_BASE_PATH.'assets/snippets/FormLister/plugin.userHelper.php';
});
Event::listen('evolution.OnWebPageInit', function ($params) {
    require MODX_BASE_PATH.'assets/snippets/FormLister/plugin.userHelper.php';
});
Event::listen('evolution.OnPageNotFound', function ($params) {
    require MODX_BASE_PATH.'assets/snippets/FormLister/plugin.userHelper.php';
});
Event::listen('evolution.OnWebLogin', function ($params) {
    require MODX_BASE_PATH.'assets/snippets/FormLister/plugin.userHelper.php';
});