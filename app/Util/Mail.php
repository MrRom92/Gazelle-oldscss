<?php

namespace Gazelle\Util;

class Mail {
    protected string $from = 'noreply';

    public function setFrom(string $from): static {
        $this->from = $from;
        return $this;
    }

    /**
     * Send an email.
     * Returns the underlying return code from mail() or fclose().
     */
    public function send(string $to, string $subject, string $body): bool {
        $from = $this->from . '@' . SITE_HOST;
        $msgId = randomString(40);
        $headers = implode("\r\n", [
            "MIME-Version: 1.0",
            "Content-type: text/plain; charset=utf-8",
            //"To: $to",
            "From: " . SITE_NAME . " <$from>",
            "Reply-To: $from",
            "Message-Id: <{$msgId}@" . MAIL_HOST . '>',
            "X-Priority: 3",
            '' // for final "\r\n"
        ]);
        if (DEBUG_EMAIL) {
            $out = fopen(TMPDIR . "/$msgId.mail", "w");
            if ($out === false) {
                return false;
            }
            fwrite($out, $headers . "\n" . $body . "\n");
            return fclose($out);
        }
        return mail($to, $subject, $body, $headers, "-f $from");
    }
}
