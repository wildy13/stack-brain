<?php
require(LanguagePath . 'notifications.php');
Auth(1);
$DB->CloseConnection();

$PageTitle   = $Lang['Notifications'];
$ContentFile = $TemplatePath . 'notifications_list.php';
include($TemplatePath . 'layout.php');
