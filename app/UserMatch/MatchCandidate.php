<?php

namespace Gazelle\UserMatch;

use Gazelle\Enum\UserMatchQuality;

class MatchCandidate {
    protected const int IP_WEAK_MATCH_DAYS = 10;
    protected const int SIMILARITY_SCORE = 70;

    protected array $keyedIps = [];

    /**
     * @param array<string> $usernames
     * @param array<string> $emails
     */
    public function __construct(
        public readonly array $usernames,
        public readonly array $emails,
        public readonly array $ips  // [ip, ?DateTimeImmutable start, ?DateTimeImmutable end]
    ) {
        foreach ($ips as $ip) {
            if (count($ip) !== 3) {
                throw new \InvalidArgumentException('invalid IP entry');
            }
            $this->keyedIps[$ip[0]][] = $ip;
        }
    }

    public function match(MatchCandidate $other): MatchResult {
        $result = new MatchResult();

        $this->matchNames($other, $result);
        $this->matchEmails($other, $result);
        $this->matchNamesEmails($other, $result);
        $this->matchIps($other, $result);

        return $result;
    }

    public function keyedIps(): array {
        return $this->keyedIps;
    }

    /**
     * match usernames against email names
     *
     * public visibility for testing only
     */
    public function matchNamesEmails(MatchCandidate $other, MatchResult $result): void {
        foreach ($this->usernames as $username) {
            $username = strtolower($username);
            $nameClean = preg_replace('/(^[0-9]+|[0-9]+$)/', '', $username);
            foreach ($other->emails as $otherEmail) {
                $hayLhs = static::cleanupEmail($otherEmail)[0];
                if ($hayLhs === null) {
                    continue;
                }
                $otherClean = preg_replace('/(^[0-9]+|[0-9]+$)/', '', $hayLhs);
                similar_text($nameClean, $otherClean, $percent);  // @phpstan-ignore-line
                if ($hayLhs === $username) {
                    $result->addNameMatch($username, $otherEmail, UserMatchQuality::partial);
                } elseif (strlen($nameClean) > 3 && strlen($otherClean) > 3 && $percent > static::SIMILARITY_SCORE) {  // @phpstan-ignore-line
                    $result->addNameMatch($username, $otherEmail, UserMatchQuality::weak);
                }
            }
        }

        foreach ($other->usernames as $otherName) {
            $otherName = strtolower($otherName);
            $otherClean = preg_replace('/(^[0-9]+|[0-9]+$)/', '', $otherName);
            foreach ($this->emails as $email) {
                $hayLhs = static::cleanupEmail($email)[0];
                if ($hayLhs === null) {
                    continue;
                }
                $nameClean = preg_replace('/(^[0-9]+|[0-9]+$)/', '', $hayLhs);
                similar_text($nameClean, $otherClean, $percent);  // @phpstan-ignore-line
                if ($hayLhs === $otherName) {
                    $result->addNameMatch($email, $otherName, UserMatchQuality::partial);
                } elseif (strlen($nameClean) > 3 && strlen($otherClean) > 3 && $percent > static::SIMILARITY_SCORE) {  // @phpstan-ignore-line
                    $result->addNameMatch($email, $otherName, UserMatchQuality::weak);
                }
            }
        }
    }

    // public visibility for testing only
    public function matchNames(MatchCandidate $other, MatchResult $result): void {
        foreach ($this->usernames as $username) {
            $username = strtolower($username);
            $nameClean = preg_replace('/(^[0-9]+|[0-9]+$)/', '', $username);
            foreach ($other->usernames as $otherName) {
                $otherName = strtolower($otherName);
                similar_text($otherName, $nameClean, $percent);
                if ($otherName === $username) {
                    $result->addNameMatch($username, $otherName, UserMatchQuality::full);
                } elseif (strlen($nameClean) > 3 && $percent > static::SIMILARITY_SCORE) {
                    $result->addNameMatch($username, $otherName, UserMatchQuality::partial);
                }
            }
        }
    }

    // public visibility for testing only
    public function matchEmails(MatchCandidate $other, MatchResult $result): void {
        foreach ($this->emails as $email) {
            [$lhs, $rhs] = static::cleanupEmail($email);
            if ($lhs === null || $rhs === null) {
                continue;
            }
            $lhsStrip = preg_replace('/(^[0-9]+|[0-9]+$|[._-])/', '', $lhs);

            foreach ($other->emails as $otherEmail) {
                [$hayLhs, $hayRhs] = static::cleanupEmail($otherEmail);
                if ($hayLhs === null || $hayRhs === null) {
                    continue;
                }
                if ($lhs === $hayLhs && $rhs === $hayRhs) {
                    $result->addEmailMatch($email, $otherEmail, UserMatchQuality::full);
                } elseif ($lhs === $hayLhs) {
                    // match on email name with different domain
                    $result->addEmailMatch($email, $otherEmail, UserMatchQuality::partial);
                } else {
                    // strip leading+trailing numbers and some specials and try again
                    $hayLhs = preg_replace('/(^[0-9]+|[0-9]+$|[._-])/', '', $hayLhs);
                    similar_text($lhsStrip, $hayLhs, $percent);  // @phpstan-ignore-line
                    if ($lhsStrip && $hayLhs && $percent > static::SIMILARITY_SCORE) {
                        $result->addEmailMatch($email, $otherEmail, UserMatchQuality::weak);
                    }
                }
            }
        }
    }

    // public visibility for testing only
    public static function cleanupEmail(string $email): array {
        // strip user+REMOVED@domain
        [$lhs, $rhs] = explode('@', preg_replace('/\+[^@]*@/', '@', $email), 2);
        if (!$rhs) {
            return [null, null];
        }
        $rhs = static::mapEmailDomain(strtolower($rhs));
        if ($rhs === 'gmail.com') {
            $lhs = str_replace('.', '', $lhs);
        }
        return [strtolower($lhs), $rhs];
    }

    protected static function mapEmailDomain(string $domain): string {
        return match ($domain) {
            'protonmail.com', 'pm.me' => 'proton.me',
            'googlemail.com'          => 'gmail.com',
            default => $domain
        };
    }

    /**
     * first finds all mutual ips, then iterates all potential matches until it finds the closest match
     *
     * public visibility for testing only
     */
    public function matchIps(MatchCandidate $other, MatchResult $result): void {
        $otherKeyed = $other->keyedIps();
        $intersection = array_intersect_key($this->keyedIps, $otherKeyed);
        foreach ($intersection as $ip => $ipEntries) {
            $otherEntries = $otherKeyed[$ip];
            // make this matching more stable by always iterating the smallest set
            $isSwapped = false;
            if (count($ipEntries) > count($otherEntries)) {
                $isSwapped = true;
                [$ipEntries, $otherEntries] = [$otherEntries, $ipEntries];
            }

            foreach ($ipEntries as $ipEntry) {
                $match = null;  // [time, other_time, match_type, days]
                $updateMatch = function ($newMatch, $times, $diffDays) use (&$match) {
                    if (
                        !$match
                        || $newMatch->value < $match[2]->value
                        || ($newMatch === $match[2] && $diffDays < $match[3])
                    ) {
                        $match = [...$times, $newMatch, $diffDays];
                    }
                };

                [$ip, $start, $end] = $ipEntry;
                foreach ($otherEntries as $otherEntry) {
                    [$ip, $otherStart, $otherEnd] = $otherEntry;

                    if (!($start && $otherStart)) {
                        $dates = $isSwapped ? [$otherStart, $start] : [$start, $otherStart];
                        $updateMatch(UserMatchQuality::weak, $dates, INF);
                        continue;
                    }

                    $end = $end ?? $start;
                    $otherEnd = $otherEnd ?? $otherStart;

                    /* possible cases:
                       * any two dates are very close
                       * one range is a true subset of the other
                       * ranges overlap into one direction
                       * no overlaps, not close
                     */
                    $startDiff = $otherStart->diff($start);
                    $endDiff = $otherEnd->diff($end);
                    $startEndDiff = $otherStart->diff($end);
                    $endStartDiff = $otherEnd->diff($start);
                    // days is the only attribute that tracks an absolute number, thanks php
                    $minDiff = min($startDiff->days, $endDiff->days, $startEndDiff->days, $endStartDiff->days);
                    $closestTimes = match ($minDiff) {
                        $startDiff->days => [$start, $otherStart],
                        $endDiff->days => [$end, $otherEnd],
                        $startEndDiff->days => [$end, $otherStart],
                        default => [$start, $otherEnd],
                    };
                    if ($isSwapped) {
                        $closestTimes = [$closestTimes[1], $closestTimes[0]];
                    }
                    if ($minDiff < 1) {
                        $updateMatch(UserMatchQuality::full, $closestTimes, $minDiff);
                        break;
                    } elseif ($startDiff->invert !== $endDiff->invert) {  // subset
                        $updateMatch(UserMatchQuality::partial, $closestTimes, $minDiff);
                    } elseif ($startEndDiff->invert !== $endStartDiff->invert) {  // partial overlap
                        $updateMatch(UserMatchQuality::partial, $closestTimes, $minDiff);
                    } elseif ($minDiff < static::IP_WEAK_MATCH_DAYS) {
                        $updateMatch(UserMatchQuality::partial, $closestTimes, $minDiff);
                    } else {
                        $updateMatch(UserMatchQuality::weak, $closestTimes, $minDiff);
                    }
                }

                if ($match) {
                    $result->addIpMatch($ip, $match[0], $match[1], $match[2]);
                }
            }
        }
    }
}
