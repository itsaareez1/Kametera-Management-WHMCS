<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/horn/css/all.min.css?v={$versionHash}" />
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/horn/css/style.css?v={$versionHash}" />
<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/horn/js/scripts.min.js?v={$versionHash}"></script>

<!-- Multilingual Condition to RTL & LTR Language -->
{if $language eq 'arabic' || $language eq 'farsi' || $language eq 'hebrew'}<html dir="rtl">
<link href="{$WEB_ROOT}/templates/orderforms/{$carttpl}/css/auto-rtl/rtl.css" property="stylesheet" rel="stylesheet"/>
{else}
<link href="{$WEB_ROOT}/templates/orderforms/{$carttpl}/css/style.css" property="stylesheet" rel="stylesheet"/>
<link href="{$WEB_ROOT}/templates/orderforms/{$carttpl}/css/custom.css" property="stylesheet" rel="stylesheet"/>
<html>
{/if}
