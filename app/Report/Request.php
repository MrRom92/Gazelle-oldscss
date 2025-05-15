<?php

namespace Gazelle\Report;

class Request extends AbstractReport {
    public function __construct(
        protected readonly int $reportId,
        protected readonly \Gazelle\Request $subject,
    ) {}

    public function template(): string {
        return 'report/request.twig';
    }

    public function bbLink(): string {
        return "the request [url={$this->subject->url()}]" . display_str($this->subject->title()) . '[/url]';
    }

    public function titlePrefix(): string {
        return 'Request Report: ';
    }

    public function title(): string {
        return $this->subject->title();
    }

    public function needReason(): bool {
        return true;
    }
}
