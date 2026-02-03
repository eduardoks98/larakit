@inject('ga', 'Eduardoks98\AnalyticsGoogle\Services\GoogleAnalyticsService')

@if($ga->isEnabled() && $ga->shouldTrackRoute())
{!! $ga->getScript() !!}
@endif
