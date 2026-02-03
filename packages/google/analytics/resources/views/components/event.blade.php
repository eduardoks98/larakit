@inject('ga', 'Eduardoks98\AnalyticsGoogle\Services\GoogleAnalyticsService')

@props(['name', 'params' => []])

@if($ga->isEnabled())
{!! $ga->event($name, $params) !!}
@endif
